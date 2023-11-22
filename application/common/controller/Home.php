<?php

namespace app\common\controller;

use think\Controller;

class Home extends Controller
{
	// 不需要登录的数组
	protected $noNeedLogin = [];

	// 登录的信息
	protected $auth = null;

	public function __construct()
	{
		parent::__construct();

		// 获取当前访问的方法名
		$action = $this->request->action();

		if(!in_array($action,$this->noNeedLogin) && !in_array('*',$this->noNeedLogin))
		{
			$this->isLogin();
		}
	}

	protected function isLogin()
	{
		$Login = cookie('business') ? cookie('business') : [];

		if(!$Login)
		{
			$this->error('请先登录',url('/home/index/login'));
		}

		$id = $Login['id'] ?? 0;
		$mobile = $Login['mobile'] ?? '';

		$business = model('business.Business')->where(['id' => $id,'mobile' => $mobile])->find();

		if(!$business)
		{
			cookie('business',null);
			$this->error('非法登录',url('/home/index/login'));
		}

		// 把登录用户的信息给子类的控制器使用
		$this->auth = $business;

		// 同时把登录的信息给视图里去使用
		$this->assign('business',$business);

	}
}