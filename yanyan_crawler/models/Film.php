<?php
/**
 * 影视剧模型类
 * @author ztt
 * @date 2017/11/10
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\Tool;
use app\models\PlaySource;

class Film extends ActiveRecord {
    public static $cover = '/system/movie_nopic@3x.png';    //无封面时显示的默认图片
    public static $flag = [1=>'综艺', 2=>'动画'];   //影视剧标签：1=综艺；2=动画
    private $_Transaction = null;  //事务处理对象
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%film}}';
    }
    
    /**
     * 影视剧入库
     * @param type $cmdCode
     * @param type $sign
     * @param type $url
     * @param type $data
     * @return boolean
     */
    public function add($data) {
//        if(empty($data['cmd_code']) || $data['cmd_code'] != Tool::md5Double('yy.leiyu.tv926')) {
//            $this->addError('', '-4:参数错误');
//            return false;
//        }
//        if(md5($data['url'].'shenjianshou.cn') != $data['sign']) {
//            $this->addError('', '-4:参数错误');
//            return false;
//        }
        $data = json_decode($data, true);
        if(empty($data)) {
           die(); 
        }
        
        // 无双影视
        if(empty($data['main_actor']) && empty($data['director']) && empty($data['area']) && empty($data['summary']) && empty($data['year'])) {
            return false;
        }
        $resource = $data['resource'];  //来源
        $params = [];
        $params['title'] = $data['title'];
        // 综艺按电视剧来处理；
        $params['flag'] = 0;
        $params['type'] = $data['type'];
        if($data['type'] == 3) {    // 综艺归类为电视剧
            $params['type'] = 2;
            $params['flag'] = 1;
        }else if($data['type'] == 4) { // 动漫多集的话归类为电视剧否则是电影
            if(count($data['play_source'][0]['way']) == 1) {
                $params['type'] = 1;
            } else {
                $params['type'] = 2;
            }
            $params['flag'] = 2;
        }
        $params['main_actor'] = implode('/', $data['main_actor']);
        $params['director'] = implode('/', $data['director']);
        $params['cover'] = $data['cover'];
        // 设置区域
        $Area = new Area();
        $data['area'] = $data['area'] == '大陆' ? '内地' : $data['area'];
        $AreaRecord = $Area->findByCondition(['name'=>$data['area']])->one();
        if(!$AreaRecord) {
            $AreaRecord = $Area->findByCondition(['name'=>'其他'])->one();
        }
        $params['area'] = $AreaRecord->id;
        $params['year'] = preg_match('/\d{4}/', $data['year']) ? $data['year'] : 0;
        $params['summary'] = $data['summary'];
        
        $OldFilmRecord = $this->findByCondition(['type'=>$params['type'], 'title'=>$params['title'], 'year'=>$params['year']])->one();
        $oldEpUpdate = 0;
        if($OldFilmRecord) {
            $oldEpUpdate = $OldFilmRecord->episode_number;
        }
        
        $filmId = $this->addFilm($params);
        
        $params['tags'] = $data['tag'];

        if(!$filmId) {
            $FilmRecord = $this->findByCondition(['type'=>$params['type'], 'title'=>$params['title'], 'year'=>$params['year']])->one();
            if(!$FilmRecord) {
                $this->addError('', '0:新增失败1');
                return false;
            }
            $filmId = $FilmRecord->id;
            $this->addTagRel($filmId, $data['type'], $params['tags']);
        }
        if($filmId) {
            // 更新标签信息
            $this->addTagRel($filmId, $data['type'], $params['tags']);
            
            // 整合播放线路和播放源
            $playArr = array_combine($data['play_route'], $data['play_source']);
            $newPlayArr = [];
            foreach($playArr as $key=>$item) {
                $newPlayArr[$key] = $item['way'];
            }
            if($data['type'] == 2 || $data['type'] == 4) {
                $this->tvAndAnimation($newPlayArr);
            }else if($data['type'] == 1) {
                $this->film($newPlayArr);
            }else {
                $this->variety($newPlayArr);
            }
            $FilmSource = new FilmSource();
            $FilmSource->add($filmId, $newPlayArr, $resource);
            if($params['type'] == 2) {
                $updateEp = $this->getUpdateEp($newPlayArr);
            }else {
                $updateEp = 1;
            }
            $params['episode_number'] = $updateEp;
            // 更新片源信息更新状态
            $this->updateProgress($filmId, $oldEpUpdate, $params);  
        }
        return true;
    }
    
    /**
     * 
     * @param type $filmId
     * @param type $epUpdate
     * @param type $type
     */
    public function updateProgress($filmId, $oldUpdate, $params) {
        $epUpdate = $params['episode_number'];
        $type = $params['type'];
        $epToday = '';
        $FilmRecord = $this->findOne($filmId);
        if($FilmRecord->type == 1) { // 电影
            $FilmSource = new FilmSource();
            $oldSql = 'select * from ' . $FilmSource->tableName() . ' where film_id=:film_id and unix_timestamp(create_time) < unix_timestamp(DATE_FORMAT(NOW(),"%Y-%m-%d"))';
            $OldFilmSourceRecord = $FilmSource->findBySql($oldSql, [':film_id'=>$filmId])->one();
            if($OldFilmSourceRecord) {
                $newSql = 'select * from ' . $FilmSource->tableName() . ' where film_id=:film_id and unix_timestamp(create_time) > unix_timestamp(DATE_FORMAT(NOW(),"%Y-%m-%d"))';
                $newFilmSourceRecords = $FilmSource->findBySql($newSql, [':film_id'=>$filmId])->all();                
                $epUpdate = 1;
                foreach($newFilmSourceRecords as $SourceRecord) {
                    $epToday .= $SourceRecord->route_id . '/';
                }
            }
        }else { // 电视剧
            if($oldUpdate !=0 && $oldUpdate != $epUpdate && $epUpdate > $oldUpdate) {
                $epUpdateNum = $epUpdate - $oldUpdate;
                for($i = 1; $i <= $epUpdateNum; $i ++) {
                    $epToday .= ($oldUpdate + $i) . '/';
                }
            }
        }
        $FilmRecord->episode_number = $epUpdate;
        $FilmRecord->episode_today = $epToday ? rtrim($epToday, '/') : $epToday;
        return $FilmRecord->save();
    }
    
    /**
     * 连续剧和动漫
     * @param type $data
     */
    public function tvAndAnimation(&$data) {
        foreach($data as $key1=>$arr) {
            foreach($arr as $key2=>$value) {
                // 去除预告片
                if(strpos($value['num'], '预告') > -1) {    
                    unset($data[$key1][$key2]);
                }else {
                    // 提取纯数字为剧集编号
                    if(count($arr) == 1) {
                        $data[$key1][$key2]['num'] = 1;
                    }else {
                        if(preg_match('/(\d+)/', $value['num'], $matches)) {
                            if($matches[1] < 10) {
                                $matches[1] = trim($matches[1], '0');
                            }
                            $data[$key1][$key2]['num'] = $matches[1];
                        }
                    }
                    $data[$key1][$key2]['title'] = $value['num'];
                }
            }
        }
    }
    
    /**
     * 电影
     * @param type $data
     */
    public function film(&$data) {
        foreach($data as $key1=>$arr) {
            if(count($arr) > 1) {
                unset($data[$key1]);
            }else {
                foreach($arr as $key2=>$value) {
                    $data[$key1][$key2]['num'] = 1;
                    $data[$key1][$key2]['title'] = $value['num'];
                }
            }
        }
    }
    
    /**
     * 综艺
     * @param type $data
     */
    public function variety(&$data) {
        foreach($data as $key1=>$arr) {
            foreach($arr as $key2=>$value) {
                $data[$key1][$key2]['num'] = $key2 + 1;
                $data[$key1][$key2]['title'] = $value['num'];
            }
        }
    }
    
    /**
    * 
    * @param type $params
    * @return type
    */
   public function addFilm($params){
        $sql = 'insert into ' . $this->tableName() 
                . '(`type`,`title`,`cover`,`director`,`main_actor`,`area`,`year`,`summary`,`flag`,`create_time`) '
                . 'values'
                . '(:type, :title, :cover, :director, :main_actor, :area, :year, :summary, :flag, now()) '
                . 'on duplicate key update cover=values(cover),director=values(director),main_actor=values(main_actor),area=values(area),'
                . 'year=values(year),summary=values(summary),flag=values(flag)';
        $binds = [];
        foreach($params as $key=>$item) {
            $binds[':' . $key] = $item;
        }
  	$conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql, $binds);
        if($cmd->execute()) {
            $filmId = Yii::$app->db->getLastInsertID();
            return $filmId;
        }
        return false;
    }
    
    /**
     * 获取最新集数
     * @param array $playSource 
     * @return int 
     */
    public function getUpdateEp($playSource) {
        $max = 0;
        foreach($playSource as $item) {
            $currCount = count($item);
            if($currCount > $max) {
                $max = $currCount;
            }
        }
        return $max;
    }
    
    /**
     * 添加影片标签关系对应
     * @param int $filmId
     * @param array $types
     * @return boolen
     */
    public function addTagRel($filmId, $type, $tags) {
        $Tag = new Tag();
        if(empty($tags)) {
            $sql = 'select tag_id from ' . $Tag->tableName() . ' where channel=:channel and name=:name';
            $TagRecord = $Tag->findBySql($sql, [':channel'=>$type, ':name'=>'其他'])->one();
            $TagRel = new TagRel();
            if(!$TagRel->addTagRel($filmId, $type, $TagRecord->tag_id)) {
                $this->addError('', '0:添加影视剧标签失败');
                return false;
            }
        }else {
            foreach($tags as $item) {
                $item = str_replace('片', '', $item);
                $item = str_replace('动漫', '', $item);
                $item = str_replace('综艺', '', $item);
                $item = str_replace('剧', '', $item);
                $tagId = 0;
                $sql = 'select tag_id from ' . $Tag->tableName() . ' where channel=:channel and name=:name';
                $TagRecord = $Tag->findBySql($sql, [':channel'=>$type, ':name'=>$item])->one();
                if(!$TagRecord) {
                    $sql = 'select tag_id from ' . $Tag->tableName() . ' where channel=:channel and name=:name';
                    $TagRecord = $Tag->findBySql($sql, [':channel'=>$type, ':name'=>'其他'])->one();
                }
                $tagId = $TagRecord->tag_id;
                $TagRel = new TagRel();
                if(!$TagRel->addTagRel($filmId, $type, $tagId)) {
                    $this->addError('', '0:添加影视剧标签失败');
                    return false;
                }
                return false;
            }
        }
    }
    
    /**
     * 添加爱奇艺数据
     * @param type $params
     */
    public function addIqiyiFilm($params) {
        $sqlName = 'select * from ' . Film::tableName() . ' where name=:name and ftype=2 and release_date like :year';
        $sqlEnName = 'select * from ' . Film::tableName() . ' where en_name=:enname and ftype=2 and release_date like :year';
        $RecordName = $this->findBySql($sqlName, [':name'=>$params['name'], ':year'=>'"' . $params['year'] . '%"'])->one();
        $RecordEnName = $this->findBySql($sqlEnName, [':enname'=>$params['en_name'], ':year'=>'"' . $params['year'] . '%"'])->one();
        $Record = null;
        if($RecordName) {
            $Record = $RecordName;
        } else {
            $Record = $RecordEnName;
        }
       
        
        if($Record) {
            $epUpdate = $params['ep_update'] ? $params['ep_update'] : $params['ep_total'];
            $Episode = new Episode();
            $EpisodeRecord = $Episode->findByCondition(['film_id'=>$Record->id])->one();
            $Episode->add($Record->id, $params['ep_info'], $params['ep_total'], $epUpdate);
            $newEpisodes = [];
            if($EpisodeRecord) {
                $sql = 'select num from ' . $Episode->tableName() . ' where film_id=:filmId and (unix_timestamp(create_time) > unix_timestamp(DATE_FORMAT(NOW(),"%Y-%m-%d")))';
                $newEpisodeRecords = $Episode->findBySql($sql, [':filmId'=>$Record->id])->asArray()->all();
                foreach($newEpisodeRecords as $item) {
                    $newEpisodes[] = $item['num'];
                }
            }
            $epToday = ($newEpisodes && count($newEpisodes) < 3) ? implode('、', $newEpisodes) : '';
            $Record->ep_today = $epToday;
            $Record->ep_total = $params['ep_total'];
            $Record->ep_update = $epUpdate;
            $Record->play_source = $params['play_source'];
            $Record->ep_status = $epUpdate == $params['ep_total'] ? 1 : 0;
            $Record->save();
        }
    }
    
    /**
     * 启动事务
     * @return void 无返回值
     */
    protected function beginTransaction() {
        $this->_Transaction = Yii::$app->db->beginTransaction();
    }
    
    /**
     * 回滚事务
     * @return void 无返回值
     */
    protected function rollback() {
        if($this->_Transaction != null) {
            $this->_Transaction->rollBack();
        }
    }
    
    /**
     * 提交事务
     * @return void 无返回值
     */
    protected function commit() {
        if($this->_Transaction != null) {
            $this->_Transaction->commit();
        }
    }
    
    
}


