<?php
/**
 * 演员模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class RouteSpider extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%route}}';
    }
    
//    public static function getDb() {
//        return Yii::$app->get('db2');
//    }
    

    
}

