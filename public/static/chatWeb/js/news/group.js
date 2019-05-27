$(function(){
    // 底部菜单两层显示
    $('.group_menu_more').click(function(){
        if($(this).children().attr('src')=='/static/chatWeb/img/news/more.png'){
            $(this).children().attr('src','/static/chatWeb/img/news/more1.png');
            $('.group_menu_submenu').css('height','auto');
        }else{
            $(this).children().attr('src','/static/chatWeb/img/news/more.png');
            $('.group_menu_submenu').css('height','.45rem');
        }
    })
    // 红包弹框显示隐藏
    $('.group_content_oneself_pack,.group_content_opposite_pack').click(function(){
        $('.group_packwrap').show();
    })
    $('.group_packwrap').click(function(){
        $('.group_packwrap').hide();
    })
    $('.group_pack').click(function(){
        event.stopPropagation();
    })

    // 发红包弹框
    $('.givered_7,.givered_9').click(function(){
        $('.group_content').hide();
        $('.give_pack').show();
        $('body').css('padding','0')
        if($(this).hasClass('givered_7')){
            $('.num').val('7');
        }else{
            $('.num').val('9');
        }
    })
    $('.lb_headWrap_return').click(function(){
        $('.group_content').show();
        $('.give_pack').hide();
        $('body').css('padding-bottom','1.4rem');
        $(".ray_wrap ul li").removeClass('active');
        $('.red_money').val('');

    })
    // 领取详情
    $('.group_pack_info').click(function(){
        $('.group_content').hide();
        $('.red_details').show();
        $('body').css('padding','0')

        var red_id = $(this).attr("data-red-id");
        var key = $('.give_btn').attr('data-key');
        if(!red_id || !key){
            layer.msg('缺失参数-');return;
        }
        $.post("/index/groupchat/getRedDetail",{"red_id":red_id,"key":key},function(msg){
                $('.sum').html(msg.data.master_info.money);
                $('.name').html(msg.data.master_info.nickname);
                $(".photo_head").attr("src", msg.data.master_info.head_imgurl);
                $(".red_total").html(msg.data.master_info.money);
                $(".ray_points").html(msg.data.master_info.ray_point);
                $(".get_ok").html(msg.data.master_info.get_num);
                $(".total_num").html(msg.data.master_info.num);
                let str = '';
                for(let i = 0;i< msg.data.detail_info.length;i++){
                    if(msg.data.detail_info[i].is_die == 2){
                        str +="<div class='item active'>"
                                +"<div class='img'>"
                                +"<img src='"+msg.data.detail_info[i].head_imgurl+"' />"
                                +"</div>"
                                +"<div class='name_time'>"
                                +"<div class='super'>"+msg.data.detail_info[i].nickname+"</div>"
                                +"<p class='time'>"
                                +"<span>"+msg.data.detail_info[i].get_time_date+"</span>"
                                +"<span>"+msg.data.detail_info[i].get_time+"</span>"
                                +"</p>"
                                +"</div>"
                                +"<div class='right_sum'>"
                                +"<p class='fig'>"+msg.data.detail_info[i].money+"</p>"
                                +"<div class='crump'>"
                                +"<span class='crump_tu'></span>"
                                +"<span class='theRay'>中雷</span>"
                                +"</div>"
                                +"</div>"
                                +"</div>"
                    }else{
                        str +="<div class='item'>"
                                +"<div class='img'>"
                                +"<img src='"+msg.data.detail_info[i].head_imgurl+"' />"
                                +"</div>"
                                +"<div class='name_time'>"
                                +"<div class='super'>"+msg.data.detail_info[i].nickname+"</div>"
                                +"<p class='time'>"
                                +"<span>"+msg.data.detail_info[i].get_time_date+"</span>"
                                +"<span>"+msg.data.detail_info[i].get_time+"</span>"
                                +"</p>"
                                +"</div>"
                                +"<div class='right_sum'>"
                                +"<p class='fig'>"+msg.data.detail_info[i].money+"</p>"
                                +"</div>"
                                +"</div>"
                    }
                    $('.info_wrap').html(str)
                }
        },'json')
    })
    $('.lb_headWrap_return').click(function(){
        $('.group_content').show();
        $('.red_details').hide();
        $('body').css('padding-bottom','1.4rem')
        $(".ray_wrap ul li").removeClass('active');
        $('.red_money').val('');
        $('.rule_set').html(0);

    })

    //改变发送按钮样式
    $('.group_menu_input').keyup(function(){
        if($(this).html()!=''){
            $('.group_menu_send').css('background','#128ae6')
        }else{
            $('.group_menu_send').css('background','#c3f4ff')
        }
    })

    //图片绑定表情包
   $('.emotion_ear').SinaEmotion($('.group_menu_input'));

   //点击图片放大
   $('.group_content').on('click','.group_content_oneself_textimg .group_content_oneself_img,.group_content_opposite_textimg .group_content_opposite_img',function(){
        if(!$(this).hasClass('magnify_active')){
            let src = $(this).attr('src');
            $(this).addClass('magnify_active');
            $('.magnify_mask').show();
            $('.magnify').attr('src',src);
        }else{
            $(this).removeClass('magnify_active');
            $('.magnify_mask').hide();
            $('.magnify').attr('src','');
        }
    });
    $('.magnify_mask').click(function(){
        $('.group_content_oneself_textimg .group_content_oneself_img,.group_content_opposite_textimg .group_content_opposite_img').removeClass('magnify_active');
        $('.magnify').attr('src','')
        $(this).hide();
    });

    // 点击发送按钮不关闭输入法
    $('.group_menu_send').click(function(){
        $('.group_menu_input').focus();
    });
})