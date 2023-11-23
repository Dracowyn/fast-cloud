<?php
/**
 * 课程控制器
 *
 * @author Dracowyn
 * @since 2023-11-23 15:22
 */

namespace app\home\controller\subject;

use app\common\controller\Home;

class Subject extends Home
{
	protected $noNeedLogin = ['search', 'info'];

	// 课程模型
	protected $SubjectModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->SubjectModel = model('subject.Subject');
	}

	// 搜索课程
	public function search()
	{
		if ($this->request->isAjax()) {
			$page = $this->request->param('page', 1, 'trim');
			$limit = $this->request->param('limit', 10, 'trim');
			$keyword = $this->request->param('search', '', 'trim');

			// 条件数组
			$map = [];

			if ($keyword) {
				$map['title'] = ['like', "%$keyword%"];
			}

			$subjectData = $this->SubjectModel->with(['category'])->where($map)->page($page, $limit)->order('create_time DESC')->select();
			$subjectCount = $this->SubjectModel->with(['category'])->where($map)->count();

			$data = [
				'SubjectData' => $subjectData,
				'SubjectCount' => $subjectCount
			];

			if ($subjectData) {
				$this->success('查询课程数据成功', null, $data);
			} else {
				$this->error('暂无课程');
			}
		}

		return $this->fetch();
	}

	// 课程详情
	public function info($subId = null)
	{
		$subId = $subId ?: $this->request->param('subid', 0, 'trim');

		$subject = $this->SubjectModel->find($subId);


		if ($subject) {
			// 获取登录信息
			$loginInfo = cookie('business') ? cookie('business') : [];
			$busId = $loginInfo['id'] ?? 0;
			$mobile = $loginInfo['mobile'] ?? '';

			// 默认点赞状态
			$subject['like_status'] = false;
			$business = model('business.Business')->where(['id' => $busId, 'mobile' => $mobile])->find();

			if ($business) {
				$likeStr = explode(',', $subject['likes']);
				$likeStr = array_filter($likeStr);
				$subject['like_status'] = in_array($business['id'], $likeStr);
			}
			$this->assign([
				'subject' => $subject
			]);
			return $this->fetch();
		} else {
			$this->error('课程不存在');
		}
	}

	public function like()
	{
		if ($this->request->isAjax()) {
			$subId = $this->request->param('subid', 0, 'trim');

			// 查询数据表是否有该课程
			$subject = $this->SubjectModel->find($subId);

			if (!$subject) {
				$this->error('课程不存在');
			}

			// 字符串转数组
			$likeStr = explode(',', $subject['likes']);
			$likeStr = array_filter($likeStr);

			if (in_array($this->auth->id, $likeStr)) {
				// 取消点赞
				$key = array_search($this->auth->id, $likeStr);
				if (!array_key_exists($key, $likeStr)) {
					$this->error('未找到点赞记录');
				}
				unset($likeStr[$key]);

				$msg = '取消点赞';
			} else {
				// 点赞
				$likeStr[] = $this->auth->id;

				$msg = '点赞';
			}

			$data = [
				'id' => $subId,
				'likes' => implode(',', $likeStr)
			];

			$result = $this->SubjectModel->isUpdate()->save($data);

			if ($result) {
				$this->success($msg . '成功');
			} else {
				$this->error($msg . '失败');
			}
		}
	}
}