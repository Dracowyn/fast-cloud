<?php
/**
 * @author Dracowyn
 * @since 2023-12-29 14:34
 */

namespace app\common\model\admin;

use think\Model;

class AuthGroupAccess extends Model
{
	protected $table = 'auth_group_access';

	protected $autoWriteTimestamp = false;

	protected $createTime = false;

	protected $updateTime = false;

	protected $field = true;
}