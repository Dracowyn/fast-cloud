<?php

namespace app\home\controller;

use app\common\controller\Home;
use app\common\library\Email;
use think\exception\PDOException;

class Business extends Home
{
	protected $BusinessModel = null;

	// 订单记录模型
	protected $OrderModel = null;

	// 消费记录模型
	protected $RecordModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->BusinessModel = model('business.Business');
		$this->OrderModel = model('business.Order');
		$this->RecordModel = model('business.Record');
	}

	public function index()
	{
		return $this->fetch();
	}

	public function profile()
	{

		if ($this->request->isPost()) {
			// 接收参数
			$params = $this->request->param();

			$data = [
				'id' => $this->auth->id,
				'nickname' => trim($params['nickname']),
				'email' => trim($params['email']),
				'gender' => $params['gender']
			];

			// 如果修改过邮箱需要重新认证
			if ($this->auth['email'] != $params['email']) {
				$data['auth'] = 0;
			}

			// 密码
			if (!empty($params['password'])) {
				$repass = md5(md5($params['password']) . $this->auth['salt']);
				if ($repass == $this->auth->password) {
					$this->error('新密码不能和旧密码一样');
				}

				$salt = build_randstr(6);
				$data['salt'] = $salt;
				$data['password'] = md5(md5($params['password']) . $salt);
			}

			// 地区
			$parentPath = model('Region')->where(['code' => $params['code']])->value('parentpath');

			if (!$parentPath) {
				$this->error('地区选择有误');
			}

			$pathArr = explode(',', $parentPath);

			$data['province'] = $pathArr[0] ?? null;
			$data['city'] = $pathArr[1] ?? null;
			$data['district'] = $pathArr[2] ?? null;

			// 头像
			if (isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0) {
				$result = build_upload('avatar');
				if ($result['code'] === 0) {
					$this->error($result['msg']);
				}
				$data['avatar'] = $result['data'];
			}

			$result = $this->BusinessModel->validate('business/Business.profile')->isUpdate()->save($data);

			if ($result === false) {
				if (isset($data['avatar']) && $_FILES['avatar']['size'] > 0) {
					@is_file('.' . $data['avatar']) && @unlink('.' . $data['avatar']);
				}

				$this->error($this->BusinessModel->getError());
			} else {
				if (isset($data['avatar']) && $_FILES['avatar']['size'] > 0) {
					@is_file('.' . $this->auth->avatar) && @unlink('.' . $this->auth->avatar);
				}

				$business = $this->BusinessModel->find($this->auth->id);

				$data = [
					'id' => $business['id'],
					'nickname' => $business['nickname'],
					'mobile' => $business['mobile'],
					'avatar' => $business['avatar'],
					'auth' => $business['auth'],
				];

				cookie('business', $data);

				$this->success('修改成功', url('home/business/index'));
			}
		}

		return $this->fetch();
	}

	public function email()
	{
		if ($this->request->isPost()) {
			$code = $this->request->param('code', '', 'trim');

			if (empty($code)) {
				$this->error('验证码不能为空');
			}

			$email = empty($this->auth->email) ? '' : $this->auth->email;

			if (empty($email)) {
				$this->error('邮箱不能为空');
			}

			$emailModel = model('Ems');

			$emailInfo = $emailModel->where(['code' => $code, 'email' => $email])->find();

			if (!$emailInfo) {
				$this->error('验证码错误，请重新输入');
			}

			// 开启事务
			$this->BusinessModel->startTrans();
			$emailModel->startTrans();

			$BusinessData = [
				'id' => $this->auth->id,
				'auth' => 1
			];

			$BusinessStatus = $this->BusinessModel->isUpdate()->save($BusinessData);

			if ($BusinessStatus === false) {
				$this->error('认证失败');
			}

			$emailStatus = $emailModel->destroy($emailInfo['id']);

			if ($BusinessStatus && $emailStatus) {
				$this->BusinessModel->commit();
				$emailModel->commit();
				$this->success('认证成功', url('home/business/index'));
			} else {
				$this->BusinessModel->rollback();
				$emailModel->rollback();
				$this->error('认证失败');
			}
		}

		return $this->fetch();
	}

	// 发送验证码
	public function send()
	{
		if ($this->request->isAjax()) {
			$email = empty($this->auth->email) ? '' : $this->auth->email;
			if (!$email) {
				$this->error('邮箱不能为空');
			}

			// 组装条件数组
			$condition = [
				'email' => $email,
				'event' => 'email',
				'times' => 0
			];

			$emailModel = model('Ems');

// 查询是否已经发送过验证码
			$emailInfo = $emailModel->where($condition)->find();

			if ($emailInfo) {
				$this->error('验证码已发送，请查看邮箱');
			}

			// 开启事务
			$emailModel->startTrans();
			$code = build_randstr(4);
			$data = [
				'email' => $email,
				'code' => $code,
				'event' => 'email',
				'times' => time()
			];

			$emailStatus = $emailModel->validate('common/Ems')->save($data);

			if ($emailStatus === false) {
				$this->error($emailModel->getError());
			}

			// 实例化
			$mail = new Email();

			// 正文内容
			$content = '您的验证码是：' . $code . '，请不要把验证码泄露给其他人。如非本人操作，可不用理会！';

			// 发件人
			$emailFrom = config('site.mail_from');

			$result = $mail->from($emailFrom)->subject('云课堂认证')->message($content)->to($email)->send();


			if ($result === true) {
				$emailModel->commit();
				$this->success('发送成功');
			} else {
				$emailModel->rollback();
				$this->error('发送失败');
			}
		}
	}

	// 我的订单
	public function order()
	{
		if ($this->request->isAjax()) {
			// 接收参数
			$page = $this->request->param('page', 1, 'trim');
			$limit = $this->request->param('limit', 10, 'trim');
			$count = $this->OrderModel->where(['busid' => $this->auth->id])->count();

			$orderList = $this->OrderModel->with(['subject'])->where(['busid' => $this->auth->id])->page($page, $limit)->select();

			$data = [
				'count' => $count,
				'list' => $orderList
			];

			if ($orderList) {
				$this->success('Success', null, $data);
			} else {
				$this->error('暂无数据');
			}
		}
		return $this->fetch();
	}

	// 我的消费记录
	public function record()
	{
		if ($this->request->isAjax()) {
			// 接收参数
			$page = $this->request->param('page', 1, 'trim');
			$limit = $this->request->param('limit', 10, 'trim');
			$count = $this->RecordModel->where(['busid' => $this->auth->id])->count();

			$recordList = $this->RecordModel->where(['busid' => $this->auth->id])->page($page, $limit)->select();

			$data = [
				'count' => $count,
				'list' => $recordList
			];

			if ($recordList) {
				$this->success('Success', null, $data);
			} else {
				$this->error('暂无数据');
			}
		}
		return $this->fetch();
	}
}
