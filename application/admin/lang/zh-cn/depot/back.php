<?php

return [
    'Id'          => '主键',
    'Code'        => '单据编号-自动生成',
    'Ordercode'   => '订单编号',
    'Busid'       => '客户外键',
    'Contact'     => '联系人',
    'Phone'       => '联系电话',
    'Address'     => '详细地址',
    'Province'    => '省',
    'City'        => '市',
    'District'    => '区',
    'Amount'      => '总价',
    'Expressid'   => '物流公司外键',
    'Expresscode' => '物流单号',
    'Createtime'  => '创建时间',
    'Remark'      => '备注',
    'Status'      => '0：未审核
1：已审核,未收货
2：已收货,未入库
3：已入库,生成入库单记录
-1：审核不通过',
    'Reason'      => '作废理由',
    'Adminid'     => '销售员',
    'Reviewerid'  => '审核员',
    'Stromanid'   => '仓管员入库',
    'Storageid'   => '入库外键',
    'Thumbs'      => '退货图集',
    'Deletetime'  => '软删除字段'
];
