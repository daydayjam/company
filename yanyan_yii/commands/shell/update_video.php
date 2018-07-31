<?php
require_once('./Db.php');
try {
    $Connect = Db::getInstance()->connect();
} catch(Exception $e) {
    die('数据库链接失败');
}
$sql = 'update';

