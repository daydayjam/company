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

class TagPoetrySpider extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%tag_poetry}}';
    }
    
    /**
     * 添加诗词标签关联
     * @param array $tagIds
     * @return boolen
     */
    public function add($tagIds, $poetryId) {
        if(empty($tagIds)) {
            $TagRecord = Tag::findByCondition(['type'=>1, 'title'=>'未知'])->one();
            $tagIds[] = $TagRecord->id;
        }
        foreach($tagIds as $value) {
            $TagPoetryRecord = TagPoetry::findByCondition(['tag_id'=>$value, 'poetry_id'=>$poetryId])->one();
            if(!$TagPoetryRecord) {
                $this->tag_id = $value;
                $this->poetry_id = $poetryId;
                $this->save();
            }
            
        }
    }
    
    
}

