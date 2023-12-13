<?php

namespace app\common\model\depot;

use think\Model;
use traits\model\SoftDelete;

class Back extends Model
{

	use SoftDelete;


	// 表名
	protected $name = 'depot_back';

	// 自动写入时间戳字段
	protected $autoWriteTimestamp = 'integer';

	// 定义时间戳字段名
	protected $createTime = 'createtime';
	protected $updateTime = false;
	protected $deleteTime = 'deletetime';

	// 追加属性
	protected $append = [
		'status_text'
	];


//0：未审核
//1：已审核，未收货
//2：已收货，未入库
//3：已入库，生成入库单记录
//-1：审核不通过',
	public function getStatusList()
	{
		return ['0' => __('未审核'), '1' => __('未收货'), '2' => __('未入库'), '3' => __('已入库'), '-1' => __('未通过')];
	}


	public function getStatusTextAttr($value, $data)
	{
		$value = $value ?: ($data['status'] ?? '');
		$list = $this->getStatusList();
		return $list[$value] ?? '';
	}


}
