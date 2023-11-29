<?php
/**
 * 课程章节验证器
 * @author Dracowyn
 * @since 2023-11-29 14:43
 */

namespace app\common\validate\subject;

use think\Validate;

class Chapter extends Validate
{
	// 定义验证规则
	protected $rule = [
		'subid' => ['require'],
		'title' => ['require'],
		'url' => ['require'],
	];

	// 定义验证提示
	protected $message = [
		'name.require' => '课程必填',
		'weight.require' => '课程章节必填',
		'url.require' => '课程视频链接必填'
	];

	// 定义验证场景
	protected $scene = [
		'add' => [],
		'edit' => [],
	];
}