<?php
/**
 * @author Dracowyn
 * @since 2023-12-15 14:44
 */

namespace app\common\model\depot\Back;

use think\Model;

class Product extends Model
{
	protected $name = 'depot_back_product';

	/**
	 * 关联查询商品
	 */
	public function products()
	{
		return $this->belongsTo('app\common\model\product\Product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
	}
}