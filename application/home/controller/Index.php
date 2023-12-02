<?php

namespace app\home\controller;

use app\common\model\business\Source;
use think\Controller;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;

class Index extends Controller
{
	protected $BusinessModel = null;

	protected $SubjectModel = null;

	public function _initialize()
	{
		$this->BusinessModel = model('business.Business');
		$this->SubjectModel = model('subject.Subject');
	}

	public function index(): string
	{
		$hotList = $this->SubjectModel->order('create_time DESC')->limit(6)->select();

		$SubjectList = $this->SubjectModel->orderRaw('LPAD(LOWER(likes),10,0) DESC')->limit(8)->select();

		$this->assign([
			'hotList' => $hotList,
			'SubjectList' => $SubjectList,
		]);
		return $this->view->fetch();
	}

	public function login()
	{
		if ($this->request->isPost()) {
			$mobile = $this->request->param('mobile', '', 'trim');
			$password = $this->request->param('password', '', 'trim');

			if (empty($mobile) || empty($password)) {
				$this->error('手机号或密码不能为空');
			}

			$business = $this->BusinessModel->where('mobile', $mobile)->find();

			if (!$business) {
				$this->error('手机号未注册');
			}

			$password = md5(md5($password) . $business['salt']);

			if ($password != $business['password']) {
				$this->error('密码错误');
			}

			$data = [
				'id' => $business['id'],
				'nickname' => $business['nickname'],
				'mobile' => $business['mobile'],
				'avatar' => $business['avatar'],
				'auth' => $business['auth'],
			];

			cookie('business', $data, 3600 * 24 * 7);

			$this->success('登录成功', url('home/business/index'));
		}

		return $this->fetch();
	}

	public function logout()
	{
		if ($this->request->isAjax()) {
			cookie('business', null);
			$this->success('退出成功', url('home/index/index'));
		}
	}

	public function register()
	{
		if ($this->request->isPost()) {
			$mobile = $this->request->param('mobile', '', 'trim');
			$password = $this->request->param('password', '', 'trim');
			$rePassword = $this->request->param('rePassword', '', 'trim');

			if (empty($mobile) || empty($password) || empty($rePassword)) {
				$this->error('手机号或密码不能为空');
			}

			if ($password != $rePassword) {
				$this->error('两次密码不一致');
			}

			$business = $this->BusinessModel->where('mobile', $mobile)->find();

			if ($business) {
				$this->error('手机号已注册');
			}

			$salt = build_randstr(6);
			$password = md5(md5($password) . $salt);

			$data = [
				'mobile' => $mobile,
				'password' => $password,
				'salt' => $salt,
				'auth' => 0,
				'money' => 0,
				'deal' => 0,
			];

			$source = null;
			try {
				$source = (new Source)->where(['name' => ['like', '%云课堂%']])->find();
			} catch (DataNotFoundException|ModelNotFoundException|DbException $e) {
			}

			if ($source) {
				$data['sourceid'] = $source['id'];
			}

			$result = $this->BusinessModel->validate('common/Business.register')->save($data);

			if ($result === false) {
				$this->error($this->BusinessModel->getError());
			} else {
				$this->success('注册成功', url('home/index/login'));
			}
		}

		return $this->fetch();
	}
}
