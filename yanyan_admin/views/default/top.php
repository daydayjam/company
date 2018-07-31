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
                            <span><?= $data['uname'] ?></span>
                        </a>
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" >
                            <a href="#" id="logout" style="color:#fff;text-decoration: none"><i class="fa fa-key"></i>退出</a>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="/js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="/assets/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/jquery.nicescroll.js"></script>
    <script type="text/javascript" src="/assets/jquery-slimscroll/jquery-ui-1.9.2.custom.min.js"></script>
    <script type="text/javascript" src="/js/jquery.scrollTo.min.js"></script>
    <script type="text/javascript" src="/js/comman.js"></script>
    <script>
        $('#logout').click(function() {
            ajax('/admin/signout', {}, function(result) {
               window.parent.location.href='/admin/login';
            });
        });
        
    </script>
</body>
</html>

