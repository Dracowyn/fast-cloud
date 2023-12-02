<?php
/**
 * 客户申领验证器
 * @author Dracowyn
 * @since 2023-12-01 17:24
 */

namespace app\common\validate\business;

use think\Validate;

class Recevie extends Validate
{
	// 验证规则
	protected $rule = [
		'applyid' => ['require', 'number'],
		'busid' => ['require', 'number'],
		'status' => ['require', 'in:apply,allot,recovery,reject'],
	];

	// 错误提示信息
	protected $message = [
		'busid.require' => '客户id必填',
		'busid.number' => '客户id必须是数字类型',
		'applyid.require' => '管理员id必填',
		'applyid.number' => '管理员id必须是数字类型',
		'status.require' => '状态必填',
		'status.in' => '状态值有误',
	];

	// 验证场景
	protected $scene = [
		'add' => ['busid', 'applyid', 'status'],
	];
}