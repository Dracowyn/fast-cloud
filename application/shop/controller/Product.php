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
	protected $businessModel = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->businessModel = new \app\common\model\business\Business;
	}

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

	// 评论图片上传接口
	public function upload(): Json
	{
		$id = $this->request->param('id', 0, 'trim');
		$business = $this->businessModel->find($id);
		if (!$business) {
			return json(['msg' => '用户不存在', 'code' => 0, 'data' => null]);
		}

		$images = build_upload('image');

//		$images = build_upload('images');
		return json($images);
	}
}