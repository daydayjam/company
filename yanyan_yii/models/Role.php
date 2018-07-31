<?php
/**
 * 角色模型类
 * @author ztt
 * @date 2017/12/13
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\User;
use app\models\Black;
use app\models\Cmd;

class Role extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%roles}}';
    }
    
    
    
}

