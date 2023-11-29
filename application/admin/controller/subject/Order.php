<?php
/**
 * @author Dracowyn
 * @since 2023-11-28 17:45
 */

namespace app\admin\controller\subject;

use app\common\controller\Backend;

class Order extends Backend
{
	protected $model = null;

	protected $relationSearch = true;
	protected $searchFields = ['code', 'total', 'subject.title', 'business.nickname'];

	public function __construct()
	{
		parent::__construct();
		$this->model = model('business.Order');
	}

	// 订单列表
	public function index()
	{
		$this->request->filter(['strip_tags', 'trim']);

		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}

			[$where, $sort, $order, $offset, $limit] = $this->buildparams();

			$list = $this->model->with(['subject','business'])->where($where)->order($sort, $order)->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];

			return json($result);
		}
		return $this->fetch();
	}

	// 删除订单
	public function del($ids = null)
	{
		$ids = $ids ?: $this->request->param('ids', 0, 'trim');
		$row = $this->model->all($ids);

		if (!$row) {
			$this->error('订单不存在');
		}

		$result = $this->model->destroy($ids);

		if ($result) {
			$this->success();
		} else {
			$this->error($this->model->getError());
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

			$list = $this->model->onlyTrashed()->with(['subject','business'])->where($where)->order($sort, $order)->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];

			return json($result);
		}

		return $this->fetch();
	}

	// 还原
	public function restore($ids = null)
	{
		$ids = $ids ?: $this->request->param('ids', 0, 'trim');

		$list = $this->model->onlyTrashed()->select($ids);

		if (!$list) {
			$this->error(__('No Results were found'));
		}

		$result = $this->model->onlyTrashed()->where(['id' => ['IN', $ids]])->update(['delete_time' => null]);

		if ($result) {
			$this->success('还原成功');
		} else {
			$this->error($this->model->getError());
		}
	}

	// 彻底删除
	public function destroy($ids = null)
	{
		$ids = $ids ?: $this->request->param('ids', 0, 'trim');

		$list = $this->model->onlyTrashed()->select($ids);

		if (!$list) {
			$this->error(__('No Results were found'));
		}

		$result = $this->model->destroy($ids);

		if ($result) {
			$this->success('删除成功');
		} else {
			$this->error($this->model->getError());
		}
	}
}