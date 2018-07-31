<html>
<head>
<meta charset="utf-8" />
<title>言言管理后台</title>
<meta content="width=device-width,initial-scale=1.0" name="viewport" />
<meta content="description" />
<link rel="shortcut icon" href="/img/favicon.ico" />
<link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" href="/assets/font-awesome/css/font-awesome.css" />
<link rel="stylesheet" href="/css/style.css" />
<link rel="stylesheet" href="/css/style-responsive.css" />

</head>
<body class="fixed-top">
    <div id="header" class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container-fluid">
                <div class="sidebar-toggle-box">
                    <div class="fa fa-reorder tooltips"></div>
                </div>
                <a class="brand">言言管理后台</a>
                <div class="top-menu">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="/img/1.jpg" width="25px" />
                            <span>小明</span>
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="#"><i class="fa fa-user"></i>我的资料</a></li>
                            <li><a href="#"><i class="fa fa-cog"></i>我的设置</a></li>
                            <li><a href="#"><i class="fa fa-key"></i>退出</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="container" class="row-fluid">
        <div class="sidebar-scroll">
            <div id="sidebar">
                <ul class="sidebar-menu" style="display: block">
                    
                </ul>
            </div>
        </div>
        <div id="main-content" style="margin-left: 180px;">
            <div class="container-fluid">
                <div class="row-fluid">
                    <span class="hidden" id="ac"><?= $this->params['ac'] ?></span>
                    <span class="hidden" id="op"><?= $this->params['ac'] ?></span>
                    <h3 class="page-title">控制台</h3>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">首页</a>
                            <span class="divider"></span>
                        </li>
                        <li class="active">控制台</li>
                    </ul>
                </div>
                
                <?= $content; ?>
 
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="/js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="/assets/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/jquery.nicescroll.js"></script>
    <script type="text/javascript" src="/assets/jquery-slimscroll/jquery-ui-1.9.2.custom.min.js"></script>
    <script type="text/javascript" src="/js/jquery.scrollTo.min.js"></script>
    <script type="text/javascript" src="/js/comman.js"></script>
    <script type="text/javascript">
        $(function() {
            //左侧菜单同步
            var ac = $('#ac').text();
            var op = $('#ac').text();
            if(ac === 'user') {
                var curMenu = $('.menu-user a');
                console.log(curMenu.offset());
                var sub = curMenu.next();
                $('.arrow', curMenu).addClass('open');
                curMenu.parent().addClass('open');
                sub.slideDown(200);
                var o = (curMenu.offset());
//                alert(o.top);
//                diff = 200 - o.top;
//                if(diff>0)
//                    $('.sidebar-scroll').scrollTo('-='+Math.abs(diff),500);
//                else
//                    $('.sidebar-scroll').scrollTo('+='+Math.abs(diff),500);
                
                
                
                
                
            }
            
            
            $.ajaxSetup({  
                async : false  
            });      
            var menuHtml = '<li class="sub-menu menu-index">'
                            +    '<a href="/index/index">'
                            +        '<i class="fa fa-dashboard"></i>'
                            +        '<span>控制台</span>'
                            +    '</a>'
                            +'</li>';
            $.get('/action/list', {}, function(result) {
                result = $.parseJSON(result);
                var data = result.data;
                for(var i=0; i<data.length; i++) {
                    var sonMenu = '';
                    for(var j=0; j<data[i].s_menu.length; j++) {
                        sonMenu += '<li><a class="" href="/'+data[i].s_menu[j].ac+'/'+data[i].s_menu[j].op+'">'+data[i].s_menu[j].aname+'</a></li>';
                    }
                    menuHtml +=  '<li class="sub-menu menu-'+data[i].type+'">'
                                    +'<a href="#" onclick="menuClick(this)">'
                                    +    '<i class="fa fa-'+data[i].type+'"></i>'
                                    +    '<span>'+data[i].aname+'</span>'
                                    +    '<span class="arrow"></span>'
                                    +'</a>'
                                    +'<ul class="sub" style="display: none;">'
                                    +   sonMenu
                                    +'</ul>'
                                +'</li>';
                }
            });
            $('.sidebar-menu').html(menuHtml);
        });
    </script>
</body>
</html>