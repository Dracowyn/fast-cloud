<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Controller;

class Business extends Home
{
	public function index()
	{
		return $this->fetch();
	}

	public function profile()
	{
		return $this->fetch();
	}
}
