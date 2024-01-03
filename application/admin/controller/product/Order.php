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

	// 退款
	public function refund($ids = null)
	{
		$ids = $ids ?: $this->request->params('ids', '', 'trim');
		$row = $this->model->find($ids);
		if (!$row) {
			$this->error('订单不存在');
		}

		if ($this->request->isPost()) {
			$params = $this->request->param('row/a');

			if (empty($params['examinereason']) && $params['refund'] == 0) {
				$this->error('请填写审核不通过原因');
			}

			// 同意仅退款
			if ($params['refund'] === '1' && $row['status'] === '-1') {
				$business = $this->businessModel->find($row['busid']);

				if (!$business) {
					$this->error('用户不存在');
				}

				// 开启事务
				$this->businessModel->startTrans();
				$this->model->startTrans();

				// 更新用户余额
				$businessData = [
					'id' => $row['busid'],
					'money' => bcadd($business['money'], $row['amount'], 2),
				];

				$businessStatus = $this->businessModel->isUpdate()->save($businessData);

				if (!$businessStatus) {
					$this->businessModel->rollback();
					$this->error($this->businessModel->getError());
				}

				// 更新订单状态
				$orderData = [
					'id' => $ids,
					'status' => '-4',
				];

				$orderStatus = $this->model->isUpdate()->save($orderData);

				if (!$orderStatus) {
					$this->businessModel->rollback();
					$this->model->rollback();
					$this->error($this->model->getError());
				}

				if ($businessStatus && $orderStatus) {
					$this->businessModel->commit();
					$this->model->commit();
					$this->success('退款成功');
				} else {
					$this->businessModel->rollback();
					$this->model->rollback();
					$this->error('退款失败');
				}
			}

			// 同意退货退款
			if ($params['refund'] === '1' && $row['status'] === '-2') {
				$data = [
					'id' => $ids,
					'status' => '-3',
				];

				$result = $this->model->isUpdate()->save($data);

				if ($result === false) {
					$this->error($this->model->getError());
				} else {
					$this->success('操作成功');
				}
			}

			// 拒绝退款
			if ($params['refund'] === '0' && $row['status'] === '-1') {
				$data = [
					'id' => $ids,
					'status' => '-5',
					'examinereason' => $params['examinereason'],
				];

				$result = $this->model->isUpdate()->save($data);

				if ($result === false) {
					$this->error($this->model->getError());
				} else {
					$this->success('操作成功');
				}
			}
		}

		$this->view->assign([
			'row' => $row,
		]);

		return $this->view->fetch();
	}

}