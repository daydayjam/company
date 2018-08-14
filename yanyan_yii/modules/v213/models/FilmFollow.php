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
use app\models\FilmSource;
use app\models\History;

class FilmFollow extends ActiveRecord {
    
    private $_Transaction = null;  //事务处理对象

    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%film_follow}}';
    }
    
    /**
     * 获取我的追剧列表
     * @param int $filmId 影视剧ID
     * @param int $epNum 剧集编号
     * @param int $page 当前页码，默认0是第一页
     * @param int $pageSize 每页显示记录数
     * @return array
     */
//    public function getList($uid = 0, $page = 0, $pagesize = 10, $order = 'update_time') {
//        $uid = $uid ? $uid : Cache::hget('id');
//        if(!is_numeric($page) || !is_numeric($pagesize)) {
//            $this->addError('', '-4:参数格式有误');
//            return false;
//        }
//        if(!in_array($order, ['update_time','create_time'])) {
//            $this->addError('', '-4:排序方式有误');
//            return false;
//        }
//        $andParams = ['uid'=>$uid];
//        $leftJoin = [
//                'tbname'=> Film::tableName(), 
//                'on'=>Film::tableName().'.id='.$this->tableName().'.film_id'
//            ];
//        $selfTbname = $this->tableName();
//        $selfSelectArr = ['ep_num as ep_follow'];
//        foreach($selfSelectArr as $key=>$self) {
//            $selfSelectArr[$key] = $selfTbname.'.'.$self;
//        }
//        $selfSelect = implode(',', $selfSelectArr);
//        $leftSelectArr = ['id','name','cover','ep_today','ep_update','ep_status','ep_total'];
//        foreach($leftSelectArr as $key=>$left) {
//            $leftSelectArr[$key] = $leftJoin['tbname'] . '.' . $left;
//        }
//        $leftSelect = implode(',', $leftSelectArr);
//        $select = $selfSelect . ',' . $leftSelect;
//        $result = $this->getListData($select, $page, $pagesize, $andParams, [], $leftJoin, $selfTbname.'.'.$order.' desc');
//        foreach($result['rows'] as $key=>$item) {   //  获取最新一集标题
//            $result['rows'][$key]['ep_title'] = '第' . $item['ep_update'] .'集';
//            $Episode = new Episode();
//            $sql = 'select title from ' . $Episode->tableName() . ' where film_id=:filmId and num=:num order by num desc limit 1';
//            $EpisodeRecord = $Episode->findBySql($sql, [':filmId'=>$item['id'], 'num'=>$item['ep_update']])->one();
//            if($EpisodeRecord && $EpisodeRecord->title) {
//                $result['rows'][$key]['ep_title'] = $EpisodeRecord->title;
//            }
//            //
//            $result['rows'][$key]['ep_today'] = $item['ep_today'] ? 1 : 0;
//            $Film = new Film();
//            $epStatus = $Film->getFilmStatus($item['ep_status'], $item['ep_total'], $item['ep_update'], $item['ep_today']);
//            $result['rows'][$key]['ep_status_all'] = $epStatus[1];
//            $result['rows'][$key]['ep_status'] = $epStatus[0];
//        }
//        return $result['rows'];
//    }
    
    /**
     * 更新追剧记录
     * @param int $filmId 影视剧ID
     * @param int $epNum 剧集编号
     * @return boolen
     */
    public function updateFollow($filmId, $epNum = 1, $routeId = 0) {
        $loginUid = Cache::hget('id');
         // 参数判断
        if(!is_numeric($filmId) || !is_numeric($epNum) || $epNum < 0 || $filmId < 0) {
            return $this->addError('', '-4:请求参数格式有误');
        }
        // 判断该影视是否有效
        $Film = new Film();
        $FilmRecord = $Film->findOne($filmId);
        if(!$FilmRecord || $FilmRecord->status == Yii::$app->params['state_code']['status_delete']) {
            return $this->addError('', '-7:您请求的影视剧不存在或已被下架');
        }
        if(!$epNum && $FilmRecord->episode_number < $epNum) {
            return $this->addError('', '-500:您想观看的集数未更新或未收录到本片源中');
        }
        $FilmSource = new FilmSource();
        $FilmSourceRecord = null;
        if($routeId) {
            $FilmSourceRecord = $FilmSource->findByCondition(['film_id'=>$filmId, 'number'=>$epNum, 'route_id'=>$routeId, 'status'=>Yii::$app->params['state_code']['status_normal']])->one();
            if(!$FilmSourceRecord) {
                $this->rollback();
                return $this->addError('', '-7:您请求的影视剧不存在或已被下架');
            }
        }
        $this->beginTransaction();
        $Record = $this->findByCondition(['user_id'=>$loginUid, 'film_id'=>$filmId])->one();
        if(!$routeId && !$Record) {  // 未追剧该部剧的情况下仅添加观影历史
            $this->user_id = $loginUid;
            $this->film_id = $filmId;    
            if(!$this->save()) {
                $this->rollback();
                return $this->addError('', '0:追剧失败，请稍后重试');
            }
        }else if($Record && $Record->status == Yii::$app->params['state_code']['status_delete']) {
            $Record->status = Yii::$app->params['state_code']['status_normal'];
            if(!$Record->save()) {
                $this->rollback();
                return $this->addError('', '0:追剧失败，请稍后重试');
            }
        }else if($routeId && $Record && $Record->status == Yii::$app->params['state_code']['status_normal']) {
            if(!is_numeric($routeId)) {
                $this->rollback();
                return $this->addError('', '-4:请求参数格式有误');
            }

            // 判断当前要追的剧集是否大于当前已追剧集，
            // 大则更新，同时更新filmFollow表中用于显示红点的episode_today,
            // 小则返回true无操作
            if($FilmRecord->type == Yii::$app->params['state_code']['film_tv']) {         
                //更新episode_today，当为电视时，存储的是电视剧集
                if($FilmRecord->episode_today) {  
                    $epTodayArr = explode('/', $FilmRecord->episode_today);
                    $epFollow = $epNum > $Record->number ? $epNum : $Record->number;
                    $offset = array_search($epFollow, $epTodayArr);
                    if($offset !== false) {
                        $newEpToday = implode('/', array_slice($epTodayArr, $offset + 1));
                        $Record->episode_today = $newEpToday;
                    }else {
                        $Record->episode_today = $FilmRecord->episode_today;
                    }
                }else {
                    $Record->episode_today = '';
                }
                if($Record->number < $epNum) {
                    $Record->number = $epNum;
                }
            }else {
                //更新episode_today，当为电影时，存储的是线路ID
                if($FilmRecord->episode_today) {
                    $epTodayArr = explode('/', $FilmRecord->episode_today);
                    $offset = array_search($routeId, $epTodayArr);
                    if($offset !== false) {
                        unset($epTodayArr[$offset]);
                        $newEpToday = implode('/', $epTodayArr);
                        $Record->episode_today = $newEpToday;
                    }else {
                        $Record->episode_today = $FilmRecord->episode_today;
                    }
                }else {
                    $Record->episode_today = '';
                }
            }
            if(!$Record->save()) {
                $this->rollback();
                return $this->addError('', '0:更新失败，请稍后重试');
            }
        }
        if($FilmSourceRecord) {
            // 更新观影记录
            $ViewRecord = new ViewRecord();
            if(!$ViewRecord->add($loginUid, $filmId, $epNum, $FilmRecord->type, $FilmRecord->title, $FilmRecord->cover, $routeId, $FilmSourceRecord->url)) {
                $this->rollback();
                $error = $ViewRecord->getCodeError();
                return $this->addError('', $error['code'] . ':' . $error['msg']);
            }
        }
        $result = $FilmRecord->type == Yii::$app->params['state_code']['film_tv'] ? $epNum : $routeId;
        $this->commit();
        return (int)$result;
    }
    
    /**
     * 取消追剧
     * @param int $filmId 影视剧ID
     * @return boolen
     */
    public function cancelFollow($filmId) {
        $loginUid = Cache::hget('id');
         // 参数判断
        if(!is_numeric($filmId) || $filmId < 0) {
            return $this->addError('', '-4:请求参数格式有误');
        }
        // 判断该影视是否有效
        $Film = new Film();
        $FilmRecord = $Film->findOne($filmId);
        if(!$FilmRecord || $FilmRecord->status == Yii::$app->params['state_code']['status_delete']) {
            return $this->addError('', '-7:您请求的影视剧不存在或已被下架');
        }
        $Record = $this->findByCondition(['film_id'=>$filmId, 'user_id'=>$loginUid, 'status'=>Yii::$app->params['state_code']['status_normal']])->one();
        if(!$Record || $Record->status == Yii::$app->params['state_code']['status_delete']) {
            return $this->addError('', '-7:您未追看该部剧或已取消追剧');
        }
        History::add($loginUid, $filmId, 'cache_follow', $Record->getAttributes());
        $Record->status = Yii::$app->params['state_code']['status_delete'];
        if(!$Record->save()) {
            return $this->addError('', '0:取消追剧失败，请稍后重试');
        }
        return true;
    }
    
    /**
     * 获取当前用户追剧列表
     * @param int $page
     * @param int $pagesize
     * @param string $order
     */
    public function getList($page = 1, $pagesize = 10, $order = 'update_time') {
        $loginUid = Cache::hget('id');
        if(!is_numeric($page) || !is_numeric($pagesize) || $page < 1 || $pagesize < 1) {
            return $this->addError('', '-4:参数格式有误');
        }
        $select = 'user_id,film_id,number,episode_today';
        $params = ['user_id'=>$loginUid, 'status'=>Yii::$app->params['state_code']['status_normal']];
        $order .= ' desc';
        $result = $this->getListData($select, $page, $pagesize, $params, $order);
        $Film = new Film();
        foreach($result['rows'] as $key=>$item) {
            $FilmRecord = $Film->findOne($item['film_id']);
            $result['rows'][$key]['title'] = $FilmRecord ? $FilmRecord->title : Yii::$app->params['text_desc']['no_title'];
            $result['rows'][$key]['type'] = $FilmRecord ? $FilmRecord->type : 1;
            $result['rows'][$key]['cover'] = $FilmRecord ? Tool::connectPath(Yii::$app->params['image_domain'], $FilmRecord->cover) : Yii::$app->params['image_domain'] . Yii::$app->params['text_desc']['default_cover'];
            $result['rows'][$key]['episode_number'] = $FilmRecord ? $FilmRecord->episode_number : 0;
            $ViewRecord = new ViewRecord();
            $VRRecord = $ViewRecord->findByCondition(['user_id'=>$loginUid, 'film_id'=>$item['film_id']])->one();
            $result['rows'][$key]['view_number'] = $FilmRecord->type == Yii::$app->params['state_code']['film_tv'] ? ($VRRecord ? $VRRecord->number : 1) : 1;
            // 处理是否有更新
            if(!$FilmRecord) {
                $isNew = 0;
            }else {
                $filmToday = $FilmRecord->episode_today ? explode('/', $FilmRecord->episode_today) : [];
                $filmFollowToday = $item['episode_today'] ? explode('/', $item['episode_today']) : [];

                $isNew = 0;
                if(!$filmToday) {
                    $isNew = 0;
                    $result['rows'][$key]['tip'] = '';
                }else {
                    if(!$filmFollowToday) {
                        $isNew = 1;
                    }else {
                        if($FilmRecord->type == Yii::$app->params['state_code']['film_tv']) {
                            if(min($filmToday) > max($filmFollowToday)) {
                                $isNew = 1;
                            }
                        }else {
                            if(!array_intersect($filmFollowToday, $filmToday)) {
                                $isNew = 1;
                            }
                        }
                    }
                }
                if($FilmRecord->type == Yii::$app->params['state_code']['film_tv']) {
                    $result['rows'][$key]['tip'] = '更新至' . $FilmRecord->episode_number . '集';
                }else {
                    $result['rows'][$key]['tip'] = $isNew ? '有新片源' : '电影';
                }
            }
            $result['rows'][$key]['is_new'] = $isNew;
            unset($result['rows'][$key]['episode_today']);
        }
        return $result;
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

