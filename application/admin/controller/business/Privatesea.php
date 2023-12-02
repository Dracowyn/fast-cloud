<?php

namespace app\admin\controller\business;

use app\admin\model\Admin;
use app\common\controller\Backend;
use app\common\model\business\Address;
use app\common\model\business\Business;
use app\common\model\business\Receive;
use app\common\model\business\Visit;

/**
 * 客户私海
 *
 * @icon fa fa-circle-o
 */
class Privatesea extends Backend
{

	/**
	 * 客户模型对象
	 */
	protected $model = null;

	/*
 * 客户申领模型
 */
	protected $receiveModel = null;

	/*
	 * 客户回访模型
	 */
	protected $visitModel = null;

	/*
	 * 管理员模型
	 */
	protected $adminModel = null;

	/*
	 * 客户地址模型
	 */
	protected $addressModel = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->model = new Business;
		$this->receiveModel = new Receive;
		$this->visitModel = new Visit;
		$this->adminModel = new Admin;
		$this->addressModel = new Address;
		$this->assign('genderList', $this->model->getGenderList());
		$this->assign('dealList', $this->model->getDealList());
		$this->assign('authList', $this->model->getAuthList());
	}

	public function index()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}

			[$where, $sort, $order, $offset, $limit] = $this->buildparams();

			$list = $this->model
				->with(['source', 'admin'])
				->where($where)
				->where('adminid', $this->auth->id)
				->order($sort, $order)
				->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];
			return json($result);
		}
		return $this->view->fetch();
	}

	public function add()
	{
		if ($this->request->isPost()) {
			$param = $this->request->post('row/a');

			// 密码加密
			$salt = build_randstr(6);

			$password = md5(md5($param['password']) . $salt);

			$data = [
				'nickname' => $param['nickname'],
				'mobile' => $param['mobile'],
				'email' => $param['email'],
				'password' => $password,
				'salt' => $salt,
				'avatar' => $param['avatar'],
				'sourceid' => $param['sourceid'],
				'adminid' => $this->auth->id,
				'gender' => $param['gender'],
				'auth' => $param['auth'],
				'money' => $param['money']
			];

			// 判断地区编码是否存在
			$this->findCode($param, $data);

			// 开启事务
			$this->model->startTrans();
			$this->receiveModel->startTrans();

			$businessStatus = $this->model->validate('common/business/Business.register')->save($data);

			if (!$businessStatus) {
				$this->model->rollback();
				$this->error($this->model->getError());
			}

			$receiveData = [
				'applyid' => $this->auth->id,
				'status' => 'allot',
				'busid' => $this->model->id,
			];

			$receiveStarts = $this->receiveModel->validate('common/business/Receive')->save($receiveData);

			if (!$receiveStarts && !$businessStatus) {
				$this->model->rollback();
				$this->receiveModel->rollback();
				// 删除头像
				if ($data['avatar']) {
					@is_file($data['avatar']) && @unlink($data['avatar']);
				}
				$this->error($this->receiveModel->getError());
			} else {
				$this->model->commit();
				$this->receiveModel->commit();
				$this->success();
			}
		}
		return $this->view->fetch();
	}

	public function edit($ids = null)
	{
		// 判断客户是否存在
		$row = $this->model->find($ids);

		if (!$row) {
			$this->error('客户不存在');
		}

		if ($this->request->isPost()) {
			$param = $this->request->param('row/a');

			$data = [
				'id' => $ids,
				'nickname' => $param['nickname'],
				'mobile' => $param['mobile'],
				'email' => $param['email'],
				'avatar' => $param['avatar'],
				'sourceid' => $param['sourceid'],
				'gender' => $param['gender'],
				'auth' => $param['auth'],
				'money' => $param['money'],
				'deal' => $param['deal']
			];

			// 修改密码
			if (!empty($param['password'])) {
				$salt = build_randstr(6);
				$password = md5(md5($param['password']) . $salt);
				$data['password'] = $password;
				$data['salt'] = $salt;
			}

			// 判断地区编码是否存在
			$this->findCode($param, $data);

			$result = $this->model->validate('common/business/Business.profile')->isUpdate()->save($data);

			if ($result) {
				$this->success();
			} else {
				// 删除头像
				if ($data['avatar']) {
					@is_file($data['avatar']) && @unlink($data['avatar']);
				}
				$this->error($this->model->getError());
			}
		}

		// 处理地区数据回显
		$row['regionCode'] = $row['district'] ?: ($row['city'] ?: $row['province']);

		$this->assign([
			'row' => $row
		]);

		return $this->view->fetch();
	}

	/**
	 * 查找地区编码
	 * @param $param
	 * @param array $data
	 * @return void
	 */
	public function findCode($param, array &$data): void
	{
		if (!empty($param['code'])) {
			$parentPath = $this->regionModel->where('code', $param['code'])->value('parentpath');
			if (!$parentPath) {
				$this->error('地区编码错误');
			}

			$path = explode(',', $parentPath);

			$province = $path[0] ?? null;
			$city = $path[1] ?? null;
			$district = $path[2] ?? null;

			$data['province'] = $province;
			$data['city'] = $city;
			$data['district'] = $district;
		}
	}
}
