<?php

/**
 * 诗词操作类
 * @date 2018/06/06
 * @author ztt
 */
namespace app\models;
use Yii;
use app\components\ActiveRecord;
use yii\db\Query;

class UrlSpider extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%url}}';
    }
    
    /**
     * 保存数据入表
     * @param type $params
     * @return type
     */
    public function addToTb($data) {
        $data = json_decode($data, true);
//        print_r($data);die;
        if(empty($data)) {
           die(); 
        }
        $sql = "insert into ".$this->tableName()
                . "(url) "
                . "values(:url)";
		$bindParams = array(
                    ':url' => $data['url']
                );	
        $conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql, $bindParams);
        $cmd->execute();
    }
    
}

