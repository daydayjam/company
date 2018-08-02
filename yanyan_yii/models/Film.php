<?php
/**
 * 影视剧模型类
 * @author ztt
 * @date 2017/11/10
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;
use app\models\Cache;

class Film extends ActiveRecord {
    public static $cover = '/system/movie_nopic@3x.png';    //无封面时显示的默认图片
    public static $flag = [1=>'综艺', 2=>'动画'];   //影视剧标签：1=综艺；2=动画
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%film}}';
    }
    
    /**
     * 获取影片详情
     * @param int $id 影片ID
     * @return array
     */
    public function getInfo($id) {
        $loginUid = Cache::hget('id');
        $result = [];
        // 获取影视剧基本信息
        $filmInfo = $this->getBrief($id);
        if(!$filmInfo) {
            $error = $this->getCodeError();
            $this->addError('', $error['code'] . ':' . $error['msg']);
            return false;
        }
        $result['is_play'] = Yii::$app->params['version']['ios']['is_play'];
        $result['film_info'] = $filmInfo;
        // 获取观看记录
        $ViewRecord = new ViewRecord();
        $VRRecord = $ViewRecord->findByCondition(['user_id'=>$loginUid, 'film_id'=>$id])->one();
        $result['film_info']['view_number'] = $VRRecord ? $VRRecord->number : 1;
        $episodeToday = $filmInfo['episode_today'];
        // 获取我看到哪一集
        // 今日更新了那几集，对比追剧中我看到那一集，
        // 我是否追了这部剧 
        $result['film_info']['is_follow'] = Yii::$app->params['state_code']['follow_no'];
        $result['film_info']['follow_number'] = 1;
        $FilmFollow = new FilmFollow();
        $FilmFollowRecord = $FilmFollow->findByCondition(['user_id'=>$loginUid, 'film_id'=>$id, 'status'=>Yii::$app->params['state_code']['status_normal']])->one();
        if($FilmFollowRecord) { 
            $result['film_info']['is_follow'] = Yii::$app->params['state_code']['follow_yes'];
            $result['film_info']['follow_number'] = $FilmFollowRecord->number;
            // 对比filmfollow的episode_today和film的episode_today是否完全不一致，是则更新filmfollow的episode_today为film的episode_today
            $episodeToday = $FilmFollowRecord->episode_today ? explode('/', $FilmFollowRecord->episode_today) : [];
            if(!$filmInfo['episode_today']) {
                $episodeToday = [];
                $FilmFollowRecord->episode_today = '';
                $FilmFollowRecord->save();
            }else {
                if(!$FilmFollowRecord->episode_today) {
                    $episodeToday = $filmInfo['episode_today'];
                    $FilmFollowRecord->episode_today = implode('/', $filmInfo['episode_today']);
                    $FilmFollowRecord->save();
                }else {
                    if($filmInfo['type'] == Yii::$app->params['state_code']['film_tv']) {
                        if(min($filmInfo['episode_today']) > max($episodeToday)) {
                            $episodeToday = $filmInfo['episode_today'];
                            $FilmFollowRecord->episode_today = implode('/', $filmInfo['episode_today']);
                            $FilmFollowRecord->save();
                        }
                    }else {
                        if(!array_intersect($episodeToday, $filmInfo['episode_today'])) {
                            $episodeToday = $filmInfo['episode_today'];
                            $FilmFollowRecord->episode_today = implode('/', $filmInfo['episode_today']);
                            $FilmFollowRecord->save();
                        }
                    }
                }
            }
        }
        if($episodeToday) {
            $episodeToday = array_map(function($var) {
                        return (int)$var;
                    }, $episodeToday);
        }
        $result['film_info']['episode_today']  = $episodeToday;   
        // 获取前十条评论信息
        $Comment = new Comment();
        $commentList = $Comment->getListByFilm($id); 
        if($commentList === false) {
            $error = $Comment->getCodeError();
            $this->addError('', $error['code'] . ':' . $error['msg']);
            return false;
        }
        $result['comment_list'] = $commentList;
        
        return $result;
    }
    
    
    
    /**
     * 获取概要信息
     * @param int $id 影视剧ID
     * @return array 影视剧概要信息
     */
    public function getBrief($id) {
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $result = [];
        $Record = $this->findOne($id);
        if(!$Record) {
            $this->addError('', '-7:您要访问的影视剧信息不存在');
            return false;
        }
        if($Record->status == Yii::$app->params['state_code']['status_delete']) {
            $this->addError('', '-7:您要访问的影视剧信息不存在或已被下架');
            return false;
        }
        // 获取标签
//        $FilmTagRel = new FilmTagRel();
//        $tags = $FilmTagRel->getTags($id, $Record->type, $Record->flag);
        
        $result['id']             = $Record->id;
        $result['title']          = $Record->title;
        $result['type']           = $Record->type;
        $result['tag']            = $Record->genre ? $Record->genre : Yii::$app->params['text_desc']['film_kind'][$Record->kind];
        $result['cover']          = Tool::connectPath(Yii::$app->params['image_domain'], $Record->cover, Yii::$app->params['text_desc']['default_cover']);
        $result['year']           = $Record->year != Yii::$app->params['state_code']['year_unknown'] ? (string)$Record->year : Yii::$app->params['text_desc']['year_unknown'];
        $result['main_actor']     = $Record->main_actor ? $Record->main_actor : Yii::$app->params['text_desc']['no_main_actor'];
        $result['summary']        = $Record->summary ? $Record->summary : Yii::$app->params['text_desc']['no_summary'];
        $result['episode_number'] = $Record->episode_number == 0 ? 1 : $Record->episode_number;
        $result['episode_today']  = $Record->episode_today ? explode('/', $Record->episode_today) : [];
        return $result; 
    }
    
    /**
     * 获取搜索页枚举影视剧列表
     * @param int $page 当前页码
     * @param int $pagesize 显示记录数
     * @return array | boolen
     */
    public function getHotList($page = 1, $pagesize = 8) {
        if (!is_numeric($page) || !is_numeric($pagesize) || $page < 0 || $pagesize < 0) {
            return $this->addError('', '-4:参数格式有误');
        }
        $result = [];
        $CacheKey = 'FILM_HOT';
//        Yii::$app->redis->flushall();die;
//        print_r(Yii::$app->redis->keys('FILM_HOT*'));die;
//        print_r(Yii::$app->redis->hgetall('FILM_HOT3_CONTENT'));die;
        if(Cache::exists($CacheKey)) {
            // 获取分类标题信息
            $hotFilmBrands = Cache::zrange($CacheKey, 0, 3);
            foreach($hotFilmBrands as $key=>$value) {
                $result[$key]['name'] = Yii::$app->params['text_desc']['hot_brand'][$value];
                // 根据分类标题信息获取相应分类的id
                $CacheListKey = $CacheKey . $value;
                $listResult = Cache::zrevrange($CacheListKey, 0, 10);
                foreach($listResult as $item) {
                    $CacheContentKey = $CacheListKey . '_CONTENT';
                    $rows = json_decode(Cache::hget($item, $CacheContentKey), true);
                     // 处理年代等字段
                    $this->dealField($rows);
                    // 给电视剧类型的数据加上剧集信息
                    $this->addEpisode($rows);
                    $result[$key]['list'][] = $rows;
                }
            }
            return $result;
        }
        $select = 'id,kind,title,cover,year,genre as tag,main_actor,episode_number,type,update_time';
        $paramsMovie = ['is_hot'=>1, 'kind'=>Yii::$app->params['state_code']['film_movie']];// 热门电影
        $resultMovie = $this->getListData($select, $page, $pagesize, $paramsMovie);
        $paramsTv = ['is_hot'=>1, 'kind'=>Yii::$app->params['state_code']['film_tv']];// 热门电视剧
        $resultTv = $this->getListData($select, $page, $pagesize, $paramsTv);
        $paramsVariety = ['is_hot'=>1, 'kind'=>Yii::$app->params['state_code']['film_variety']];// 热门综艺
        $resultVariety = $this->getListData($select, $page, $pagesize, $paramsVariety);
        $paramsAnimation = ['is_hot'=>1, 'kind'=>Yii::$app->params['state_code']['film_animation']];// 热门动漫
        $resultAnimation = $this->getListData($select, $page, $pagesize, $paramsAnimation);
        
        $result = [];
        if($resultMovie['rows']) {
            Cache::zadd($CacheKey, Yii::$app->params['state_code']['film_movie'], Yii::$app->params['state_code']['film_movie']);
            foreach($resultMovie['rows'] as $value) {
                $orderBy = $value['year'] + strtotime($value['update_time']);
                Cache::zadd($CacheKey . Yii::$app->params['state_code']['film_movie'], $orderBy, $value['id']);
                Cache::hset($value['id'], json_encode($value), $CacheKey . Yii::$app->params['state_code']['film_movie'] . '_CONTENT');
            }
            
            // 处理年代等字段
            $this->dealListField($resultMovie['rows']);
            // 给电视剧类型的数据加上剧集信息
            $this->addListEpisode($resultMovie['rows']);
            $result[] = ['name'=>Yii::$app->params['text_desc']['hot_movie'], 'list'=>$resultMovie['rows']];
        }
        if($resultTv['rows']) {
            Cache::zadd($CacheKey, Yii::$app->params['state_code']['film_tv'], Yii::$app->params['state_code']['film_tv']);
            foreach($resultTv['rows'] as $value) {
                $orderBy = $value['year'] + strtotime($value['update_time']);
                Cache::zadd($CacheKey . Yii::$app->params['state_code']['film_tv'], $orderBy, $value['id']);
                Cache::hset($value['id'], json_encode($value), $CacheKey . Yii::$app->params['state_code']['film_tv'] . '_CONTENT');
            }
            
            $this->dealListField($resultTv['rows']);
            // 给电视剧类型的数据加上剧集信息
            $this->addListEpisode($resultTv['rows']);
            $result[] = ['name'=>Yii::$app->params['text_desc']['hot_tv'], 'list'=>$resultTv['rows']];
        }
        if($resultVariety['rows']) {
            Cache::zadd($CacheKey, Yii::$app->params['state_code']['film_variety'], Yii::$app->params['state_code']['film_variety']);
            foreach($resultVariety['rows'] as $value) {
                $orderBy = $value['year'] + strtotime($value['update_time']);
                Cache::zadd($CacheKey . Yii::$app->params['state_code']['film_variety'], $orderBy, $value['id']);
                Cache::hset($value['id'], json_encode($value), $CacheKey . Yii::$app->params['state_code']['film_variety'] . '_CONTENT');
            }
            
            $this->dealListField($resultVariety['rows']);
            $this->addListEpisode($resultVariety['rows']);
//            $this->addType($resultVariety['rows']);
            $result[] = ['name'=>Yii::$app->params['text_desc']['hot_variety'], 'list'=>$resultVariety['rows']];
        }
        if($resultAnimation['rows']) {
            Cache::zadd($CacheKey, Yii::$app->params['state_code']['film_animation'], Yii::$app->params['state_code']['film_animation']);
            foreach($resultAnimation['rows'] as $value) {
                $orderBy = $value['year'] + strtotime($value['update_time']);
                Cache::zadd($CacheKey . Yii::$app->params['state_code']['film_animation'], $orderBy, $value['id']);
                Cache::hset($value['id'], json_encode($value), $CacheKey . Yii::$app->params['state_code']['film_animation'] . '_CONTENT');
            }
            
            $this->dealListField($resultAnimation['rows']);
            $this->addListEpisode($resultAnimation['rows']);
            $result[] = ['name'=>Yii::$app->params['text_desc']['hot_animation'], 'list'=>$resultAnimation['rows']];
        }
        
        return $result;
    }
    
    /**
     * 
     * @param type $list
     */
    public function dealListField(&$list) {
        foreach($list as $key=>$value) {
            $list[$key]['year']           = $value['year'] != Yii::$app->params['state_code']['year_unknown'] ? (string)$value['year'] : Yii::$app->params['text_desc']['year_unknown'];
            $list[$key]['main_actor']     = $value['main_actor'] ? $value['main_actor'] : Yii::$app->params['text_desc']['no_main_actor'];
            if(isset($list[$key]['summary'])) {
                $list[$key]['summary']    = $value['summary'] ? $value['summary'] : Yii::$app->params['text_desc']['no_summary'];
            }
        }
    }
    
    public function dealField(&$item) {
        $item['year']           = $item['year'] != Yii::$app->params['state_code']['year_unknown'] ? (string)$item['year'] : Yii::$app->params['text_desc']['year_unknown'];
        $item['main_actor']     = $item['main_actor'] ? $item['main_actor'] : Yii::$app->params['text_desc']['no_main_actor'];
        if(isset($item['summary'])) {
            $item['summary']    = $item['summary'] ? $item['summary'] : Yii::$app->params['text_desc']['no_summary'];
        }
    }
    
    /**
     * 给电视剧类型的数据加上剧集信息
     * @param type $list
     */
    public function addListEpisode(&$list) {
        $loginUid = Cache::hget('id');
        $FilmFollow = new FilmFollow();
        foreach($list as $key=>$value) {
            $list[$key]['is_follow'] = Yii::$app->params['state_code']['follow_no'];
            $FilmFollowRecord = $FilmFollow->findByCondition(['film_id'=>$value['id'], 'user_id'=>$loginUid])->one();
            if($FilmFollowRecord) {
                $list[$key]['is_follow'] = Yii::$app->params['state_code']['follow_yes'];
            }
            if($value['type'] == Yii::$app->params['state_code']['film_tv']) {
                $list[$key]['follow_number'] = 0;
                $list[$key]['view_number'] = 1;
                $ViewRecord = new ViewRecord();
                $VRRecord = $ViewRecord->findByCondition(['user_id'=>$loginUid, 'film_id'=>$value['id']])->one();
                if($VRRecord) {
                    $list[$key]['view_number'] = $VRRecord->number;
                }
                $list[$key]['tip'] = '更新至' . $value['episode_number'] . '集';
                if($FilmFollowRecord) {
                    $list[$key]['follow_number'] = $FilmFollowRecord->number;
                }
            }
        }
    }
    
     public function addEpisode(&$item) {
        $loginUid = Cache::hget('id');
        $FilmFollow = new FilmFollow();
        $item['is_follow'] = Yii::$app->params['state_code']['follow_no'];
        $FilmFollowRecord = $FilmFollow->findByCondition(['film_id'=>$item['id'], 'user_id'=>$loginUid])->one();
        if($FilmFollowRecord) {
            $item['is_follow'] = Yii::$app->params['state_code']['follow_yes'];
        }
        if($item['type'] == Yii::$app->params['state_code']['film_tv']) {
            $item['follow_number'] = 0;
            $item['view_number'] = 1;
            $ViewRecord = new ViewRecord();
            $VRRecord = $ViewRecord->findByCondition(['user_id'=>$loginUid, 'film_id'=>$item['id']])->one();
            if($VRRecord) {
                $item['view_number'] = $VRRecord->number;
            }
            $item['tip'] = '更新至' . $item['episode_number'] . '集';
            if($FilmFollowRecord) {
                $item['follow_number'] = $FilmFollowRecord->number;
            }
        }
    }
    
    
    /**
     * 通过关键字搜索影视剧
     * @param string $keyword 关键字
     * @param int $page 页码
     * @param int $pagesize 记录数
     * @return mixed array=获取成功
     */
    public function searchByKeyword($keyword, $page = 1, $pagesize = 10) {
        $loginUid = Cache::hget('id');
        if(empty($keyword)) {
            return $this->addError('', '-3:搜索关键词不可为空');
        }
        if(!is_numeric($page) || !is_numeric($pagesize) || $page < 0 || $pagesize < 0) {
            return $this->addError('', '-4:参数格式有误');
        }
        $Query = new Query();
        $data = $Query->select(['id', 'title', 'kind', 'type', 'genre', 'cover', 'main_actor', 'year', 'cover', 'episode_number'])
                        ->from($this->tableName())
                        ->where(['like', 'title', $keyword])
                        ->orWhere(['like', 'title_no_mark', $keyword])
                        ->orWhere(['like', 'main_actor', $keyword])
                        ->orderBy('id desc')
                        ->limit($pagesize)
                        ->offset(($page-1)*$pagesize)
                        ->all();
        //增加剧集返回
        foreach($data as $key=>$item) {
            $data[$key]['year'] = $item['year'] ? $item['year'] : Yii::$app->params['text_desc']['year_unknown'];
            $data[$key]['main_actor'] = $item['main_actor'] ? $item['main_actor'] : Yii::$app->params['text_desc']['no_main_actor'];

            $data[$key]['tag'] = $item['genre'] ? $item['genre'] : Yii::$app->params['text_desc']['film_kind'][$item['kind']];
            $data[$key]['cover'] = Tool::connectPath(Yii::$app->params['image_domain'], $item['cover'], Yii::$app->params['text_desc']['default_cover']);
            if($item['type'] == Yii::$app->params['state_code']['film_tv']) {
                $data[$key]['follow_number'] = 0;
                $data[$key]['view_number'] = 1;
                $ViewRecord = new ViewRecord();
                $VRRecord = $ViewRecord->findByCondition(['user_id'=>$loginUid, 'film_id'=>$item['id']])->one();
                if($VRRecord) {
                    $data[$key]['view_number'] = $VRRecord->number;
                }
                $FilmFollow = new FilmFollow();
                $FilmFollowRecord = $FilmFollow->findByCondition(['film_id'=>$item['id'], 'user_id'=>$loginUid])->one();
                if($FilmFollowRecord) {
                    $data[$key]['follow_number'] = $FilmFollowRecord->number;
                }
            }
        }
        
        $result = [];
        $result['page'] = (int)$page;
        $result['page_size'] = (int)$pagesize;
        $result['list'] = $data;
        return $result;
    }
    
    /**
     * 获取最近更新影视剧列表
     * @param int $page 当前页码
     * @param int $pagesize 每页显示记录数
     * @return array
     */
    public function getNewList($page = 1, $pagesize = 8) {
        $loginUid = Cache::hget('id');
        if (!is_numeric($page) || !is_numeric($pagesize) || $page < 1 || $pagesize < 1) {
            return $this->addError('', '-4:参数格式有误');
        }
        $select = 'id, title, type, episode_number, cover';
        $params = [];
        $order = 'update_time desc, type desc, kind desc, year desc';
        $result = $this->getListData($select, $page, $pagesize, $params, $order);
//        foreach($result['rows'] as $key=>$item) {
//            $result['rows'][$key]['episode_number'] = '更新至' .$item['episode_number']. '集';
//        }
        //增加剧集返回
        foreach($result['rows'] as $key=>$item) {
            // 获取标签
            $result['rows'][$key]['tip'] = '';
            $result['rows'][$key]['cover'] = Tool::connectPath(Yii::$app->params['image_domain'], $item['cover'], Yii::$app->params['text_desc']['default_cover']);
            if($item['type'] == Yii::$app->params['state_code']['film_tv']) {
                $result['rows'][$key]['tip'] = '更新至' . $item['episode_number'] . '集';
                $result['rows'][$key]['follow_number'] = 0;
                $result['rows'][$key]['view_number'] = 1;
                $ViewRecord = new ViewRecord();
                $VRRecord = $ViewRecord->findByCondition(['user_id'=>$loginUid, 'film_id'=>$item['id']])->one();
                if($VRRecord) {
                    $result['rows'][$key]['view_number'] = $VRRecord->number;
                }
                $FilmFollow = new FilmFollow();
                $FilmFollowRecord = $FilmFollow->findByCondition(['film_id'=>$item['id'], 'user_id'=>$loginUid])->one();
                if($FilmFollowRecord) {
                    $result['rows'][$key]['follow_number'] = $FilmFollowRecord->number;
                }
            }
        }
        return $result['rows'];
    }
    
    /**
     * 通过关键字搜索影视剧
     * @param string $tags 关键字
     * @param int $page 页码
     * @param int $pagesize 记录数
     * @return mixed array=获取成功
     */
    public function searchByTag($tags, $page = 1, $pagesize = 21) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($page) || !is_numeric($pagesize) || $page < 0 || $pagesize < 0) {
            return $this->addError('', '-4:参数格式有误');
        }
        $kind = 0;
        $tagsArr = explode(',', $tags);
        if($tags && count($tagsArr) != 4) {
            return $this->addError('', '-4:参数格式有误');
        }
        foreach($tagsArr as $key=>$value) {
            $tagsArr[$key] = trim($value);
        }
        
        $query = (new Query())->select(['id', 'title', 'kind', 'type', 'cover', 'episode_number', 'episode_today'])
                        ->from($this->tableName())
                        ->where('1=1');
        if($tags) {
            // 类别
            if($tagsArr[0] == '电影') {
                $kind = 1;
            }else if($tagsArr[0] == '电视剧') {
                $kind = 2;
            }else if($tagsArr[0] == '综艺') {
                $kind = 3;
            }else if($tagsArr[0] == '动画') {
                $kind = 4;
            }
            // 地区
            $areaArr = [];
            if($tagsArr[1] == '港台') {
                $areaArr = ['香港', '台湾'];
            }else if($tagsArr[1] == '日韩') {
                $areaArr = ['日本', '韩国'];
            }else if($tagsArr[1] == '欧美') {
                $areaArr = ['德国', '法国', '英国', '西班牙', '瑞典', '瑞士', '挪威', '奥地利', '意大利', '芬兰', '美国', '加拿大'];
            }else if($tagsArr[1] == '大陆') {
                $areaArr = ['大陆', '内地'];
            }
            if($kind > 0) {
                $query->andWhere('kind=' . $kind);
//                if($kind == 1) {
//                    $query->orWhere('type=' . $kind);
//                }
                if($kind == 4) {
                    $query->orWhere('kind_extra=' . $kind);
                }
            }
            
            if($areaArr) {
                $query->andWhere(['in', 'area', $areaArr]);
            }else if ($tagsArr[1] == '其他') {
                $arrArea = ['香港', '台湾', '日本', '韩国', '德国', '法国', '英国', '西班牙', '瑞典', '瑞士', '挪威', '奥地利', '意大利', '芬兰', '美国', '加拿大'];
                $query->andWhere(['not in', 'area', $arrArea]);
            }     
            $this->writeLog($tagsArr[2]);
            if($tagsArr[2]) {
                $query->andWhere('genre like "%' . $tagsArr[2].'%"');
            }
            if(is_numeric($tagsArr[3])) {
                $query->andWhere('year="' . $tagsArr[3].'"');
            }else if($tagsArr[3] == '更早') {
                $query->andWhere('year<2014');
            }
        }
        $data = $query->orderBy('update_time desc')
                        ->limit($pagesize)
                        ->offset(($page-1)*$pagesize)
                
