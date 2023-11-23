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
				$map['title'] = ['like',"%$keyword%"];
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
	public function info($subId = null) {
		$subId = $subId ?: $this->request->param('subid',0,'trim');

		$subject = $this->SubjectModel->find($subId);

		if ($subject) {
			$this->assign([
				'subject' => $subject
			]);
			return $this->fetch();
		} else {
			$this->error('课程不存在');
		}
	}
}