<?php
/**
 * @author Dracowyn
 * @since 2023-12-29 14:37
 */

namespace app\stock\controller;

use think\Controller;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Admin extends Controller
{
	protected $adminModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->adminModel = new \app\admin\model\Admin;
	}

	/**
	 * 获取管理员头像
	 * @return mixed|null
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 */
	public function avatar()
	{
		if ($this->request->isPost()) {
			$id = $this->request->param('id', 0, 'trim');

			$admin = $this->adminModel->find($id);

			if (!$admin) {
				return null;
			}

			return $admin['avatar_cdn'];
		}

		return null;
	}
}