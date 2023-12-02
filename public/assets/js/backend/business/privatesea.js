define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
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
                    table: 'business',
                }
            });

            var table = $("#table");

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
                        {field: 'password', title: __('Password'), operate: 'LIKE'},
                        {field: 'salt', title: __('Salt'), operate: 'LIKE'},
                        {field: 'avatar', title: __('Avatar'), operate: 'LIKE', events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'gender', title: __('Gender'), searchList: {"0":__('Gender 0'),"1":__('Gender 1'),"2":__('Gender 2')}, formatter: Table.api.formatter.normal},
                        {field: 'source_id', title: __('Source_id')},
                        {field: 'deal', title: __('Deal'), searchList: {"0":__('Deal 0'),"1":__('Deal 1')}, formatter: Table.api.formatter.normal},
                        {field: 'openid', title: __('Openid'), operate: 'LIKE'},
                        {field: 'province', title: __('Province'), operate: 'LIKE'},
                        {field: 'city', title: __('City'), operate: 'LIKE'},
                        {field: 'district', title: __('District'), operate: 'LIKE'},
                        {field: 'adminid', title: __('Adminid')},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'delete_time', title: __('Delete_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'email', title: __('Email'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'auth', title: __('Auth'), searchList: {"0":__('Auth 0'),"1":__('Auth 1')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
