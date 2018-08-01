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

class FilmProgress extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%film_progress}}';
    }
    
    /**
     * 
     * @param type $filmId
     * @param type $epUpdate
     * @param type $type
     */
    public function updateProgress($filmId, $params) {
        $epUpdate = $params['episode_number'];
        $type = $params['type'];
        $Record = $this->findByCondition(['film_id'=>$filmId, 'type'=>$type])->one();
        if(!$Record) {
            $this->film_id = $filmId;
            $this->type = $type;
            $this->ep_update = $epUpdate;
            $this->save();
        }else {
            $Film = new Film();
            $FilmRecord = $Film->findByCondition(['type'=>$type, 'title'=>$params['title'], 'year'=>$params['year']])->one();
            if($FilmRecord->type == 1) { // 电影
                $FilmSource = new FilmSource();
                $oldSql = 'select * from ' . $FilmSource->tableName() . ' where film_id=:film_id and unix_timestamp(create_time) < unix_timestamp(DATE_FORMAT(NOW(),"%Y-%m-%d"))';
                $OldFilmSourceRecord = $FilmSource->findBySql($oldSql, [':film_id'=>$filmId])->one();
                if($OldFilmSourceRecord) {
                    $newSql = 'select * from ' . $FilmSource->tableName() . ' where film_id=:film_id and unix_timestamp(create_time) > unix_timestamp(DATE_FORMAT(NOW(),"%Y-%m-%d"))';
                    $newFilmSourceRecords = $FilmSource->findBySql($newSql, [':film_id'=>$filmId])->all();
                    $epToday = '';
                    $epUpdate = 1;
                    foreach($newFilmSourceRecords as $SourceRecord) {
                        $epToday .= $SourceRecord->route_id . '/';
                    }
                }
            }else { // 电视剧
                $oldUpdate = $Record->ep_update;
                $epToday = '';
                if($oldUpdate != $epUpdate && $epUpdate > $oldUpdate) {
                    $epUpdateNum = $epUpdate - $oldUpdate;
                    for($i = 1; $i <= $epUpdateNum; $i ++) {
                        $epToday .= ($oldUpdate + $i) . '/';
                    }
                }
            }
            $Record->ep_update = $epUpdate;
            $Record->ep_today = $epToday ? rtrim($epToday, '/') : $epToday;
            return $Record->save();
        }
    }
    
}

