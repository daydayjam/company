<?php
/**
 * 和文字说明相关的文案配置
 */
return [
    // 小程序唯一标识
   'APP_ID' => 'wx62d4d8858e95079f',
    // 小程序的 app secret
   'SECRET' => 'd7db250f23fab4dacf3414493135211e',
    // 授权类型
   'GRANT_TYPE' => 'authorization_code',
    // 自定义项目微信私钥
    'PRIVATE_KEY' => '_#sign_poetry',
    // 本地服务器token失效时间,30天
    'EXPIRE_TIME' => 259200,
    // 微信accesstoken键名
    'ACCESS_TOKEN_KEY' => 'poetry_weixin_#key',
    // 微信获取accessToken的url
    'ACCESS_TOKEN_URL' => 'https://api.weixin.qq.com/cgi-bin/token',
    // 获取access_token填写client_credential
    'ACCESS_TOKEN_GRANT_TYPE' => 'client_credential',
    // 模板消息ID
    'TEMPLATE_ID' => 'OW3B9PrbAQ8rl_hKKEbPvzeb75Zg647aXfJ9JSjog8I',
    // 当日是否已经发过模板消息的前缀
    'TEMPLATE_TODAY_PREFIX' => 'TEMPLATE_TODAY_'
];

