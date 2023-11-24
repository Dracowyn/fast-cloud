<?php
/**
 * 用户消费记录验证器
 * @author Dracowyn
 * @since 2023-11-24 14:26
 */

namespace app\common\validate;

use think\Validate;

class Record extends Validate
{
	protected $rule = [
		'total' => 'require',
		'busid' => 'require',
		'content' => 'require',
	];

	protected $message = [
		'total.require' => '消费金额必填',
		'busid.require' => '用户必须填写',
		'content.require' => '消费描述必须填写',
	];
}