<?php
/**
 * 线路模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class Poetry extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%poetry}}';
    }
    
    public static function getDb() {
        return Yii::$app->get('db2');
    }
    
    
    
    
    
    
    
}

