<?php echo $this->fetch('top.html'); ?>
<script type="text/javascript">
$(function(){
    $('#register_form').validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd');
            error_td.find('label').hide();
            error_td.append(error);
        },
        success       : function(label){
            label.addClass('validate_right').text('OK!');
        },
        onkeyup: false,
        rules : {
            user_name : {
                required : true,
                byteRange: [3,15,'<?php echo $this->_var['charset']; ?>'],
                remote   : {
                    url :'index.php?app=member&act=check_user&ajax=1',
                    type:'get',
                    data:{
                        user_name : function(){
                            return $('#user_name').val();
                        }
                    },
                    beforeSend:function(){
                        var _checking = $('#checking_user');
                        _checking.prev('.field_notice').hide();
                        _checking.next('label').hide();
                        $(_checking).show();
                    },
                    complete :function(){
                        $('#checking_user').hide();
                    }
                }
            },
            password : {
                required : true,
                minlength: 6
            },
            password_confirm : {
                required : true,
                equalTo  : '#password'
            },
            email : {
                required : true,
                email    : true
            },
            captcha : {
                required : true,
                remote   : {
                    url : 'index.php?app=captcha&act=check_captcha',
                    type: 'get',
                    data:{
                        captcha : function(){
                            return $('#captcha1').val();
                        }
                    }
                }
            },
            agree : {
                required : true
            }
        },
        messages : {
            user_name : {
                required : '您必须提供一个用户名',
                byteRange: '用户名必须在3-15个字符之间',
                remote   : '您提供的用户名已存在'
            },
            password  : {
                required : '您必须提供一个密码',
                minlength: '密码长度应在6-20个字符之间'
            },
            password_confirm : {
                required : '您必须再次确认您的密码',
                equalTo  : '两次输入的密码不一致'
            },
            email : {
                required : '您必须提供您的电子邮箱',
                email    : '这不是一个有效的电子邮箱'
            },
            captcha : {
                required : '请输入右侧图片中的文字',
                remote   : '验证码错误'
            },
            agree : {
                required : '您必须阅读并同意该协议,否则无法注册'
            }
        }
    });

    var captchaSet = "<?php echo $this->_var['captcha']; ?>";
    var captchaDom = $(".captcha");
    var smsDom = $("#sms");
    $("#sms").hide();
    if (captchaSet != 1) 
        $(".captcha").hide();
    $(".send_vcode").attr('disabled', null);
    $("#user_name").blur(function(){
        $(".email").after(smsDom).after(captchaDom);
        if(is_mobile($(this).val())){
            captchaDom.fadeIn("slow");
            smsDom.fadeIn("slow");
            $(".send_vcode").bind("click", send_click);
        }else{
            if (captchaSet != 1) {
                captchaDom.remove();
            }
            smsDom.remove();
        }
    });
    
    $(".send_vcode").bind("click", send_click);
});

