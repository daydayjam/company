<?php
/**
 * 透传模型类
 * @author ztt
 * @date 2017/11/24
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\CmdHelper;


class Cmd extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%cmds}}';
    }
    
    /**
     * 
     * @param type $content
     * @return boolean
     */
    public function add($content) {
        $this->content = json_encode($content);
        if(!$this->save()) {
            return false;
        }
        return $this->id;
    }
    
    
    
    

    
}

