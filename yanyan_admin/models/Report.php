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
    public static $type = [0, 1];
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%report}}';
    }
    
    /**
     * 
     * @param type $status
     * @param type $uid
     * @param type $mobile
     * @param type $nick
     * @param type $page
     * @return type
     */
    public function getList($params, $page = 1, $record = 20) {
        $andParams = [];
        if($params['reason']) {
            $andParams['reason'] = $params['reason'];
        }
        if($params['status']) {
            $andParams['status'] = $params['status'];
        }
        if($params['type']) {
            $andParams['type'] = $params['type'];
        }
        $result = $this->getListData($select = '*', $page, $record, $andParams);
        foreach($result['rows'] as $key=>$item) {
            if($item['uid']) {
                $User = new User();
                $UserRecord = $User->findOne($item['uid']);
                if($UserRecord) {
                    $result['rows'][$key]['nick'] = $UserRecord->nickname;
                }
            }
            if($item['type'] == 1) {
                $User = new User();
                $UserRecord = $User->findOne($item['assoc_id']);
                if($UserRecord) {
                    $result['rows'][$key]['to_info'] = $UserRecord->nickname;
                }
            } else {
                $result['rows'][$key]['to_info'] = '';
                $Comment = new Comment();
                $CommentRecord = $Comment->findOne($item['assoc_id']);
                if($CommentRecord) {
                    $result['rows'][$key]['to_info'] = $CommentRecord->comment;
                }
            }
        }
        $result['page'] = $page;
        $result['record'] = $record;
        return $result;
    }
    
}

