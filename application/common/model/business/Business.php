<?php
/**
 * @author Dracowyn
 * @since 2023-11-21 15:35
 */

namespace app\common\model\business;

use think\Model;

class Business extends Model
{
	// 指向数据表
	protected $name = 'business';

	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = true;

	// 定义创建时间的字段名
	protected $createTime = 'create_time';

	// 定义更新时间的字段名
	protected $updateTime = 'update_time';

	// 追加数据表的不存在字段
	protected $append = [
		'mobile_text',
		'avatar_cdn'
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
}