<?php

/**
 * 诗词操作类
 * @date 2018/06/06
 * @author ztt
 */
namespace app\models;
use Yii;
use app\components\ActiveRecord;
use yii\db\Query;

class TagPoetry extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%tag_poetry}}';
    }
    
    
}

