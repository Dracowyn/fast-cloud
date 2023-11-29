<?php
/**
 * 回收站
 * @author Dracowyn
 * @since 2023-11-29 15:22
 */

namespace app\admin\controller\subject;

use app\common\controller\Backend;

class Recyclebin extends Backend
{
	public function index()
	{
		return $this->fetch();
	}
}