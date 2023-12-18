<?php

namespace app\common\model\product;

use think\Env;
use think\Model;
use traits\model\SoftDelete;


class Type extends Model
{
	use SoftDelete;

	// 表名
	protected $name = 'product_type';

	// 自动写入时间戳字段
	protected $autoWriteTimestamp = false;

	// 定义时间戳字段名
	protected $createTime = false;
	protected $updateTime = false;
	protected $deleteTime = false;

	// 追加属性
	protected $append = [
		'thumb_cdn',
	];

	protected static function init()
	{
		self::afterInsert(function ($row) {
			$pk = $row->getPk();
			$row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
		});
	}

	public function getTypeList()
	{
		return $this->column('name', 'id');
	}

	// 商品分类缩略图
	public function getThumbCdnAttr($value, $data)
	{
		$thumb = $data['thumb'] ?? '';
		if (!is_file('.' . $thumb)) {
			$thumb = '/assets/img/qrcode.png';
		}
		$cdn = Env::get('site.url', config('site.url'));
		return $cdn . $thumb;
	}
}
