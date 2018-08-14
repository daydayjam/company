<?php
/**
 * 线路模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;

class Route extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%route}}';
    }
    
    /**
     * 获取完整名称
     * @param int $routeId 线路ID
     * @return string
     */
    public function getFullName($routeId) {
        $Record = $this->findOne($routeId);
        if(!$Record) {
            return '线路异常';
        }
        $ParentRecord = $this->findOne($Record->parent_id);
        if(!$ParentRecord) {
            return '线路异常';
        }
        return $ParentRecord->name . ' 频道' . $Record->sort;
    }
    
    
    
    
    
}

