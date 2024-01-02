<?php
/**
 * 订单控制器
 * @author Dracowyn
 * @since 2024-01-02 17:21
 */

namespace app\admin\controller\product;

use app\common\controller\Backend;
use think\Exception;
use think\exception\DbException;
use think\response\Json;

class Order extends Backend
{
	protected $relationSearch = true;

	protected $searchFields = ['code', 'business.nickname', 'express.name', 'expresscode'];

	protected $model = null;

	protected $businessModel = null;

	protected $orderProductModel = null;

	public function __construct()
	{
		parent::__construct();
		$this->model = new \app\common\model\product\order\Order;
		$this->businessModel = new \app\common\model\business\Business;
		$this->orderProductModel = new \app\common\model\product\order\Product;
		$this->view->assign("statusList", $this->model->getStatusList());
	}

	public function index()
	{
		$this->request->filter(['strip_tags', 'trim']);

		if ($this->request->isAjax()) {
			if ($this->request->request('keyField')) {
				return $this->selectpage();
			}
			list($where, $sort, $order, $offset, $limit) = $this->buildparams();

			$list = $this->model
				->with(['business', 'express'])
				->where($where)
				->order($sort, $order)
				->paginate($limit);

			$result = array("total" => $list->total(), "rows" => $list->items());

			return json($result);
		}
		return $this->view->fetch();
	}

	// 订单详情
	public function info($ids = null)
	{
		$row = $this->model
			->with(['business', 'express', 'address' => ['provinces', 'citys', 'districts'], 'sale', 'review', 'dispatched'])
			->find($ids);

		if (!$row) {
			$this->error(__('No Results were found'));
		}

		$orderProductData = $this->orderProductModel->with(['products'])->where('orderid', $ids)->select();

		$this->view->assign([
			'row' => $row,
			'orderProductData' => $orderProductData,
		]);

		return $this->view->fetch();
	}
}