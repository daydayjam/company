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
     * 获取剧集列表
     * @param int $filmId 电视剧ID
     * @param int $followEp 已经看到第几集
     * @return array
     */
    public function getList($filmId, $totalEp, $updateEp, $followEp) {
        $Query = new Query();
        $result = $Query->select('num,title,is_show')
                        ->from($this->tableName())
                        ->where(['film_id'=>$filmId])
                        ->all();
        foreach($result as $key=>$item) {
            if(!$item['title']) {
                $result[$key]['title'] = '第'.$item['num'].'集';
            }
            if(!$item['is_show']) {
                $result[$key]['title'] = '尚未公布';
            }
            $result[$key]['is_watch'] = 0;
            if($item['num'] <= $followEp) {
                $result[$key]['is_watch'] = 1;
            }
        }
        if($totalEp > $updateEp && count($result) != $totalEp) {
            for($i = $updateEp; $i<$totalEp; $i++) {
                $result[$i]['num'] = $i+1;
                $result[$i]['title'] = '尚未公布';
                $result[$i]['is_show'] = 0;
                $result[$i]['is_watch'] = 0;
            }
        }
        return $result;
    }
    
    /**
     * 获取剧集详情
     * @param int $filmId 影视剧ID
     * @param int $epNum 剧集编号
     * @return array
     */
    public function getInfo($filmId, $epNum) {
        $uid = Cache::hget('id');
        $Film = new Film();
        $FilmRecord = $Film->findOne($filmId);
        $Episode = new Episode();
        $EpisodeRecord = $Episode->findByCondition(['film_id'=>$filmId, 'num'=>$epNum])->one();
        $FollowFilm = new FollowFilm();
        $FollowFilmRecord = $FollowFilm->findByCondition(['film_id'=>$filmId, 'uid'=>$uid])->one();
        $epStatus = $Film->getFilmStatus($FilmRecord->ep_status, $FilmRecord->ep_total, $FilmRecord->ep_update, $FilmRecord->ep_today, $epNum);
        $info = [
            'id'=>$filmId,
            'name'=>$FilmRecord->name,
            'cover'=>$FilmRecord->cover,
            'ep_follow'=>$FollowFilmRecord ? $FollowFilmRecord->ep_num : 0,
            'ep_update'=>$FilmRecord->ep_update,
            'ep_today'=>$FilmRecord->ep_today ? 1 : 0,
            'ep_status'=>$epStatus[0],
            'ep_status_all'=>$epStatus[1],
            'ep_total'=>$FilmRecord->ep_total,
            'ep_title'=>$EpisodeRecord ? $EpisodeRecord->title : ''
        ];
        return $info;
    }
    
    /**
     * 爬虫入剧集
     * @param int $fid
     * @param array $params
     * @return boolen
     */
    public function add($fid, $epList, $epTotal, $epUpdate) {
        $sql = "insert into ".$this->tableName()  . "(film_id,num,title,description,is_show,length,create_time) values";
        for($i = 0; $i < count($epList); $i++){
            $ep = $epList[$i];
            
            if($epUpdate >= $ep['ep_num']) {
                $ep['ep_status'] = 1;
            }
            if(!$ep['ep_status']) {
                continue;
            }
            
            
            
            if($i > 0){
                $sql .= ",";
            }

            $sql .= "(".$fid.",'".addslashes($ep['ep_num'])."','".($ep['ep_title'] ? addslashes($ep['ep_title']) : '')."','".($ep['ep_desc'] ? addslashes($ep['ep_desc']) : '')."','".(addslashes($ep['ep_status']) ? 1 : 0)."','".($ep['ep_len'] ? addslashes($ep['ep_len']) : '')."',now())";
  	}
        $sql .= " on duplicate key update film_id=values(film_id),num=values(num),title=if(values(title)!='',values(title),title),description=if(values(description)!='',values(description),description),is_show=values(is_show),length=values(length)";
        $conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql);
        return $cmd->execute();
    }
    
    public function getEpInfo($filmId, $epNum) {
        return $this->findByCondition(['film_id'=>$filmId, 'num'=>$epNum])->asArray()->one();
    }
    
    
    
    

    
}

