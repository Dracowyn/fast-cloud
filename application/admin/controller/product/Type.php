<?php

namespace app\admin\controller\product;

use app\common\controller\Backend;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\response\Json;

/**
 * 商品分类管理
 *
 * @icon fa fa-circle-o
 */
class Type extends Backend
{

	/**
	 * Type模型对象
	 * @var \app\common\model\product\Type
	 */
	protected $model = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->model = new \app\common\model\product\Type;

	}



	/**
	 * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
	 * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
	 * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
	 */

	// 重写添加方法
	public function add()
	{
		if ($this->request->isPost()) {
			$params = $this->request->post("row/a");
			if (empty($params)) {
				$this->error('参数错误');
			}
			// 保存
			$result = $this->model->validate('common/product/Type')->save($params);
			if ($result) {
				$thumb = ltrim($params['thumb'], '/');
				@is_file($thumb) && @unlink($thumb);
				$this->success();
			} else {
				$this->error($this->model->getError());
			}
		}
		return $this->view->fetch();
	}

	// 重写编辑方法
	public function edit($ids = null)
	{
		$row = $this->model->get($ids);
		if (!$row) {
			$this->error(__('No Results were found'));
		}

		if ($this->request->isPost()) {
			$params = $this->request->post("row/a");
			if (empty($params)) {
				$this->error('参数错误');
			}
			// 保存
			$result = $row->validate('common/product/Type')->isUpdate()->save($params);
			if ($result) {
				if ($params['thumb'] != $row['thumb']) {
					$thumb = ltrim($row['thumb'], '/');
					@is_file($thumb) && @unlink($thumb);
				}
				$this->success();
			} else {
				if ($params['thumb'] != $row['thumb']) {
					$thumb = ltrim($params['thumb'], '/');
					@is_file($thumb) && @unlink($thumb);
				}
				$this->error($row->getError());
			}
		}
		$data = [
			'row' => $row
		];
		return $this->view->fetch('', $data);
	}

	/**
	 * 重写删除方法
	 *
	 * @param $ids
	 * @return void
	 * @throws DbException
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 */
	public function del($ids = null)
	{
		if (false === $this->request->isPost()) {
			$this->error(__("Invalid parameters"));
		}
		$ids = $ids ?: $this->request->post("ids");
		if (empty($ids)) {
			$this->error(__('Parameter %s can not be empty', 'ids'));
		}
		$pk = $this->model->getPk();
		$adminIds = $this->getDataLimitAdminIds();
		if (is_array($adminIds)) {
			$this->model->where($this->dataLimitField, 'in', $adminIds);
		}
		$list = $this->model->where($pk, 'in', $ids)->select();

		$count = 0;
		Db::startTrans();
		try {
			foreach ($list as $item) {
				$count += $item->delete();
			}
			Db::commit();
		} catch (PDOException|Exception $e) {
			Db::rollback();
			$this->error($e->getMessage());
		}
		if ($count) {
			foreach ($list as $item) {
				$thumb = ltrim($item['thumb'], '/');
				@is_file($thumb) && @unlink($thumb);
			}
			$this->success();
		}
		$this->error(__('No rows were deleted'));
	}

}
