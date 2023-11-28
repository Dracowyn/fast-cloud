<?php
/**
 * @author Dracowyn
 * @since 2023-11-28 16:30
 */

namespace app\admin\controller\subject;

use app\common\controller\Backend;

class Category extends Backend
{
	protected $model = null;

	protected $relationSearch = true;
	protected $searchFields = 'id,name,weight';

	public function __construct()
	{
		parent::__construct();
		$this->model = model('subject.Category');
	}

	// 分类列表
	public function index()
	{
		$this->request->filter(['strip_tags', 'trim']);

		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}

			[$where, $sort, $order, $offset, $limit] = $this->buildparams();

			$list = $this->model->where($where)->order($sort, $order)->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];

			return json($result);
		}
		return $this->fetch();
	}

	// 添加分类
	public function add()
	{
		if ($this->request->isPost()) {
			$params = $this->request->post('row/a');

			if (empty($params)) {
				$this->error(__('Parameter %s can not be empty', ''));
			}


			// 添加数据
			$result = $this->model->validate('common/subject/Category')->save($params);

			if ($result) {
				$this->success();
			} else {
				$this->error($this->model->getError());
			}
		}
		return $this->fetch();
	}

	// 编辑分类
	public function edit($ids = null)
	{
		$ids = $ids ?? $this->request->param('ids/a');
		$row = $this->model->get($ids);

		if (!$row) {
			$this->error(__('No Results were found'));
		}

		$this->assign([
			'row' => $row
		]);

		if ($this->request->isPost()) {
			$params = $this->request->post('row/a');

			if (empty($params)) {
				$this->error(__('Parameter %s can not be empty', ''));
			}

			$params['id'] = $ids;

			// 更新数据
			$result = $this->model->validate('common/subject/Category')->isUpdate()->save($params);

			if ($result) {
				$this->success();
			} else {
				$this->error($this->model->getError());
			}
		}
		return $this->fetch();

	}

	// 删除分类
	public function del($ids = null)
	{
		$ids = $ids ?: $this->request->param('ids', 0, 'trim');
		$list = $this->model->all($ids);

		if (empty($list)) {
			$this->error('课程分类不存在');
		}

		$result = $this->model->destroy($ids);
		if ($result) {
			$this->success();
		} else {
			$this->error($this->model->getError());
		}
	}
}