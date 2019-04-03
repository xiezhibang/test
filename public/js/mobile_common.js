/**
 * addcart 将商品加入购物车
 * @goods_id  商品id
 * @num   商品数量
 * @form_id  商品详情页所在的 form表单
 * @to_catr 加入购物车后再跳转到 购物车页面 默认不跳转 1 为跳转
 * layer弹窗插件请参考http://layer.layui.com/mobile/
 */
function AjaxAddCart(goods_id, num, to_cart) {
    //如果有商品规格 说明是商品详情页提交
    if ($("#buy_goods_form").length > 0) {
        $.ajax({
            type: "POST",
            url: "/index.php?m=Mobile&c=Cart&a=ajaxAddCart",
            data: $('#buy_goods_form').serialize(),// 你的formid 搜索表单 序列化提交
            dataType: 'json',
            success: function (data) {
                // 加入购物车后再跳转到 购物车页面
                if (data.status < 0) {
                    layer.open({content: data.msg, time: 2});
                    return false;
                }
                var cart_num = parseInt($('#tp_cart_info').html()) + parseInt($('#number').val());
                $('#tp_cart_info').html(cart_num)
                layer.open({
                    content: '添加成功！',
                    btn: ['再逛逛', '去购物车'],
                    shadeClose: false,
                    yes: function () {
                        layer.closeAll();
                    }, no: function () {
                        location.href = "/index.php?m=Mobile&c=Cart&a=cart";
                    }
                });
            }
        });
    } else { //否则可能是商品列表页 、收藏页商品点击加入购物车
        $.ajax({
            type: "POST",
            url: "/index.php?m=Mobile&c=Cart&a=ajaxAddCart",
            data: {goods_id: goods_id, goods_num: num},
            dataType: 'json',
            success: function (data) {
                layer.open({content: data.msg, time: 3});
                if (data.status === 1) {
                    tp_cart_info = $('#tp_cart_info');
                    cart_num = parseInt(tp_cart_info.html()) + parseInt(num);
                    tp_cart_info.html(cart_num);
                    if (to_cart == 1)  //直接购买
                    {
                        location.href = "/index.php?m=Mobile&c=Cart&a=cart";
                    }
                    return false;
                }else{
                    location.reload();
                    return false;
                }
            }
        });
    }
}

// 点击收藏商品
function collect_goods(goods_id) {
    $.ajax({
        type: "GET",
        dataType: "json",
        url: "/index.php?m=Mobile&c=goods&a=collect_goods&goods_id=" + goods_id,//+tab,
        success: function (data) {
            alert(data.msg);
        }
    });
}