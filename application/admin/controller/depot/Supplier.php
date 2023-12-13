<?php

namespace app\admin\controller\depot;

use app\common\controller\Backend;
use Exception;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 供应商
 *
 * @icon fa fa-circle-o
 */
class Supplier extends Backend
{

	/**
	 * Supplier模型对象
	 */
	protected $model = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->model = new \app\common\model\depot\Supplier;

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

	public function add()
	{
		if ($this->request->isPost()) {
			$params = $this->request->post("row/a");

			if ($params['code']) {
				$parentPath = model('Region')->where(['code' => $params['code']])->value('parentpath');
				if (!$parentPath) {
					$this->error('地区选择有误');
				}
				$pathArr = explode(',', $parentPath);

				$params['province'] = $pathArr[0];
				$params['city'] = $pathArr[1];
				$params['district'] = $pathArr[2];
			}

			unset($params['code']);

			$result = $this->model->validate('app\common\validate\depot\Supplier.add')->save($params);

			if ($result) {
				$this->success();
			} else {
				$this->error($this->model->getError());
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
			$params = $this->request->post("row/a");

			if ($params['code']) {
				$parentPath = model('Region')->where(['code' => $params['code']])->value('parentpath');
				if (!$parentPath) {
					$this->error('地区选择有误');
				}
				$pathArr = explode(',', $parentPath);

				$params['province'] = $pathArr[0];
				$params['city'] = $pathArr[1];
				$params['district'] = $pathArr[2];
			}

			unset($params['code']);

			$params['id'] = $ids;

			$result = $this->model->validate('common/depot/Supplier')->isUpdate()->save($params);

			if ($result) {
				$this->success();
			} else {
				$this->error($this->model->getError());
			}
		}
		$this->assign("row", $row);
		return $this->view->fetch();
	}

}
