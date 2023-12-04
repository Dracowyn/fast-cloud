<?php
/**
 * 客户信息
 * @author Dracowyn
 * @since 2023-12-04 17:17
 */

namespace app\admin\controller\business;

use app\admin\model\Admin;
use app\common\controller\Backend;
use app\common\model\business\Address;
use app\common\model\business\Business;
use app\common\model\business\Receive;
use app\common\model\business\Visit;


class Privateinfo extends Backend
{
	/**
	 * 客户模型对象
	 */
	protected $model = null;

	/*
 * 客户申领模型
 */
	protected $receiveModel = null;

	/*
	 * 客户回访模型
	 */
	protected $visitModel = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->model = new Business;
		$this->receiveModel = new Receive;
		$this->visitModel = new Visit;
		$this->assign('genderList', $this->model->getGenderList());
		$this->assign('dealList', $this->model->getDealList());
		$this->assign('authList', $this->model->getAuthList());
	}

	// 回收到公海
	public function recovery($ids = null)
	{
		$ids = !empty($ids) ? explode(',', $ids) : [];
		// 判断客户是否存在
		$row = $this->model->column('id');
		foreach ($ids as $item) {
			if (!in_array($item, $row)) {
				$this->error(__('没有找到该用户'));
			}
		}

		$receiveData = [];
		$businessData = [];

		foreach ($ids as $item) {
			$receiveData[] = [
				'busid' => $item,
				'status' => 'recovery',
				'applyid' => $this->auth->id
			];

			$businessData[] = [
				'id' => $item,
				'adminid' => null
			];
		}

		// 开启事务
		$this->receiveModel->startTrans();
		$this->model->startTrans();

		$receiveStatus = $this->receiveModel->saveAll($receiveData);
		$businessStatus = $this->model->saveAll($businessData);

		if ($receiveStatus && $businessStatus) {
			$this->receiveModel->commit();
			$this->model->commit();
			$this->success();
		} else {
			$this->receiveModel->rollback();
			$this->model->rollback();
			$this->error($this->model->getError() & $this->receiveModel->getError());
		}
	}

	// 客户详情
	public function index()
	{
		$ids = $this->request->param('ids', 0, 'trim');
		// 判断客户是否存在
		$row = $this->model->find($ids);

		if (!$row) {
			$this->error('客户不存在');
		}
		$this->assign([
			'row' => $row,
		]);

		return $this->view->fetch();
	}

	// 客户回访列表
	public function visit($ids = null)
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);

		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}

			list($where, $sort, $order, $offset, $limit) = $this->buildparams();

			$list = $this->visitModel
				->with('admin', 'business')
				->where($where)
				->where('busid', $ids)
				->order($sort, $order)
				->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];
			return json($result);
		}

		return $this->view->fetch();
	}

	// 客户回访添加
	public function add($ids = null)
	{
		// 判断客户是否存在
		$row = $this->model->find($ids);

		if (!$row) {
			$this->error('客户不存在');
		}

		if ($this->request->isPost()) {
			$param = $this->request->param('row/a');

			$data = [
				'busid' => $ids,
				'adminid' => $this->auth->id,
				'content' => $param['content'],
			];

			$result = $this->visitModel->validate('common/business/Visit')->save($data);

			if ($result) {
				$this->success();
			} else {
				$this->error($this->visitModel->getError());
			}
		}

		return $this->view->fetch();
	}

	// 客户回访编辑
	public function edit($ids = null)
	{
		// 判断客户是否存在
		$row = $this->visitModel->find($ids);

		if (!$row) {
			$this->error('客户不存在');
		}

		if ($this->request->isPost()) {
			$param = $this->request->param('row/a');

			$data = [
				'id' => $ids,
				'busid' => $row['busid'],
				'adminid' => $row['adminid'],
				'content' => $param['content'],
			];

			$result = $this->visitModel->validate('common/business/Visit')->isUpdate()->save($data);

			if ($result) {
				$this->success();
			} else {
				$this->error($this->visitModel->getError());
			}
		}

		$this->assign([
			'row' => $row
		]);

		return $this->view->fetch();
	}

	// 客户回访删除
	public function del($ids = null)
	{
		$ids = !empty($ids) ? explode(',', $ids) : [];
		// 判断客户是否存在
		$row = $this->visitModel->column($ids);

		foreach ($ids as $item) {
			if (!in_array($item, $row)) {
				$this->error(__('没有找到该回访记录'));
			}
		}

		$result = $this->visitModel->destroy($ids);

		if ($result) {
			$this->success();
		} else {
			$this->error($this->visitModel->getError());
		}
	}

	// 客户申请领取列表
	public function receive($ids = null)
	{
		$this->request->filter(['strip_tags']);

		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}
			list($where, $sort, $order, $offset, $limit) = $this->buildparams();

			$list = $this->receiveModel
				->with('admin', 'business')
				->where($where)
				->where('busid', $ids)
				->order($sort, $order)
				->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];

			return json($result);
		}

		return $this->view->fetch();
	}
}