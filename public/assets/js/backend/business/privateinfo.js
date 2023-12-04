define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    const Controller = {
        index: function () {
            // 初始化
            Table.api.init()

            // 绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                const panel = $($(this).attr("href"));
                if (panel.length > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function () {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }

                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
        },
        table: {
            info: function () {

            },
            visit: function () {
                // 回访列表
                const table2 = $("#table2");
                table2.bootstrapTable({
                    url: 'business/privateinfo/visit?ids=' + Fast.api.query('ids'),
                    extend: {
                        add_url: 'business/privateinfo/add?ids=' + Fast.api.query('ids'),
                        edit_url: 'business/privateinfo/edit',
                        del_url: 'business/privateinfo/del',
                        multi_url: 'business/privateinfo/multi',
                        table: 'business_visit',
                    },
                    toolbar: '#toolbar2',
                    sortName: 'visit.createtime',
                    search: false,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), sortable: true},
                            {field: 'business.nickname', title: __('Nickname'), operate: 'LIKE'},
                            {field: 'content', title: __('Content'), operate: 'LIKE'},
                            {field: 'admin.nickname', title: __('AdminNickname'), sortable: false, searchable: false},
                            {
                                field: 'createtime',
                                title: __('VisitCreateTime'),
                                formatter: Table.api.formatter.datetime,
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table2,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate,
                            }
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(table2);
            },
            // 申请记录
            receive: function () {
                // 表格1
                const table3 = $("#table3");

                table3.bootstrapTable({
                    url: 'business/privateinfo/receive?ids=' + Fast.api.query('ids'),
                    extends: {
                        table: 'business_receive',
                    },
                    toolbar: '#toolbar3',
                    sortName: 'receive.applytime',
                    search: false,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), sortable: true},
                            {field: 'business.nickname', title: __('Nickname'), operate: 'LIKE'},
                            {field: 'admin.nickname', title: __('AdminNickname'), sortable: false, searchable: false},
                            {field: 'status_text', title: __('StatusText'), sortable: false, searchable: false},
                            {
                                field: 'applytime',
                                title: __('Applytime'),
                                sortable: true,
                                searchable: true,
                                formatter: Table.api.formatter.datetime,
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                            }
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(table3);
            }
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        del: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"))
            },
        },
    };
    return Controller;
});