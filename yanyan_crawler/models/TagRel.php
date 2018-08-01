<?php
/**
 * 标签影片关系模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;

class TagRel extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%film_tag_rel}}';
    }
    
    /**
     * 添加关联影片标签数据
     * @param type $filmId
     * @param type $channel
     * @param type $tagId
     */
    public function addTagRel($filmId, $channel, $tagId) {
        $Record = $this->findByCondition(['channel'=>$channel, 'tag_id'=>$tagId, 'film_id'=>$filmId])->one();
        if($Record) {
            return true;
        }
        $this->channel = $channel;
        $this->tag_id = $tagId;
        $this->film_id = $filmId;
        return $this->save();
    }

    
}

