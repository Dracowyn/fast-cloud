<?php

namespace app\admin\controller\depot;

use app\common\controller\Backend;
use app\common\model\business\Address;
use app\common\model\business\Business;
use app\common\model\product\order\Order;
use app\common\model\depot\back\Product;

/**
 * 退货管理
 *
 * @icon fa fa-circle-o
 */
class Back extends Backend
{

	/**
	 * Back模型对象
	 */
	protected $model = null;

	protected $relationSearch = false;

	protected $backProductModel = null;

	protected $orderModel = null;

	protected $addressModel = null;

	protected $businessModel = null;

	protected $orderProduct = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->model = new \app\common\model\depot\back\Back;
		$this->backProductModel = new Product;
		$this->orderModel = new Order;
		$this->addressModel = new Address;
		$this->businessModel = new Business;
		$this->orderProduct = new \app\common\model\product\order\Product;
		$this->view->assign("statusList", $this->model->getStatusList());
	}

	/**
	 * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
	 * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
	 * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
	 */

	public function index()
	{
		// 设置过滤方法
		$this->request->filter(['strip_tags', 'trim']);

		// 判断是否有Ajax请求
		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}

			[$where, $sort, $order, $offset, $limit] = $this->buildparams();

			$list = $this->model
				->with(['business'])
				->where($where)
				->order($sort, $order)
				->paginate($limit);

			$result = ['total' => $list->total(), 'rows' => $list->items()];

			return json($result);
		}

		return $this->view->fetch();
	}

	// 添加退货
	public function add()
	{
		if ($this->request->isPost()) {
			$params = $this->request->param('row/a');

			// 开启事务
			$this->model->startTrans();
			$this->backProductModel->startTrans();

			// 查询订单
			$order = $this->orderModel->where(['code' => $params['ordercode']])->find();

			// 判断订单是否存在
			if (!$order) {
				$this->error('订单不存在');
			}

			// 查询订单关联商品
			$orderProducts = $this->orderProduct->where(['orderid' => $order->id])->select();

			// 判断订单是否存在商品
			if (!$orderProducts) {
				$this->error('订单不存在商品');
			}

			// 查询用户关联地址
			$address = $this->addressModel->find(['id' => $params['addressid']]);
			if (!$address) {
				$this->error('请选择联系人以及地址');
			}

			// 封装退货单数据
			$backData = [
				'code' => build_order("BP"),
				'ordercode' => $params['ordercode'],
				'busid' => $order->busid,
				'remark' => $params['remark'],
				'amount' => $order->amount,
				'status' => 0,
				'adminid' => $this->auth->id,
				'address' => $address->address,
				'contact' => $address->consignee,
				'phone' => $address->mobile,
				'province' => $address->province,
				'city' => $address->city,
				'district' => $address->district,
			];

			// 添加退货单
			$backStatus = $this->model->validate('common/depot/back/Back')->save($backData);


			// 判断退货单是否添加成功
			if (!$backStatus) {
				$this->model->rollback();
				$this->error($this->model->getError());
			}

			// 封装退货单商品数据
			$backProducts = [];

			// 循环订单商品
			foreach ($orderProducts as $item) {
				// 封装退货单商品数据
				$backProducts[] = [
					'backid' => $this->model->id,
					'proid' => $item['proid'],
					'nums' => $item['nums'],
					'price' => $item['price'],
					'total' => $item['total']
				];
			}

			// 添加退货单商品
			$backProductStatus = $this->backProductModel->validate('common/depot/back/BackProduct')->saveAll($backProducts);

			// 判断退货单商品是否添加成功
			if (!$backProductStatus) {
				$this->model->rollback();
				$this->backProductModel->rollback();
				$this->error('退货单商品添加失败');
			}

			// 大判断
			if ($backStatus && $backProductStatus) {
				// 提交事务
				$this->model->commit();
				$this->backProductModel->commit();
				$this->success('退货单添加成功');
			} else {
				// 回滚事务
				$this->model->rollback();
				$this->backProductModel->rollback();
				$this->error('退货单添加失败');
			}
		}

		return $this->view->fetch();
	}

	// 编辑退货单
	public function edit()
	{

	}

	// 查询订单
	public function order()
	{
		if ($this->request->isAjax()) {
			$code = $this->request->param('code', '');

			$order = $this->orderModel->with(['business'])->where(['code' => $code])->find();

			if (!$order) {
				$this->error('订单不存在');
			}

			$orderProduct = $this->orderProduct->with(['products'])->where(['orderid' => $order->id])->select();

			$addressList = $this->addressModel->with(['provinces','citys','districts'])->where(['busid' => $order->busid])->select();

			$this->success('查询成功', null, [
				'order' => $order,
				'orderProduct' => $orderProduct,
				'addressList' => $addressList
			]);
		}
	}


}
