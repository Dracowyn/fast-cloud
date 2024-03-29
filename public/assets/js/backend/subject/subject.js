define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    const Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/subject/index',
                    add_url: 'subject/subject/add',
                    edit_url: 'subject/subject/edit',
                    del_url: 'subject/subject/del',
                    chapter_url: 'subject/chapter/index',
                    table: 'subject'
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
                sortName: 'create_time',
                // 列
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title')},
                        {
                            field: 'thumbs',
                            title: __('Thumbs'),
                            operate: false,
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {field: 'like_count', title: __('Likes')},
                        {field: 'price', title: __('Price')},
                        {field: 'category.name', title: __('CateName')},
                        {
                            field: 'create_time',
                            title: __('Createtime'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'chapter',
                                    title: '章节',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    url: $.fn.bootstrapTable.defaults.extend.chapter_url
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };

    return Controller;
});