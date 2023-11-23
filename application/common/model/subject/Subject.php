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
		'thumbs_cdn',
		'create_time_text',
		'like_count',
		'chapter_count'
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

	// 关联查询=> 链表查询
	public function category()
	{
		/**
		 * 相对关联
		 * 1.关联模型名
		 * 2.关联外键
		 * 3.主键
		 * 4.别名
		 * 5.join方法
		 */
		return $this->belongsTo('app\common\model\subject\Category', 'cateid', 'id', [], 'LEFT')->setEagerlyType(0);
	}

	// 创建时间
	public function getCreateTimeTextAttr($value, array $data)
	{
		$time = $data['create_time'] ?? '';
		return datetime($time, 'Y-m-d');
	}

	// 点赞数
	public function getLikeCountAttr($value, $data)
	{
		$likeStr = $data['likes'] ?? '';
		$likeArr = explode(',', $likeStr);

		// 过滤控制符
		$likeArr = array_filter($likeArr);

		return count($likeArr);
	}

	public function getChapterCountAttr($value, $data)
	{
		$subId = $data['id'] ?? '';
		return model('subject.Chapter')->where(['subid' => $subId])->count();
	}

}