<?php
/**
 * 支付控制器
 * @author Dracowyn
 * @since 2023-12-25 15:44
 */

namespace app\pay\controller;

use app\common\model\admin\Admin;
use app\common\model\pay\Pay;
use think\Controller;
use think\response\Json;


class Index extends Controller
{
	protected $adminModel = null;

	protected $payModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->adminModel = new Admin;
		$this->payModel = new Pay;
	}

	// 登录
	public function login(): ?Json
	{
		if ($this->request->isPost()) {
			$username = $this->request->param('username', '', 'trim');
			$password = $this->request->param('password', '', 'trim');

			$admin = $this->adminModel->where(['username' => $username])->find();

			if (!$admin) {
				return json(['code' => 0, 'msg' => '账号不存在', 'data' => null]);
			}

			// 匹配密码
			$password = md5(md5($password) . $admin['salt']);

			if ($password !== $admin['password']) {
				return json(['code' => 0, 'msg' => '密码错误', 'data' => null]);
			}

			if ($admin['status'] !== 'normal') {
				return json(['code' => 0, 'msg' => '账号已被禁用', 'data' => null]);
			}

			// 封装返回数据
			$data = [
				'id' => $admin['id'],
				'username' => $admin['username'],
				'nickname' => $admin['nickname'],
				'avatar_cdn' => $admin['avatar_cdn']
			];

			return json(['code' => 1, 'msg' => '登录成功', 'data' => $data]);
		}
		return null;
	}

	// 当客户端监听到支付成功后，会调用此接口，将支付信息写入数据库
	public function check(): ?Json
	{
		if ($this->request->isPost()) {
			$price = $this->request->param('price', 0, 'trim');
			$adminId = $this->request->param('adminid', 0, 'trim');

			$payTime = $this->request->param('paytime', '', 'trim');

			$admin = $this->adminModel->find($adminId);

			if (!$admin) {
				return json(['code' => 0, 'msg' => '账号不存在', 'data' => null]);
			}
			if ($admin['status'] !== 'normal') {
				return json(['code' => 0, 'msg' => '账号已被禁用', 'data' => null]);
			}

			$pay = $this->payModel->where(['price' => $price, 'status' => 0])->find();

			if (!$pay) {
				return json(['code' => 0, 'msg' => '查询不到该订单', 'data' => null]);
			}

			$payTime = strtotime($payTime);

			$data = [
				'id' => $pay['id'],
				'status' => 1,
				'paytime' => $payTime
			];

			$result = $this->payModel->isUpdate(true)->save($data);

			if ($result === false) {
				return json(['code' => 0, 'msg' => '更新订单状态失败', 'data' => null]);
			}

			// 获取更新后的订单数据
			$UpdatePayData = $this->payModel->find($pay['id']);

			return json(['code' => 1, 'msg' => '查询成功', 'data' => $UpdatePayData]);
		}
		return null;
	}

	// 创建支付订单
	public function create()
	{
		if ($this->request->isPost()) {
			$params = $this->request->param();

			// 订单原价
			$money = $params['originalprice'] ?? 0;

			// 查询支付表最后一次未支付记录
			$pay = $this->PayModel->where(['status' => 0])->order('id DESC')->find();

			// 获取最后一次支付的递减值
			$subPrice = !empty($pay) ? bcadd(0.01, bcsub($pay['originalprice'], $pay['price'], 2), 2) : 0.01;

			// 封装数据
			$data = [
				'code' => build_order('Pay_'),
				'name' => $params['name'] ?? '',
				'third' => $params['third'] ?? '',
				'paytype' => $params['paytype'] ?? 0,
				'originalprice' => $money,
				'price' => bcsub($money, $subPrice, 2),
				'paypage' => $params['paypage'] ?? 0,
				'reurl' => $params['reurl'] ?? '',
				'callbackurl' => $params['callbackurl'] ?? '',
				'wxcode' => $params['wxcode'] ?? '',
				'zfbcode' => $params['zfbcode'] ?? '',
				'status' => 0
			];

			$result = $this->payModel->validate('common/pay/Pay')->save($data);

			if ($result === false) {
				return json(['code' => 0, 'msg' => $this->payModel->getError(), 'data' => null]);
			} else {

				$pay = $this->payModel->find($this->payModel->id);

				if (isset($data['paypage']) && $data['paypage'] == 0) {
					return json(['code' => 1, 'msg' => '支付订单创建成功', 'data' => $pay]);
				} else {

					return $this->fetch('page', ['pay' => $pay]);
				}
			}
		}
	}

	// 创建充值订单成功后轮遍查询
	public function status()
	{
		if ($this->request->isPost()) {
			$payId = $this->request->param('payid', 0, 'trim');

			$pay = $this->payModel->find($payId);

			if (!$pay) {
				return json(['code' => 0, 'msg' => '支付订单不存在', 'data' => $pay]);
			}

			switch ($pay['status']) {
				case 0:
					return json(['code' => 0, 'msg' => '订单未支付', 'data' => $pay]);
				case 1:
					return json(['code' => 1, 'msg' => '订单已支付', 'data' => $pay]);
				case 2:
					return json(['code' => 0, 'msg' => '订单已关闭', 'data' => $pay]);
			}
		}
	}
}