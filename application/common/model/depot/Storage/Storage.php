<?php

namespace app\common\model\depot\Storage;

use think\Model;
use traits\model\SoftDelete;

class Storage extends Model
{
	use SoftDelete;

	// 表名
	protected $name = 'depot_storage';

	// 自动写入时间戳字段
	protected $autoWriteTimestamp = 'integer';

	// 定义时间戳字段名
	protected $createTime = 'createtime';
	protected $updateTime = false;
	protected $deleteTime = 'deletetime';

	// 追加属性
	protected $append = [
		'type_text',
		'status_text'
	];


	public function getTypeList()
	{
		return ['1' => __('直销入库'), '2' => __('退货入库')];
	}

	public function getStatusList()
	{
		return ['0' => __('待审批'), '1' => __('审批失败'), '2' => __('待入库'), '3' => __('入库完成')];
	}


	public function getTypeTextAttr($value, $data)
	{
		$value = $value ?: ($data['type'] ?? '');
		$list = $this->getTypeList();
		return $list[$value] ?? '';
	}


	public function getStatusTextAttr($value, $data)
	{
		$value = $value ?: ($data['status'] ?? '');
		$list = $this->getStatusList();
		return $list[$value] ?? '';
	}

	/**
	 * 关联查询 供应商
	 */
	public function supplier()
	{
		return $this->belongsTo('app\common\model\depot\Supplier', 'supplierid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	/**
	 * 关联查询 入库员
	 */
	public function admin()
	{
		return $this->belongsTo('app\admin\model\Admin', 'adminid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	/**
	 * 关联查询 审核员
	 */
	public function reviewer()
	{
		return $this->belongsTo('app\admin\model\Admin', 'reviewerid', 'id', [], 'LEFT')->setEagerlyType(0);
	}


}
