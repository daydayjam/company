<?php
$route = [
    //未登录用户可以访问的路由
    'guest' => [
        'test/*',
        'user/login',
        'poetry/*',
        'document/info',
        'tag/*',
        'exception/*',
        'recite/send',
        'spider/*',
        'rhesis/*',
        'dictionary/*'
    ]
];

return $route;
