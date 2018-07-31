<?php
$route = [
    //未登录用户可以访问的路由
    'guest' => [
        'admin/login',
        'admin/signin',
        'admin/signout',
        
        
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
