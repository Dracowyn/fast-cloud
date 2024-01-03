define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    const Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'depot/storage/index' + location.search,
                    add_url: 'depot/storage/add',
                    edit_url: 'depot/storage/edit',
                    del_url: 'depot/storage/del',
                    multi_url: 'depot/storage/multi',
                    import_url: 'depot/storage/import',
                    table: 'depot_storage',
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
                //
                onLoadSuccess: function () {
                    $('.btn-editone').data('area', ['100%', '100%'])

                    // 给添加按钮添加`data-area`属性
                    $(".btn-add").data("area", ["100%", "100%"]);
                },
                columns: [
                    [
                        {
                            checkbox: true,
                            formatter: function (value, row, index) {
                                if (row.status === '1') {
                                    return {disabled: true};
                                } else if (row.status === '2') {
                                    return {disabled: true};
                                } else if (row.status === '3') {
                                    return {disabled: true};
                                }
                            }
                        },
                        {field: 'id', title: __('Id')},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'supplier.name', title: __('Supplierid')},
                        {
                            field: 'type',
                            title: __('Type'),
                            searchList: {"1": __('直销入库'), "2": __('退货入库')},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {
                                "0": __('待审批'),
                                "1": __('审批失败'),
                                "2": __('待入库'),
                                "3": __('入库完成')
                            },
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'process',
                                    title: '通过审核',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    icon: 'fa fa-leaf',
                                    confirm: '确认通过审核吗？',
                                    url: 'depot/storage/process?status=1',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible: function (row) {

                                        return row.status < 1
                                    }
                                },
                                {
                                    name: 'cancel',
                                    title: '撤销审核',
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    icon: 'fa fa-reply',
                                    url: 'depot/storage/cancel',
                                    confirm: '确认要撤回审核吗？',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible: function (row) {
                                        if (row.status === '2') {
                                            return true
                                        } else if (row.status === 1) {
                                            return true
                                        }

                                        return false
                                    }
                                },
                                {
                                    name: 'pcancel',
                                    title: '未通过审核',
                                    classname: 'btn btn-xs btn-info btn-ajax',
                                    icon: 'fa fa-exclamation-triangle',
                                    confirm: '确认未通过审核吗？',
                                    url: 'depot/storage/process?status=0',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible: function (row) {
                                        return row.status < 1
                                    }
                                },
                                {
                                    name: 'storage',
                                    title: '确认入库',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    icon: 'fa fa-leaf',
                                    confirm: '确认确认入库吗？',
                                    url: 'depot/storage/storage',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible: function (row) {
                                        return row.status === '2'
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: '详情',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    url: 'depot/storage/detail',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    icon: 'fa fa-eye'
                                },
                                {
                                    name: 'edit',
                                    title: '编辑',
                                    classname: 'btn btn-xs btn-success btn-editone',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    icon: 'fa fa-pencil',
                                    url: 'depot/storage/edit',
                                    visible: function (row) {
                                        return row.status !== 3
                                    }
                                },
                                {
                                    name: 'del',
                                    title: '删除',
                                    classname: 'btn btn-xs btn-danger btn-delone',
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    icon: 'fa fa-trash',
                                    visible: function (row) {
                                        return row.status !== 3
                                    }
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

            // 选择供应商
            $('#c-supplierid').click(function () {
                Backend.api.open('depot/storage/supplier', '供应商', {
                    area: ['80%', '80%'],
                    callback: function (data) {
                        // 在这里可以接收弹出层中使用`Fast.api.close(data)`进行回传数据
                        $('#c-supplierid').val(data.name)
                        $('#c-mobile').val(data.mobile)

                        let region_text = data.provinces.name + data.citys.name + data.districts.name + data.address;

                        $('#c-Region').val(region_text);

                        $('#supplierid').val(data.id)
                    }
                })
            });

            // 初始化商品表格
            $('#table').bootstrapTable({
                columns: [
                    {
                        field: 'id',
                        title: '主键',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'name',
                        title: '商品名称',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'unit',
                        title: '单位',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'price',
                        title: '商品单价',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'nums',
                        title: '数量',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'subtotal',
                        title: '小计',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'typeid',
                        title: '商品分类',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'operate',
                        title: __('Operate'),
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                ]
            })

            // 存放选择商品数据
            let list = [];

            // 存放提交商品数据
            let ProductList = []

            // 存放商品id
            let ProidList = []

            // 添加商品按钮事件
            $('#product').click(function () {
                Backend.api.open('depot/storage/product', '商品', {
                    area: ['80%', '80%'],
                    callback: function (data) {

                        if (data) {
                            // 判断选择商品是否存在表格里
                            if (ProidList.includes(data.id)) {
                                Backend.api.msg('添加商品已存在')
                                return false
                            }

                            list.push(data)

                            let tr = ''

                            tr += `<tr style="text-align: center; vertical-align: middle; " data-index="0">`
                            tr += `<td class="">${data.id}</td>`
                            tr += `<td>${data.name}</td>`
                            tr += `<td>${data.unit.name}</td>`
                            tr += `<td><input name="row[price]" class="price" type="number" min="0" /></td>`
                            tr += `<td><input name="row[nums]" class="nums" type="number" min="0" /></td>`
                            tr += `<td class="subtotal"></td>`
                            tr += `<td>${data.type.name}</td>`
                            tr += `<td>
                                        <button class="btn btn-primary btn-xs ProAdd">添加</button>
                                        <button class="btn btn-danger btn-xs ProDel">删除</button>
                                    </td>`
                            tr += `</tr>`

                            ProidList.push(data.id)

                            tr += `<tr id="count" style="text-align: center; vertical-align: middle; ">`
                            tr += `<td>合计</td>`
                            tr += `<td></td>`
                            tr += `<td></td>`
                            tr += `<td></td>`
                            tr += `<td class="product-nums">0</td>`
                            tr += `<td class="total">0</td>`
                            tr += `<td></td>`
                            tr += `<td></td>`
                            tr += `</tr>`

                            $('#product').css({'display': 'none'})

                            $('#table tbody').html(tr)
                        }
                    }
                })
            })

            // 从表格删除商品
            $('#table').on('click', '.ProDel', function () {

                let index = $(this).parents('tr').data('index')

                // 删除自己
                $(this).parents('tr').remove()

                // 从数组里面删除选择删除商品
                list.splice(index, 1)

                if (list.length === 0) {
                    // 删除表格最后一个tr
                    $('#count').remove();

                    // 给个提示给操作员
                    const tr = `<tr class="no-records-found"><td colspan="9">没有找到匹配的记录</td></tr>`;

                    // 显示添加商品按钮
                    $('#product').css({'display': 'inline-block'})

                    // 把tr添加表格
                    $('#table tbody').html(tr);
                }

                // 从提交商品数据移除选择删除商品
                ProductList.splice(index, 1)

                // 从商品id数据移除选择删除商品，方便后面还可以添加回来该商品
                ProidList.splice(index, 1)

                // 重新更新隐藏域的数据
                $('#products').val(JSON.stringify(ProductList))

                const subtotal = $('.subtotal');

                let total = 0;

                for (let item of subtotal) {
                    total += item.innerText ? parseFloat(item.innerText) : 0
                }

                // 重新更新隐藏域的数据
                $('#total').val(total)

                // 显示总价
                $('.total').text(total.toFixed(2))

                // 计算每件商品数量
                var nums = $('.nums')

                var ProductsNums = 0

                for (let item of nums) {
                    ProductsNums += item.value ? parseInt(item.value) : 0
                }

                // 显示数量
                $('.product-nums').text(ProductsNums)

                return false
            })

            // 从表格添加商品
            $('#table').on('click', '.ProAdd', function () {

                // 获取表格添加商品按钮的父级的data属性
                let index = $(this).parents('tr').data('index')

                // 获取表格添加商品的按钮的父级
                let parent = $(this).parents('tr');

                // 打开一个新的窗口
                Backend.api.open('depot/storage/product', '商品', {
                    area: ['80%', '80%'],
                    callback: function (data) {
                        // 判断选择商品是否存在表格里
                        if (ProidList.includes(data.id)) {
                            Backend.api.msg('添加商品已存在')
                            return false
                        }

                        let tr = `
                            <tr style="text-align: center; vertical-align: middle; " data-index="${index + 1}">
                                <td class="proid">${data.id}</td>
                                <td>${data.name}</td>
                                <td>${data.unit.name}</td>
                                <td><input name="row[price]" class="price" type="number" min="1" /></td>
                                <td><input name="row[nums]" class="nums" type="number" min="1" /></td>
                                <td class="subtotal"></td>
                                <td>${data.type.name}</td>
                                <td>
                                    <button class="btn btn-primary btn-xs ProAdd">添加</button>
                                    <button class="btn btn-danger btn-xs ProDel">删除</button>
                                </td>
                            </tr>
                        `;

                        parent.after(tr)

                        list.push(data)

                        ProidList.push(data.id)
                    }
                })

                return false
            })


            // 给单价的输入框添加事件
            $('#table').on('blur', '.price', function () {

                // 清除错误提示
                $(this).next().remove()

                // 判断单价是为空
                if (!$.trim($(this).val())) {
                    $(this).after(`<span style="color:red;margin-left:5px;">此处不能为空</span>`)
                    return false
                }

                // 如果输入的值等于0提示
                if ($.trim($(this).val()) === 0) {
                    $(this).after(`<span style="color:red;margin-left:5px;">单价不能少1.00</span>`)
                    return false
                }

                // 获取单价
                const price = $(this).val() ? parseFloat($(this).val()) : 0;

                // 获取相邻的数量
                const num = $(this).parent('td').next().find('input').val() ? parseInt($(this).parent('td').next().find('input').val()) : 0;

                // 获取当前的商品id
                const proid = $(this).parent().prev().prev().prev().text();

                // 获取商品的单价*数量的总价
                var Price = price * num

                if (Price >= 0) {
                    // 把商品的总价在小计显示
                    $(this).parent('td').next().next().text(Price.toFixed(2));

                    // 计算每件商品数量
                    var nums = $('.nums');

                    // 总商品数量
                    var ProductsNums = 0;

                    for (let item of nums) {
                        ProductsNums += item.value ? parseInt(item.value) : 0
                    }

                    // 赋值
                    $('.product-nums').text(ProductsNums)

                    // 获取总价
                    var subtotal = $('.subtotal')

                    var total = 0

                    for (let item of subtotal) {
                        total += item.innerText ? parseFloat(item.innerText) : 0
                    }

                    $('.total').text(total.toFixed(2))

                    // 重新更新隐藏域的数据
                    $('#total').val(total)

                    if (ProidList.includes(proid)) {
                        for (let item of ProductList) {
                            if (proid === item.id) {
                                item.price = price
                                item.nums = num
                                item.total = Price
                            }
                        }

                    } else {
                        ProductList.push({
                            id: proid,
                            price: price,
                            nums: num,
                            total: Price // 这个是单价*数量
                        })

                        ProidList.push(proid)
                    }

                    // 重新更新隐藏域的数据
                    $('#products').val(JSON.stringify(ProductList))
                }

            })

            // 数量
            $('#table').on('blur', '.nums', function () {

                $(this).next().remove()

                if (!$(this).val()) {
                    $(this).after(`<span style="color:red;margin-left:5px;">此处不能为空</span>`)

                    return false
                }

                if ($(this).val() === 0) {
                    $(this).after(`<span style="color:red;margin-left:5px;">数量不能少1</span>`)
                    return false
                }

                // 获取相邻的单价
                const price = $(this).parent('td').prev().find('input').val() ? parseFloat($(this).parent('td').prev().find('input').val()) : 0;

                // 获取当前的商品id
                const proid = $(this).parent().prev().prev().prev().prev().text();

                // 获取当前的数量
                const num = $(this).val() ? parseInt($(this).val()) : 0;

                const Price = price * num;

                if (Price >= 0) {
                    $(this).parent('td').next().text(Price.toFixed(2))

                    // 计算每件商品数量
                    const nums = $('.nums');

                    let ProductsNums = 0;

                    for (let item of nums) {
                        ProductsNums += item.value ? parseInt(item.value) : 0
                    }


                    $('.product-nums').text(ProductsNums)

                    const subtotal = $('.subtotal');

                    let total = 0;

                    for (let item of subtotal) {
                        total += item.innerText ? parseFloat(item.innerText) : 0
                    }

                    $('.total').text(total.toFixed(2))

                    $('#total').val(total)

                    if (ProidList.includes(proid)) {
                        for (let item of ProductList) {
                            if (proid === item.id) {
                                item.price = price
                                item.nums = num
                                item.total = Price
                            }
                        }

                    } else {
                        ProductList.push({
                            id: proid,
                            price: price,
                            nums: num,
                            total: Price // 这个是单价*数量
                        })

                        ProidList.push(proid)
                    }

                    $('#products').val(JSON.stringify(ProductList))
                }
            })


            Controller.api.bindevent();
        },
        edit: function () {

            $('#c-supplierid').click(function () {
                Backend.api.open('depot/storage/supplier', '供应商', {
                    area: ['80%', '80%'],
                    callback: function (data) {
                        // 在这里可以接收弹出层中使用`Fast.api.close(data)`进行回传数据
                        $('#c-supplierid').val(data.name)
                        $('#c-mobile').val(data.mobile)

                        let region_text = data.provinces.name + data.citys.name + data.districts.name + data.address;

                        $('#c-Region').val(region_text);

                        $('#supplierid').val(data.id)
                    }
                })
            });

            let table = $('#table');

            // 初始化表格
            table.bootstrapTable({
                columns: [
                    {
                        field: 'id',
                        title: '主键',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'name',
                        title: '商品名称',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'unit',
                        title: '单位',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'price',
                        title: '商品单价',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'nums',
                        title: '数量',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'subtotal',
                        title: '小计', align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'typeid',
                        title: '商品分类',
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                    {
                        field: 'operate',
                        title: __('Operate'),
                        align: 'center',
                        halign: 'center',
                        valign: 'middle'
                    },
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 获取php传过来的数据
            const ProData = Config.Product.productData;

            // 添加商品的数据
            const list = [];

            // 存放商品的数量单价数据
            const ProductList = [];

            // 存放商品的数量单价id
            const ProidList = [];

            // 定义删除时，把已在数据库的商品追加到这里
            const DelIdList = [];

            if (ProData.length > 0) {
                let tr = ''

                let index = 0

                let TotalCount = 0

                let NumCount = 0

                for (let item of ProData) {
                    tr += `<tr data-index="${index++}" style="text-align: center; vertical-align: middle; ">`
                    tr += `<td>${item.product.id}</td>`
                    tr += `<td>${item.product.name}</td>`
                    tr += `<td>${item.product.unit.name}</td>`
                    tr += `<td><input name="row[price]" class="price" type="number" min="0" value="${item.price}" /></td>`
                    tr += `<td><input name="row[nums]" class="nums" type="number" min="0" value="${item.nums}" /></td>`
                    tr += `<td class="subtotal">${item.total}</td>`
                    tr += `<td>${item.product.type.name}</td>`
                    tr += `<td>
                                <button class="btn btn-primary btn-xs ProAdd">添加</button>
                                <button class="btn btn-danger btn-xs ProDel">删除</button>
                            </td>`
                    tr += `</tr>`

                    tr += `<tr id="count">`

                    list.push(item.product)

                    ProidList.push(item.product.id)

                    ProductList.push({
                        id: item.id,
                        proid: item.product.id,
                        price: item.price,
                        nums: item.nums.toString(),
                        total: item.total // 这个是单价*数量
                    })

                    TotalCount += item.total ? parseFloat(item.total) : 0

                    NumCount += item.nums ? parseInt(item.nums) : 0
                }

                tr += `<td>合计</td>`
                tr += `<td></td>`
                tr += `<td></td>`
                tr += `<td></td>`
                tr += `<td class="product-nums">${NumCount}</td>`
                tr += `<td class="total">${TotalCount.toFixed(2)}</td>`
                tr += `<td></td>`
                tr += `<td></td>`
                tr += `</tr>`

                $('#products').val(JSON.stringify(ProductList))

                $('#product').css({'display': 'none'})

                $('#table tbody').html(tr)
            }

            // 添加商品按钮事件
            $('#product').click(function () {
                Backend.api.open('depot/storage/product', '商品', {
                    area: ['80%', '80%'],
                    callback: function (data) {
                        if (data) {

                            if (ProidList.includes(data.id)) {
                                Backend.api.msg('添加商品已存在')
                                return false
                            }

                            list.push(data)

                            let tr = ''

                            tr += `<tr data-index="0">`
                            tr += `<td>${data.id}</td>`
                            tr += `<td>${data.name}</td>`
                            tr += `<td>${data.unit.name}</td>`
                            tr += `<td><input name="row[price]" class="price" type="number" min="0" /></td>`
                            tr += `<td><input name="row[nums]" class="nums" type="number" min="0" /></td>`
                            tr += `<td class="subtotal"></td>`
                            tr += `<td>${data.type.name}</td>`
                            tr += `<td>
                                        <button class="btn btn-primary btn-xs ProAdd">添加</button>
                                        <button class="btn btn-danger btn-xs ProDel">删除</button>
                                    </td>`
                            tr += `</tr>`

                            ProidList.push(data.id)

                            tr += `<tr id="count">`
                            tr += `<td>合计</td>`
                            tr += `<td></td>`
                            tr += `<td></td>`
                            tr += `<td></td>`
                            tr += `<td class="product-nums">0</td>`
                            tr += `<td class="total">0</td>`
                            tr += `<td></td>`
                            tr += `<td></td>`
                            tr += `</tr>`

                            $('#product').css({'display': 'none'})

                            $('#table tbody').html(tr)
                        }
                    }
                })
            })

            // 从表格删除商品
            $('#table').on('click', '.ProDel', function () {

                if (list.length === 1) {

                    Backend.api.msg('这是最后一件商品')
                    return false

                }

                let index = $(this).parents('tr').data('index')

                $(this).parents('tr').remove()

                list.splice(index, 1)

                // 追加数组
                DelIdList.push(ProductList[index].id)

                ProductList.splice(index, 1)

                ProidList.splice(index, 1)

                $('#products').val(JSON.stringify(ProductList))

                const subtotal = $('.subtotal');

                let total = 0;

                for (let item of subtotal) {
                    total += item.innerText ? parseFloat(item.innerText) : 0
                }

                $('#total').val(total)

                $('.total').text(total.toFixed(2))

                // 计算每件商品数量
                var nums = $('.nums')

                var ProductsNums = 0

                for (let item of nums) {
                    ProductsNums += item.value ? parseInt(item.value) : 0
                }


                $('.product-nums').text(ProductsNums)

                $('#delproid').val(JSON.stringify(DelIdList))

                return false
            })

            // 从表格添加商品
            $('#table').on('click', '.ProAdd', function () {

                let index = $(this).parents('tr').data('index')

                let parent = $(this).parents('tr')
                Backend.api.open('depot/storage/product', '商品', {
                    area: ['80%', '80%'],
                    callback: function (data) {
                        if (arr.includes(data.id)) {
                            Backend.api.msg('添加商品已存在')
                            return false
                        }

                        let tr = `
                            <tr data-index="${index + 1}">
                                <td>${data.id}</td>
                                <td>${data.name}</td>
                                <td>${data.unit.name}</td>
                                <td><input name="row[price]" class="price" type="number" min="1" /></td>
                                <td><input name="row[nums]" class="nums" type="number" min="1" /></td>
                                <td class="subtotal"></td>
                                <td>${data.type.name}</td>
                                <td>
                                    <button class="btn btn-primary btn-xs ProAdd">添加</button>
                                    <button class="btn btn-danger btn-xs ProDel">删除</button>
                                </td>
                            </tr>
                        `;

                        parent.after(tr)

                        list.push(data)

                        arr.push(data.id)
                    }
                })

                return false
            })


            // 判断单价是是否为空
            $('#table').on('blur', '.price', function () {

                $(this).next().remove()

                if (!$.trim($(this).val())) {
                    $(this).after(`<span style="color:red;margin-left:5px;">此处不能为空</span>`)
                    return false
                }

                if ($.trim($(this).val()) === 0) {
                    $(this).after(`<span style="color:red;margin-left:5px;">单价不能少于1.00</span>`)
                    return false
                }

                // 获取单价
                const price = $(this).val() ? parseFloat($(this).val()) : 0;

                // 获取相邻的数量
                const num = $(this).parent('td').next().find('input').val() ? parseInt($(this).parent('td').next().find('input').val()) : 0;

                // 获取当前的商品id
                const proid = $(this).parent().prev().prev().prev().text();

                const Price = price * num;

                if (Price >= 0) {
                    $(this).parent('td').next().next().text(Price.toFixed(2))

                    // 计算每件商品数量
                    const nums = $('.nums');

                    var ProductsNums = 0

                    for (let item of nums) {
                        ProductsNums += item.value ? parseInt(item.value) : 0
                    }

                    $('.product-nums').text(ProductsNums)

                    var subtotal = $('.subtotal')

                    var total = 0

                    for (let item of subtotal) {
                        total += item.innerText ? parseFloat(item.innerText) : 0
                    }

                    $('.total').text(total.toFixed(2))

                    $('#total').val(total)

                    if (ProidList.includes(parseInt(proid))) {
                        for (let item of ProductList) {
                            if (proid === item.proid) {
                                item.price = price
                                item.nums = num
                                item.total = Price
                            }
                        }

                    } else {
                        ProductList.push({
                            proid: proid,
                            price: price,
                            nums: num,
                            total: Price // 这个是单价*数量
                        })

                        ProidList.push(parseInt(proid))
                    }

                    $('#products').val(JSON.stringify(ProductList))
                }

            })

            // 数量
            $('#table').on('blur', '.nums', function () {

                $(this).next().remove()

                if (!$.trim($(this).val())) {
                    $(this).after(`<span style="color:red;margin-left:5px;">此处不能为空</span>`)

                    return false
                }

                if ($.trim($(this).val()) === 0) {
                    $(this).after(`<span style="color:red;margin-left:5px;">数量不能少于1</span>`)
                    return false
                }

                // 获取相邻的单价
                const price = $(this).parent('td').prev().find('input').val() ? parseFloat($(this).parent('td').prev().find('input').val()) : 0;

                // 获取当前的商品id
                const proid = $(this).parent().prev().prev().prev().prev().text();

                // 获取当前的数量
                const num = $(this).val() ? parseInt($(this).val()) : 0;

                const Price = price * num;

                if (Price >= 0) {
                    $(this).parent('td').next().text(Price.toFixed(2))

                    // 计算每件商品数量
                    const nums = $('.nums');

                    let ProductsNums = 0;

                    for (let item of nums) {
                        ProductsNums += item.value ? parseInt(item.value) : 0
                    }

                    $('.product-nums').text(ProductsNums)

                    const subtotal = $('.subtotal');

                    let total = 0;

                    for (let item of subtotal) {
                        total += item.innerText ? parseFloat(item.innerText) : 0
                    }

                    $('.total').text(total.toFixed(2))

                    $('#total').val(total)

                    // 判断该商品id是否存在，如果存在就更新数据，不存在就添加数据
                    if (ProidList.includes(parseInt(proid))) {
                        for (let item of ProductList) {
                            if (proid === item.proid) {
                                item.price = price
                                item.nums = num
                                item.total = Price
                            }
                        }

                    } else {
                        ProductList.push({
                            proid: proid,
                            price: price,
                            nums: num,
                            total: Price // 这个是单价*数量
                        })

                        ProidList.push(parseInt(proid))
                    }

                    // 更新隐藏域的数据
                    $('#products').val(JSON.stringify(ProductList))
                }
            })

            Controller.api.bindevent();
        },
        supplier: function () {
            // 初始化表格参数配置
            Table.api.init({});
            const supplierTable = $('#table');

            supplierTable.bootstrapTable({
                url: 'depot/storage/supplier',
                pk: 'id',
                sortName: 'supplier.id',
                search: false,
                // 关闭导出
                showExport: false,
                // 关闭通用搜索
                commonSearch: false,
                // 关闭显示的列
                showColumns: false,
                // 关闭切换显示
                showToggle: false,
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'provinces.name', title: __('Province'), operate: 'LIKE'},
                        {field: 'citys.name', title: __('City'), operate: 'LIKE'},
                        {field: 'districts.name', title: __('District'), operate: 'LIKE'},
                        {field: 'address', title: __('Address'), operate: 'LIKE'},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: supplierTable,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'chapter', text: '选择',
                                    title: '选择',
                                    classname: 'btn btn-primary btn-xs supplier',
                                },
                            ]
                        }
                    ]
                ]
            });

            $('#table').on('click', '.supplier', function () {

                let index = $(this).parents('tr').data('index')

                let data = Table.api.getrowdata(supplierTable, index);

                Backend.api.close(data)
            })

            // 为表格绑定事件
            Table.api.bindevent(supplierTable);

        },
        product: function () {
            // 初始化表格
            Table.api.init({})

            let ProductTable = $('#table')

            ProductTable.bootstrapTable({
                url: 'depot/storage/product',
                pk: 'id',
                sortName: 'product.id',
                search: false,
                // 关闭导出
                showExport: false,
                // 关闭通用搜索
                commonSearch: false,
                // 关闭显示的列
                showColumns: false,
                // 关闭切换显示
                showToggle: false,
                columns: [
                    [
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {
                            field: 'flag',
                            title: __('Flag'),
                            searchList: {"0": __('下架'), "1": __('上架')},
                            formatter: Table.api.formatter.flag
                        },
                        {field: 'stock', title: __('Stock')},
                        {field: 'type.name', title: __('Typeid')},
                        {field: 'unit.name', title: __('Unitid')},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: ProductTable,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'chapter', text: '选择',
                                    title: '选择',
                                    classname: 'btn btn-primary btn-xs product',
                                },
                            ]
                        }
                    ]
                ]
            })

            $('#table').on('click', '.product', function () {
                let index = $(this).parents('tr').data('index')

                let data = Table.api.getrowdata(ProductTable, index);

                Backend.api.close(data)
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
