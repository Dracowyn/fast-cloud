<?php
/**
 * @author Dracowyn
 * @since 2023-12-25 15:34
 */

namespace app\common\model\pay;

use think\Model;

class Pay extends Model
{
	protected $name = 'pay';

	protected $autoWriteTimestamp = true;

	protected $createTime = 'createtime';

	protected $updateTime = false;

	protected $deleteTime = false;

	protected $append = [
		'paytype_text',
		'status_text',
		'paytime_text'
	];

	public function getPaytypeList(): array
	{
		return ['0' => __('微信支付'), '1' => __('支付宝支付')];
	}

	public function getStatusList(): array
	{
		return ['0' => __('待支付'), '1' => __('已支付'), '2' => __('已关闭')];
	}


	public function getPaytypeTextAttr($value, $data)
	{
		$value = $value ? $value : ($data['paytype'] ?? '');
		$list = $this->getPaytypeList();
		return $list[$value] ?? '';
	}


	public function getStatusTextAttr($value, $data)
	{
		$value = $value ? $value : ($data['status'] ?? '');
		$list = $this->getStatusList();
		return $list[$value] ?? '';
	}


	public function getPaytimeTextAttr($value, $data)
	{
		$value = $value ? $value : ($data['paytime'] ?? '');
		return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
	}

	protected function setPaytimeAttr($value)
	{
		return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
	}
}