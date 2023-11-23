<?php
/**
 * @author Dracowyn
 * @since 2023-11-23 11:38
 */

namespace app\common\model\subject;

use think\Env;
use think\Model;
use traits\model\SoftDelete;

class Subject extends Model
{
	// 调用软删除类
	use SoftDelete;

	// 指向数据表
	protected $name = 'subject';

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
		'thumbs_cdn'
	];

	// 封面图的获取器
	public function getThumbsCdnAttr($value, array $data)
	{
		$thumbs = $data['thumbs'] ?? '';
		if (!is_file('.' . $thumbs)) {
			$thumbs = '/assets/images/web.jpg';
		}
		$cdn = model('Config')->where(['name' => 'url'])->value('value');
		$cdn = Env::get('site.url', $cdn);
		return $cdn . $thumbs;
	}

}