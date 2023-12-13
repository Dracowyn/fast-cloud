<?php

namespace app\admin\controller\depot;

use app\common\controller\Backend;
use app\common\model\product\Product;

/**
 * 入库管理
 *
 * @icon fa fa-circle-o
 */
class Storage extends Backend
{

	/**
	 * Storage模型对象
	 */
	protected $model = null;

	/*
	 * 商品模型
	 */
	protected $productModel = null;

	/*
	 * 供应商模型
	 */
	protected $supplierModel = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->model = new \app\common\model\depot\Storage;
		$this->productModel = new Product;
		$this->supplierModel = new \app\common\model\depot\Supplier;
		$this->view->assign("typeList", $this->model->getTypeList());
		$this->view->assign("statusList", $this->model->getStatusList());
	}


	public function index()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isAjax()) {
			//如果发送的来源是Selectpage，则转发到Selectpage
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}
			list($where, $sort, $order, $offset, $limit) = $this->buildparams();
			$list = $this->model
				->with(['supplier'])
				->where($where)
				->order($sort, $order)
				->paginate($limit);
			$result = array("total" => $list->total(), "rows" => $list->items());
			return json($result);
		}
		return $this->view->fetch();
	}

	public function add()
	{
		if ($this->request->isPost()) {
			$params = $this->request->param('row/a');

			// 商品数据转换成数组
			$productList = json_decode($params['product'], true);

			//开启事务
			$this->model->startTrans();
			$this->productModel->startTrans();

			// 封装入库单数据
			$storageData = [
				'code' => build_order('SU'),
				'supplierid' => $params['supplier'],
				'type' => $params['type'],
				'amount' => $params['amount'],
				'remark' => $params['remark'],
				'status' => 0
			];

			// 入库单入库
			$storageStatus = $this->model->validate('common/depot/Storage')->save($storageData);

			if ($storageStatus === false) {
				$this->model->rollback();
				$this->error($this->model->getError());
			}

			// 封装入库商品数据
			$productData = [];

			foreach ($productList as $item) {
				$productData[] = [
					'storageid' => $this->model->id,
					'proid' => $item['id'],
					'nums' => $item['nums'],
					'price' => $item['price'],
					'total' => $item['total'],
				];
			}

			// 验证数据
			$productStatus = $this->productModel->validate('common/depot/Product')->saveAll($productData);

			if ($productStatus === false) {
				$this->model->rollback();
				$this->productModel->rollback();
				$this->error($this->productModel->getError());
			}

			if ($storageStatus && $productStatus) {
				$this->model->commit();
				$this->productModel->commit();
				$this->success();
			} else {
				$this->model->rollback();
				$this->productModel->rollback();
				$this->error();
			}

		}
		return $this->view->fetch();
	}

	public function edit($ids = null)
	{
		$row = $this->model->get($ids);
		if (!$row) {
			$this->error(__('No Results were found'));
		}

		if ($this->request->isPost()) {
			$params = $this->request->param('row/a');

			// 商品数据转换成数组
			$productList = json_decode($params['product'], true);

			//开启事务
			$this->model->startTrans();
			$this->productModel->startTrans();

			// 封装入库单数据
			$storageData = [
				'code' => build_order('SU'),
				'supplierid' => $params['supplier'],
				'type' => $params['type'],
				'amount' => $params['amount'],
				'remark' => $params['remark'],
				'status' => 0
			];

			// 入库单入库
			$storageStatus = $this->model->validate('common/depot/Storage')->save($storageData, ['id' => $ids]);

			if ($storageStatus === false) {
				$this->model->rollback();
				$this->error($this->model->getError());
			}

			// 封装入库商品数据
			$productData = [];

			foreach ($productList as $item) {
				$productData[] = [
					'storageid' => $this->model->id,
					'proid' => $item['id'],
					'nums' => $item['nums'],
					'price' => $item['price'],
					'total' => $item['total'],
				];
			}

			// 验证数据
			$productStatus = $this->productModel->validate('common/depot/Product')->saveAll($productData);

			if ($productStatus === false) {
				$this->model->rollback();
				$this->productModel->rollback();
				$this->error($this->productModel->getError());
			}

			if ($storageStatus && $productStatus) {
				$this->model->commit();
				$this->productModel->commit();
				$this->success();
			} else {
				$this->model->rollback();
				$this->productModel->rollback();
				$this->error();
			}

		}

	}


}
