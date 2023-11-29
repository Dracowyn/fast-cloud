<?php
/**
 * 章节管理控制器
 * @author Dracowyn
 * @since 2023-11-29 14:21
 */

namespace app\admin\controller\subject;

use app\common\controller\Backend;

class Chapter extends Backend
{
	protected $model = null;

	protected $searchFields = 'title';
	public function __construct()
	{
		parent::__construct();
		$this->model = model('subject.Chapter');
	}

	// 章节列表
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

	// 添加章节
	public function add()
	{
		$subid = $this->request->param('subid', 0, 'trim');

		if ($this->request->isPost()) {
			$params = $this->request->post('row/a');

			if (empty($params)) {
				$this->error(__('Parameter %s can not be empty', ''));
			}

			$subject = model('subject.Subject')->find($subid);
			if (!$subject) {
				$this->error('课程不存在');
			}

			$params['subid'] = $subid;

			$result = $this->model->validate('common/subject/Chapter')->save($params);

			if ($result) {
				$this->success();
			} else {
				@is_file('.' . $params['url']) && @unlink('.' . $params['url']);
				$this->error($this->model->getError());
			}
		}

		return $this->fetch();
	}

	// 修改章节
}