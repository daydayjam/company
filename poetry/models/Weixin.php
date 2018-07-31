<?php
/**
 * 微信操作类
 * @date 2018/06/05
 * @author ztt
 */
namespace app\models;
use Yii;

class Weixin {
    // 置换 session_key 和 openid 的url地址
    const URL = 'https://api.weixin.qq.com/sns/jscode2session';
    
    /**
     * 通过code获取session_key 和 openid等
     * @param string $code 临时登录凭证code
     * @return array
     */
    public function codeToInfo($code) {
        if(empty($code)) {
            return false;
        }
        $data = [
            'appid'      => Yii::$app->params['weixin']['APP_ID'],
            'secret'     => Yii::$app->params['weixin']['SECRET'],
            'js_code'    => $code,
            'grant_type' => Yii::$app->params['weixin']['GRANT_TYPE']
        ];
        
        $url = self::URL . '?' . http_build_query($data);
        $result = json_decode($this->curlRequest($url), true);
        if(isset($result['errcode'])) {
            return false;
        }
        return $result;
    }
   
    /**
     * 
     * @param type $url 访问的URL
     * @param type $post post数据(不填则为GET)
     * @param type $cookie 提交的$cookies
     * @param type $returnCookie 是否返回$cookies
     * @return type
     */
    public function curlRequest($url, $post = '', $cookie = '', $returnCookie = 0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);// https
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
    }   
}

