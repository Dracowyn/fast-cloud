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

	// 追加不存在的字段
	protected $append = [
		'create_time_text',
		'comment_status',
	];

	// 关联课程
	public function subject()
	{
		return $this->belongsTo('app\common\model\Subject\Subject', 'subid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	public function business()
	{
		return $this->belongsTo('app\common\model\Business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	// 创建时间的获取器
	public function getCreateTimeTextAttr($value, array $data)
	{
		$time = $data['create_time'] ?? '';
		return datetime($time);
	}

	// 评论状态获取器
	public function getCommentStatusAttr($value, array $data)
	{
		$subid = $data['subid'] ?? 0;
		$busid = $data['busid'] ?? 0;

		$status = false;
		$comment = model('subject.Comment')->where(['subid' => $subid, 'busid' => $busid])->find();
		if ($comment) {
			$status = true;
		}
		return $status;
	}
}