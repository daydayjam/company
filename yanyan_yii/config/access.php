<?php
$route = [
    //未登录用户可以访问的路由
    'guest' => [
        'test/*',
        'film/*',
        'filmsource/*',
        'viewrecord/*',
        'comment/*',
        'user/*',
        'spider/*',
        'news/*',
        'default/*',
        'init/*'
    ],
    //冻结用户可以访问的路由
    'freeze' => [
        'user/register',
        'user/regcheck',
        'user/info',
        'user/thirdregister',
        'feedback/add',
        'report/add',
        
    ]
];

return $route;
