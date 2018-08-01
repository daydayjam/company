<?php
$route = [
    //未登录用户可以访问的路由
    'guest' => [
        'user/regcheck',
        'user/login',
        'user/register',
        'user/thirdlogin',
        'user/thirdregister',
        'user/findpwd',
        'user/followfilm',
        'user/info',
        'film/artists',
        'film/index',
        'film/info',
        'film/search',
        'film/hotlist',
        'film/epinfo',
        'feedback/add',
        'comment/newlist',
        'comment/info',
        'comment/filmlist',
        'comment/epcmtlist',
        'comment/userlist',
        'comment/cmtlist',
        'cmd/savefilm',
        'cmd/savesgfilm',
        'default/index',
        'default/exception',
        'authcode/get',
        'authcode/verify',
        'test/*',
        'motto/*',
        'default/add',
        'site/*'
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
