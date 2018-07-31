<?php
/**
 * 线路模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;

class FilmSourceFeedback extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%film_source_feedback}}';
    }
    
    /**
     * 添加片源报错反馈信息
     * @param int $userId 用户ID
     * @param int $filmId 影视剧ID
     * @param int $epNum 剧集编号
     * @param int $routeId 线路ID
     * @return boolen 
     */
    public function add($userId, $filmId, $epNum, $routeId) {
        $this->user_id = $userId;
        $this->film_id = $filmId;
        $this->number = $epNum;
        $this->route_id = $routeId;
        return $this->save();
    }
    
    
    
    
    
}

