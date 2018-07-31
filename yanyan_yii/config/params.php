<?php

return [
    'adminEmail' => 'admin@example.com',
    'web_url' => 'https://yy.leiyu.tv',
    'image_domain' => 'http://img.leiyu.tv',
    'upload' => [
        'path' => ROOT_DIR . '/upload',
        'image' => [
            'img_type' => 'jpg,jpeg,gif,png',
            'avatar' => [
                'max_size' => 100,
                'max_width' => 200,
                'max_height' => 200,
                'default' => 'default_avatar.png'
            ],
            'normal' => [
                'max_size' => 500,
                'max_width' => 1920,
                'max_height' => 1920,
                'default' => 'default_avatar.png'
            ]
        ]
    ],
    'unique_token' => 'YANYAN926TOKEN',
    'expire' => '604800', //秒数=7天
    'current_version'=> '2.0.1', //当前app版本号
    'old_version_01'=>'2.0.0', //老版本
    'state_code' => require_once 'code.php',
    'text_desc' => require_once 'text.php',
    'page_size' => 10,
    'version' => require_once 'version.php'
];
