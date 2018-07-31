<?php

return [
    'adminEmail' => 'admin@example.com',
    'image_domain' => 'http://dev.poetryimg.leiyu.tv',
    'upload' => [
        'path' => ROOT_DIR . '/upload',
        'image' => [
            'img_type' => 'jpg,jpeg,png,bmp',
            'ocr' => [
                'max_size' => 4096,
                'max_width' => 200,
                'max_height' => 200,
                'default' => 'default_avatar.png'
            ]
        ]
    ],
    'unique_token' => 'LOCALYANYAN926TOKEN',
    'expire' => '604800', //秒数=7天
    'current_version'=> '2.0.1', //当前app版本号
    'old_version_01'=>'2.0.0', //老版本
    'code' => require_once 'code.php',
    'text' => require_once 'text.php',
    'baidu' => require_once 'baidu.php',
    'weixin' => require_once 'weixin.php',
    'document' => require_once 'document.php',
    'solr' => require_once 'solr.php',
    'page' => 1,
    'pagesize' => 10,
    'search_pagesize' => 5,
    'tag_changed' => 0,   // 标签是否更新
];
