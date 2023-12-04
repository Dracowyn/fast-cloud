<?php
/**
 * @author Dracowyn
 * @since 2023-11-21 15:35
 */

namespace app\common\model\business;

use think\Model;
use traits\model\SoftDelete;

class Business extends Model
{
	use SoftDelete;

	// 指向数据表
	protected $name = 'business';

	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = true;

	// 定义创建时间的字段名
	protected $createTime = 'create_time';

	// 定义更新时间的字段名
	protected $updateTime = 'update_time';

	// 定义软删除的字段名
	protected $deleteTime = 'delete_time';

	// 追加数据表的不存在字段
	protected $append = [
		'mobile_text',
		'avatar_cdn',
		'create_time_text',
		'update_time_text',
		'delete_time_text',
		'gender_text',
		'deal_text',
		'region_text'
	];

	/**
	 * 手机号的获取器
	 * @param String|Int $value 当前字段名的值
	 * @param array $data 当前整行数据
	 */
	public function getMobileTextAttr($value, array $data)
	{
		$mobile = $data['mobile'] ?? '';
		return substr_replace($mobile, '****', 3, 4);
	}


	/**
	 * @param $value
	 * @param array $data 当前整行数据
	 * @return string 返回头像的完整路径
	 */
	public function getAvatarCdnAttr($value, array $data)
	{
		$avatar = $data['avatar'] ?? '';
		if (!is_file('.' . $avatar)) {
			$avatar = '/assets/img/avatar.png';
		}
		$cdn = config('site.url');
		return $cdn . $avatar;
	}

	// 获取性别：0为未知，1为男性，2为女性
	public function getGenderList()
	{
		return [0 => '保密', 1 => '男', 2 => '女'];
	}

	public function getDealList()
	{
		return [0 => '未成交', 1 => '已成交'];
	}

	public function getAuthList()
	{
		return [0 => '未认证', 1 => '已认证'];
	}

	protected function setCreateTimeAttr($value)
	{
		return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
	}

	protected function setUpdateTimeAttr($value)
	{
		return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
	}

	public function getCreateTimeTextAttr($value, $data)
	{
		$value = $value ?: ($data['create_time'] ?? '');
		return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
	}


	public function getUpdateTimeTextAttr($value, $data)
	{
		$value = $value ?: ($data['update_time'] ?? '');
		return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
	}


	public function getDeleteTimeTextAttr($value, $data)
	{
		$value = $value ?: ($data['delete_time'] ?? '');
		return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
	}

	// 获取性别
	public function getGenderTextAttr($value, $data)
	{
		$genderList = [0 => '保密', 1 => '男', 2 => '女'];

		$gender = $data['gender'] ?? '';

		if ($gender >= '0') {
			return $genderList[$gender];
		}
		return null;
	}

	// 成交状态
	public function getDealTextAttr($value, $data)
	{
		$dealList = [0 => '未成交', 1 => '已成交'];

		$deal = $data['deal'] ?? '';

		if ($deal >= '0') {
			return $dealList[$deal];
		}
		return null;
	}

	// 获取地区
	public function getRegionTextAttr($value, $data)
	{
		$province = model('Region')->where(['code' => $data['province']])->find();
		$city = model('Region')->where(['code' => $data['city']])->find();
		$district = model('Region')->where(['code' => $data['district']])->find();
		$output = [];
		if ($province) {
			$output[] = $province['name'];
		}
		if ($city) {
			$output[] = $city['name'];
		}
		if ($district) {
			$output[] = $district['name'];
		}
		return implode('-', $output);
	}

	// 关联客户来源
	public function source()
	{
		return $this->belongsTo('app\common\model\business\Source', 'sourceid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	public function admin()
	{
		return $this->belongsTo('app\admin\model\Admin', 'adminid', 'id', [], 'LEFT')->setEagerlyType(0);
	}
}