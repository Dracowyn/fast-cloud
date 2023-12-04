define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: "business/recyclesea/index",
                    del_url: "business/recyclesea/destroy",
                    red_url: "business/recyclesea/restore",
                    multi_url: "business/recyclesea/multi",
                    table: "business",
                },
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: "id",
                sortName: "business.delete_time",
                columns: [
                    [
                        {checkbox: true},
                        {field: "id", title: __("Id"), sortable: true},
                        {
                            field: "nickname",
                            title: __("Nickname"),
                            operate: "LIKE",
                        },
                        {
                            field: "mobile",
                            title: __("Mobile"),
                            operate: "LIKE",
                        },
                        {
                            field: "source.name",
                            title: __("SourceName"),
                            operate: "LIKE",
                        },
                        {
                            field: "gender_text",
                            title: __("Gender"),
                            sortable: false,
                            searchable: false,
                        },
                        {
                            field: "deal",
                            title: __("Deal"),
                            searchList: {0: __("未成交"), 1: __("已成交")},
                            formatter: Table.api.formatter.normal,
                        },
                        {
                            field: "admin.username",
                            title: __("AdminNickname"),
                            operate: "LIKE",
                        },
                        {
                            field: "delete_time_text",
                            title: __("Deletetime"),
                            sortable: false,
                            searchable: false,
                            formatter: Table.api.formatter.datetime,
                            operate: "RANGE",
                            addclass: "datetimerange",
                        },
                        {
                            field: "operate",
                            title: __("Operate"),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: "recyclesea",
                                    confirm: "确定要还原吗",
                                    title: "还原数据",
                                    extend: 'data-toggle="tooltip"',
                                    icon: "fa fa-reply",
                                    classname:
                                        "btn btn-xs btn-success btn-ajax",
                                    url: $.fn.bootstrapTable.defaults.extend.red_url,
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                },
                            ],
                        },
                    ],
                ],
            });

            // 还原，确认框的方法
            $(".btn-reduction").on("click", function () {
                let ids = Table.api.selectedids(table);
                ids = ids.toString();

                layer.confirm(
                    "确定要还原吗?",
                    {title: "还原", btn: ["是", "否"]},
                    function (index) {

                        $.post(
                            $.fn.bootstrapTable.defaults.extend.red_url,
                            {ids: ids},
                            function (response) {
                                if (response.code === 1) {
                                    Toastr.success(response.msg);
                                    $(".btn-refresh").trigger("click");
                                } else {
                                    Toastr.error(response.msg);
                                }
                            },
                            "json"
                        );

                        layer.close(index);
                    }
                );
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        del: function () {
            Controller.api.bindevent();
        },
        red: function () {
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