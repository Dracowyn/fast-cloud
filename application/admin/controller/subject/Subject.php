<?php
/**
 * @author Dracowyn
 * @since 2023-11-28 14:21
 */

namespace app\admin\controller\subject;

use app\common\controller\Backend;

class Subject extends Backend
{
	protected $model = null;

	protected $relationSearch = true;

	protected $searchFields = 'id,title,price,category.name';

	public function __construct()
	{
		parent::__construct();
		$this->model = model('subject.Subject');

		$cateList = model('subject.Category')->order('weight DESC')->column('id,name');

		$this->assign([
			'CateList' => $cateList
		]);
	}

	// 课程列表
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

	// 添加课程
	public function add()
	{
		if ($this->request->isPost()) {
			$params = $this->request->post('row/a');

			if (empty($params)) {
				$this->error(__('Parameter %s can not be empty', ''));
			}

			$result = $this->model->validate('common/subject/subject')->save($params);

			if ($result) {
				$this->success();
			} else {
				@is_file('.' . $params['thumbs']) && @unlink('.' . $params['thumbs']);
				$this->error($this->model->getError());
			}
		}

		return $this->fetch();
	}

	// 修改课程
	public function edit($ids = null)
	{
		$ids = $ids ?: $this->request->param('ids', 0, 'trim');

		$row = $this->model->get($ids);

		if (!$row) {
			$this->error(__('No Results were found'));
		}

		if ($this->request->isPost()) {
			$params = $this->request->post('row/a');

			if (empty($params)) {
				$this->error(__('Parameter %s can not be empty', ''));
			}

			$result = $row->validate('common/subject/subject')->isUpdate()->save($params);

			if ($result) {
				if ($row['thumbs'] != $params['thumbs']) {
					@is_file('.' . $row['thumbs']) && @unlink('.' . $row['thumbs']);
				}
				$this->success();
			} else {
				if ($row['thumbs'] != $params['thumbs']) {
					@is_file('.' . $params['thumbs']) && @unlink('.' . $params['thumbs']);
				}
				$this->error($row->getError());
			}
		}

		$this->assign([
			'row' => $row
		]);

		return $this->fetch();
	}

	// 删除课程
	public function del($ids = null)
	{
		$ids = $ids ?: $this->request->param('ids', 0, 'trim');

		$list = $this->model->all($ids);

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