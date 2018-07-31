<?php

/*
 * 创建一个继承自活动记录类的类History。去代表和读取History表的数据
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class History extends ActiveRecord {
    /*
     * 设置默认的表名 ，供框架调用
     * @return 表名
     */

    public static function tableName() {
        return '{{%history}}';
    }

    /**
     * 历史记录添加
     * @param int $userId 被操作的用户ID
     * @param int $assocId 被操作的记录的ID
     * @param string $action 操作名
     * @param array $data 存储的数据
     * @return boolen 是否保存成功  true=成功
     */
    public static function add($userId, $assocId, $action, $data) {
        $History = new History();
        $History->uid = $userId;
        $History->assoc_id = $assocId;
        $History->action = $action;
        $History->old_data = json_encode($data);
        $History->client_ip = ip2long(Yii::$app->request->getUserIP());
        if ($History->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取用户历史记录列表
     * @param string $action 操作名
     * @param int $page 默认当前页码
     * @param int $record 默认每页记录数
     * @return array 返回用户列表信息
     */
    public function getList($action, $page = 1, $record = 10) {
        $result = array(
            'total' => 0,
            'rows' => array()
        );
        if (!preg_match('/\d+/', $page) || $page < 1) {
            return $result;
        }
        if (!preg_match('/\d+/', $record) || $record < 1) {
            return $result;
        }
        $sql = 'select count(*) as count from ' . $this->tableName() . ' where action=:action';
        $Cmd = Yii::app()->db->createCommand($sql);
        $row = $Cmd->queryRow(true, array(':action' => $action));
        $result['total'] = $row['count'];
        $sql = 'select * from ' . $this->tableName() . ' where action=:action limit :offset, :limit';
        $Cmd->setText($sql);
        $offset = ($page - 1) * $record;
        $Cmd->bindValue(':action', $action);
        $Cmd->bindValue(':offset', $offset);
        $Cmd->bindValue(':limit', intval($record));
        //$param = array(':action'=> $action, ':offset'=>$offset, ':limit'=>$record);
        //$resutl['rows'] = $Cmd->queryAll(true, $param);
        $result['rows'] = $Cmd->queryAll(true);
        foreach ($result['rows'] as $key => $item) {
            $result['rows'][$key]['old_data'] = json_decode($item['old_data'], true);
        }
        return $result;
    }

}
