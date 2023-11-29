<?php

namespace app\common\model\product;

use think\Model;
use traits\model\SoftDelete;

class Product extends Model
{

    use SoftDelete;
    // 表名
    protected $name = 'product';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'flag_text',
        'status_text'
    ];
    

    
    public function getFlagList()
    {
        return ['1' => __('Flag 1'), '2' => __('Flag 2'), '3' => __('Flag 3')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['flag']) ? $data['flag'] : '');
        $list = $this->getFlagList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
