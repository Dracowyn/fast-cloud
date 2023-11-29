<?php
/**
 * @author Dracowyn
 * @since 2023-11-23 15:06
 */

namespace app\common\model\subject;

use think\Model;
use traits\model\SoftDelete;

class Chapter extends Model
{
	// 指向数据表
	protected $name = 'subject_chapter';

	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = true;

	// 使用软删除
	use SoftDelete;

	// 定义创建时间的字段名
	protected $createTime = 'create_time';

	// 定义更新时间的字段名
	protected $updateTime = 'update_time';

	// 定义软删除的字段名
	protected $deleteTime = 'delete_time';

	// 追加不存在的字段
	protected $append = [

	];
}