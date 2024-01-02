<?php
/**
 * 订单控制器
 * @author Dracowyn
 * @since 2024-01-02 17:21
 */

namespace app\admin\controller\product;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\DbException;
use think\response\Json;

class Order extends Backend
{
	protected $relationSearch = true;

	protected $searchFields = ['code', 'business.nickname', 'express.name', 'expresscode'];

	protected $model = null;

	protected $businessModel = null;

	protected $orderProductModel = null;

	protected $expressModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->model = new \app\common\model\product\order\Order;
		$this->businessModel = new \app\common\model\business\Business;
		$this->orderProductModel = new \app\common\model\product\order\Product;
		$this->expressModel = new \app\common\model\Express;
		$this->view->assign("statusList", $this->model->getStatusList());
	}

	public function index()
	{
		$this->request->filter(['strip_tags', 'trim']);

		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}
			list($where, $sort, $order, $offset, $limit) = $this->buildparams();

			$list = $this->model
				->with(['business', 'express'])
				->where($where)
				->order($sort, $order)
				->paginate($limit);

			$result = array("total" => $list->total(), "rows" => $list->items());

			return json($result);
		}
		return $this->view->fetch();
	}

	// 订单详情
	public function info($ids = null)
	{
		$row = $this->model
			->with(['business', 'express', 'address' => ['provinces', 'citys', 'districts'], 'sale', 'review', 'dispatched'])
			->find($ids);

		if (!$row) {
			$this->error(__('No Results were found'));
		}

		$orderProductData = $this->orderProductModel->with(['products'])->where('orderid', $ids)->select();

		$this->view->assign([
			'row' => $row,
			'orderProductData' => $orderProductData,
		]);

		return $this->view->fetch();
	}

	// 发货
	public function deliver($ids = null)
	{
		$row = $this->model->find($ids);

		if (!$row) {
			$this->error(__('No Results were found'));
		}

		if ($this->request->isPost()) {
			$params = $this->request->param('row/a');

			$data = [
				'id' => $ids,
				'expressid' => $params['expressid'],
				'expresscode' => $params['expresscode'],
				'shipmanid' => $this->auth->id,
				'status' => 2,
			];

			$validate = [
				[
					'expressid' => 'require',
					'expresscode' => 'require|unique:order',
				],
				[
					'expressid.require' => '请选择快递公司',
					'expresscode.require' => '请填写快递单号',
					'expresscode.unique' => '快递单号已存在',
				],
			];

			$result = $this->model->validate(...$validate)->isUpdate()->save($data);

			if ($result === false) {
				$this->error($this->model->getError());
			} else {
				$this->success('发货成功');
			}
		}

		// 物流公司数据
		$expressData = $this->expressModel->column('id,name');

		$this->view->assign([
			'row' => $row,
			'expressData' => $expressData,
		]);

		return $this->view->fetch();
	}

	// 软删除
	public function del($ids = null)
	{
		$ids = $ids ?: $this->request->params('ids', '', 'trim');

		$row = $this->model->where('id', 'in', $ids)->select();

		if (!$row) {
			$this->error('请选择需要删除的订单');
		}

		$result = $this->model->destroy($ids);

		if (!$result) {
			$this->error('删除失败');
		} else {
			$this->success('删除成功');
		}
	}

	// 回收站
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
				->with(['express', 'business'])
				->where($where)
				->order($sort, $order)
				->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];

			return json($result);
		}
		return $this->fetch();
	}

	// 恢复
	public function restore($ids = null)
	{
		$ids = $ids ?: $this->request->params('ids', '', 'trim');
		$row = $this->model->onlyTrashed()->where('id', 'in', $ids)->select();
		if (!$row) {
			$this->error('请选择需要还原的数据');
		}
		$result = $this->model->onlyTrashed()->where('id', 'in', $ids)->update(['deletetime' => null]);

		if (!$result) {
			$this->error('还原失败');
		} else {
			$this->success('还原成功');
		}
	}

	// 真实删除
	public function destroy($ids = null)
	{
		$ids = $ids ?: $this->request->params('ids', '', 'trim');
		$row = $this->model->onlyTrashed()->where(['id' => ['in', $ids]])->select();
		if (!$row) {
			$this->error('请选择需要删除的数据');
		}

		Db::startTrans();
		try {
			$orderProductData = $this->orderProductModel->where(['orderid' => ['in', $ids]])->column('id');
			$orderStatus = $this->model->destroy($ids, true);
			if (!$orderStatus) {
				throw new Exception(__($this->model->getError()));
			}

			$orderProductStatus = $this->orderProductModel->destroy($orderProductData);
			if (!$orderProductStatus) {
				throw new Exception(__($this->orderProductModel->getError()));
			}

			Db::commit();
		} catch (Exception $e) {
			Db::rollback();
			$this->error($e->getMessage());
		}

		$this->success();
	}


}