<?php
/**
 * 反馈模型类
 * @author ztt
 * @date 2017/11/30
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class Feedback extends ActiveRecord {
    public static $type = [1=>'意见&bug反馈',2=>'封停申诉'];    //反馈类型,1=意见&bug反馈，2=封停申诉
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%feedback}}';
    }
    
    /**
     * 获取列表
     * @param type $params
     * @param int $page
     * @param int $record
     * @return array|boolen
     */
    public function getList($params, $page = 1, $record = 20) {
        $andParams = [];
        $select = 'id,type,uid,content,contact,create_time';
        $result = $this->getListData($select, $page, $record, $andParams);
        foreach($result['rows'] as $key=>$item) {
            $result['rows'][$key]['type'] = self::$type[$item['type']];
            if($item['uid']) {
                $User = new User();
                $UserRecord = $User->findOne($item['uid']);
                if($UserRecord) {
                    $result['rows'][$key]['nickname'] = $UserRecord->nickname;
                }
            }
            
        }
        $result['page'] = $page;
        $result['record'] = $record;
        return $result;
    }
    
}

