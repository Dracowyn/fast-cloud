define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    const Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/privatesea/index' + location.search,
                    add_url: 'business/privatesea/add',
                    edit_url: 'business/privatesea/edit',
                    del_url: 'business/privatesea/del',
                    multi_url: 'business/privatesea/multi',
                    import_url: 'business/privatesea/import',
                    recovery_url: 'business/privatesea/recovery',
                    detail_url: 'business/privateinfo/index',
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
                        {field: 'admin.nickname', title: __('Adminid')},
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
                                    name: 'business',
                                    title: '客户详情',
                                    extend: 'data-toggle="tooltip"',
                                    classname: "btn btn-xs btn-primary btn-dialog vivst_test",
                                    icon: 'fa fa-eye',
                                    url: $.fn.bootstrapTable.defaults.extend.detail_url,
                                },
                                {
                                    name: 'recovery',
                                    confirm: '确定要回收吗？',
                                    title: '客户回收',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-recycle',
                                    extend: 'data-toggle="tooltip"',
                                    url: $.fn.bootstrapTable.defaults.extend.recovery_url,
                                    refresh: true
                                }
                            ],
                        }
                    ]
                ]
            });

            $('.btn-reduction').on('click', function () {
                let ids = Table.api.selectedids(table);
                ids = ids.toString()
                layer.confirm('确定要回收吗?', {title: '回收', btn: ['是', '否']},
                    function (index) {

                        $.post($.fn.bootstrapTable.defaults.extend.recovery_url, {
                            ids: ids,
                            action: 'success',
                            reply: ''
                        }, function (response) {
                            if (response.code === 1) {
                                Toastr.success(response.msg)
                                $(".btn-refresh").trigger('click');
                            } else {
                                Toastr.error(response.msg)
                            }
                        }, 'json');

                        layer.close(index);
                    }
                );

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
