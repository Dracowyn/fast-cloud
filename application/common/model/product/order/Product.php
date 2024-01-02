<?php
/**
 * @author Dracowyn
 * @since 2024-01-02 14:20
 */

namespace app\common\model\product\order;

use think\Model;

class Product extends Model
{
	protected $name = "product_order";

	protected $autoWriteTimestamp = false;

	public function products()
	{
		return $this->belongsTo('app\common\model\product\Product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
	}
}