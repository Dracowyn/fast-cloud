<?php
/**
 * 地区模型
 *
 * @author Dracowyn
 * @since 2023-12-01 15:27
 */


namespace app\common\model\business;

use think\Model;

use traits\model\SoftDelete;

class Address extends Model
{
	use SoftDelete;

	protected $name = 'business_address';

	protected $autoWriteTimestamp = true;
//	protected $createTime = 'createtime'; //插入的时候设置的字段名
//	protected $updateTime = 'updatetime';
	protected $createTime = false;
	protected $updateTime = false;
	protected $deleteTime = 'deletetime';

	// 给模型定义一个关联查询
	public function provinces()
	{
		return $this->belongsTo('app\common\model\Region', 'province', 'code', [], 'LEFT')->setEagerlyType(0);
	}

	// 查询城市
	public function citys()
	{
		return $this->belongsTo('app\common\model\Region', 'city', 'code', [], 'LEFT')->setEagerlyType(0);
	}

	// 查询地区
	public function districts()
	{
		return $this->belongsTo('app\common\model\Region', 'district', 'code', [], 'LEFT')->setEagerlyType(0);
	}
}