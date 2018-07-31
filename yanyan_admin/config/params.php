<?php

return [
    'adminEmail' => 'admin@example.com',
    'image_domain' => 'http://img.yy.leiyu.tv',
    'upload' => [
        'path' => 'D:/PhpSystem/www/yanyan/upload',
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
            ],
            'chatroom' => [
                'max_size' => 500,
                'max_width' => 1920,
                'max_height' => 1920,
                'default' => 'default_avatar.png'
            ],
            'film' => [
                'max_size' => 500,
                'max_width' => 1920,
                'max_height' => 1920,
                'default' => 'default_avatar.png'
            ]
        ]
    ],
    'unique_token' => 'YANYANADMIN',
    'expire' => '604800', //秒数=7天
];
