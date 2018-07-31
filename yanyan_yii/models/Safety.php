<?php
/**
 * 安全机制操作类
 * @author zhangtiantian
 * date:2017/10/30
 */
namespace app\models;

use app\models\Cache;

class Safety
{
	
    /**
     * 计算20分钟内当前IP访问次数
     * @param string $action 访问类型
     * @param string $className 类名，默认为当前类名
     * @return int 访问次数
     */
    public static function ipTimes($action, $checkTime = 1200, $className = __CLASS__) {
        //设置key值，如果用户已登录则将userId也设置入key值
        $loginUid = Cache::hget('id');
        $key = $className.'_'.$action;
        if($loginUid){
            $key.= '_'.$loginUid;
        }
        //获取缓存中的key值,获取到的是一个数组，格式为array(111111,111111,111111...)
        $history = unserialize(Cache::get($key));
        //设置访问次数初始值
        $times = 0;
        $currentTime = time();
        $newHistory = array();
        //判断缓存是否存在，若存在则遍历其中的值，
        //若遍历到的值在20分钟以内，则$times+1
        if ($history) {
            foreach ($history as $timestamp) {
                $diffTime = $currentTime - $checkTime;
                if ($diffTime < $timestamp) {
                    $times ++;
                    $newHistory[] = $timestamp;
                }
            }
        }
        $newHistory[] = $currentTime;
        Cache::setex($key, $checkTime, serialize($newHistory));
        return $times;
    }

    /**
     * 删除用户的ip计数器
     * @param string $action 访问类型
     * @param string 类名，默认当前类名
     * @return void
     */
     public static function ipClear($action, $className = __CLASS__) {
        $loginUid = Cache::hget('id');
        //设置key值，如果用户已登录则将userId也设置入key值
        $key = $className.'_'.$action;
        if (isset($loginUid)) {
            $key .= '_'.$loginUid;
        }
        $history = unserialize(Cache::get($key));
        if($history) {
            Cache::del($key);
        }
     }

    /**
     * 获取用户IP
     * @return string 用户IP地址
     */
     public static function getClientIp() {
        if(getenv('HTTP_CLIENT_IP')) {
                $ip = getenv('HTTP_CLIENT_IP');
        }else if(getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
        }else if(getenv('REMOTE_ADDR')) {
                $ip = getenv('REMOTE_ADDR');  
        }else {
                $ip = '';
        }
        return $ip;
     }

}