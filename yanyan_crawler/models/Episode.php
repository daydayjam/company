<?php
/**
 * 剧集模型类
 * @author ztt
 * @date 2018/01/18
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;
use app\models\Film;
use app\models\FollowFilm;
use app\models\Episode;

class Episode extends ActiveRecord {
    public static $ctype = [1, 2];
    const SINGLE_PRAISE_LIMIT = 10;    //单人点赞上限


    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%episode}}';
    }
    
    /**
     * 爬虫入剧集
     * @param int $fid
     * @param array $params
     * @return boolen
     */
    public function add($fid, $epList, $epTotal, $epUpdate) {
        $sql = "insert into ".$this->tableName()."(film_id,num,title,description,is_show,length,create_time) values";
        for($i = 0; $i < count($epList); $i++){
            $ep = $epList[$i];
            
            if($epUpdate >= $ep['ep_num']) {
                $ep['ep_status'] = 1;
            } else {
                continue;
            }
            
            
            
            if($i > 0){
                $sql .= ",";
            }

            $sql .= "(".$fid.",'".addslashes($ep['ep_num'])."','".($ep['ep_title'] ? addslashes($ep['ep_title']) : '')."','".($ep['ep_desc'] ? addslashes($ep['ep_desc']) : '')."','".(addslashes($ep['ep_status']) ? 1 : 0)."','".(isset($ep['ep_len']) ? addslashes($ep['ep_len']) : '')."',now())";
  	}
        $sql .= " on duplicate key update film_id=values(film_id),num=values(num),title=if(values(title)!='',values(title),title),description=if(values(description)!='',values(description),description),is_show=values(is_show),length=values(length)";
        $conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql);
        return $cmd->execute();
    }
    

    
}

