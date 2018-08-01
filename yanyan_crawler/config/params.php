<?php

return [
    'adminEmail' => 'admin@example.com',
    'domain' => 'http://127.0.0.2',
    'image_domain' => 'http://127.0.0.4',
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
    'unique_token' => 'LOCALYANYAN926TOKEN',
    'expire' => '604800', //秒数=7天
    'current_version'=> '2.0.1', //当前app版本号
    'old_version_01'=>'2.0.0', //老版本
];
