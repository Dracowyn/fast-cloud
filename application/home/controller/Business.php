<?php

namespace app\home\controller;

use app\common\controller\Home;

class Business extends Home
{
	protected $BusinessModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->BusinessModel = model('business.Business');
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
}
