<?php

namespace app\admin\controller\product;

use app\common\controller\Backend;
use think\Exception;
use think\exception\DbException;
use think\response\Json;

/**
 * 商品管理
 *
 * @icon fa fa-circle-o
 */
class Product extends Backend
{

	/**
	 * 是否是关联查询
	 */
	protected $relationSearch = true;

	/**
	 * Product模型对象
	 * @var \app\common\model\product\Product
	 */
	protected $model = null;

	/**
	 * 商品分类模型对象
	 * @var \app\common\model\product\Type
	 */
	protected $typeModel = null;

	/**
	 * 商品单位模型
	 * @var \app\common\model\product\Unit
	 */
	protected $unitModel = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->model = new \app\common\model\product\Product;
		$this->typeModel = new \app\common\model\product\Type;
		$this->unitModel = new \app\common\model\product\Unit;
		$this->view->assign("flagList", $this->model->getFlagList());
		$this->view->assign("statusList", $this->model->getStatusList());
		$this->view->assign("typeList", $this->typeModel->getTypeList());
		$this->view->assign("unitList", $this->unitModel->getUnitList());
	}

	/**
	 * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
	 * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
	 * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
	 */

	/**
	 * 查看
	 *
	 * @return string|Json
	 * @throws Exception
	 * @throws DbException
	 */
	public function index()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags', 'trim']);
		if (false === $this->request->isAjax()) {
			return $this->view->fetch();
		}
		//如果发送的来源是 Selectpage，则转发到 Selectpage
		if ($this->request->request('keyField')) {
			return $this->selectpage();
		}
		[$where, $sort, $order, $offset, $limit] = $this->buildparams();
		$list = $this->model
			->with(['type', 'unit'])
			->where($where)
			->order($sort, $order)
			->paginate($limit);
		$result = ['total' => $list->total(), 'rows' => $list->items()];
		return json($result);
	}

	// 重写添加方法
	public function add()
	{
		if ($this->request->isPost()) {
			$params = $this->request->post("row/a");
			// 验证数据
			$result = $this->model->validate('common/product/Product')->save($params);
			if ($result === false) {
				// 如果插入失败删除上传的图片
				$this->delThumbs($params);
			} else {
				$this->success();
			}
		}
		return $this->view->fetch();
	}

	// 重写编辑方法
	public function edit($ids = null)
	{
		$row = $this->model->get($ids);
		$ids = $ids ?: $this->request->param('ids', '', 'trim');
		if (!$row) {
			$this->error(__('No Results were found'));
		}
		if ($this->request->isPost()) {
			$params = $this->request->post("row/a");
			$params['id'] = $ids;
			// 验证数据
			$result = $this->model->validate('common/product/Product')->isUpdate()->save($params);
			if ($result === false) {
				// 如果插入失败删除上传的图片
				$this->delThumbs($params);
			} else {
				$thumbs = explode(',', $params['thumbs']);
				$oldThumbs = explode(',', $row['thumbs']);
				$thumbs = array_filter($thumbs);
				foreach ($oldThumbs as $oldThumb) {
					if (!in_array($oldThumb, $thumbs)) {
						$oldThumb = ltrim($oldThumb, '/');
						@is_file($oldThumb) && @unlink($oldThumb);
					}
				}
				$this->success();
			}
		}
		$this->view->assign("row", $row);
		return $this->view->fetch();
	}

	/**
	 * 删除上传失败的图片
	 * @param $params
	 * @return void
	 */
	public function delThumbs($params): void
	{
		if (!empty($params['thumbs'])) {
			$thumbsArr = explode(',', $params['thumbs']);
			$thumbsArr = array_filter($thumbsArr);
			foreach ($thumbsArr as $thumb) {
				$thumb = ltrim($thumb, '/');
				@is_file($thumb) && @unlink($thumb);
			}
		}
		$this->error($this->model->getError());
	}

	// 重写回收站方法
	public function recyclebin()
	{
		$this->request->filter(['strip_tags', 'trim']);
		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}
			[$where, $sort, $order, $offset, $limit] = $this->buildparams();

			$list = $this->model
				->onlyTrashed()
				->with(['type', 'unit'])
				->where($where)
				->order($sort, $order)
				->paginate($limit);
			$result = ['total' => $list->total(), 'rows' => $list->items()];
			return json($result);
		}
		return $this->view->fetch();
	}

	// 重写真实删除方法
	public function destroy($ids = "")
	{
		if ($this->request->isAjax()) {
			$row = $this->model->onlyTrashed()->where(['id' => ['in',$ids]])->select();

			if (!$row) {
				$this->error(__('No Results were found'));
			}

			$result = $this->model->destroy($ids,true);

			if ($result) {
				foreach ($row as $item) {
					// 把thumbs字段的值转换成数组
					$thumbs = !empty($item['thumbs']) ? explode(',', $item['thumbs']) : [];
					// 删除空元素
					$thumbs = array_filter($thumbs);
					foreach ($thumbs as $thumb) {
						$thumb = ltrim($thumb, '/');
						@is_file($thumb) && @unlink($thumb);
					}
				}
				$this->success();
			} else {
				$this->error($this->model->getError());
			}
		}
	}
}
