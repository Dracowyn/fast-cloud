<?php
/**
 * 管理员模型
 * @author Dracowyn
 * @since 2023-12-25 15:41
 */

namespace app\common\model\admin;

use think\Model;
use app\common\model\Config as ConfigModel;
use think\Env;

class Admin extends Model
{
	protected $name = 'admin';

	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = true;
	// 定义时间戳字段名
	protected $createTime = 'createtime';
	protected $updateTime = 'updatetime';

	protected $append = [
		'group_text',  // 角色的名称
		'avatar_cdn',
	];


	/**
	 * 重置用户密码
	 * @author baiyouwen
	 */
	public function resetPassword($uid, $NewPassword)
	{
		$passwd = $this->encryptPassword($NewPassword);
		return $this->where(['id' => $uid])->update(['password' => $passwd]);
	}

	// 密码加密
	protected function encryptPassword($password, $salt = '', $encrypt = 'md5')
	{
		return $encrypt($password . $salt);
	}

	//角色组的别名
	public function getGroupTextAttr($value, $data)
	{
		//权限分组表
		$AuthGroupAccessModel = model('AuthGroupAccess');

		//分组表
		$AuthGroupModel = model('AuthGroup');

		$gid = $AuthGroupAccessModel->where(['uid' => $data['id']])->value('group_id');

		if (!$gid) {
			return '暂无角色组';
		}

		//分组的名称
		$name = $AuthGroupModel->where(['id' => $gid])->value('name');

		if (!$name) {
			return '暂无角色组名称';
		}

		return $name;
	}

	/**
	 * 获取个人头像信息  获取器
	 * @param string $value
	 * @param array $data
	 * @return string
	 * get + AvatarCdn + Attr
	 */
	public function getAvatarCdnAttr($value, $data)
	{
		// 获取系统配置表里面的网站地址
		$url = ConfigModel::where('name', 'url')->value('value');

		$url = Env::get('site.url', $url);

		// 把admin数据表的avatar字段里把带有域名的值去掉域名
		$avatar = str_replace($url, '', $data['avatar']);

		if (!is_file('.' . $data['avatar'])) {
			$avatar = '/assets/img/avatar.png';
		}

		return $url . $avatar;
	}
}