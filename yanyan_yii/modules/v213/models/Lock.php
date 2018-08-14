<?php
/**
 * 锁操作类
 * @author ztt
 * @date 2017/11/13
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
class Lock extends ActiveRecord {
    
    /**
     * 加锁
     * @param string $key 键名
     * @return boolen true=加锁成功
     */
    public static function addlock($key) {
        $Redis = Yii::$app->redis;
        if($Redis->setnx($key, 1)) {
            $Redis->expire($key, 30);
            return true;
        }
        //防止死锁
        if($Redis->ttl($key) == -1) {
            $Redis->expire($key, 5);
        }
        return false;
    }
    
    /**
     * 解锁
     * @param string $key 键名
     * @return boolen true=解锁成功
     */
    public static function unLock($key) {
        return Yii::$app->redis->del($key);
    }
    
}


