<?php
/**
 * 客户回收站
 * @author Dracowyn
 * @since 2023-12-04 17:54
 */

namespace app\admin\controller\business;

use app\admin\model\Admin;
use app\common\controller\Backend;
use app\common\model\business\Business;
use think\Request;

class Recyclesea extends Backend
{
	protected $relationSearch = true;

	protected $searchFields = 'id,nickname,source.name';

	protected $model = null;

	protected $adminModel = null;

	public function _initialize()
	{
		parent:: _initialize();
		$this->model = new Business;
		$this->adminModel = new Admin;
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
				->onlyTrashed()
				->with(['source', 'admin'])
				->where($where)
				->order($sort, $order)
				->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];
			return json($result);
		}
		return $this->view->fetch();
	}

	// 恢复客户
	public function restore($ids = null)
	{
		$ids = empty($ids) ? [] : explode(',', $ids);
		foreach ($ids as $item) {
			$row = $this->model->onlyTrashed()->find($item);
			if (!$row) {
				$this->error(__('没有找到该客户'));
			}
		}

		$result = $this->model->onlyTrashed()->where('id', 'in', $ids)->update(['delete_time' => null]);

		if ($result) {
			$this->success();
		} else {
			$this->error($this->model->getError());
		}
	}

	// 删除客户
	public function destroy($ids = null)
	{
		$ids = empty($ids) ? [] : explode(',', $ids);

		// 客户头像列表
		$avatarList = [];

		foreach ($ids as $item) {
			$row = $this->model->onlyTrashed()->find($item);
			if (!$row) {
				$this->error(__('没有找到该客户'));
			}
			// 客户头像
			$avatarList[] = $row['avatar'];
		}

		$result = $this->model->onlyTrashed()->where('id', 'in', $ids)->delete(true);

		if ($result) {
			// 删除客户头像
			foreach ($avatarList as $item) {
				$src = substr($item, 1);
				@is_file($src) && @unlink($src);
			}
			$this->success();
		} else {
			$this->error($this->model->getError());
		}
	}

}