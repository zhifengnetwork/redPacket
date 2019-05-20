
$(function(){
    $('html, body').animate({
        scrollTop: $('html, body').height()
    },100);
    //改变发送按钮样式
    $('.dialog_menu_input').keyup(function(){
        if($(this).html()!=''){
            $('.dialog_menu_send').css('background','#128ae6')
        }else{
            $('.dialog_menu_send').css('background','#c3f4ff')
        }
    })

    
    // 显示转账弹框
    $('.transfer').click(function(){
        $('.dialog_transfer_wrap').show()
        $('.dialog_transfer_user_name').html(to_name);
        $(".dialog_transfer_user_img").attr("src", to_head);
    })

    // 隐藏转账弹框
    $('.dialog_transfer_back').click(function(){
        $('.dialog_transfer_wrap').hide()
        $('.dialog_transfer_num_input').val('')
    })
    //点击表情包的图片，让滚动条滚动到底部
    $('.emotion_ear').click(function(){
    	
//  	console.log($(document).scrollTop())
//  	$("html,body").animate({scrollTop:$(document).scrollTop()},1000);
    	
    	
    })  
   //图片绑定表情包
   $('.emotion_ear').SinaEmotion($('.dialog_menu_input'));
// $('#face').SinaEmotion($('.emotion'));
   //测试本地解析
   

    // 输入密码
    $(function() {
        /**
         * 全局变量
         * **/
        /*存储-当前滚动条的位置*/
        var thisScroll_num = null;
        /*获取支付密码（后台*/
        var pass_val = '';
        $('.digital_key').on('click',function(){
            /*触摸-添加样式*/
            $(this).addClass('number_trem_active');
            /*当前的元素*/
            var this_eve = $(this);
            /*当前的数字*/
            var this_nun = Number(this_eve.html());
            /*当前-状态(输入数字true，删除false)*/
            var this_state = true;
            /*删除-按钮*/
            if(this_eve.attr('data-state') == 'delete'){
                /*如果当元素为空时*/
                if(pass_val == ""){
                    layer.msg('请输入密码');
                    // console.log('密码为空!');
                    remove_class($(this));
                    return false;
                }
                /*删除最后一个元素*/
                arr_str = pass_val.split('');
                /*数组删除最后一项*/
                arr_str.pop();
                pass_val = arr_str.join('');
                      //if(pass_val.length){
                // console.log('模拟删除代码',pass_val.length);
                /*页面-模拟删除代码*/
                if(pass_val.length > 0){
                    $('.password_box_num .trem_box').eq(pass_val.length).removeClass("show_box");
                }else {
                    $('.password_box_num .trem_box').eq(0).removeClass("show_box");
                }
                this_state = false;
            }
            /*获取支付密码*/
            if(pass_val.length < 6 && this_state){
                pass_val += this_nun;
            }
            /*状态*/
            if(this_state){
                /*页面-模拟密码-代码*/
                $('.password_box_num .trem_box').eq(pass_val.length-1).addClass("show_box");
            }
            // console.log(pass_val);
            /*密码长度为6时，ajax*/
            if(pass_val.length == 6){
                remove_class($(this));
                // $('.loading_wrap').show();
                // 验证pwd
                var key = $('.dialog_transfer_affirm').attr('data-key');
                var tr_num = $('.dialog_transfer_num_input').val();
                // var uid = $('.dialog_transfer_affirm').attr('data-uid');
                if(!key){
                    layer.msg('缺少参数!');return false;
                }
                if(!tr_num){
                    layer.msg('金额有误!');return false;
                }
                if(!toid){
                    layer.msg('缺少参数-to_uid');return false;
                }

                $.post("/index/message/checkPwd",{"money":tr_num, "pwd":pass_val, "key":key, "to_uid":toid},function(msg){
                        
                    if(msg.code==1){
                        layer.msg(msg.msg);
                        // 修改页面余额
                        $('.now_account').html(msg.data.account);
                        $('.account_now').html(msg.data.account);
                        $('.dialog_transfer_num').attr('data-account',msg.data.account);

                        $('.dialog_transfer_wrap').hide();
                        $('.dialog_transfer_num_input').val('');
                        $('.bottom_alert_wrap').hide();
                        pass_val='';
                        $('.trem_box').removeClass('show_box');

                        var str = '';
                            str +=  '<div class="dialog_content_oneself clearfloat">'
                                +   '<div class="dialog_content_oneself_imgwrap">'
                                +   '<img class="dialog_content_oneself_img" src="'+from_head+'">'
                                +   '</div>'
                                +   '<div class="dialog_content_oneself_info">'
                                +   '<p class="dialog_content_oneself_name">'+fromid_name+'</p>'
                                +   '<div class="dialog_content_oneself_transfer">'
                                +   '<div class="oneself_transfer_info">'
                                +   '<div class="oneself_transfer_imgwrap">'
                                +   '<img class="oneself_transfer_img" src="/static/chatWeb/img/news/transfer2.png">'
                                +   '</div>'
                                +   '<div class="oneself_transfer_price">'
                                +   '<div>给'+to_name+'转账</div>'
                                +   '<div>￥'+tr_num+'</div>'
                                +   '</div>'
                                +   '</div>'
                                +   '<p class="oneself_transfer_text">转帐</p>'
                                +   '</div>'
                                +   '</div>'
                                +   '</div>'
                            $('.dialog_content').append(str);
                            $('html, body').animate({
                                scrollTop: $('html, body').height()
                            }, 'slow');

                            // 保存数据入库
                            save_message(tr_num,type='transfer',fromid,toid);
                            console.log('保存数据入库');

                            // 推送给收款用户
                            var message = '{"data":"'+tr_num+'","type":"transfer","fromid":"'+fromid+'","toid":"'+toid+'","send_key":"'+send_key+'"}';
                            ws.send(message); // 向服务器发送信息

                    }else{
                        layer.msg(msg.msg);
                        $('.mask_text').hide();
                        $('.bottom_alert_box').hide();
           
                        // setTimeout(function(){
                            // $('.loading_wrap').hide();
                            $('.mask_text').show();
                            $('.bottom_alert_box').show();
                            pass_val='';
                            $('.trem_box').removeClass('show_box')
                        // },1100);
                        return false;
                    }
                },'json')
            }
            remove_class($(this));
        })
        /*离开-删除样式*/
        function remove_class(_this){
            /*模拟-touchs事件，因为iso上失效*/
            setTimeout(function(){
                _this.removeClass('number_trem_active');
            },100);
            
        }
        
        /*点击出现-底部弹窗*/
        $('.dialog_transfer_affirm').on('click',function(){
            
            var account = $('.dialog_transfer_num').attr('data-account');
            // 余额不足提示
            if(account<1){
                layer.msg('当前余额为0');return;
            }

            // 判断是否输入转账金额
            var tr_num = $('.dialog_transfer_num_input').val();
            if(!tr_num){
                layer.msg('请输入转账金额');return;
            }
            if(eval(tr_num)>eval(account)){
                layer.msg('转账金额不足');return;
            }
            $('.tr_num').html(tr_num);
            $('.now_account').html(account);

            $('.bottom_alert_wrap').show();
            /*底部弹窗(出现)*/
            $('.bottom_alert_box').animate({'bottom':'0'});
            /*获取当前滚动条的位置*/
            thisScroll_num = $(document).scrollTop();
            // console.log('获取当前滚动条的位置',thisScroll_num);
            /**禁止底部滑动
             * 设置为fixed之后会飘到顶部，所以要动态计算当前用户所在高度
             **/
            $('.wrap').css({
                'position':'fixed',
                'top': -thisScroll_num,
                'left': 0,
            });

            // 收付款方头像与名称
            
        })
        /*关闭遮罩层*/
        $('.delete_img_box').on('click',function(){
            $('.bottom_alert_wrap').hide();
            pass_val='';
            $('.trem_box').removeClass('show_box');
            /*底部弹窗(隐藏)*/
            $('.bottom_alert_box').animate({'bottom':-($('.bottom_alert_box').height())});
            /*恢复底部滑动*/
            $('.wrap').css({
                'position':'',
                'top': '',
                'left': '',
            });
            // console.log(66666,thisScroll_num);
            /*恢复当前用户滚动的位置！*/
            $(document).scrollTop(thisScroll_num);
            
        })
    });

	
})
