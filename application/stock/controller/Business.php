<?php
/**
 * @author Dracowyn
 * @since 2024-01-05 17:41
 */


namespace app\stock\controller;

use think\Controller;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Env;
use think\exception\DbException;
use think\response\Json;


class Business extends Controller
{
	protected $adminModel = null;

	protected $businessModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->adminModel = new \app\common\model\admin\Admin;
		$this->businessModel = new \app\common\model\business\Business;
	}


	/**
	 * 获取客户头像
	 * @return mixed|null
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 */
	public function avatar()
	{
		if ($this->request->isPost()) {
			$id = $this->request->param('id', 0, 'trim');

			$business = $this->businessModel->find($id);

			if (!$business) {
				return null;
			}

			return $business['avatar_cdn'];
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
	public function upload(): ?Json
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

			if (!$result) {
				return json([
					'code' => 500,
					'msg' => '上传头像失败',
					'data' => null,
				]);
			} else {
				$cdn = Env::get('site.cdn_url', config('site.cdn_url')) ?? Env::get('site.url', config('site.url'));
				$avatarCDN = $cdn . $result['data'];
				$data = [
					'avatar' => $result['data'],
					'avatar_cdn' => $avatarCDN,
				];
				return json([
					'code' => 0,
					'msg' => '上传头像成功',
					'data' => $data,
				]);
			}
		}
		return null;
	}
}