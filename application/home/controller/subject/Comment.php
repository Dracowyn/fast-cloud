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

	// 订单模型
	protected $OrderModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->CommentModel = model('subject.Comment');
		$this->OrderModel = model('business.Order');
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

	// 增加评论
	public function add()
	{
		$orderId = $this->request->param('orderid', 0, 'trim');
		$order = $this->OrderModel->where(['id' => $orderId])->find();

		if (!$order) {
			$this->error('订单不存在');
		}

		// 判断是否已经评论过
		$isComment = [
			'busid' => $this->auth->id,
			'subid' => $order['subid']
		];
		$comment = $this->CommentModel->where($isComment)->find();

		$this->assign([
			'order' => $order,
			'comment' => $comment
		]);


		if ($comment) {
			$this->error('您已经评论过了');
		}

		if ($this->request->isPost()) {
			// 接收参数
			$content = $this->request->param('content', '', 'trim');
			$data = [
				'busid' => $this->auth->id,
				'subid' => $order['subid'],
				'content' => $content
			];
			$result = $this->CommentModel->validate('common/subject/Comment')->save($data);
			if ($result) {
				$this->success('评论成功', url('home/business/index'));
			} else {
				$this->error('评论失败');
			}
		}
		return $this->fetch();
	}
}