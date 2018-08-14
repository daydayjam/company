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
use app\models\FilmSourceFeedback;

class FilmSource extends ActiveRecord {
    
    private $_Transaction = null;  //事务处理对象
    // 特色标签集合
    static $label = [1=>['color'=>'#6DC772','name'=>'在线看'], 2=>['color'=>'#76B3DB', 'name'=>'无广告'], 3=>['color'=>'#F06868', 'name'=>'需会员'], 4=>['color'=>'#F09797', 'name'=>'可下载']];
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%film_source}}';
    }
    
    /**
     * 获取影视剧单集片源列表
     * @param int $filmId 影视剧ID
     * @param int $epNum 剧集编号
     * @return array
     */
    public function getList($filmId, $epNum = 1) {
        $loginUid = Cache::hget('id');
        // 参数判断
        if(!is_numeric($filmId) || !is_numeric($epNum) || $epNum < 1 || $filmId < 0) {
            return $this->addError('', '-4:请求参数格式有误');
        }
        // 判断该影视是否有效
        $Film = new Film();
        $FilmRecord = $Film->findOne($filmId);
        if(!$FilmRecord || $FilmRecord->status == Yii::$app->params['state_code']['status_delete']) {
            return $this->addError('', '-7:您请求的影视剧不存在或已被下架');
        }
        if($FilmRecord->type == Yii::$app->params['state_code']['film_tv'] && $FilmRecord->episode_number < $epNum) {
            return $this->addError('', '-500:您想观看的集数未更新或未收录到本片源中');
        }
        $result = [];
        // [
        //  {
        //    'name' => '无双影视'，
        //    'list' => [
        //          {
        //              'name' => '线路1'，
        //              'label' => [
        //                      '在线看',
        //                      '可下载'
        //                  ],
        //              'url' => 'http://www.baidu.com'
        //          }
        //      ]
        //  }
        // ]
        $records = $this->findByCondition(['film_id'=>$filmId, 'number'=>$epNum, 'status'=>Yii::$app->params['state_code']['status_normal']])->all();  
        
        $routeArr = [];
        $lastRouteId = 0;
        $Route = new Route();
        $ViewRecord = new ViewRecord();
        $VRRecord = $ViewRecord->find()->where(['user_id'=>$loginUid, 'film_id'=>$filmId, 'number'=>$epNum])->orderBy('create_time desc')->limit(1)->one();
        if($loginUid && $VRRecord) {
            $lastRouteId = $VRRecord->route_id;
        }
        foreach($records as $value) {
            $routeId = $value->route_id;
            $RouteRecord = $Route->findOne($routeId);
            if(!in_array($RouteRecord->parent_id, $routeArr)) { 
                $routeArr[] = $RouteRecord->parent_id;
                $ParentRouteRecord = $Route->findOne($RouteRecord->parent_id);
                if($ParentRouteRecord->status != Yii::$app->params['state_code']['status_normal']) {
                    continue;
                }
                $routeArr[$RouteRecord->parent_id] = $ParentRouteRecord->name;
                $result[$RouteRecord->parent_id]['name'] = $ParentRouteRecord->name;
                $labelArr = explode('/', $value->label);
                foreach($labelArr as $label) {
                    $result[$RouteRecord->parent_id]['label'][] = ['num'=>(int)$label, 'name'=>self::$label[$label]['name'], 'color'=>self::$label[$label]['color']];
                }
                $result[$RouteRecord->parent_id]['list'][] = [
                                                            'id'      => $value->route_id, 
                                                            'name'    => $FilmRecord->title . ' 频道' . $RouteRecord->sort, 
                                                            'url'     => $value->url, 
                                                            'is_last' => $lastRouteId == $routeId ? 1 : 0
                                                        ];
            } else {
                $result[$RouteRecord->parent_id]['list'][] = [
                    'id' => $value->route_id,
                    'name' => $FilmRecord->title . ' 频道' . $RouteRecord->sort,
                    'url' => $value->url,
                    'is_last' => $lastRouteId == $routeId ? 1 : 0
                ];
            }
        }
        $newRes = [];
        foreach($result as $item) {
            $newRes[] = $item;
        }
        return $newRes;
    }
    
    /**
     * 片源报错反馈
     * @param int $filmId
     * @param int $epNum
     * @param int $routeId
     * @return boolen
     */
    public function feedback($filmId, $epNum, $routeId) {
        $loginUid = Cache::hget('id');
        // 参数判断
        if(!is_numeric($filmId) || !is_numeric($epNum) || !is_numeric($routeId) || $epNum < 1 || $filmId < 0 || $routeId < 0) {
            return $this->addError('', '-4:请求参数格式有误');
        }
        // 加锁，确保一个用户只反馈过一次
        $key = 'FILMSOURCE_FEEDBACK_'.$loginUid.'_'.$epNum.'_'.$routeId;
        Lock::unLock($key);
        if(!Lock::addlock($key)) {
            $this->addError('', '-10:处理中，请耐心等待');
            return false;
        }
        // 判断该影视是否有效
        $Film = new Film();
        $FilmRecord = $Film->findOne($filmId);
        if(!$FilmRecord || $FilmRecord->status == Yii::$app->params['state_code']['status_delete']) {
            Lock::unLock($key);
            return $this->addError('', '-7:您请求的影视剧不存在或已被下架');
        }
        if($FilmRecord->episode_number < $epNum) {
            Lock::unLock($key);
            return $this->addError('', '-500:您要反馈的片源未更新或未收录到本片源中');
        }
        $Record = $this->findByCondition(['film_id'=>$filmId, 'number'=>$epNum, 'route_id'=>$routeId])->one();
        if(!$Record) {
            Lock::unLock($key);
            return $this->addError('', '-500:您要反馈的片源未更新或未收录到本片源中');
        }
        if($Record->status == Yii::$app->params['state_code']['status_delete']) {
            Lock::unLock($key);
            return $this->addError('', '-501:您要反馈的片源已关闭或已下架');
        }
        $FilmSourceFeedback = new FilmSourceFeedback();
        $FSFRecord = $FilmSourceFeedback->findByCondition(['user_id'=>$loginUid, 'film_id'=>$filmId, 'number'=>$epNum, 'route_id'=>$routeId])->one();
        if($FSFRecord) {
            Lock::unLock($key);
            return $this->addError('', '-502:您已反馈过该片源，我们会尽快处理，请耐心等待，感谢您的关注');
        }
        $this->beginTransaction();
        if(!$FilmSourceFeedback->add($loginUid, $filmId, $epNum, $routeId)) {
            Lock::unLock($key);
            $this->rollback();
            $error = $FilmSourceFeedback->getCodeError();
            return $this->addError('', $error['code'] . ':' . $error['msg']);
        }
        $sql = 'select count(*) as cnt from ' . $FilmSourceFeedback->tableName() . ' where film_id=:film_id and number=:number and route_id=:route_id';
        $crrCnt = $FilmSourceFeedback->findBySql($sql, [':film_id'=>$filmId, ':number'=>$epNum, ':route_id'=>$routeId])->asArray()->one();
        if($crrCnt['cnt'] >= Yii::$app->params['state_code']['film_source_feedback']) {
            $Record->status = Yii::$app->params['state_code']['status_delete'];
            if(!$Record->save()) {
                Lock::unLock($key);
                $this->rollback();
                return $this->addError('', '0:反馈失败，请稍后重试');
            }
        }
        Lock::unLock($key);
        $this->commit();
        return true;
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

