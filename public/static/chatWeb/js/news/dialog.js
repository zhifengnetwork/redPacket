
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

    // 发送消息
  

  

    // 显示转账弹框
    $('.transfer').click(function(){
        $('.dialog_transfer_wrap').show()
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
   
	
})
