<?php
/**
 * @author Dracowyn
 * @since 2023-12-29 14:33
 */

namespace app\common\model\admin;

use think\Model;

class AuthGroup extends Model
{
	protected $autoWriteTimestamp = true;

	protected $createTime = 'createtime';

	protected $updateTime = 'updatetime';

	public function getNameAttr($value, $data)
	{
		return __($value);
	}

}