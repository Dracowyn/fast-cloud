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
    }



}
