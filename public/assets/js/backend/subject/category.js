define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    const Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/category/index',
                    add_url: 'subject/category/add',
                    edit_url: 'subject/category/edit',
                    del_url: 'subject/category/del',
                    table: 'category'
                }
            });

            // 获取Dom元素
            const table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                // 请求地址
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                // 主键
                pk: 'id',
                // 排序字段
                sortName: 'weight',
                // 列
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('CateName'), operate: 'LIKE'},
                        {field: 'weight', title: __('Weight'), sortable: true},
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