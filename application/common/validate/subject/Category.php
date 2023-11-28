<?php
/**
 * @author Dracowyn
 * @since 2023-11-28 17:14
 */

namespace app\common\validate\subject;

use think\Validate;

class Category extends Validate
{
	protected $rule = [
		'name' => 'require',
		'weight' => 'require',
	];

	protected $message = [
		'cate_name.require' => '分类名称必须填写',
		'weight.require' => '权重必须填写',
	];
}