<?php
/**
 * @author Dracowyn
 * @since 2023-11-23 18:37
 */

namespace app\common\model\subject;

use think\Model;

class Comment extends Model
{
	// 指向数据表
	protected $name = 'subject_comment';

	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = true;

	// 定义创建时间的字段名
	protected $createTime = 'create_time';

	// 定义更新时间的字段名
	protected $updateTime = 'update_time';

	public function business()
	{
		return $this->belongsTo('app\common\model\business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
	}
}