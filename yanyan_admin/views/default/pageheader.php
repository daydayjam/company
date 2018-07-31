<html>
<head>
<meta charset="utf-8" />
<title>言言管理后台</title>
<meta content="width=device-width,initial-scale=1.0" name="viewport" />
<meta content="description" />
<link rel="shortcut icon" href="/img/favicon.ico" />
<link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" href="/assets/fileinput/css/fileinput.min.css" />
<link rel="stylesheet" href="/assets/font-awesome/css/font-awesome.css" />
<link rel="stylesheet" href="/assets/font-awesome/css/font-awesome.css" />
<link rel="stylesheet" href="/assets/datetimepicker/jquery.datetimepicker.css">
<link rel="stylesheet" href="/css/style.css?r=<?= rand(1000, 999999) ?>" />
<link rel="stylesheet" href="/css/style-responsive.css" />

</head>
<body>
<div id="container" class="row-fluid">
    <div id="main-content" style="margin-left: 18px;">
        <div class="row-fluid">
            <h3 class="page-title" style="display: inline-block"><?= $params['ac_name'] ?></h3>
            <button id="refresh-btn" class="btn btn-default pull-right" style="margin-top: 10px;margin-right: 10px;">刷新</button>
            <ul class="breadcrumb">
                <li>
                    <a href="javascript:void(0)" style="text-decoration: none"><?= $params['ac_name'] ?></a>
                    <span class="divider"></span>
                </li>
                <li class="active"><?= $params['op_name'] ?></li>
            </ul>
        </div>
    

