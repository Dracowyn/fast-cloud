define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    const Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/chapter/index' + location.search,
                    add_url: 'subject/chapter/add?subid=' + Fast.api.query('ids'),
                    edit_url: 'subject/chapter/edit',
                    del_url: 'subject/chapter/del',
                    multi_url: 'subject/chapter/multi',
                    import_url: 'subject/chapter/import',
                    table: 'subject_chapter',
                }
            });

            const table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'create_time',
                queryParams: function (res) {
                    // 把条件字符串转成对象
                    let filters = JSON.parse(res.filter);

                    // 通过FastAdmin提供的Api获取课程id
                    let subid = Fast.api.query('ids');

                    filters.subid = subid ?? 0;

                    res.filter = JSON.stringify(filters);

                    return res;
                },
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'title',
                            title: __('Title'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'create_time',
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