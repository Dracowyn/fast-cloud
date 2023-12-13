<?php

namespace app\common\model\depot;

use think\Model;


class Supplier extends Model
{

	// 表名
	protected $name = 'depot_supplier';

	// 自动写入时间戳字段
	protected $autoWriteTimestamp = 'integer';

	// 定义时间戳字段名
	protected $createTime = 'createtime';
	protected $updateTime = false;
	protected $deleteTime = false;

	// 追加属性
	protected $append = [

	];

	// 一对一关联省份
	public function provinces()
	{
		return $this->belongsTo('app\common\model\Region','province','code',[],'LEFT')->setEagerlyType(0);
	}

	public function citys()
	{
		return $this->belongsTo('app\common\model\Region','city','code',[],'LEFT')->setEagerlyType(0);
	}

	public function districts()
	{
		return $this->belongsTo('app\common\model\Region','district','code',[],'LEFT')->setEagerlyType(0);
	}

}
