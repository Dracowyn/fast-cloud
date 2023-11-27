<?php
/**
 * 评论验证器
 * @author Dracowyn
 * @since 2023-11-24 17:52
 */

namespace app\common\validate\subject;

use think\Validate;

class Comment extends Validate
{
	protected $rule = [
		'subid' => 'require',
		'busid' => 'require',
		'content' => 'require',
	];

	protected $message = [
		'subid.require' => '课程必须填写',
		'busid.require' => '用户必须填写',
		'content.require' => '评论内容必须填写',
	];
}
