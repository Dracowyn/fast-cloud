<?php

namespace app\admin\controller\depot;

use app\common\controller\Backend;
use app\common\model\product\Product;
use Exception;
use PDOException;
use think\Db;

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

	/*
	 * 退货模型
	 */
	private $backModel = null;

	/*
	 * 商品入库模型
	 */
	protected $depotProductModel = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->model = new \app\common\model\depot\Storage\Storage;
		$this->productModel = new Product;
		$this->supplierModel = new \app\common\model\depot\Supplier;
		$this->backModel = new \app\common\model\depot\Back\Back;
		$this->depotProductModel = new \app\common\model\depot\Storage\Product;
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
			$this->depotProductModel->startTrans();

			// 封装入库单数据
			$storageData = [
				'code' => build_order('SU'),
				'supplierid' => $params['supplierid'],
				'type' => $params['type'],
				'amount' => $params['total'],
				'remark' => $params['remark'],
				'status' => 0
			];

			// 入库单入库
			$storageStatus = $this->model->validate('common/depot/storage/Storage')->save($storageData);

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
			$productStatus = $this->depotProductModel->validate('common/depot/storage/Product')->saveAll($productData);

			if ($productStatus === false) {
				$this->model->rollback();
				$this->depotProductModel->rollback();
				$this->error($this->depotProductModel->getError());
			}

			if ($storageStatus && $productStatus) {
				$this->model->commit();
				$this->depotProductModel->commit();
				$this->success();
			} else {
				$this->model->rollback();
				$this->depotProductModel->rollback();
				$this->error();
			}

		}
		return $this->view->fetch();
	}

	public function edit($ids = null)
	{
		$row = $this->model->get($ids);

		$productList = $this->depotProductModel->where('storageid', $row['id'])->select();

		if (!$row) {
			$this->error(__('No Results were found'));
		}

		$supplier = $this->supplierModel->with(['provinces', 'citys', 'districts'])->where('id', $row['supplierid'])->find();

		if (!$supplier) {
			// 查询退货订单信息
			$back = $this->backModel->with(['business'])->where(['storageid' => $row['id']])->find();
			if (!$back) {
				$this->error(__('退货单不存在'));
			}
		}

		if (!$productList) {
			$this->error(__('该商品不存在'));
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
				'id' => $row['id'],
				'type' => $params['type'],
				'amount' => $params['total'],
				'remark' => $params['remark'],
				'status' => $row['status'],
			];

			if ($row['type'] == 1) {
				$storageData['supplierid'] = $params['supplierid'];
			}

			if ($supplier) {
				$storageStatus = $this->model->validate('common/depot/storage/Storage.edit')->isUpdate()->save($storageData);
			} else {
				$storageStatus = $this->model->validate('common/depot/storage/Storage.back_edit')->isUpdate()->save($storageData);
			}

			if (!$storageStatus) {
				$this->model->rollback();
				$this->error($this->model->getError());
			}

			// 封装入库商品数据
			$productData = [];

			// 封装修改时新增的商品数据
			$addProductData = [];

			foreach ($productList as $item) {
				if (isset($item['id'])) {
					$productData[] = [
						'id' => $item['id'],
						'proid' => $item['proid'],
						'nums' => $item['nums'],
						'price' => $item['price'],
						'total' => $item['total'],
					];
				} else {
					$addProductData[] = [
						'storageid' => $row['id'],
						'proid' => $item['proid'],
						'nums' => $item['nums'],
						'price' => $item['price'],
						'total' => $item['total'],
					];
				}
			}

			$delProductId = json_decode($params['delproid']);

			// 删除不需要的商品
			if (!empty($delProductId)) {
				$delProductStatus = $this->depotProductModel->destroy($delProductId);
				if (!$delProductStatus) {
					$this->model->rollback();
					$this->productModel->rollback();
					$this->error(__('删除商品失败'));
				}
			}

			$newProductStatus = $this->depotProductModel->validate('common/depot/storage/Product')->saveAll($addProductData);

			// 验证数据
			$productStatus = $this->productModel->validate('common/depot/storage/Product.edit')->saveAll($productData);

			if ($productStatus === false || $newProductStatus === false) {
				$this->model->rollback();
				$this->productModel->rollback();
				$this->error($this->depotProductModel->getError());
			}

			if ($productStatus && $storageStatus && $newProductStatus) {
				$this->model->commit();
				$this->productModel->commit();
				$this->success();
			} else {
				$this->model->rollback();
				$this->productModel->rollback();
				$this->error();
			}
		}

		$productData = [];

		foreach ($productList as $item) {
			$product = $this->productModel->with(['type', 'unit'])->find($item['proid']);

			$productData[] = [
				'id' => $item['id'],
				'price' => $item['price'],
				'nums' => $item['nums'],
				'total' => $item['total'],
				'product' => $product
			];
		}

		$data = [
			'row' => $row,
			'supplier' => $supplier
		];

		if (!$supplier) {
			$data['back'] = $back;
		}

		$this->assignconfig('Product', ['productData' => $productData]);

		return $this->view->fetch('', $data);
	}

	// 回收站
	public function del($ids = null)
	{
		$this->request->filter(['strip_tags', 'trim']);

		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}

			[$where, $sort, $order, $offset, $limit] = $this->buildparams();

			$list = $this->model
				->onlyTrashed()
				->with(['supplier'])
				->where($where)
				->order($sort, $order)
				->paginate($limit);

			$result = array("total" => $list->total(), "rows" => $list->items());

			return json($result);
		}

		return $this->view->fetch();
	}

	// 还原
	public function restore($ids = null)
	{
		$row = $this->model->onlyTrashed()->where(['id' => ['in', $ids]])->select();
		if (!$row) {
			$this->error(__('No Results were found'));
		}

		$result = $this->model->onlyTrashed()->where(['id' => ['in', $ids]])->update(['deletetime' => null]);
		if ($result) {
			$this->success();
		} else {
			$this->error(__('还原失败'));
		}
	}

	// 真实删除
	public function destroy($ids = null)
	{
		if ($this->request->isAjax()) {
			Db::startTrans();

			try {
				$row = $this->model->onlyTrashed()->where(['id' => ['in', $ids]])->select();

				if (empty($row)) {
					throw new Exception(__("请选择需要删除入库单"));
				}

				$StorageProduct = $this->depotProductModel->where(['storageid' => ['in', $ids]])->column('id');

				$StorageStatus = $this->model->destroy($ids, true);

				if ($StorageStatus === FALSE) {
					throw new Exception(__($this->model->getError()));
				}

				$ProductStatus = $this->depotProductModel->destroy($StorageProduct);

				if ($ProductStatus === FALSE) {
					throw new Exception(__($this->depotProductModel->getError()));
				}

				Db::commit();
			} catch (PDOException|Exception $e) {
				Db::rollback();
				$this->error(__($e->getMessage()));
			}

			$this->success();
		}
	}

	// 撤回审核
	public function cancel($ids = null)
	{
		if ($this->request->isAjax()) {
			$row = $this->model->get($ids);

			if (!$row) {
				$this->error(__('No Results were found'));
			}

			$data = [
				'id' => $row['id'],
				'status' => 0,
				'reviewid' => $this->auth->id,
			];

			$result = $this->model->isUpdate()->save($data);

			if ($result) {
				$this->success();
			} else {
				$this->error(__('撤回失败'));
			}
		}
	}

	// 入库详情
	public function detail($ids = null)
	{
		$row = $this->model->with(['admin', 'reviewer'])->find($ids);
		$supplier = $this->supplierModel->with(['provinces', 'citys', 'districts'])->where('id', $row['supplierid'])->find();
		$productList = $this->depotProductModel->where('storageid', $row['id'])->select();

		$productData = [];

		foreach ($productList as $item) {
			$product = $this->productModel->with(['type', 'unit'])->find($item['proid']);

			$productData[] = [
				'id' => $item['id'],
				'price' => $item['price'],
				'nums' => $item['nums'],
				'total' => $item['total'],
				'product' => $product
			];
		}

		$data = [
			'row' => $row,
			'supplier' => $supplier,
			'productData' => $productData
		];

		return $this->view->fetch('', $data);
	}

	// 入库审核
	public function process()
	{
		if ($this->request->isAjax()) {
			$id = $this->request->param('ids');

			$status = $this->request->param('status');

			$data = [];

			if ($status == 1) {
				$data = [
					'id' => $id,
					'status' => 2,
					'reviewrid' => $this->auth->id,
				];
			} else {
				$data = [
					'id' => $id,
					'status' => 1,
					'reviewerid' => $this->auth->id,
				];
			}

			$result = $this->model->isUpdate()->save($data);

			if ($result) {
				$this->success();
			} else {
				$this->error(__('审核失败'));
			}
		}
	}

	// 确认入库
	public function storage()
	{
		if ($this->request->isAjax()) {
			$id = $this->request->param('ids');
			$productList = $this->depotProductModel->where('storageid', $id)->select();

			$this->model->startTrans();
			$this->productModel->startTrans();

			$data = [
				'id' => $id,
				'status' => 3,
				'adminid' => $this->auth->id
			];

			$result = $this->model->isUpdate()->save($data);

			if (!$result) {
				$this->model->rollback();
				$this->error(__('确认入库失败'));
			}

			$productData = [];

			foreach ($productList as $item) {
				$product = $this->productModel->find($item['proid']);

				if ($product['id'] == $item['proid']) {
					$productData[] = [
						'id' => $product['id'],
						'stock' => bcadd($product['stock'], $item['nums'])
					];
				}
			}

			$productStatus = $this->productModel->isUpdate()->saveAll($productData);

			if (!$productStatus) {
				$this->model->rollback();
				$this->productModel->rollback();
				$this->error(__('确认入库失败'));
			}

			if ($result && $productStatus) {
				$this->model->commit();
				$this->productModel->commit();
				$this->success();
			} else {
				$this->model->rollback();
				$this->productModel->rollback();
				$this->error(__('确认入库失败'));
			}
		}
	}

	// 查询供应商
	public function supplier()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags', 'trim']);

		// 判断是否有Ajax请求
		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}

			[$where, $sort, $order, $offset, $limit] = $this->buildparams();

			$list = $this->supplierModel
				->with(['provinces', 'citys', 'districts'])
				->where($where)
				->group($sort)
				->order($sort, $order)
				->paginate($limit);
			$result = ['total' => $list->total(), 'rows' => $list->items()];
			return json($result);
		}
		return $this->view->fetch();
	}

	// 商品
	public function product()
	{
		$this->request->filter(['strip_tags', 'trim']);
		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}

			[$where, $sort, $order, $offset, $limit] = $this->buildparams();

			$list = $this->productModel
				->with(['type', 'unit'])
				->where($where)
				->order($sort, $order)
				->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];
			return json($result);
		}
		return $this->view->fetch();
	}

}
