<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=47.94.204.92;dbname=poetry',
    'username' => 'root',
    'password' => 'CrtingFairyOnline2013',
    'charset' => 'utf8mb4',
    'tablePrefix' => 'tb_',
    'attributes' => [
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::ATTR_CASE => PDO::CASE_LOWER, 
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        
    ]
    
];

//return [
//    'class' => 'yii\db\Connection',
//    'dsn' => 'mysql:host=39.105.53.143;dbname=poetry',
//    'username' => 'root',
//    'password' => 'CrtingFairy2013',
//    'charset' => 'utf8',
//    'tablePrefix' => 'tb_',
//    'attributes' => [
//        PDO::ATTR_STRINGIFY_FETCHES => false,
//        PDO::ATTR_EMULATE_PREPARES => false,
//        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
//        PDO::ATTR_CASE => PDO::CASE_LOWER, 
//        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
//        
//    ]
//    
//];
