<?php

namespace app\admin\controller\business;

use app\admin\model\Admin;
use app\common\controller\Backend;
use app\common\model\business\Business;
use app\common\model\business\Receive;
use app\common\model\business\Visit;
use app\common\model\Region;

/**
 * 客户公海
 *
 * @icon fa fa-circle-o
 */
class Highsea extends Backend
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

	/*
	 * 管理员模型
	 */
	protected $adminModel = null;

	/*
	 * 地区模型
	 */
	protected $regionModel = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->model = new Business;
		$this->receiveModel = new Receive;
		$this->visitModel = new Visit;
		$this->adminModel = new Admin;
		$this->regionModel = new Region;
		$this->assign('genderList', $this->model->getGenderList());
		$this->assign('dealList', $this->model->getDealList());
		$this->assign('authList', $this->model->getAuthList());
	}

	public function index()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}

			[$where, $sort, $order, $offset, $limit] = $this->buildparams();

			$list = $this->model
				->with('source')
				->where($where)
				->where('adminid', 'null')
				->order($sort, $order)
				->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];
			return json($result);
		}
		return $this->view->fetch();
	}

	// 添加客户
	public function add()
	{
		return null;
	}

	// 编辑客户
	public function edit($ids = null)
	{
		return null;
	}

	// 分配客户
	public function allot($ids = null)
	{
		$ids = !empty($ids) ? explode(',', $ids) : [];
		$row = $this->model->all($ids);


		if (!$row) {
			$this->error('客户不存在');
		}

		if ($this->request->isPost()) {
			$list = [];
			$businessList = [];

			$params = $this->request->post('row/a');


			foreach ($row as $item) {
				$list[] = [
					'applyid' => $params['adminid'],
					'status' => 'allot',
					'busid' => $item['id'],
				];

				$businessList[] = [
					'id' => $item['id'],
					'adminid' => $params['adminid'],
				];

			}


			// 开启事务
			$this->model->startTrans();
			$this->receiveModel->startTrans();

			// 更新客户表
			$businessStatus = $this->model->saveAll($businessList);

			// 更新申领表
			$receiveStatus = $this->receiveModel->saveAll($list);

			if (!$businessStatus && !$receiveStatus) {
				$this->model->rollback();
				$this->receiveModel->rollback();
				$this->error($this->receiveModel->getError());
			} else {
				$this->model->commit();
				$this->receiveModel->commit();
				$this->success('申领成功');
			}
		}

		$adminData = $this->adminModel->column('id,username');

		$this->assign([
			'AdminData' => $adminData,
			'row' => $row
		]);

		return $this->view->fetch();
	}

	// 领取客户
	public function receive($ids = null)
	{
		$ids = !empty($ids) ? explode(',', $ids) : [];
		$row = $this->model->all($ids);

		if (!$row) {
			$this->error('客户不存在');
		}

		$list = [];
		$businessList = [];

		foreach ($row as $item) {
			$list[] = [
				'applyid' => $this->auth->id,
				'status' => 'apply',
				'busid' => $item['id'],
			];

			$businessList[] = [
				'id' => $item['id'],
				'adminid' => $this->auth->id,
			];

		}

		// 开启事务
		$this->model->startTrans();
		$this->receiveModel->startTrans();

		// 更新客户表
		$businessStatus = $this->model->saveAll($businessList);

		// 更新申领表
		$receiveStatus = $this->receiveModel->saveAll($list);

		if (!$businessStatus && !$receiveStatus) {
			$this->model->rollback();
			$this->receiveModel->rollback();
			$this->error($this->receiveModel->getError());
		} else {
			$this->model->commit();
			$this->receiveModel->commit();
			$this->success('申请成功');
		}
	}


}