function send_click(){
    var sendVcode = $(".send_vcode");
    var captcha = $('#captcha1').val();
    var phone = $("#user_name").val();
    if (captcha == '') {
        alert('请输入验证码');
        return;
    }
    if (!is_mobile(phone)) {
        alert('请填写正确的手机号');
        return;
    }
    $(this).attr('disabled', 'true');
    $(this).val('发送中...');
    $(this).css('background-color', '#d2d2d2');
    $.ajax({
        type : "POST",
        url : 'index.php',
        data : 'app=member&act=ajax_validate_sms&captcha1=' + captcha + '&phone=' + phone,
        dataType:"json",
        success:function(data){
            if(data.done == 'succ'){
                alert('验证码已发送，请查收');
                time($(".send_vcode"));
            }else{
                sendVcode.attr('disabled', null);
                sendVcode.val('重新发送');
                alert(data.msg);
            }
        },
        error: function(){
            sendVcode.attr('disabled', null);
            sendVcode.val('发送验证码');
            alert('sms_send_failure');
        }
    });
}
/* 检测是否手机号 */
function is_mobile(phone){
    if (!phone) return false;
    var reg = /^0?1((3|8)[0-9]|5[0-35-9]|4[57])\d{8}$/; // 验证规则 稍微宽泛一点 更大兼容后续
    return reg.test(phone);
}
var wait = 120;
function time(sv){
    if (wait == 0) {
        sv.attr('disabled', null);
        sv.val('发送验证码');
        wait = 120;
        return;
    } else {
        sv.attr('disabled', 'true');
        sv.val(wait + '秒重试')
        wait-- ;

    }
    setTimeout(function(){
        time(sv);
    }, 1000);
}
</script>
<!-- <script type="text/javascript">
$(function(){
	poshytip_message($('#user_name'));
	poshytip_message($('#password'));
	poshytip_message($('#password_confirm'));	
	poshytip_message($('#email'));
	poshytip_message($('#captcha1'));
});
</script> -->
<style>
.w{width:990px;}
#sms_vcode {width: 65px;}
</style>
<div id="main" class="w-full">
<div id="page-register" class="w login-register mt20 mb20">
	<div class="w logo mb10">
		<p><a href="<?php echo $this->_var['site_url']; ?>" title="<?php echo $this->_var['site_title']; ?>"><img alt="<?php echo $this->_var['site_title']; ?>" src="<?php echo $this->_var['site_logo']; ?>" /></a></p>
	</div>
	<div class="w clearfix">
		<div class="col-main">
    	<ul class="clearfix">
        	<li class="icon_1"><i></i>购买商品支付订单</li>
			<li class="icon_2"><i></i>申请开店销售商品</li>
			<li class="icon_3"><i></i>收藏你喜欢的商品</li>
			<li class="icon_4"><i></i>收藏你喜欢的店铺</li>
			<li class="icon_5"><i></i>商品咨询服务评价</li>
			<li class="icon_6"><i></i>安全交易诚信无忧</li>
        </ul>
		<h4>如果您是本站用户</h4>
		<div class="login-field">
			<span>已注册用户<a href="index.php?app=member&act=login" class="login-field-btn">请登录</a></span>
			<span>或者 忘记密码？ <a href="<?php echo url('app=find_password'); ?>" class="clew">通过邮箱</a>|<a href="<?php echo url('app=find_password&act=sms_find_password'); ?>" class="clew">通过短信</a></span>
		</div>
	</div>
		<div class="col-sub">
		<div class="form">
    		<div class="title">用户注册</div>
       	 	<div class="content">
				<form name="" id="register_form" method="post" action="">
        			<dl class="clearfix">
                		<dt>用户名</dt>
                    	<dd>
                    		<input type="text" style="width:245px;height:26px;" id="user_name" class="input"  name="user_name" title="user_name_tip"  />
                        	<br /><label></label>
                    	 </dd>
                	</dl>
             		<dl class="clearfix">
                		<dt>密&nbsp;&nbsp;&nbsp;码</dt>
                    	<dd>
                    		<input class="input" type="password" id="password" name="password" title="password_tip" />
                        	<div class="clr"></div><label></label>
                    	</dd>
                	</dl>
                	<dl class="clearfix">
              			<dt>确认密码</dt>
                    	<dd>
                    		<input class="input" type="password" id="password_confirm" name="password_confirm" title="password_confirm_tip" />
                        	<div class="clr"></div><label></label>
                   	 	</dd>
                	</dl>
                	<dl class="clearfix">
                		<dt>电子邮箱</dt>
                    	<dd>
                    		<input class="input" type="text" id="email" name="email" title="email_tip" />
                        	<div class="clr"></div><label></label>
                    	</dd>
                	</dl>
                	<dl class="captcha clearfix">
                		<dt>验证码</dt>
                    	<dd class="captcha clearfix">
                    		<input type="text" class="input float-left" name="captcha"  id="captcha1" title="captcha_tip" />
                        	<img height="26" id="captcha" src="index.php?app=captcha&amp;<?php echo $this->_var['random_number']; ?>" class="float-left" />
                        	<a href="javascript:change_captcha($('#captcha'));" class="float-left">看不清，换一张</a>
                        	<div class="clr"></div><label></label>
                    	</dd>
                	</dl>
                    <dl class="clearfix" id="sms">
                        <dt>短信验证码</dt>
                        <dd>
                            <input type="text" name="sms_vcode" class="text" id="sms_vcode" />
                            <input type="button" name="send_vcode" class="btn send_vcode" value="发送验证码" />
                            <div class="clr"></div><label></label>
                        </dd>
                    </dl>
           			<dl class="clearfix">
                		<dt>&nbsp;</dt>
                    	<dd class="mall-eula">
                    		<input id="clause" type="checkbox" name="agree" value="1" class="agree-checkbox" checked="checked" />
                 			<span>我已阅读并同意 <a href="<?php echo url('app=article&act=system&code=eula'); ?>" target="_blank">用户服务协议</a></span>
                        	<div class="clr"></div><label></label>
                    	</dd>
               	 	</dl>
                	<dl class="clearfix">
                		<dt>&nbsp;</dt>
                    	<dd>
                 			<input type="submit" name="Submit"value="立即注册"class="register-submit"title="立即注册" />
                  			<input type="hidden" name="ret_url" value="<?php echo $this->_var['ret_url']; ?>" />
                    	 </dd>
                	</dl>
      			</form>                  
   			</div>
		</div>
    </div>
    </div>
</div>
</div>
<?php echo $this->fetch('footer.html'); ?>
