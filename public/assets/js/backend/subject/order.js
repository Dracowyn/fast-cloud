
// {
//     "id": 34,
//     "subid": 113,
//     "busid": 26,
//     "total": "999.99",
//     "code": "SUB202311241800392666934127",
//     "create_time": 1700820039,
//     "update_time": 1700820039,
//     "delete_time": null,
//     "subject": {
//     "id": 113,
//         "title": "Java",
//         "content": "<p>Java<br><\/p>",
//         "thumbs": "\/uploads\/20230908\/20230908173417gkXRHzJwNE6nf0VF.jpg",
//         "likes": "",
//         "price": "999.99",
//         "cateid": 20,
//         "create_time": 1694165666,
//         "update_time": 1700735463,
//         "delete_time": null,
//         "thumbs_cdn": "\/uploads\/20230908\/20230908173417gkXRHzJwNE6nf0VF.jpg",
//         "create_time_text": "2023-09-08",
//         "like_count": 0,
//         "chapter_count": 3
// },
//     "create_time_text": "2023-11-24 18:00:39",
//     "comment_status": false
// },
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    const Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/order/index',
                    del_url: 'subject/order/del',
                    table: 'order'
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
                sortName: 'id',
                // 列
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'code', title: __('Code')},
                        {field: 'busid', title: __('BusId')},
                        {field: 'business.nickname', title: __('Nickname')},
                        {field: 'subject.title', title: __('SubjectTitle')},
                        {field: 'total', title: __('OrderTotal')},
                        {field: 'create_time_text', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', sortable: true},
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
        del: function () {
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