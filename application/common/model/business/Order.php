<?php
/**
 * 订单模型
 * @author Dracowyn
 * @since 2023-11-24 14:23
 */

namespace app\common\model\business;

use think\Model;
use traits\model\SoftDelete;

class Order extends Model
{
	use SoftDelete;

	// 指向数据表
	protected $name = 'subject_order';

	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = true;

	// 定义创建时间的字段名
	protected $createTime = 'create_time';

	// 定义更新时间的字段名
	protected $updateTime = 'update_time';

	// 定义软删除字段
	protected $deleteTime = 'delete_time';
}