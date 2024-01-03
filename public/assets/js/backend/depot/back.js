define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    const Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'depot/back/index' + location.search,
                    add_url: 'depot/back/add',
                    edit_url: 'depot/back/edit',
                    del_url: 'depot/back/del',
                    multi_url: 'depot/back/multi',
                    import_url: 'depot/back/import',
                    table: 'depot_back',
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
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'ordercode', title: __('Ordercode'), operate: 'LIKE'},
                        {field: 'business.nickname', title: __('Busid')},
                        {field: 'contact', title: __('Contact'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {
                                "0": __('未审核'),
                                "1": __('已审核，未收货'),
                                "2": __('已收货，未入库'),
                                "3": __('已入库'),
                                "-1": __('审核不通过')
                            },
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    title: '详情',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    url: 'depot/back/detail',
                                    icon: 'fa fa-eye'
                                },
                                {
                                    name: 'process',
                                    title: '通过审核',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-leaf',
                                    confirm: '确认通过审核吗？',
                                    url: 'depot/back/process',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible: function (row) {
                                        return row.status === '0';
                                    }
                                },
                                {
                                    name: 'receipt',
                                    title: '确认收货',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-leaf',
                                    confirm: '确认收货吗？',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    url: 'depot/back/receipt',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible: function (row) {

                                        return row.status === '1'
                                    }
                                },
                                {
                                    name: 'storage',
                                    title: '确认入库',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    icon: 'fa fa-leaf',
                                    confirm: '确认入库吗？',
                                    url: 'depot/back/storage',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible: function (row) {
                                        return row.status === '2'
                                    }
                                },
                                {
                                    name: 'fail',
                                    title: '未通过审核',
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-exclamation-triangle',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    confirm: '确认未通过审核吗？',
                                    url: 'depot/back/fail',
                                    visible: function (row) {
                                        return row.status === '0';
                                    }
                                },
                                {
                                    name: 'cancel',
                                    title: '撤销审核',
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa fa-reply',
                                    url: 'depot/back/cancel',
                                    confirm: '确认要撤回审核吗？',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible: function (row) {
                                        return row.status === '1';


                                    }
                                },
                                {
                                    name: 'edit',
                                    title: '编辑',
                                    classname: 'btn btn-xs btn-success btn-editone',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    icon: 'fa fa-pencil',
                                    url: 'depot/storage/edit',
                                    visible: function (row) {
                                        return !(row.status === '2' || row.status === '3');
                                    }
                                },
                                {
                                    name: 'del',
                                    title: '删除',
                                    classname: 'btn btn-xs btn-danger btn-delone',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    icon: 'fa fa-trash',
                                    visible: function (row) {
                                        return !(row.status === '2' || row.status === '3');
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
                url: 'depot/back/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'ordercode', title: __('Ordercode'), operate: 'LIKE'},
                        {field: 'business.nickname', title: __('Busid')},
                        {field: 'contact', title: __('Contact'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {
                                "0": __('未审核'),
                                "1": __('已审核，未收货'),
                                "2": __('已收货，未入库'),
                                "3": __('已入库'),
                                "-1": __('审核不通过')
                            },
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
                                    url: 'depot/back/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'depot/back/destroy',
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

        add: function () {
            $('#table').bootstrapTable({
                columns: [
                    {
                        field: 'id',
                        title: '主键',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'name',
                        title: '商品名称',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'price',
                        title: '商品单价',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'nums',
                        title: '数量',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'total',
                        title: '总价',
                        halign: 'center',
                        valign: 'middle'
                    },
                ]
            })

            $('#table').hide()

            $('#c-ordercode').change(function () {
                const code = $(this).val();
                GetOrder(code)
            })

            function GetOrder(code) {
                $.ajax({
                    type: "post",
                    url: 'depot/back/order',
                    data: {
                        code
                    },
                    dataType: "json",
                    success: function (res) {

                        if (res.code === 0) {
                            Toastr.error(res.msg)

                            return false
                        }

                        $('#table').show()

                        let tr = ''
                        // SU202211181107373113749

                        for (let item of res.data.orderProduct) {
                            tr += `<tr style="text-align: center; vertical-align: middle; ">`
                            tr += `<td>${item.products.id}</td>`
                            tr += `<td>${item.products.name}</td>`
                            tr += `<td>${item.price}</td>`
                            tr += `<td>${item.nums}</td>`
                            tr += `<td>${item.total}</td>`
                            tr += `</tr>`
                        }

                        $('#table tbody').html(tr);

                        // 收货地址
                        let option = '';

                        for (let item of res.data.addressList) {
                            option += `<option value="${item.id}" ${item.id === res.data.order.businessaddrid ? 'selected="selected"' : ''}>联系人：${item.consignee} 联系方式：${item.mobile} 地址：${item.provinces.name}-${item.citys.name}-${item.districts.name} ${item.address}</option>`
                        }

                        $('#addressid').html(option).selectpicker('refresh')
                    }
                });
            }

            Controller.api.bindevent();
        },
        edit: function () {
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
