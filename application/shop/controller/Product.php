<?php
/**
 * @author Dracowyn
 * @since 2023-12-18 16:03
 */

namespace app\shop\controller;

use think\Controller;
use think\response\Json;

class Product extends Controller
{
	public function thumb(): string
	{
		$proId = $this->request->param('proid', 0, 'trim');
		$productModel = new \app\common\model\product\Product;
		$product = $productModel->where(['id' => $proId])->find();
		return $product['thumb_cdn'];
	}

	public function thumbs(): Json
	{
		$proId = $this->request->param('proid', 0, 'trim');
		$product = model('product.Product')->where(['id' => $proId])->find();
		return json($product['thumbs_cdn']);
	}
}