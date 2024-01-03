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

	protected $storageModel = null;

	protected $storageProductModel = null;

	public function _initialize()
	{
		parent::_initialize();
		$this->model = new \app\common\model\depot\back\Back;
		$this->backProductModel = new Product;
		$this->orderModel = new Order;
		$this->addressModel = new Address;
		$this->businessModel = new Business;
		$this->orderProduct = new \app\common\model\product\order\Product;
		$this->storageModel = new \app\common\model\depot\storage\Storage;
		$this->storageProductModel = new \app\common\model\depot\storage\Product;
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
	public function edit($ids = null)
	{
		$row = $this->model->with(['business'])->find($ids);
		if (!$row) {
			$this->error(__('No Results were found'));
		}

		if ($this->request->isPost()) {
			$params = $this->request->param('row/a');

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
			if (!$orderProducts) {
				$this->error('订单不存在商品');
			}

			// 封装退货单数据
			$backData = [
				'id' => $ids,
				'code' => $order->code,
				'ordercode' => $params['ordercode'],
				'busid' => $order->busid,
				'remark' => $params['remark'],
				'amount' => $order->amount,
				'status' => $row['status'],
				'adminid' => $this->auth->id,
			];

			// 查询地址
			$address = $this->addressModel->where(['id' => $params['addressid']])->find();

			if (!$address) {
				$this->error('请选择联系人以及地址');
			}

			// 合并地址数据
			$backData = array_merge($backData, [
				'address' => $address->address,
				'contact' => $address->consignee,
				'phone' => $address->mobile,
				'province' => $address->province,
				'city' => $address->city,
				'district' => $address->district,
			]);

			// 更新退货单
			$backStatus = $this->model->validate('common/depot/back/Back')->isUpdate()->save($backData);

			if (!$backStatus) {
				$this->model->rollback();
				$this->error($this->model->getError());
			}

			// 默认退货订单状态
			$backProductStatus = true;

			// 判断单号是否修改
			if ($params['ordercode'] !== $row['ordercode']) {
				// 封装退货单商品数据
				$backProducts = [];
				foreach ($orderProducts as $item) {
					$backProducts[] = [
						'backid' => $this->model->id,
						'proid' => $item['proid'],
						'nums' => $item['nums'],
						'price' => $item['price'],
						'total' => $item['total']
					];
				}

				// 保存退货单商品
				$backProductStatus = $this->backProductModel->validate('common/depot/back/BackProduct')->saveAll($backProducts);
			}

			if (!$backProductStatus) {
				$this->model->rollback();
				$this->backProductModel->rollback();
				$this->error($this->backProductModel->getError());
			}

			if ($backStatus && $backProductStatus) {
				$this->model->commit();
				$this->backProductModel->commit();
				$this->success('退货单修改成功');
			} else {
				$this->model->rollback();
				$this->backProductModel->rollback();
				$this->error('退货单修改失败');
			}
		}

		$addressWhere = [
			'busid' => $row->busid,
			'consignee' => $row->contact,
			'mobile' => $row->phone,
			'address' => $row->address,
			'province' => $row->province,
			'city' => $row->city,
			'district' => $row->district,
		];

		$addressId = $this->addressModel->where($addressWhere)->value('id');

		$row['addressid'] = $addressId;

		$backProductList = $this->backProductModel->with(['products'])->where(['backid' => $row['id']])->select();

		$addressData = $this->addressModel->with(['provinces', 'citys', 'districts'])->where(['busid' => $row->busid])->select();

		// 封装下拉地址数据
		$addressList = [];

		foreach ($addressData as $key => $item) {
			$addressList[$item['id']] = "联系人：{$item['consignee']} 联系方式：{$item['mobile']} 地址：{$item['provinces']['name']}-{$item['citys']['name']}-{$item['districts']['name']} {$item['address']}";
		}

		$this->assignconfig('back', ['backProductList' => $backProductList]);

		$data = [
			'row' => $row,
			'addressList' => $addressList,
		];

		return $this->view->fetch('', $data);
	}

	// 退货单详情
	public function detail($ids = null)
	{
		$row = $this->model->with(['business'])->find($ids);
		if (!$row) {
			$this->error(__('No Results were found'));
		}

		$backProductList = $this->backProductModel->with(['products'])->where(['backid' => $row['id']])->select();

		$data = [
			'row' => $row,
			'backProductList' => $backProductList,
		];

		return $this->view->fetch('', $data);
	}

	// 退货单审核通过
	public function process()
	{
		if ($this->request->isAjax()) {
			$ids = $this->request->param('ids', '');

			$back = $this->model->find($ids);

			if (!$back) {
				$this->error('退货单不存在');
			}


			$back->status = '1';
			$back->reviewerid = $this->auth->id;

			$status = $back->save();

			if (!$status) {
				$this->error('审核失败');
			}

			$this->success('审核成功');
		}
	}

	// 撤销审核
	public function cancel()
	{
		if ($this->request->isAjax()) {
			$ids = $this->request->param('ids', '');

			$back = $this->model->find($ids);

			if (!$back) {
				$this->error('退货单不存在');
			}

			$back->status = '0';
			$back->reviewerid = $this->auth->id;

			$status = $back->save();

			if (!$status) {
				$this->error('撤销失败');
			}

			$this->success('撤销成功');
		}
	}

	// 确认收货
	public function receipt()
	{
		if ($this->request->isAjax()) {
			$ids = $this->request->param('ids', '');

			$back = $this->model->find($ids);

			if (!$back) {
				$this->error('退货单不存在');
			}

			// 查询订单
			$order = $this->orderModel->where(['code' => $back->ordercode])->find();

			if (!$order) {
				$this->error('订单不存在');
			}

			// 查询用户
			$business = $this->businessModel->find($order->busid);

			if (!$business) {
				$this->error('用户不存在');
			}

			// 开启事务
			$this->model->startTrans();
			$this->businessModel->startTrans();
			$this->orderModel->startTrans();

			// 更新退货单状态
			$back->status = '2';
			$backStatus = $back->save();

			if (!$backStatus) {
				$this->model->rollback();
				$this->error($this->model->getError());
			}

			// 更新用户余额
			$business->money = bcadd($business->money, $back->amount, 2);

			$businessStatus = $business->save();

			if (!$businessStatus) {
				$this->model->rollback();
				$this->businessModel->rollback();
				$this->error($this->businessModel->getError());
			}

			// 更新订单状态
			$order->status = '-4';
			$orderStatus = $order->save();

			if (!$orderStatus) {
				$this->model->rollback();
				$this->businessModel->rollback();
				$this->orderModel->rollback();
				$this->error($this->orderModel->getError());
			}

			if ($backStatus && $businessStatus && $orderStatus) {
				$this->model->commit();
				$this->businessModel->commit();
				$this->orderModel->commit();
				$this->success('确认收货成功');
			} else {
				$this->model->rollback();
				$this->businessModel->rollback();
				$this->orderModel->rollback();
				$this->error('确认收货失败');
			}
		}
	}

	// 确认入库
	public function storage()
	{
		if ($this->request->isAjax()) {
			$ids = $this->request->param('ids', '');

			$back = $this->model->find($ids);

			if (!$back) {
				$this->error('退货单不存在');
			}

			// 查询退货单商品列表
			$orderProducts = $this->backProductModel->where(['backid' => $back->id])->select();
			if (!$orderProducts) {
				$this->error('退货单不存在商品');
			}

			// 开启事务
			$this->model->startTrans();
			$this->storageModel->startTrans();
			$this->storageProductModel->startTrans();

			// 封装入库数据
			$storageData = [
				'code' => build_order('SU'),
				'type' => 2,
				'amount' => $back['amount'],
				'status' => '0'
			];

			// 添加入库单
			$storageStatus = $this->storageModel->validate('common/depot/storage/Storage.back')->save($storageData);

			if (!$storageStatus) {
				$this->model->rollback();
				$this->error($this->storageModel->getError());
			}

			// 封装入库商品数据
			$storageProducts = [];

			foreach ($orderProducts as $item) {
				$storageProducts[] = [
					'storageid' => $this->storageModel->id,
					'proid' => $item['proid'],
					'nums' => $item['nums'],
					'price' => $item['price'],
					'total' => $item['total']
				];
			}

			// 添加入库商品
			$storageProductStatus = $this->storageProductModel->validate('common/depot/storage/Product')->saveAll($storageProducts);

			if (!$storageProductStatus) {
				$this->model->rollback();
				$this->storageModel->rollback();
				$this->error($this->storageProductModel->getError());
			}

			// 更新退货单状态
			$backData = [
				'status' => '3',
				'stromanid' => $this->auth->id,
				'storageid' => $this->storageModel->id,
			];

			$backStatus = $back->save($backData);

			if (!$backStatus) {
				$this->model->rollback();
				$this->storageModel->rollback();
				$this->storageProductModel->rollback();
				$this->error($this->model->getError());
			}

			if ($storageStatus && $storageProductStatus && $backStatus) {
				$this->model->commit();
				$this->storageModel->commit();
				$this->storageProductModel->commit();
				$this->success('确认入库成功');
			} else {
				$this->model->rollback();
				$this->storageModel->rollback();
				$this->storageProductModel->rollback();
				$this->error('确认入库失败');
			}
		}
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

			$addressList = $this->addressModel->with(['provinces', 'citys', 'districts'])->where(['busid' => $order->busid])->select();

			$this->success('查询成功', null, [
				'order' => $order,
				'orderProduct' => $orderProduct,
				'addressList' => $addressList
			]);
		}
	}


}
