var url = 'http://127.0.0.3';

//汉堡左侧菜单
$('.fa-reorder').click(function() {
    if($('#sidebar > ul').is(':visible') === true) {
        $('#main-content').css('margin-left', '0');
        $('#sidebar').css('margin-left', '-180px');
        $('#sidebar > ul').hide();
        $('#container').addClass('sidebar-closed');
    } else {
        $('#main-content').css('margin-left', '180px');
        $('#sidebar').css('margin-left', '0');
        $('#sidebar > ul').show();
        $('#container').removeClass('sidebar-closed');
    }
});

//左侧下拉菜单
$('#sidebar .sub-menu > a').click(function() {
    var last = $('.sub-menu.open', $('#sidebar'));
    last.removeClass('open');
    $('.arrow', last).removeClass('open');
    $('.sub', last).slideUp(200);
    var sub = $(this).next();
    if (sub.is(':visible')) {
        $('.arrow', $(this)).removeClass('open');
        $(this).parent().removeClass('open');
        sub.slideUp(200);
    } else {
        $('.arrow', $(this)).addClass('open');
        $(this).parent().addClass('open');
        sub.slideDown(200);
    }
    var o = ($(this).offset());
    diff = 200 - o.top;
    if(diff>0)
        $('.sidebar-scroll').scrollTo('-='+Math.abs(diff),500);
    else
        $('.sidebar-scroll').scrollTo('+='+Math.abs(diff),500);
}); 

function menuClick(object) {
    var last = $('.sub-menu.open', $('#sidebar'));
    last.removeClass('open');
    $('.arrow', last).removeClass('open');
    $('.sub', last).slideUp(200);
    var sub = $(object).next();
    if (sub.is(':visible')) {
        $('.arrow', $(object)).removeClass('open');
        $(object).parent().removeClass('open');
        sub.slideUp(200);
    } else {
        $('.arrow', $(object)).addClass('open');
        $(object).parent().addClass('open');
        sub.slideDown(200);
    }
    var o = ($(object).offset());
    diff = 200 - o.top;
    if(diff>0)
        $('.sidebar-scroll').scrollTo('-='+Math.abs(diff),500);
    else
        $('.sidebar-scroll').scrollTo('+='+Math.abs(diff),500);
}

// custom scrollbar
$(".sidebar-scroll").niceScroll({styler:"fb",cursorcolor:"#4A8BC2", cursorwidth: '5', cursorborderradius: '0px', background: '#404040', cursorborder: ''});

//ajax请求
function ajax(url, data, callback, method = 'POST') {
    $.ajax({
        url: url,
        data: data,
        type: method,
        dataType: 'json',
        success: callback
    });
}

//获取表单数据
function getFormData() {
    var d = {};
    var t = $('form').serializeArray();
    $.each(t, function() {
        d[this.name] = this.value;
    });
    return d;
}

//格式化时间
function format(time) {
    var data = new Date(time);  
    var year = data.getFullYear();  //获取年
    var month = data.getMonth()<9 ? 0+data.getMonth()+1 : data.getMonth()+1;    //获取月
    var day = data.getDate()<10 ? 0+data.getDate() : data.getDate(); //获取日
    var hours = data.getHours()<10 ? 0+data.getHours() : data.getHours(); 
    var minutes = data.getMinutes()<10 ? 0+data.getMinutes() : data.getMinutes();
    return year+'-'+month+'-'+day+' '+hours+':'+minutes+':'+'00';
}

$('#refresh-btn').click(function() {
    window.location.reload();
});
