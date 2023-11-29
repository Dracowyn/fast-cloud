define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    const Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {}
            });

            // 选项卡事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                // 获取选中的元素
                let panel = $($(this).attr('href'));

                // 判断选中的元素是存在
                if (panel.length > 0) {
                    // 根据id值改变this的指向
                    Controller.table[panel.attr('id')].call(this);
                    $(this).on('click', function (e) {
                        // 根据id值获取相应的面板并且查找带有btn-refresh这个类名的元素，做了一个模拟点击事件
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }

                // 移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            // 必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
        },
        table: {
            subject: function () {
                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        recyclebin_url: 'subject/subject/recyclebin',
                        del_url: 'subject/subject/destroy',
                        restore_url: 'subject/subject/restore',
                        table: 'subject',
                    }
                });

                // 获取表格的元素
                const table = $('#table1');

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.recyclebin_url,
                    pk: 'id',
                    sortName: 'delete_time',
                    toolbar: '#toolbar1',
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), searchable: true},
                            {field: 'title', title: __('SubjectTitle'), operate: 'LIKE'},
                            {
                                field: 'thumbs_cdn',
                                title: __('SubjectThumbsCdn'),
                                searchable: false,
                                formatter: Table.api.formatter.image
                            },
                            {field: 'price', title: __('SubjectPrice'), operate: 'LIKE'},
                            {field: 'like_count', title: __('SubjectLikesCount'), searchable: false},
                            {
                                field: 'category.name', title: __('SubjectCategoryName'), operate: 'LIKE',
                                formatter: function (value) {
                                    let CateName = value ?? '未知分类'
                                    return "<span style='display: block;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;' title='" + CateName + "'>" + CateName + "</span>";
                                },
                                // 固定列最大宽度，超出隐藏
                                cellStyle: function (value, row, index, field) {
                                    return {
                                        css: {
                                            "white-space": "nowrap",
                                            "text-overflow": "ellipsis",
                                            "overflow": "hidden",
                                            "max-width": "200px"
                                        }
                                    };
                                }
                            },
                            {
                                field: 'create_time',
                                title: __('Deletetime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
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
                                        name: 'restore',
                                        title: '还原',
                                        icon: 'fa fa-reply',
                                        // 确认框
                                        confirm: '确定要还原吗',
                                        classname: 'btn btn-xs btn-success btn-ajax',
                                        // 请求地址
                                        url: $.fn.bootstrapTable.defaults.extend.restore_url,
                                        extend: 'data-toggle="tooltip" data-container="body"',
                                        // 请求成功回调函数
                                        success: function (data, ret) {
                                            $(".btn-refresh").trigger("click");
                                        },
                                        // 请求失败回调函数
                                        error: function (err) {
                                            console.log(err);
                                        }
                                    }
                                ]
                            }
                        ]
                    ],
                });
                Table.api.bindevent(table);
            },
            order: function () {
                console.log('订单');
            }
        }
    };
    return Controller;
});