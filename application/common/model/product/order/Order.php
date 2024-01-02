<?php
/**
 * @author Dracowyn
 * @since 2024-01-02 14:11
 */

namespace app\common\model\product\order;

use think\Model;
use think\model\relation\BelongsTo;
use traits\model\SoftDelete;

class Order extends Model
{
	use SoftDelete;

	protected $name = "order";

	protected $autoWriteTimestamp = true;

	protected $createTime = "createtime"; //插入的时候设置的字段名
	protected $updateTime = false;
	protected $deleteTime = "deletetime";

	protected $append = [
		'status_text'
	];

	public function getStatusList(): array
	{
		return [
			'0' => __('未支付'),
			'1' => __('已支付'),
			'2' => __('已发货'),
			'3' => __('已收货'),
			'4' => __('已完成'),
			'-1' => __('仅退款'),
			'-2' => __('退款退货'),
			'-3' => __('售后中'),
			'-4' => __('退货成功'),
			'-5' => __('退货失败')
		];
	}

	// 获取订单状态的文字
	public function getStatusTextAttr($value, $data)
	{
		$value = $value ? $value : ($data['status'] ?? '');
		$list = $this->getStatusList();
		return $list[$value] ?? '';
	}

	// 关联用户
	public function business(): BelongsTo
	{
		return $this->belongsTo('app\common\model\business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	// 关联用户收货地址
	public function address(): BelongsTo
	{
		return $this->belongsTo('app\common\model\business\Address', 'businessaddrid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	// 销售员
	public function sale(): BelongsTo
	{
		return $this->belongsTo('app\common\model\Admin', 'adminid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	// 审核员
	public function review(): BelongsTo
	{
		return $this->belongsTo('app\common\model\Admin', 'checkmanid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	// 发货员
	public function dispatched(): BelongsTo
	{
		return $this->belongsTo('app\common\model\Admin', 'shipmanid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	// 关联查询订单商品
	public function orderProduct(): BelongsTo
	{
		return $this->belongsTo('app\common\model\product\order\Product', 'id', 'orderid', [], 'LEFT')->setEagerlyType(0);
	}

}


