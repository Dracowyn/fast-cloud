<?php
/**
 * @author Dracowyn
 * @since 2023-12-18 16:03
 */

namespace app\shop\controller;

use think\Controller;

class Product extends Controller
{
	public function thumb()
	{
		$proId = $this->request->param('proid', 0, 'trim');
		$productModel = new \app\common\model\product\Product;
		$product = $productModel->where(['id' => $proId])->find();
		return $product['thumb_cdn'];
	}
}