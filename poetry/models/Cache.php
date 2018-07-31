<?php
/**
 * 缓存类，包含缓存操作简易调用
 * @author ztt
 * @date 2017/11/21
 */
namespace app\models;

use Yii;

class Cache {
    
    /**
     * 添加缓存
     * @param string $key 键名
     * @param string $value 需要缓存的值
     * @return boolen true=添加成功
     */
    public static function set($key, $value) {
        return Yii::$app->redis->set($key, $value);
    }
    
    /**
     * 添加缓存并设置失效时间
     * @param string $key 键名
     * @param int $expire 失效时间，秒数
     * @param string $value 需要缓存的值
     * @return boolen true=设置成功
     */
    public static function setex($key, $expire, $value) {
        return Yii::$app->redis->setex($key, $expire, $value);
    }
    
    /**
     * 获取键值
     * @param string $key 键名
     * @return mixed 键值
     */
    public static function get($key) {
        return Yii::$app->redis->get($key);
    }
    
    /**
     * 将哈希表 key 中的域 field 的值设为 value
     * @param string $field  字段名
     * @param string $value 需要缓存的值
     * @param string $key 键名
     * @return mixed
     */
    public static function hset($field, $value, $key = '') {
        if($key == '') {
            if(isset($_REQUEST['token'])) {
                $key = $_REQUEST['token'];
            } else {
                return false;
            }
        }
        return Yii::$app->redis->hset($key, $field, $value);
    }
    
    /**
     * 同时将多个 field-value (域-值)对设置到哈希表 key 中
     * @param string $key 键名
     * @param string $filed  字段名
     * @param string $value 需要缓存的值
     * @return mixed
     */
//    public static function hmset($key, $field, $value) {
//        return Yii::$app->redis->hset($key, $field, $value);
//    }
    
    /**
     * 返回哈希表 key 中给定域 field 的值。
     * @param string $field  字段名
     * @param string $key 键名
     * @return mixed
     */
    public static function hget($field, $key = '') {
        if($key == '') {
            if(isset($_REQUEST['token'])) {
                $key = $_REQUEST['token'];
            } else {
                return false;
            }
        }
        return Yii::$app->redis->hget($key, $field);
    }
    
    /**
     * 返回哈希表 key 中，所有的域和值。
     * @param string $key 键名
     * @return mixed 以列表形式返回哈希表的域和域的值。若 key 不存在，返回空列表。
     */
    public static function hgetall($key = '') {
        if($key == '') {
            if(isset($_REQUEST['token'])) {
                $key = $_REQUEST['token'];
            } else {
                return false;
            }
        }
        return Yii::$app->redis->hgetall($key);
    }
    
    /**
     * 查看哈希表 key 中，给定域 field 是否存在。
     * @param string $key 键名
     * @param string $field  字段名
     * @return int 1=存在；0=不存在
     */
    public static function hexists($key, $field) {
        return Yii::$app->redis->hexists($key, $field);
    }
    
    /**
     * 检查给定 key 是否存在
     * @param string $key 键名
     * @return int 1=存在；0=不存在
     */
    public static function exists($key) {
        return Yii::$app->redis->exists($key);
    }
    
    /**
     * 设置过期时间
     * @param string $key 键名
     * @param int $expire 过期时间，秒数，默认7天
     * @return int 1=设置成功；0=设置失败
     */
    public static function expire($key, $expire) {
        return Yii::$app->redis->expire($key, $expire);
    }

    /**
     * 删除一个或多个缓存
     * @param array $keys 键名
     * @return boolen true=删除成功
     */
    public static function del($keys) {
        return Yii::$app->redis->del($keys);
    }
    /**
     * 将一个或多个值 value 插入到列表 key 的表头
     * @param string $field  字段名
     * @param string $value 需要缓存的值
     * @param string $key 键名
     * @return mixed
     */
    public static function lpush($key, $value) {
        return Yii::$app->redis->lpush($key, $value);
    }
    
    public static function lrange($key, $start, $end) {
        return Yii::$app->redis->lrange($key, $start, $end);
    }
    
    /**
     * 递增1
     * @param type $key
     * @return type
     */
    public static function incr($key) {
        return Yii::$app->redis->incr($key);
    } 
}

