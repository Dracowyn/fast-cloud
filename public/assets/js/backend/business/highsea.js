define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    const Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/highsea/index' + location.search,
                    add_url: 'business/highsea/add',
                    edit_url: 'business/highsea/edit',
                    del_url: 'business/highsea/del',
                    multi_url: 'business/highsea/multi',
                    import_url: 'business/highsea/import',
                    allot_url : 'business/highsea/allot',
                    receive_url : 'business/highsea/receive',
                    table: 'business',
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
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {
                            field: 'gender',
                            title: __('Gender'),
                            searchList: {"0": __('保密'), "1": __('男'), "2": __('女')},
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'email',
                            title: __('Email'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'auth',
                            title: __('Auth'),
                            searchList: {"0": __('未认证'), "1": __('已认证')},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'source.name', title: __('Source_id')},
                        // {field: 'openid', title: __('Openid'), operate: 'LIKE'},
                        // {field: 'adminid', title: __('Adminid')},
                        {field: 'money', title: __('Money'), operate: 'BETWEEN'},
                        {
                            field: 'deal',
                            title: __('Deal'),
                            searchList: {"0": __('未成交'), "1": __('已成交')},
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'update_time',
                            title: __('Update_time'),
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
                                    name: 'receive',
                                    icon: 'fa fa-arrow-down',
                                    confirm: '确定要领取吗',
                                    title: '领取',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-success btn-ajax btn-receive',
                                    url: 'business/highsea/receive?ids={id}',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    }
                                },
                                {
                                    name: 'allot',
                                    icon: 'fa fa-arrow-up',
                                    title: '分配',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-success btn-xs btn-dialog',
                                    url: 'business/highsea/allot?ids={id}',
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

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'business/highsea/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {
                            field: 'gender',
                            title: __('Gender'),
                            searchList: {"0": __('保密'), "1": __('男'), "2": __('女')},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'source.name', title: __('Source_id')},
                        {
                            field: 'deal',
                            title: __('Deal'),
                            searchList: {"0": __('未成交'), "1": __('已成交')},
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'delete_time',
                            title: __('Delete_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'money', title: __('Money'), operate: 'BETWEEN'},
                        {
                            field: 'email',
                            title: __('Email'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'auth',
                            title: __('Auth'),
                            searchList: {"0": __('未认证'), "1": __('已认证')},
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
