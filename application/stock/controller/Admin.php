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
use think\response\Json;

class Admin extends Controller
{
	protected $adminModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->adminModel = new \app\common\model\admin\Admin();
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

	/**
	 * 上传管理员头像
	 * @return Json|null
	 * @throws DataNotFoundException
	 * @throws DbException
	 * @throws ModelNotFoundException
	 */
	public function upload()
	{
		if ($this->request->isPost()) {
			$adminId = $this->request->param('adminid', 0, 'trim');

			$admin = $this->adminModel->find($adminId);

			if (!$admin) {
				return json([
					'code' => 404,
					'msg' => '管理员不存在',
					'data' => null,
				]);
			}

			if ($admin['status'] !== 'normal') {
				return json([
					'code' => 403,
					'msg' => '账号已被禁用',
					'data' => null,
				]);
			}

			$result = build_upload('avatar');

			$data = [
				'id' => $admin->id,
				'avatar' => $result['data'],
			];

			$res = $this->adminModel->isUpdate()->save($data);

			if ($res === false) {
				@is_file('.' . $res['data']) && @unlink('.' . $res['data']);
				return json([
					'code' => 500,
					'msg' => '更新头像失败',
					'data' => null,
				]);
			} else {
				@is_file('.' . $admin['avatar']) && @unlink('.' . $admin['avatar']);
				return json([
					'code' => 0,
					'msg' => '更新头像成功',
					'data' => $result['data'],
				]);
			}
		}
		return null;
	}
}