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
use app\models\Tool;
use app\models\Chatroom;
use app\models\Cache;

class Film extends ActiveRecord {
    private $_Transaction = null;  //事务处理对象
    public static $cover = '/system/movie_nopic@3x.png';    //无封面时显示的默认图片
    public static $type = [1, 2];   //影视剧标签：1=综艺；2=动画
    public static $kind = [1,2,3,4];   //影视剧类型：1=电影；2=电视剧
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%film}}';
    }
    
    /**
     * 获取列表
     * @param type $params
     * @param int $page
     * @param int $record
     * @return array|boolen
     */
    public function getList($params, $page = 1, $record = 20) {
        $andParams = [];
        if($params['id']) {
            $andParams['id'] = $params['id'];
        }
        if($params['kind']) {
            $andParams['kind'] = $params['kind'];
        }
        if($params['is_hot']!='') {
            $andParams['is_hot'] = $params['is_hot'];
        }
        if($params['genre']) {
            $andParams['genre'] = [
                'val'=>'%' . $params['genre'] . '%',
                'op' =>'like'
            ];
        }
        if($params['title']) {
            $andParams['title'] = [
                'val'=>'%' . $params['title'] . '%',
                'op' =>'like'
            ];
        }
        if($params['area']) {
            $andParams['area'] = [
                'val'=>'%' . $params['area'] . '%',
                'op' =>'like'
            ];
        }
        if($params['year']) {
            $andParams['year'] = [
                'val'=>'%' . $params['year'] . '%',
                'op' =>'like'
            ];
        }
        $select = 'id,title,kind,genre,kind_extra,area,year,is_hot';
        $result = $this->getListData($select, $page, $record, $andParams, [], [], 'id asc');
        $result['page'] = $page;
        $result['record'] = $record;
        return $result;
    }
    
    /**
     * 设置是否热门
     * @param int $id
     * @param int $isHot
     * @return boolean
     */
    public function setIsHot($id, $isHot) {
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!in_array($isHot, [0, 1])) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Record = $this->findOne($id);
        if(!$Record) {
            $this->addError('', '-7:该影视剧不存在');
            return false;
        }
        if($Record->is_hot == $isHot) {
            return true;
        }
        $Record->is_hot = $isHot;
        if(!$Record->save()) {
            $this->addError('', '0:设置失败');
            return false;
        }
        // 修改影视剧热门列表缓存中的值
        // 取消热门，将缓存中的值删除
        $CacheKey = 'FILM_HOT';
        if($isHot == 0) {
            Yii::$app->redis->zrem($CacheKey .$Record->kind, $Record->id);
            Yii::$app->redis->hdel($CacheKey .$Record->kind . '_CONTENT', $Record->id);
        }else {
            $result = [];
            $result['id'] = $Record->id;
            $result['kind'] = $Record->kind;
            $result['title'] = $Record->title;
            $result['cover'] = $Record->cover;
            $result['year'] = $Record->year;
            $result['tag'] = $Record->genre;
            $result['main_actor'] = $Record->main_actor;
            $result['episode_number'] = $Record->episode_number;
            $result['type'] = $Record->type;
            $result['update_time'] = date('Y-m-d H:i:s');
            $orderBy = $Record->year + strtotime($Record->update_time);
            Yii::$app->redis->zadd($CacheKey . $Record->kind, $orderBy, $Record->id);
            Yii::$app->redis->hset($CacheKey . $Record->kind . '_CONTENT', $Record->id, json_encode($result));
        }
        return true;
    }
    
    /**
     * 获取影视剧信息详情
     * @param int $id 影视剧ID
     * @return array
     */
    public function getInfo($id) {
        $Query = new Query();
        $result = $Query->select('id,kind,title,cover,director,genre,kind_extra,area,main_actor,year,summary')
                        ->from($this->tableName())
                        ->where(['id'=>$id])
                        ->one();
        if(!$result['cover']) {
            $result['cover'] = Yii::$app->params['image_domain'] . self::$cover;
        }else {
            $result['cover'] = Tool::connectPath(Yii::$app->params['image_domain'], $result['cover']);
        }
        $result['director'] = json_decode($result['director'], true);
        $result['genre'] = explode('/', $result['genre']);
        $result['kind_extra'] = $result['kind_extra'];
        return $result;
    }
    
    /**
     * 修改影视剧信息
     * @param array $params
     * @return boolen
     */
    public function updateFilm($params) {
        if(!is_numeric($params['id'])) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!$params['title'] || !$params['kind']) {
            $this->addError('', '-3:影片中文名、影视剧类型不能为空');
            return false;
        }
        if(!in_array($params['kind'], self::$kind)) {
            $this->addError('', '-4:影视剧类型格式有误');
            return false;
        }
        if($params['year'] && !preg_match('/^\d{4}$/', $params['year'])) {
            $this->addError('', '-4:年代格式有误');
            return false;
        }
        $Record = $this->findOne($params['id']);
        if(!$Record) {
            $this->addError('', '-7:该影视剧不存在');
            return false;
        }
        $this->beginTransaction();
        //图片上传
        $coverUrl = '';
        if($params['cover']) {
            $Attachment = new Attachment();
            $cover = preg_replace('/^(data:\s*image\/(\w+);base64,)/', '', $params['cover']);
            $result = $Attachment->uploadBase64Img($cover, 'film');
            if(!$result) {
                $this->rollback();
                $error = $Attachment->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
            $coverUrl = $result['path'];
        }
        $Record->title = $params['title'];
        $Record->kind = $params['kind'];
        $Record->year = $params['year'];
        $Record->area = $params['area'];
        $Record->genre = $params['genre'];
        $Record->summary = $params['summary'];
        $Record->cover = $coverUrl ? $coverUrl : $Record->cover;
        if(!$Record->save()) {
            $this->rollback();
            $this->addError('', '0:更新失败');
            return false;
        }
        $this->commit();
        if($Record->is_hot == 1) {
            $result = [];
            $result['id'] = $Record->id;
            $result['kind'] = $Record->kind;
            $result['title'] = $Record->title;
            $result['cover'] = $Record->cover;
            $result['year'] = $Record->year;
            $result['tag'] = $Record->genre;
            $result['main_actor'] = $Record->main_actor;
            $result['episode_number'] = $Record->episode_number;
            $result['type'] = $Record->type;
            $result['update_time'] = date('Y-m-d H:i:s');
            $orderBy = $Record->year + strtotime($Record->update_time);
            $CacheKey = 'FILM_HOT';
            Yii::$app->redis->zadd($CacheKey . $Record->kind, $orderBy, $Record->id);
            Yii::$app->redis->hset($CacheKey . $Record->kind . '_CONTENT', $Record->id, json_encode($result));
        }
        return true;
    }
    
    /**
     * 删除影片
     * @param int $id
     * @return boolean
     */
    public function delFilm($id) {
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Record = $this->findOne($id);
        if(!$Record) {
            $this->addError('', '-7:该影视剧不存在');
            return false;
        }
        $Comment = new Comment();
        $CmtRecord = $Comment->findByCondition(['assoc_id'=>$id, 'assoc_type'=>0])->one();
        if($CmtRecord) {
            $this->addError('', '-7:包含相关评论，请删除评论后再删除该影片');
            return false;
        }
        if(!$Record->delete()) {
            $this->addError('', '0:删除失败');
            return false;
        }
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

