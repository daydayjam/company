<?php
/**
 * 设置模型类
 * @author ztt
 * @date 2017/10/31
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class Report extends ActiveRecord {
    public static $reason = [0,1,2,3,4,5];    //举报原因，0=其他；1=暴力色情；2=人身攻击；3=广告骚扰；4=谣言及虚假信息；5=政治敏感，默认为0
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%report}}';
    }
    
    /**
     * 添加举报信息
     * @param type $uid 用户ID
     * @param type $assocId 被举报信息关联ID
     * @param type $type 举报对象，0=举报信息；1=举报人，默认为0
     * @param type $reason 举报原因，0=其他；1=暴力色情；2=人身攻击；3=广告骚扰；4=谣言及虚假信息；5=政治敏感，默认为0
     */
    public function add($uid, $assocId, $type = 0, $reason = 0) {
        if(!is_numeric($uid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(empty($assocId) || !is_numeric($assocId)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!in_array($type, [0, 1])) {
            $this->addError('', '-4:举报对象格式有误');
            return false;
        }
        if(!in_array($reason, self::$reason)) {
            $this->addError('', '-4:举报对象格式有误');
            return false;
        }
        $User = new User();
        $UserRecord = $User->findOne($uid);
        if(!$UserRecord) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        if($type == 0) {    //举报信息
           $Comment = new Comment();
           $CommentRecord = $Comment->findOne($assocId);
           if(!$CommentRecord) {
                $this->addError('', '-7:要举报的信息不存在');
                return false;
           }
        } else {    //举报人
            $UserReportedRecord = $User->findOne($assocId);
            if(!$UserReportedRecord) {
                $this->addError('', '-7:要举报的用户不存在');
                return false;
           }
           if($UserReportedRecord->ustatus == -1) {
               $this->addError('', '-103:该用户已因涉嫌违规已被停封处理，无法操作');
                return false;
           }
        }
        $Record = $this->findByCondition(['uid'=>$uid, 'assoc_id'=>$assocId, 'type'=>$type, 'reason'=>$reason])->one();
        if($Record) {
            $this->addError('', '-9:请勿重复举报');
            return false;
        }
        $this->uid = $uid;
        $this->type = $type;
        $this->reason = $reason;
        $this->assoc_id = $assocId;
        if(!$this->save()) {
            $this->addError('', '0:举报失败');
            return false;
        }
        return true;
    }
    
}

