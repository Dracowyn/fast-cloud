<?php

namespace app\common\model\product;

use think\Model;
use traits\model\SoftDelete;

class Product extends Model
{

	use SoftDelete;

	// 表名
	protected $name = 'product';

	// 自动写入时间戳字段
	protected $autoWriteTimestamp = 'integer';

	// 定义时间戳字段名
	protected $createTime = 'create_time';
	protected $updateTime = 'update_time';
	protected $deleteTime = 'delete_time';

	// 追加属性
	protected $append = [
		'flag_text',
		'status_text'
	];


	public function getFlagList()
	{
		return ['1' => __('新品'), '2' => __('热销'), '3' => __('推荐')];
	}

	public function getStatusList()
	{
		return ['0' => __('下架'), '1' => __('上架')];
	}


	public function getFlagTextAttr($value, $data)
	{
		$value = $value ? $value : ($data['flag'] ?? '');
		$list = $this->getFlagList();
		return $list[$value] ?? '';
	}


	public function getStatusTextAttr($value, $data)
	{
		$value = $value ? $value : ($data['status'] ?? '');
		$list = $this->getStatusList();
		return $list[$value] ?? '';
	}

	public function type()
	{
		return $this->belongsTo('app\common\model\product\Type', 'typeid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	public function unit()
	{
		return $this->belongsTo('app\common\model\product\Unit', 'unitid', 'id', [], 'LEFT')->setEagerlyType(0);
	}


}
