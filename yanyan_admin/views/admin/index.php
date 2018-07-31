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
    <frameset rows="60,*" framespacing="0" border="0">
        <frame src="/default/top?uid=<?= $data ?>" name="header-frame"></frame>
        <frameset cols="180,*" framespacing="0" border="0">
            <frame src="/default/menu" name="menu-frame"></frame>
            <frame src="/console/index?ac=&op=" name="main-frame"></frame>
        </frameset>
    </frameset>
    
    <script type="text/javascript" src="/js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="/assets/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/jquery.nicescroll.js"></script>
    <script type="text/javascript" src="/assets/jquery-slimscroll/jquery-ui-1.9.2.custom.min.js"></script>
    <script type="text/javascript" src="/js/jquery.scrollTo.min.js"></script>
    <script type="text/javascript" src="/js/comman.js"></script>
    
</body>
</html>