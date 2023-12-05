<?php

namespace addons\wechat\controller;

use addons\wechat\library\Config;
use addons\wechat\model\WechatAutoreply;
use addons\wechat\model\WechatCaptcha;
use addons\wechat\model\WechatContext;
use addons\wechat\model\WechatResponse;
use addons\wechat\model\WechatConfig;

use EasyWeChat\Factory;
use addons\wechat\library\Wechat as WechatService;
use addons\wechat\library\Config as ConfigService;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use think\Log;

/**
 * 微信接口
 */
class Index extends \think\addons\Controller
{

	public $app = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->app = Factory::officialAccount(Config::load());
	}

	/**
	 *
	 */
	public function index()
	{
		$this->error("当前插件暂无前台页面");
	}

	/**
	 * 微信API对接接口
	 */
	public function api()
	{
		$this->app->server->push(function ($message) {
			$wechatService = new WechatService;

			$matches = null;
			$openid = $message['FromUserName']; // 发送方
			$to_openid = $message['ToUserName'];// 接收方（该公众号id）

			$unknownMessage = WechatConfig::getValue('default.unknown.message');
			$unknownMessage = $unknownMessage ? $unknownMessage : "";

			switch ($message['MsgType']) {
				case 'event': //事件消息
					$event = $message['Event'];
					$eventkey = $message['EventKey'] ? $message['EventKey'] : $message['Event'];
					//验证码消息
					if (in_array($event, ['subscribe', 'SCAN']) && preg_match("/^captcha_([a-zA-Z0-9]+)_([0-9\.]+)/", $eventkey, $matches)) {
						return WechatCaptcha::send($openid, $matches[1], $matches[2]);
					}
					switch ($event) {
						case 'subscribe'://添加关注
							$subscribeMessage = WechatConfig::getValue('default.subscribe.message');
							return $subscribeMessage ?: "欢迎关注云平台\n菜单使用\n1、回复以下关键字可重新获取菜单：[1，help，菜单，h]\n2、需要获取相关课程可回复课程名称（例）：课程：PHP\n3、需要获取自己购买的订单请回复：我的订单";
						case 'unsubscribe'://取消关注
							return '';
						case 'LOCATION'://获取地理位置
							return '';
						case 'VIEW': //跳转链接,eventkey为链接
							return '';
						case 'SCAN': //扫码
							return '';

						case 'CLICK':
							if ($eventkey == 'order') {
								return $this->getOrder($openid);
							}
							break;

						default:
							break;
					}

					$wechatResponse = WechatResponse::where(["eventkey" => $eventkey, 'status' => 'normal'])->find();
					if ($wechatResponse) {
						$responseContent = (array)json_decode($wechatResponse['content'], true);
						$wechatContext = WechatContext::where(['openid' => $openid])->order('id', 'desc')->find();
						$data = ['eventkey' => $eventkey, 'command' => '', 'refreshtime' => time(), 'openid' => $openid];
						if ($wechatContext) {
							$wechatContext->save($data);
						} else {
							$wechatContext = WechatContext::create($data, true);
						}
						$result = $wechatService->response($this, $openid, '', $responseContent, $wechatContext);
						if ($result) {
							return $result;
						}
					}
					return $unknownMessage;
				case 'text': //文字消息
				case 'image': //图片消息
				case 'voice': //语音消息
				case 'video': //视频消息
				case 'location': //坐标消息
				case 'link': //链接消息
				default: //其它消息
					//自动回复处理
					if ($message['MsgType'] == 'text') {
						$autoreply = null;
						$autoreplyList = WechatAutoreply::where('status', 'normal')->cache(true)->order('weigh DESC,id DESC')->select();
						foreach ($autoreplyList as $index => $item) {
							//完全匹配和正则匹配
							if ($item['text'] == $message['Content'] || (in_array(mb_substr($item['text'], 0, 1), ['#', '~', '/']) && preg_match($item['text'], $message['Content'], $matches))) {
								$autoreply = $item;
								break;
							}
						}

						if ($autoreply) {
							$wechatResponse = WechatResponse::where(["eventkey" => $autoreply['eventkey'], 'status' => 'normal'])->find();
							if ($wechatResponse) {
								$responseContent = (array)json_decode($wechatResponse['content'], true);
								$wechatContext = WechatContext::where(['openid' => $openid])->order('id', 'desc')->find();
								$result = $wechatService->response($this, $openid, $message['Content'], $responseContent, $wechatContext, $matches);
								if ($result) {
									return $result;
								}
							}
						}


						// 菜单回复
						if (in_array($message['Content'], [1, 'help', 'h', '菜单'])) {
							return new Text("菜单使用\n1、回复以下关键字可重新获取菜单：[1，help，菜单，h]\n2、需要获取相关课程可回复课程名称（例）：课程：PHP\n3、需要获取自己购买的订单请回复：我的订单");
						}

						if (strpos($message['Content'], '课程') !== false) {
							$str = preg_replace('/:|：/', ':', $message['Content']);

							// 用正则获取课程名称
							preg_match('/课程:(.*)/', $str, $res);

							$subjectName = $res[1] ?? '';

							if (!$subjectName) {
								return new Text('回复格式不正确');
							}

							return new Text($subjectName);
						}

						// 关键字：我的订单
						if (trim($message['Content']) === '我的订单') {
							return $this->getOrder($openid);
						}

					}
					return $unknownMessage;
			}
			return ""; //SUCCESS
		});

		$response = $this->app->server->serve();
		// 将响应输出
		$response->send();
		return;
	}

	public function menu()
	{
		// 菜单数组
		$buttons = [
			[
				"name" => '云课堂',
				'sub_button' => [
					[
						// 网页
						"type" => "view",
						// 菜单名称
						"name" => "官网",
						// 网页地址
						"url" => "https://api.dracowyn.com"
					],
					[
						"type" => "click",
						"name" => "全部课程",
						"key" => "subject"
					]
				]
			],
			[
				"name" => "商城",
				"sub_button" => [
					[
						"type" => "view",
						"name" => "搜索",
						"url" => "https://www.soso.com/"
					],
					[
						"type" => "view",
						"name" => "视频",
						"url" => "https://v.qq.com/"
					],
					[
						"type" => "click",
						"name" => "赞一下我们",
						"key" => "V1001_GOOD"
					],
				],
			],
			[
				"name" => "我的",
				"sub_button" => [
					[
						"type" => 'click',
						"name" => '我的订单',
						"key" => 'order'
					]
				]
			]
		];

		// 调用menu对象创建一个菜单
		$result = $this->app->menu->create($buttons);

		if ($result) {
			echo '创建菜单成功';
		} else {
			echo '创建菜单失败';
		}
	}

	// 获取我的订单
	public function getOrder($openid)
	{
		$openid = empty($openid) ? session('openid') : $openid;

		if (!$openid) {
			$result = $this->app->oauth->scopes(['snsapi_userinfo'])->redirect();

			return $result->send();
		}

		$business = model('common/business/Business')->where(['openid' => $openid])->find();

		if (!$business) {
			// 返回一个文本地址给用户进行绑定
			$url = url('/home/index/bind', null, true, true);
			$content = "<a href='{$url}'>您未授权，无法查询！请先绑定</a>";
			return new Text($content);
		}

		// 根据用户id获取所有的该用户订单
		$orderIds = model('common/subject/Order')->where(['busid' => $business['id']])->column('id');

		$orderData = model('common/subject/Order')->with(['subject'])->where(['order.id' => ['IN', $orderIds]])->select();

		if (empty($orderData)) {
			return new Text('暂无订单记录');
		}

		$data = [];

		foreach ($orderData as $item) {
			$data[] = new NewsItem([
				'title' => "您购买课程{$item['subject']['title']}",
				'description' => "订单号：{$item['code']}，共消费了￥ {$item['total']}元",
				'url' => url('/home/subject/subject/info', ['subid' => $item['subid']], true, true),
				'image' => $item['subject']['thumbs_cdn']
			]);
		}

		return new News($data);
	}

	/**
	 * 登录回调
	 */
	public function callback()
	{
		$user = $this->app->oauth->user();

		$openid = $user->getId();

		if (empty($openid)) {
			$this->error('获取不到授权', url('/home/index/login'));
		}

		$business = model('common/business/Business')->where(['openid' => $openid])->find();

		if (!$business) {
			$this->error('您未绑定账号，请先绑定账号', url('/home/index/bind', ['openid' => $openid]));
		}

		session('openid', $openid);

		cookie('business', ['id' => $business['id'], 'mobile' => $business['mobile'], 'openid' => $business['openid']]);

		$this->success('授权成功', url('/home/subject/order/index'));
	}

	/**
	 * 支付回调
	 */
	public function notify()
	{
		Log::record(file_get_contents('php://input'), "notify");
		$response = $this->app->handlePaidNotify(function ($message, $fail) {
			// 你的逻辑
			return true;
			// 或者错误消息
			$fail('Order not exists.');
		});

		$response->send();
		return;
	}

}
