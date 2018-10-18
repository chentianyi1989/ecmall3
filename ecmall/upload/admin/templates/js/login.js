$(function() {
    var activeCondition = $('.active-condition').val();
    /* 未激活站点登录后台必须先激活 */
    if(activeCondition == ''){ //未激活
        $('.mask-black').css('display', 'block');
    }
    $('.ohter-ecmall').click(function () {
        $('.form-box').css('display', 'none');
        $('.yunqi-wrap').css('display', 'block');
    });

    $('.ohter-yunqi').click(function () {
        $('.form-box').css('display', 'block');
        $('.yunqi-wrap').css('display', 'none');
    });

    // $('.panel-close').click(function () {
    //     $('.mask-black').css('display', 'none');
    // });
})