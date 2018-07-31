<html>
<head>
<meta charset="utf-8" />
<title>言言管理后台</title>
<meta content="width=device-width,initial-scale=1.0" name="viewport" />
<meta content="description" />
<link rel="shortcut icon" href="/img/favicon.ico" />
<link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" href="/assets/font-awesome/css/font-awesome.css" />
<link rel="stylesheet" href="/css/style.css?r=<?= rand(1000, 999999) ?>" />
<link rel="stylesheet" href="/css/style-responsive.css" />

</head>
    
<body class="lock">
    <div class="lock-header">
        <!-- BEGIN LOGO -->
        <a class="center" id="logo" href="index.html">
            <img class="center" alt="logo" src="/img/logo.png">
        </a>
        <!-- END LOGO -->
    </div>
    <form>
        <div class="login-wrap">
            <div class="metro single-size red">
                <div class="locked">
                    <i class="fa fa-lock"></i>
                    <span>登录</span>
                </div>
            </div>
            <div class="metro double-size green">
                <div class="input-append lock-input">
                    <input type="text" name="uname" placeholder="Username">
                </div>
            </div>
            <div class="metro double-size yellow">
                <div class="input-append lock-input">
                    <input type="password" name="pwd" placeholder="Password">
                </div>
            </div>
            <div class="metro single-size terques login">
                <button type="button" id="submitBtn" class="btn login-btn">
                    登录
                    <i class="fa fa-long-arrow-right"></i>
                </button>
            </div>
        </form>
<!--        <div class="login-footer">
            <div class="remember-hint pull-left">
                <input type="checkbox" id=""> 记住密码
            </div>
            <div class="forgot-hint pull-right">
                <a id="forget-password" class="" href="javascript:;">忘记密码?</a>
            </div>
        </div>-->
    </div>

<!-- END BODY -->

    <script type="text/javascript" src="/js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="/assets/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/jquery.nicescroll.js"></script>
    <script type="text/javascript" src="/assets/jquery-slimscroll/jquery-ui-1.9.2.custom.min.js"></script>
    <script type="text/javascript" src="/js/jquery.scrollTo.min.js"></script>
    <script type="text/javascript" src="/js/comman.js"></script>
    <script type="text/javascript">
        $('#submitBtn').click(function() {
            var data = getFormData();
            ajax('/admin/signin', data, function(result) {
                alert(result.msg);
                if(result.code == 1) {
                    window.location.href = '/admin/index?uid='+result.data;
                } else {
                    window.location.reload();
                }
            });
        });
        
        //回车触发登录点击事件
        $('body').keydown(function() {
            if (event.keyCode == 13) {    //keyCode=13是回车键
                $('#submitBtn').trigger('click');
            }
        });
    </script>
</body>
</html>