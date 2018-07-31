<?php

/**
 * 用户打卡签到操作类
 * @date 2018/06/026
 * @author ztt
 */
namespace app\models;

use app\components\ActiveRecord;

class SignLog extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%sign_log}}';
    }
    
    /**
     * 用户打卡签到记录
     * @return int
     */
    public function add() {
        $loginUserId = Cache::hget('user_id');
        
        $Record = $this->find()->where(['user_id'=>$loginUserId])->orderBy('create_time desc')->limit(1)->one();
        if(!$Record) {
            $this->user_id = $loginUserId;
            $this->sign_day = 1;
            if(!$this->save()) {
                return $this->addError('', '400:签到失败，请稍后重试');
            }
            return $this->sign_day;
        }
        // 判断当日是否已签到
        if(Tool::isToday($Record->create_time)) {
            return $this->addError('', '401:您今日已签到');
        }
        $this->user_id = $loginUserId;
        $this->sign_day = $Record->sign_day + 1;
        if(!$this->save()) {
            return $this->addError('', '400:签到失败，请稍后重试');
        }
        return $this->sign_day;
    }
}

