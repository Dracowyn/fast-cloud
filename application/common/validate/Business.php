<?php
/**
 * @author Dracowyn
 * @since 2023-11-21 15:38
 */

namespace app\common\validate;

use think\Validate;

class Business extends Validate
{
	// 验证规则
	protected $rule = [
		'mobile' => ['require', 'regex:/(^1[3|4|5|7|8][0-9]{9}$)/', 'unique:business'],
		'money' => ['number', '>=:0'],
		'auth' => ['number', 'in:0,1'],
		'deal' => ['number', 'in:0,1'],
		'nickname' => ['require'],
		'email' => ['email', 'require'],
	];

	// 错误提示信息
	protected $message = [
		'mobile.require' => '手机号必填',
		'mobile.unique' => '手机号已存在，请重新输入',
		'mobile.regex' => '手机号格式错误，请重新输入',
		'salt.require' => '密码盐必填',
		'money.number' => '余额必须是数字类型',
		'money.>=' => '余额必须大于等于0元',
		'auth.number' => '认证状态的类型有误',
		'auth.in' => '认证状态的值有误',
		'deal.number' => '成交状态的类型有误',
		'deal.in' => '成交状态的值有误',
		'nickname.require' => '昵称必填',
		'email.require' => '邮箱必填',
		'email.email' => '邮箱格式错误'
	];

	// 验证场景
	protected $scene = [
		'register' => ['mobile', 'salt', 'money', 'auth', 'deal'],
		'profile' => ['nickname', 'email']
	];
}