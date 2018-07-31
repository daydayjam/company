<?php
/**
 * 资讯模型类
 * @author ztt
 * @date 2018/04/02
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;
use app\models\Tool;
use app\models\Chatroom;
use app\models\Cache;

class News extends ActiveRecord {
    private $_Transaction = null;  //事务处理对象
    
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%news}}';
    }
    
    /**
     * 获取列表
     * @param type $params
     * @param int $page
     * @param int $record
     * @return array|boolen
     */
    public function getList($params, $page = 1, $record = 20) {
        $andParams = ['is_delete'=>0];
        if($params['source_type']) {
            $andParams['source_type'] = $params['source_type'];
        }
        
        if($params['title']) {
            $andParams['title'] = [
                'val'=>'%' . $params['title'] . '%',
                'op' =>'like'
            ];
        }
        if($params['description']) {
            $andParams['description'] = [
                'val'=>'%' . $params['description'] . '%',
                'op' =>'like'
            ];
        }
        if($params['author']) {
            $name = str_replace('"','',json_encode($params['author']));  
            $name = str_replace("\\",'_',$name);  
            $andParams['author'] = [
                'val'=>'%' . $name . '%',
                'op' =>'like'
            ];
        }
        $select = 'id,title,source_type,author,description,video_info,source_url,praise_cnt,comment_cnt,pubdate';
        $result = $this->getListData($select, $page, $record, $andParams, [], [], 'pubdate desc, update_time desc');
        foreach($result['rows'] as $key=>$item) {
            $result['rows'][$key]['author'] = json_decode($item['author'], true);
            $result['rows'][$key]['description'] = mb_strlen($item['description']) > 20 ? mb_substr($item['description'], 0, 20) . '...' : $item['description'];
            $result['rows'][$key]['title'] = $item['title'] ? $item['title'] : mb_substr($item['description'], 0, 20) . '...';
        }
        $result['page'] = $page;
        $result['record'] = $record;
        return $result;
    }
    
    /**
     * 获取资讯信息详情
     * @param int $id 资讯ID
     * @return array
     */
    public function getInfo($id) {
        $Query = new Query();
        $result = $Query->select('id,source_type,title,author,cover,description,')
                        ->from($this->tableName())
                        ->where(['id'=>$id])
                        ->one();
        if(!$result['cover']) {
            $result['cover'] = Yii::$app->params['image_domain'] . self::$cover;
        }else {
            $result['cover'] = Tool::connectPath(Yii::$app->params['image_domain'], $result['cover']);
        }
        $result['director'] = json_decode($result['director'], true);
        $result['types'] = explode('/', $result['types']);
        $result['flag'] = explode('/', $result['flag']);
        return $result;
    }
    
    /**
     * 添加影视剧信息
     * @param array $params
     * @return boolen
     */
    public function saveFilm($params) {
        if(!$params['name'] || !$params['ftype']) {
            $this->addError('', '-3:影片中文名、影视剧类型不能为空');
            return false;
        }
        if(!in_array($params['ftype'], self::$ftype)) {
            $this->addError('', '-4:影视剧类型格式有误');
            return false;
        }
        if($params['flag'] && !in_array($params['flag'], self::$flag)) {
            $this->addError('', '-4:影片标签格式有误');
            return false;
        }
        if($params['score'] && !is_numeric($params['score'])) {
            $this->addError('', '-4:评分格式有误');
            return false;
        }
        if($params['release_date'] && !preg_match('/\d{4}(-\d{1,2}(-\d{1,2})?)?/', $params['release_date'])) {
            $this->addError('', '-4:上映时间格式有误');
            return false;
        }
        $Record = $this->findByCondition(['name'=>$params['name'], 'release_date'=>$params['release_date'], 'ftype'=>$params['ftype']])->one();
        if($Record) {
            $this->addError('', '-7:该影视剧已存在');
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
        $this->name = $params['name'];
        $this->en_name = $params['en_name'];
        $this->ftype = $params['ftype'];
        $this->release_date = $params['release_date'];
        $this->flag = $params['flag'];
        $this->score = $params['score'];
        $this->acts_main = $params['acts_main'];
        $this->director = '';
        $this->actors = '';
        $this->artists = '';
        $this->posters = '';
        $this->types = $params['types'];
        $this->summary = $params['summary'];
        $this->cover = $coverUrl;
        if(!$this->save()) {
            $this->rollback();
            $this->addError('', '0:更新失败');
            return false;
        }
        $this->commit();
        return true;
    }
    
    /**
     * 添加影视剧信息
     * @param array $params
     * @return boolen
     */
    public function updateFilm($params) {
        if(!is_numeric($params['id'])) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!$params['name'] || !$params['ftype']) {
            $this->addError('', '-3:影片中文名、影视剧类型不能为空');
            return false;
        }
        if(!in_array($params['ftype'], self::$ftype)) {
            $this->addError('', '-4:影视剧类型格式有误');
            return false;
        }
        if($params['flag'] && !in_array($params['flag'], self::$flag)) {
            $this->addError('', '-4:影片标签格式有误');
            return false;
        }
        if($params['score'] && !is_numeric($params['score'])) {
            $this->addError('', '-4:评分格式有误');
            return false;
        }
        if($params['release_date'] && !preg_match('/\d{4}(-\d{1,2}(-\d{1,2})?)?/', $params['release_date'])) {
            $this->addError('', '-4:上映时间格式有误');
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
        $Record->name = $params['name'];
        $Record->en_name = $params['en_name'];
        $Record->ftype = $params['ftype'];
        $Record->release_date = $params['release_date'];
        $Record->flag = $params['flag'];
        $Record->score = $params['score'];
        $Record->types = $params['types'];
        $Record->summary = $params['summary'];
        $Record->cover = $coverUrl ? $coverUrl : $Record->cover;
        if(!$Record->save()) {
            $this->rollback();
            $this->addError('', '0:更新失败');
            return false;
        }
        //有聊天时则更改聊天室名称
        if($Record->ease_id && $Record->name != $params['name']) {
            $Chatroom = new Chatroom();
            $RoomRecord = $Chatroom->findOne($params['id']);
            $RoomRecord->name = $params['name'];
        }
        $this->commit();
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
        $CmtRecord = $Comment->findByCondition(['tv_id'=>$id])->one();
        if($CmtRecord) {
            $this->addError('', '-7:包含相关评论，请删除评论后再删除该影片');
            return false;
        }
        $Chatroom = new Chatroom();
        $RoomRecord = $Chatroom->findOne($id);
        if($RoomRecord) {
            $this->addError('', '-7:已开通聊天室，请删除聊天室后再删除该影片');
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

