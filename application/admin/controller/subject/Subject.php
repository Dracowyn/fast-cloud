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

	public function __construct()
	{
		parent::__construct();
		$this->model = model('subject.Subject');
	}

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


}