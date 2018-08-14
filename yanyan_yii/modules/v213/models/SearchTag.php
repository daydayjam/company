<?php
/**
 * 搜索标签模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;

class SearchTag extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%search_tag}}';
    }
    
    /**
     * 获取标签列表
     * @return void
     */
    public function getList() {
        $searchTagKey = 'SEARCH_TAG';
        $result = Cache::get($searchTagKey);
        if($result) {
            return json_decode($result, true);
        }
        $result = [];
        $parentRecords = $this->findBySql('select id from ' . $this->tableName() . ' where parent_id=0')->orderBy('listorder asc')->asArray()->all();
        foreach($parentRecords as $key=>$item) {
            $sonRecords = $this->findBySql('select name from ' . $this->tableName() . ' where parent_id='.$item['id'])->orderBy('listorder asc')->asArray()->all();
            foreach($sonRecords as $sonRecord) {
                $result[$key][] = $sonRecord['name'];
            }
        }
        Cache::set($searchTagKey, json_encode($result));
        return $result;
    }
    
    
    
    
    
}

