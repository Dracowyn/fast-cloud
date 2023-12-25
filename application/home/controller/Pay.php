<?php
/**
 * 支付控制器
 * @author Dracowyn
 * @since 2023-12-25 16:14
 */

namespace app\home\controller;

use app\common\controller\Home;
use app\common\model\business\Record;
use think\Env;

class Pay extends Home
{
	protected $noNeedLogin = ['callback'];

	protected $businessModel = null;

	protected $payModel = null;

	protected $recordModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->businessModel = new \app\common\model\business\Business;
		$this->payModel = new \app\common\model\pay\Pay;
		$this->recordModel = new Record;
	}

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
		if ($this->request->isPost()) {
			$params = $this->request->param();

			// 获取充值金额
			$price = isset($params['originalprice']) ? floatval($params['originalprice']) : 0;

			// 获取第三方参数
			$third = isset($params['third']) ? json_decode($params['third'], true) : [];

			$thirdArr = json_decode($third, true);

			// 获取用户id
			$busId = isset($thirdArr['busid']) ? intval($thirdArr['busid']) : 0;

			// 支付方式
			$payType = isset($params['paytype']) ? intval($params['paytype']) : 0;

			// 获取支付订单号
			$payId = isset($params['id']) ? trim($params['id']) : '';

			// 获取支付状态
			$pay = $this->payModel->find($payId);

			if (!$pay) {
				return json(['code' => 0, 'msg' => '支付订单不存在', 'data' => null]);
			}

			$payment = '';

			switch ($payType) {
				case 0:
					$payment = '微信支付';
					break;
				case 1:
					$payment = '支付宝支付';
					break;
			}
			if ($price <= 0) {
				return json(['code' => 0, 'msg' => '充值金额不能小于0.01元', 'data' => null]);
			}

			// 查询用户是否存在
			$business = $this->businessModel->find($busId);
			if (!$business) {
				return json(['code' => 0, 'msg' => '用户不存在', 'data' => null]);
			}

			// 开启事务
			$this->businessModel->startTrans();
			$this->recordModel->startTrans();

			// 组装数据
			$businessData = [
				'id' => $business['id'],
				'money' => bcadd($business['money'], $price, 2)
			];

			$validate = [
				[
					'id' => 'require',
					'money' => ['number', '>=:0'],
				],
				[
					'id.require' => '用户不存在',
					'money.number' => '余额必须是数字类型',
					'money.>=' => '余额必须大于等于0元'
				]
			];

			$businessStatus = $this->businessModel->validate(...$validate)->isUpdate()->save($businessData);

			if ($businessStatus === false) {
				$this->businessModel->rollback();
				return json(['code' => 0, 'msg' => $this->businessModel->getError(), 'data' => null]);
			}

			$payPrice = $params['price'] ?? 0;

			$recordData = [
				'total' => $price,
				'content' => "通过{$payment}充值了 $price 元,实付{$payPrice} 元",
				'busid' => $business['id'],
			];

			$recordStatus = $this->recordModel->validate('common/business/Record')->save($recordData);

			if ($recordStatus === false) {
				$this->recordModel->rollback();
				return json(['code' => 0, 'msg' => $this->recordModel->getError(), 'data' => null]);
			}

			if ($businessStatus && $recordStatus) {
				$this->businessModel->commit();
				$this->recordModel->commit();
				return json(['code' => 1, 'msg' => '充值成功', 'data' => null]);
			} else {
				$this->businessModel->rollback();
				$this->recordModel->rollback();
				return json(['code' => 0, 'msg' => '充值失败', 'data' => null]);
			}
		}
		return null;
	}


	// 订单支付完成后跳转页面
	public function notice()
	{
		return $this->success('支付成功', url('/home/business/index'));
	}
}