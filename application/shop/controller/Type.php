<?php
/**
 * @author Dracowyn
 * @since 2023-12-18 16:05
 */

namespace app\shop\controller;
use think\Controller;
class Type extends Controller
{
	public function thumb()
	{
		$typeId = $this->request->param('typeid');
		$typeModel = new \app\common\model\product\Type;
		$type = $typeModel->find($typeId);
		return $type['thumb_cdn'];
	}
}