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

	// 评论模型
	protected $CommentModel = null;

	// 章节模型
	protected $ChapterModel = null;

	// 订单模型
	protected $OrderModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->SubjectModel = model('subject.Subject');
		$this->CommentModel = model('subject.Comment');
		$this->ChapterModel = model('subject.Chapter');
		$this->OrderModel = model('business.Order');
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

			$commentList = $this->CommentModel->with(['business'])->where(['subid' => $subId])->order('id asc')->limit(5)->select();
			$chapterList = $this->ChapterModel->where(['subid' => $subId])->limit(5)->order('create_time DESC')->select();

			if ($business) {
				$likeStr = explode(',', $subject['likes']);
				$likeStr = array_filter($likeStr);
				$subject['like_status'] = in_array($business['id'], $likeStr);
			}
			$this->assign([
				'subject' => $subject,
				'commentList' => $commentList,
				'chapterList' => $chapterList,
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

	// 播放视频
	public function play()
	{
		if ($this->request->isAjax()) {
			$subId = $this->request->param('subid', 0, 'trim');
			$cid = $this->request->param('cid', 0, 'trim');

			$subject = $this->SubjectModel->find($subId);

			if (!$subject) {
				$this->error('课程不存在');
			}

			$orderWhere = [
				'subid' => $subId,
				'busid' => $this->auth->id,
			];

			$order = $this->OrderModel->where($orderWhere)->find();

			if (!$order) {
				$this->error('请先购买课程', null, ['buy' => true]);
			}

			$where = [
				'subid' => $subId,
			];

			if ($cid) {
				$where['id'] = $cid;
			}

			$chapter = $this->ChapterModel->where($where)->order('id asc')->limit(1)->find();

			if ($chapter) {
				$this->success('Success', null, $chapter);
			} else {
				$this->error('暂无章节');
			}
		}
	}

	// 购买课程
	public function buy()
	{
		if ($this->request->isAjax()) {
			$subId = $this->request->param('subid', 0, 'trim');

			$subject = $this->SubjectModel->find($subId);

			if (!$subject) {
				$this->error('课程不存在');
			}

			$orderWhere = [
				'subid' => $subId,
				'busid' => $this->auth->id,
			];

			$order = $this->OrderModel->where($orderWhere)->find();

			if ($order) {
				$this->error('您已购买该课程');
			}


			// 判断用户余额是否充足
			$updatePrice = bcsub($this->auth->money, $subject['price'], 2);

			if ($updatePrice < 0) {
				$this->error('余额不足，请充值');
			}

			// 加载模型
			$businessModel = model('business.Business');
			$recordModel = model('business.Record');

			// 开启事务
			$this->OrderModel->startTrans();
			$businessModel->startTrans();
			$recordModel->startTrans();

			// 订单数据
			$orderData = [
				'subid' => $subId,
				'busid' => $this->auth->id,
				'total' => $subject['price'],
				'code' => build_order('SUB'),
			];

			$orderStatus = $this->OrderModel->validate('common/subject/Order')->save($orderData);

			if (!$orderStatus) {
				$this->error($this->OrderModel->getError());
			}

			// 更新用户余额
			$businessData = [
				'id' => $this->auth->id,
				'money' => $updatePrice
			];

			if ($this->auth->deal != 1) {
				$businessData['deal'] = 1;
			}

			// 自定义验证器
			$validate = [                // 规则
				[
					'money' => ['number', '>=:0'],
				],
				// 错误信息
				[
					'money.number' => '余额必须是数字类型',
					'money.>=' => '余额必须大于等于0元',
				]
			];

			$businessStatus = $businessModel->validate(...$validate)->isUpdate()->save($businessData);

			if (!$businessStatus) {
				$this->OrderModel->rollback();
				$this->error($businessModel->getError());
			}

			// 消费记录
			$recordData = [
				'total' => $subject['price'],
				'content' => "购买【{$subject['title']}】花费￥{$subject['price']}元",
				'busid' => $this->auth->id,
			];

			$recordStatus = $recordModel->validate('Record')->save($recordData);

			if (!$recordStatus) {
				$this->OrderModel->rollback();
				$businessModel->rollback();
				$this->error($recordModel->getError());
			}

			// 判断
			if ($orderStatus && $businessStatus && $recordStatus) {
				$this->OrderModel->commit();
				$businessModel->commit();
				$recordModel->commit();
				$this->success('购买成功');
			} else {
				$this->OrderModel->rollback();
				$businessModel->rollback();
				$recordModel->rollback();
				$this->error('购买失败');
			}
		}
	}
}