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
}