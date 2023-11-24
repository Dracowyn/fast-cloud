<?php
/**
 * 课程订单验证器
 * @author Dracowyn
 * @since 2023-11-24 14:28
 */

namespace app\common\validate;

use think\Validate;

class Order extends Validate
{
	protected $rule = [
		'subid' => 'require',
		'busid' => 'require',
		'total' => 'require',
		'code' => ['require', 'unique:subject_order'],
	];

	protected $message = [
		'subid.require' => '课程必须填写',
		'busid.require' => '用户必须填写',
		'total.require' => '消费金额必须填写',
		'code.require' => '订单号必须填写',
		'code.unique' => '订单号已重复',
	];
}