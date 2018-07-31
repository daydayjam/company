<?php
/**
 * 文章模型类
 * @author ztt
 * @date 2018/03/09
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class AuthorSpider extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%author}}';
    }
    
    /**
     * 
     * @param type $name
     * @param type $year
     * @param type $authorInfo
     * @return type
     */
    public function add($name, $year, $avatar, $description) {
        
        $sql = "insert into ".$this->tableName()
                . "(name,year,avatar,description,create_time,update_time) "
                . "values(:name,:year,:avatar,:description,unix_timestamp(now()),unix_timestamp(now())) "
                . "on duplicate key update avatar=values(avatar),description=values(description)";
		$bindParams = array(
                        ":name"        => $name,
                        ":year"        => $year,
                        ":avatar"      => $avatar,
                        ":description" => $description
                );	
        $conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql, $bindParams);
        if($cmd->execute()) {
            $id = Yii::$app->db->getLastInsertID();
            return $id;
        }else {
            if($name == '佚名') {
                $Record = $this->findByCondition(['name'=>$name, 'year'=>$year])->one();
            }else {
                $Record = $this->findByCondition(['name'=>$name, 'description'=>$description])->one();
            }
            if(!$Record) {
               return false;
            }
            return $Record->id;
        }
    }
}