//                ->createCommand()->sql;
//        echo $data;die;
                        ->all();
        //增加剧集返回
        foreach($data as $key=>$item) {
            // 获取标签
            $data[$key]['cover'] = Tool::connectPath(Yii::$app->params['image_domain'], $item['cover'], Yii::$app->params['text_desc']['default_cover']);
            $FilmFollow = new FilmFollow();
            $FilmFollowRecord = $FilmFollow->findByCondition(['film_id'=>$item['id'], 'user_id'=>$loginUid])->one();
            
            if($item['type'] == Yii::$app->params['state_code']['film_tv']) {
                $data[$key]['follow_number'] = 0;
                $data[$key]['view_number'] = 1;
                $ViewRecord = new ViewRecord();
                $VRRecord = $ViewRecord->findByCondition(['user_id'=>$loginUid, 'film_id'=>$item['id']])->one();
                if($VRRecord) {
                    $data[$key]['view_number'] = $VRRecord->number;
                }
                if($FilmFollowRecord) {
                    $data[$key]['follow_number'] = $FilmFollowRecord->number;
                }
                
                // 处理是否有更新
                $filmToday = $item['episode_today'] ? explode('/', $item['episode_today']) : [];
                $filmFollowToday = $FilmFollowRecord && $FilmFollowRecord->episode_today ? explode('/', $item['episode_today']) : [];
                
                $data[$key]['tip'] = '更新至' . $item['episode_number'] . '集';
            }else {
                $data[$key]['tip'] = '电影';
            }
        }
        
        $result = [];
        $result['page'] = (int)$page;
        $result['pagesize'] = (int)$pagesize;
        $result['list'] = $data;
        return $result;
    }

    
}

