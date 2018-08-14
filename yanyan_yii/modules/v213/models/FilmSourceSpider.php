<?php
/**
 * 播放源模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;
use app\models\RouteSpider;

class FilmSourceSpider extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%film_source}}';
    }
    
//    public static function getDb() {
//        return Yii::$app->get('db2');
//    }
    
    /**
     * 添加播放源
     * @param int $filmId
     * @param array $data
     * @param int $resource
     * @return type
     */
    public function add($filmId, $data, $resource) {
//        if($resource == '无双影视' || $resource == '007影视' || $resource == '去看TV' || $resource == '看看屋') {    // 无双影视
//            $label = '1';
//        }
        $label = '1';
        $sql = 'insert into ' . $this->tableName() . ' (`film_id`, `number`, `title`, `url`, `route_id`, `label`, `create_time`) values';
        foreach($data as $dataKey=>$item) {
            foreach($item as $value) {
                $parentRouteId = 0;
                $Route = new RouteSpider();
                $ParentRouteRecord = $Route->findByCondition(['name'=>$resource, 'parent_id'=>0])->one();
                if(!$ParentRouteRecord) {
                    $Route = new RouteSpider();
                    // 创建该路由
                    $Route->name = $resource;
                    $Route->parent_id = 0;
                    $Route->save();
                    $parentRouteId = $Route->id;
                } else {
                    $parentRouteId = $ParentRouteRecord->id;
                }
                $lastRouteRecord = $Route->findBySql('select count(id) as cnt from ' . $Route->tableName() . ' where parent_id=' . $parentRouteId)->asArray()->one();
                
                $RouteRecord = $Route->findByCondition(['parent_id'=>$parentRouteId, 'name'=>$dataKey])->one();
                if(!$RouteRecord) {
                    $Route = new RouteSpider();
                    $Route->parent_id = $parentRouteId;
                    $Route->name = $dataKey;
                    $Route->sort = $lastRouteRecord ? $lastRouteRecord['cnt'] + 1 : 1;
                    $Route->save();
                    $routeId = $Route->id;
                }else {
                    $routeId = $RouteRecord->id;
                }
                $sql .= '(' . $filmId . ', ' . $value['num'] . ', "' . $value['title'] . '", "' . $value['url'] . '", ' . $routeId . ', ' . $label . ', now()),';
            }
        }
        $sql = rtrim($sql, ',');
        $sql .= ' on duplicate key update title=values(title),url=values(url)';
        $conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql);
        return $cmd->execute();
    }
    
    
    

    
}

