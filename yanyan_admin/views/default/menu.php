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
<style>
    body {
        background-color: #404040;
    }
</style>
</head>
    <div class="sidebar-scroll">
        <div id="sidebar">
            <ul class="sidebar-menu" style="display: block">
                <li class="sub-menu menu-index">
                    <a href="/console/index" target="main-frame">
                        <i class="fa fa-dashboard"></i>
                        <span>控制台</span>
                    </a>
                </li>
                <?php foreach($data as $key=>$menu) { ?>
                <li class="sub-menu">
                    <a href="#">
                        <i class="fa fa-<?= $menu['type']?>"></i>
                        <span><?= $menu['aname']?></span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub" style="display: none;">
                        <?php foreach($menu['s_menu'] as $sMenu) { ?>
                        <li><a class="" href="/<?= $sMenu['ac']?>/<?= $sMenu['op']?>?ac_name=<?= $menu['aname'] ?>&op_name=<?= $sMenu['aname'] ?>" target="main-frame"><?= $sMenu['aname'] ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    
    <script type="text/javascript" src="/js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="/assets/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/jquery.nicescroll.js"></script>
    <script type="text/javascript" src="/assets/jquery-slimscroll/jquery-ui-1.9.2.custom.min.js"></script>
    <script type="text/javascript" src="/js/jquery.scrollTo.min.js"></script>
    <script type="text/javascript" src="/js/comman.js"></script>
</body>
</html>

