define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    const Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/order/index' + location.search,
                    del_url: 'product/order/del',
                    multi_url: 'product/order/multi',
                    info_url: 'product/order/info',
                    deliver_url: 'product/order/deliver',
                    refund_url: 'product/order/refund',
                    import_url: 'product/order/import',
                    table: 'order',
                }
            });

            const table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {
                            checkbox: true
                        },
                        {field: 'id', title: __('Id')},
                        {
                            field: 'code',
                            title: __('Code'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {field: 'business.nickname', title: __('Busid'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'express.name', title: __('Expressid')},
                        {
                            field: 'expresscode',
                            title: __('Expresscode'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: function (value) {
                                if (!value) {
                                    return '-';
                                }
                                return `<div class="autocontent-item " style="white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:250px;">${value}</div>`
                            }
                        },

                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {
                                "0": __('未支付'),
                                "1": __('已支付'),
                                "2": __('已发货'),
                                "3": __('已收货'),
                                "4": __('已完成'),
                                "-1": __('仅退款'),
                                "-2": __('退款退货'),
                                "-3": __('售后中'),
                                "-4": __('退货成功'),
                                "-5": __('退货失败')
                            },
                            operate: 'LIKE',
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'info',
                                    title: '订单详情',
                                    extend: 'data-toggle="tooltip"',
                                    classname: "btn btn-xs btn-primary btn-dialog",
                                    icon: 'fa fa-eye',
                                    url: $.fn.bootstrapTable.defaults.extend.info_url,
                                },
                                {
                                    name: 'deliver',
                                    title: '发货',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    url: $.fn.bootstrapTable.defaults.extend.deliver_url,
                                    icon: 'fa fa-leaf',
                                    visible: function (row) {
                                        return row.status === '1' || row.status === '2';
                                    }
                                },
                                {
                                    name: 'refund',
                                    title: '退货审核',
                                    extend: 'data-toggle="tooltip"',
                                    classname: "btn btn-xs btn-primary btn-dialog",
                                    icon: 'fa fa-check',
                                    url: $.fn.bootstrapTable.defaults.extend.refund_url,
                                    visible: function (row) {
                                        return row.status === '-1' || row.status === '-2';
                                    }
                                }
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            const table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'product/order/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'code',
                            title: __('Code'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'business.nickname', title: __('Busid'), operate: 'LIKE'},
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {
                                "0": __('未支付'),
                                "1": __('已支付'),
                                "2": __('已发货'),
                                "3": __('已收货'),
                                "4": __('已完成'),
                                "-1": __('仅退款'),
                                "-2": __('退款退货'),
                                "-3": __('售后中'),
                                "-4": __('退货成功'),
                                "-5": __('退货失败')
                            },
                            operate: 'LIKE',
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '140px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'product/order/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'product/order/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        // 发货
        deliver: function () {
            Controller.api.bindevent();
        },
        // 退货审核
        refund: function () {

            $('#c-refund').change(function () {
                let val = $(this).val();

                if (val === 1) {
                    $('#examinereason').hide();
                    $('#c-examinereason').val('');
                } else {
                    $('#examinereason').show();
                }
            });

            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});