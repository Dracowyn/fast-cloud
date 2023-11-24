<?php
/**
 * 用户消费记录模型
 * @author Dracowyn
 * @since 2023-11-24 14:21
 */

namespace app\common\model\business;

use think\Model;

class Record extends Model
{
	// 指向数据表
	protected $name = 'business_record';

	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = true;

	// 定义创建时间的字段名
	protected $createTime = 'create_time';

	// 定义更新时间的字段名
	protected $updateTime = 'update_time';
}