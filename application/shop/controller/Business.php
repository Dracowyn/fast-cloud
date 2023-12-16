<?php
/**
 * @author Dracowyn
 * @since 2023-12-16 9:57
 */

namespace app\shop\controller;

use think\Controller;

class Business extends Controller
{
	protected $businessModel = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->businessModel = new \app\common\model\business\Business;
	}

	public function avatar()
	{
		$id = $this->request->param('id', 0, 'trim');
		$business = $this->businessModel->where('id', $id)->find();
		if (!$business) {
			return json(['msg' => '用户不存在', 'code' => 0]);
		}
		return json(['msg' => '获取用户头像成功', 'code' => 1, 'data' => ['avatar' => $business['avatar_cdn']]]);
	}

	public function upload()
	{
		$id = $this->request->param('id', 0, 'trim');
		$business = $this->businessModel->find($id);
		if (!$business) {
			return json(['msg' => '用户不存在', 'code' => 0, 'data' => null]);
		}
		$avatar = build_upload('avatar');
		return json($avatar);
	}

	public function del()
	{
		$id = $this->request->param('id', 0, 'trim');
		$avatar = $this->request->param('avatar', '', 'trim');
		$business = $this->businessModel->find($id);
		if (!$business) {
			return json(['msg' => '用户不存在', 'code' => 0, 'data' => null]);
		}
		@is_file('.' . $avatar) && @unlink('.' . $avatar);
		return json(['msg' => '删除头像成功', 'code' => 1, 'data' => null]);
	}
}

