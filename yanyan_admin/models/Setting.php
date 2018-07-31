<?php
/**
 * 设置模型类
 * @author ztt
 * @date 2017/10/31
 */
namespace app\models;

use app\components\ActiveRecord;

class Setting extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%setting}}';
    }
    
    /**
     * 获取某用户的设置列表
     * @param int $uid 用户ID
     * @return array 设置列表
     */
    public function getList($uid) {
        $sql = 'select * from ' . $this->tableName() . ' where uid=:uid';
        $params = ['uid' => $uid];
        $record = $this->findBySql($sql, $params)->asArray()->one();
        $record = empty($record) ? [] : $record;
        return $record;
    }
    
    /**
     * 添加用户设置
     */
    public function add($uid) {
        $this->uid = $uid;
        return $this->save();
    }
    
    /**
     * 更改用户推送设置
     * @param int $uid 用户ID
     * @param int $isDisturb 是否免打扰，0=否；1=是，默认为0
     * @param int $isShowmsg 是否显示通知内容，0=否；1=是，默认为1
     * @param int $isSound 声音是否开启，0=否；1=是，默认为1
     * @param int $isShock 震动是否开启，0=否；1=是，默认为1
     * @return boolean true=修改成功
     */
    public function updateSetting($uid, $isDisturb = 0, $isShowmsg = 1, $isSound = 1, $isShock = 1) {
        $setArr = [0, 1];
        if(!is_numeric($uid)) {
            $this->addError('', '-4:参数格式不正确');
           return false;
        }
        if(!in_array($isDisturb, $setArr)) {
           $this->addError('', '-4:参数格式不正确');
           return false;
        }
        if(!in_array($isShowmsg, $setArr)) {
           $this->addError('', '-4:参数格式不正确');
           return false;
        }
        if(!in_array($isSound, $setArr)) {
           $this->addError('', '-4:参数格式不正确');
           return false;
        }
        if(!in_array($isShock, $setArr)) {
           $this->addError('', '-4:参数格式不正确');
           return false;
        }
        $Record = $this->findOne($uid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        $isCommit = false;
        if($Record->is_disturb != $isDisturb) {
            $isCommit = true;
            $Record->is_disturb = $isDisturb;
        }
        if($Record->is_showmsg != $isShowmsg) {
            $isCommit = true;
            $Record->is_showmsg = $isShowmsg;
        }
        if($Record->is_sound != $isSound) {
            $isCommit = true;
            $Record->is_sound = $isSound;
        }
        if($Record->is_shock != $isShock) {
            $isCommit = true;
            $Record->is_shock = $isShock;
        }
        if($isCommit && !$Record->save()) {
            $this->addError('', '0:修改失败');
            return false;
        }
        return true;
    }
    
}

