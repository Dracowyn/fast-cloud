<?php
/**
 * 支付控制器
 * @author Dracowyn
 * @since 2023-12-25 16:14
 */

namespace app\home\controller;

use app\common\controller\Home;
use think\Env;

class Pay extends Home
{
	// 不需要登录的数组
	protected $noNeedLogin = ['callback'];

	// 充值
	public function pay()
	{
		if ($this->request->isPost()) {
			// 充值金额
			$money = $this->request->param('money', 0, 'trim');
			// 支付类型
			$payType = $this->request->param('paytype', 0, 'trim');

			// 金额的值转成浮点类型
			$money = floatval($money);

			// 充值金额必须大于0
			if ($money < 0) {
				$this->error('充值金额不能小于0.01元');
			}

			// 携带第三方参数 主要把用户id存到数据表
			$third = json_encode(['busid' => $this->auth->id]);

			// 获取当前站点的域名
			$url = model('Config')->where(['name' => 'url'])->value('value');

			// 获取微信收款码
			$wxcode = model('Config')->where(['name' => 'wxcode'])->value('value');

			// 获取支付宝收款码
			$zfbcode = model('Config')->where(['name' => 'zfbcode'])->value('value');

			$url = Env::get('site.url', $url);

			// 拼接下单接口
			$CreateUrl = $url . '/pay/index/create';

			// 组装下单数据
			$data = [
				'name' => '余额充值',
				'third' => $third,
				'paytype' => $payType,
				// 充值原金额
				'originalprice' => $money,
				'paypage' => 1,// 0 json 1 收银页面
				'reurl' => $url . '/home/pay/notice',
				'callbackurl' => $url . '/home/pay/callback',
				'wxcode' => $url . $wxcode,
				'zfbcode' => $url . $zfbcode
			];

			$result = httpRequest($CreateUrl, $data);

			echo $result;
			exit;

		}

		return $this->fetch();
	}

	public function callback()
	{

	}


	// 订单支付完成后跳转页面
	public function notice()
	{
		return $this->success('支付成功', url('/home/business/index'));
	}
}