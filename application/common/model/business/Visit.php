<?php
/**
 * 回访模型
 * @author Dracowyn
 * @since 2023-12-01 15:21
 */

namespace app\common\model\business;

use think\Model;

class Visit extends Model
{
	protected $name = "business_visit";

	protected $autoWriteTimestamp = true;
	protected $createTime = "createtime"; //插入的时候设置的字段名
	protected $updateTime = false;

	public function admin()
	{
		return $this->belongsTo('app\admin\model\Admin', 'adminid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	public function business()
	{
		return $this->belongsTo('app\common\model\Business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
	}
}

