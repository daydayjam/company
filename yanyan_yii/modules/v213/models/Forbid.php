<?php
/**
 * 禁言模型类
 * @author ztt
 * @date 2017/12/13
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class Forbid extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%forbids}}';
    }
    
    /**
     * 添加禁言
     * @param int $uid 用户ID
     * @param int $roomId 聊天室ID
     * @return boolen true=添加成功
     */
    public function add($uid, $roomId) {
        $Record = $this->findByCondition(['room_id'=>$roomId, 'uid'=>$uid])->one();
        if($Record) {
            $Record->start_time = date('Y-m-d H:i:s');
//        $Record->end_time = date('Y-m-d H:i:s', strtotime('+ 1 hour'));
            $Record->end_time = date('Y-m-d H:i:s', strtotime('+ 10 minute'));
            return $Record->save();
        }
        $this->room_id = $roomId;
        $this->uid = $uid;
        $this->start_time = date('Y-m-d H:i:s');
//        $this->end_time = date('Y-m-d H:i:s', strtotime('+ 1 hour'));
        $this->end_time = date('Y-m-d H:i:s', strtotime('+ 10 minute'));
        return $this->save();
    }
    
    
}

