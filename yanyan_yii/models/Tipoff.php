<?php
/**
 * 举报模型类
 * @author ztt
 * @date 2017/12/14
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class Tipoff extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%tipoff}}';
    }
    
    /**
     * 添加举报记录
     * @param int $uid 用户ID
     * @param int $roomId 聊天室ID，即影视剧ID
     * @param int $to_uid 要举报的用户ID
     * @param string $reason 举报原因
     * @param int $needNum 需要同意的人数
     * @return type
     */
    public function add($uid, $roomId, $to_uid, $reason, $needNum = 3) {
        $this->uid = $uid;
        $this->to_uid = $to_uid;
        $this->room_id = $roomId;
        $this->reason = $reason;
        $this->need_num = $needNum;
        return $this->save();
    }
    
    /**
     * 更新已同意人数信息
     * @param int $tipoffId 举报ID
     * @param string $agreeUids 已同意的用户ID串，格式 1,2,3
     * @return boolen true=添加成功
     */
    public function updateAgreeNum($tipoffId, $agreeUids) {
        $Record = $this->findOne($tipoffId);
        $Record->agree_num = $Record->agree_num + 1;
        $Record->agree_uids = $agreeUids;
        return $Record->save();
    }
    
    
}

