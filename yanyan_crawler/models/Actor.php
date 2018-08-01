<?php
/**
 * 演员模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;

class Actor extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%actors}}';
    }
    
    /**
     * 演员入库
     * @param type $actors
     * @return type
     */
    public function add($actors) {
        $sql = "insert into ".$this->tableName()."(id,act_name,en_name,avatar,country,birth_date,act_desc) values";
  	for($i = 0; $i < count($actors); $i++){
            $act = $actors[$i];
            if($i > 0){
                $sql .= ",";
            }
            $birthDate = trim($act['info']['birth_date']);
            if(!empty($birthDate)) {
                if(preg_match('/^\d{4}/', $birthDate)) {
                    $index = strpos($birthDate, '~');
                    if($index === 0) {
                        $birthDate = '';
                    }else {
                        if($index) {
                            $birthDate = trim(substr($birthDate, 0, $index));
                        }
                    }
                }
            }
            $country = $act['info']['country'];

            $sql .= "(".$act["id"].",'".addslashes($act["name"])."','".addslashes($act["en_name"])."','".addslashes($act["avatar"])."','".addslashes($country)."','".addslashes($birthDate)."','".addslashes($act["info"]['act_desc'])."')";
  	}
  	$sql .= " on duplicate key update act_name=values(act_name),en_name=values(en_name),avatar=values(avatar),country=values(country),birth_date=values(birth_date),act_desc=values(act_desc)";
  	$conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql);
        return $cmd->execute();
    }

    
}

