<?php
/**
 * 评论控制器
 * @author Dracowyn
 * @since 2023-11-23 18:59
 */


namespace app\home\controller\subject;

use app\common\controller\Home;

class Comment extends Home
{
	protected $noNeedLogin = ['index'];

	// 评论模型
	protected $CommentModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->CommentModel = model('subject.Comment');
	}

	public function index()
	{
		if ($this->request->isAjax()) {
			// 接收参数
			$subid = $this->request->param('subid', 0, 'trim');
			$page = $this->request->param('page', 1, 'trim');
			$limit = $this->request->param('limit', 5, 'trim');
			$count = $this->CommentModel->where(['subid' => $subid])->count();

			$commentList = $this->CommentModel->with(['business'])->where(['subid' => $subid])->page($page, $limit)->select();

			$data = [
				'count' => $count,
				'list' => $commentList
			];

			if ($commentList) {
				$this->success('Success', null, $data);
			} else {
				$this->error('暂无数据');
			}
		}

		return $this->fetch();
	}
}