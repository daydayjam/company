<?php
$host = '127.0.0.1';
$port = '3306';
$user = 'root1';
$pwd = '';
$dbName = 'yanyan';

//try {
//    $dbh = new PDO('mysql:$host;', $user, $pwd);
//} catch (PDOException $e) {
//    echo '数据库连接失败：' . $e->getMessage();    //打印异常信息
//}

try{
    $Mysqli = new Mysqli($host, $user, $pwd, $dbName);
    print_r($Mysqli);die;
}catch (Exception $e){        //捕获异常
    echo '数据库连接失败：' . $e->getMessage();    //打印异常信息
}

//$dbh->query("SELECT wrongcolumn FROM wrongtable");
//
//
//try{
//    $Pdo = new PDO('mysql:$host;', $user, $pwd);
//}catch (Exception $e){        //捕获异常
//    echo $e->getMessage();    //打印异常信息
//}

