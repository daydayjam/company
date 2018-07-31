<?php
/**
 * 评论模型类
 * @author ztt
 * @date 2017/11/9
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;
use app\models\Tool;
use app\models\Praise;
use app\models\Safety;
use app\models\Attachment;
use app\models\Cmd;
use app\models\CmdHelper;
use app\models\Film;
use app\models\User;

class Comment extends ActiveRecord {
    public static $ctype = [1=>'电影', 2=>'电视剧'];
    private $_Transaction = null;  //事务处理对象
    const SINGLE_PRAISE_LIMIT = 10;    //单人点赞上限


    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%comment}}';
    }
    
    /**
     * 获取评论列表
     * @param type $params
     * @param type $page
     * @param type $record
     */
    public function getList($params, $page = 1, $record = 20) {
        $selfTbname = $this->tableName();
        $leftJoin = ['tbname'=>Film::tableName(),'on'=>Film::tableName().'.id='.$this->tableName().'.assoc_id'];
        $andParams = [];
        if($params['ctype']) {
            $andParams['ctype'] = $params['ctype'];
        }
        if($params['ftitle']) {
            $andParams['title'] = [
                'val'=>'%' . $params['ftitle'] . '%',
                'op' =>'like'
            ];
        }
        if($params['cmt']) {
            $andParams['comment'] = [
                'val'=>'%' . $params['cmt'] . '%',
                'op' =>'like'
            ];
        }
        $selfSelectArr = ['id','assoc_type','assoc_id','ctype','comment','praise_cnt','comment_cnt','pics','create_time'];
        foreach($selfSelectArr as $key=>$self) {
            $selfSelectArr[$key] = $selfTbname.'.'.$self;
        }
        $selfSelect = implode(',', $selfSelectArr);
        $leftSelectArr = ['title'];
        foreach($leftSelectArr as $key=>$left) {
            $leftSelectArr[$key] = $leftJoin['tbname'] . '.' . $left;
        }
        $leftSelect = implode(',', $leftSelectArr);
        $select = $selfSelect . ',' . $leftSelect;
        $result = $this->getListData($select, $page, $record, $andParams, [], $leftJoin);
        foreach($result['rows'] as $key=>$item) {
            $result['rows'][$key]['pics_cnt'] = 0;
            if($item['pics']) {
                $pics = json_decode($item['pics'], true);
                $result['rows'][$key]['pics_cnt'] = count($pics);
            }
            if(mb_strlen($item['comment']) > 20) {
                $result['rows'][$key]['comment'] = mb_substr($result['rows'][$key]['comment'], 0, 20) . '...';
            }
        }
        $result['page'] = $page;
        $result['record'] = $record;        
        return $result;
    }
    
    /**
     * 获取子评论
     * @param int $cid
     * @return array
     */
    public function getAdd($cid, $assocType = 0) {
        if(!is_numeric($cid)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $result = (new \yii\db\Query())
                ->select('c.id,c.uid,c.reply_uid,c.`comment`,c.praise_cnt,c.comment_cnt,c.is_agree,u.nickname,u.gender,concat("'.Yii::$app->params['image_domain'].'",`u`.`avatar`) as avatar,u2.nickname as reply_nick,c.create_time')
                ->from($this->tableName() . ' as c')
                ->leftJoin(User::tableName() . ' as u', 'u.id=c.uid')
                ->leftJoin(User::tableName() . ' as u2', 'u2.id=c.reply_uid')
                ->where(['c.mcid'=>$cid, 'c.assoc_type'=>$assocType])
                ->orderBy('c.id desc')
                ->all();
        return $result;
    }
    
    /**
     * 删除评论
     * @param int $id
     * @return boolen
     */
    public function del($id, $assocType = 0) {
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $mcid = 0;
        $cmtCnt = 0;
        $this->beginTransaction();
        $Record = null;
        if($assocType == 1) { //删除资讯
            $mcid = $id;
            $News = new News();
            $Record = $News->findOne($id);
            if(!$Record) {
                $this->rollback();
                $this->addError('', '-7:该资讯不存在');
                return false;
            }
            //删除资讯相关评论
            $subCmtRecord = $this->findByCondition(['mcid'=>$id, 'assoc_type'=>1, 'is_delete'=>0])->one();
            if($subCmtRecord && !$this->updateAll(['is_delete'=>1], ['mcid'=>$id, 'assoc_type'=>1])) {
                $this->rollback();
                $this->addError('', '0:子评论删除失败');
                return false;
            }
            $Record->is_delete = 1;
            if(!$Record->save()) {
                $this->rollback();
                $this->addError('', '0:资讯删除失败');
                return false;
            }
        }
        else {  //删除评论
            $Record = $this->findOne($id);
            if(!$Record) {
                $this->rollback();
                $this->addError('', '-7:该评论不存在');
                return false;
            }
            if($Record->assoc_type == 1) {  //资讯相关评论
                $mcid = $Record->mcid;
                $News = new News();
                $NewsRecord = $News->findOne($Record->mcid);
                if(!$NewsRecord) {
                    $this->rollback();
                    $this->addError('', '-7:该相关资讯不存在');
                    return false;
                }
                $subCmtRecords = $this->findByCondition(['comment_id'=>$id, 'is_delete'=>0])->asArray()->all();
                if($subCmtRecords && !$this->updateAll(['is_delete'=>1], ['comment_id'=>$id])) {
                    $this->rollback();
                    $this->addError('', '0:子评论删除失败1');
                    return false;
                }
                $cmtCnt = $NewsRecord->comment_cnt - count($subCmtRecords) - 1;
                $NewsRecord->comment_cnt = $cmtCnt;
                if(!$NewsRecord->save()) {
                    $this->rollback();
                    $this->addError('', '0:子评论删除失败2');
                    return false;
                }
            }
            else {
                $mcid = $Record->mcid;
                if($Record->mcid) {
                    $McmtRecord = $this->findOne($Record->mcid);
                    if(!$McmtRecord) {
                        $this->rollback();
                        $this->addError('', '-7:该主评论不存在');
                        return false;
                    }
                    $subCmtRecords = $this->findByCondition(['comment_id'=>$id, 'is_delete'=>0])->asArray()->all();
                    if($subCmtRecords && !$this->updateAll(['is_delete'=>1], ['comment_id'=>$id])) {
                        $this->rollback();
                        $this->addError('', '0:子评论删除失败3');
                        return false;
                    }
                    $cmtCnt = $McmtRecord->comment_cnt - count($subCmtRecords) - 1;
                    $McmtRecord->comment_cnt = $cmtCnt;
                    if(!$McmtRecord->save()) {
                        $this->rollback();
                        $this->addError('', '0:主评论被评论次数更新失败');
                        return false;
                    }
                } 
                else {
                    $subMcCmtRecord = $this->findByCondition(['mcid'=>$id, 'is_delete'=>0])->one();
                    if($subMcCmtRecord && !$this->updateAll(['is_delete'=>1], ['mcid'=>$id])) {
                        $this->rollback();
                        $this->addError('', '0:子评论删除失败4');
                        return false;
                    }
                }
            }
            $Record->is_delete = 1;
            if(!$Record->save()) {
                $this->rollback();
                $this->addError('', '0:删除失败');
                return false;
            }
        }
        
        $this->commit();
        return ['mcid'=>$mcid,'cmt_cnt'=>$cmtCnt];
    }
    
    /**
     * 获取详情
     * @param type $id
     * @return boolean
     */
    public function getInfo($id) {
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $Record = $this->findOne($id);
        if(!$Record) {
            $this->addError('', '-7:该评论不存在');
            return false;
        }
        $Query = new Query();
        $result = $Query->select('c.id,c.ctype,c.comment,c.assoc_id,c.pics,c.create_time,f.title,u.nickname as reply_nick')
                        ->from($this->tableName(). ' as c')
                        ->leftJoin(Film::tableName(). ' as f', 'f.id=c.assoc_id')
                        ->leftJoin(User::tableName() . ' as u', 'u.id=c.reply_uid')
                        ->where(['c.id'=>$id])
                        ->one();
        if($result['pics']) {
            $pics = json_decode($result['pics'], true);
            foreach($pics as $key=>$item) {
                $pics[$key]['path'] = Tool::connectPath(Yii::$app->params['image_domain'], $item['path']);
            }
            $result['pics'] = $pics;
        }
        $result['ctype'] = self::$ctype[$result['ctype']];
        
        return $result;
    }
    
    /**
     * 创建评论或提问
     * @param int $fid
     * @param int $uid
     * @param string $cmt
     * @param array $picArr
     * @return boolean
     */
    public function saveCmt($uid, $cmt, $fid = 0, $picArr = []) {
        if($fid && !is_numeric($fid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!is_numeric($uid)) {
            $this->addError('', '-4:用户ID格式有误');
            return false;
        }
        if(!$cmt) {
            $this->addError('', '-3:评论内容不能为空');
            return false;
        }
        if(mb_strlen($cmt) < 8) {
            $this->addError('', '-4:评论内容长度应不少于8个字');
            return false;
        }
        $User = new User();
        $UserRecord = $User->findOne($uid);
        if(!$UserRecord) {
            $this->addError('', '-7:该系统用户不存在');
            return false;
        }
        if($fid) {
            $Film = new Film();
            $FilmRecord = $Film->findOne($fid);
            if(!$FilmRecord) {
                $this->addError('', '-7:该关联影视不存在');
                return false;
            }
        }
        //图片处理
        $imgArr = [];
        foreach($picArr as $key=>$pic) {
            if(!empty($pic)) {
                $Attachment = new Attachment();
                $pic = preg_replace('/^(data:\s*image\/(\w+);base64,)/', '', $pic);
                $uploadResult = $Attachment->uploadBase64Img($pic, 'normal');
                if(!$uploadResult) {
                    $error = $Attachment->getCodeError();
                    $this->addError('', $error['code'].':'.$error['msg']);
                    return false;
                }
                $imgArr[$key]['path'] = $uploadResult['path'];
                $imgArr[$key]['width'] = $uploadResult['width'];
                $imgArr[$key]['height'] = $uploadResult['height'];
            }
        }
        
        //添加评论信息
        $this->uid = $uid;
        $this->assoc_id = $fid ? $fid : 0;
        $this->ctype = $fid ? 1 : 2;
        $this->comment = $cmt;
        $this->pics = empty($imgArr) ? '' : json_encode($imgArr);
        if(!$this->save()) {
            $this->addError('', '0:评论失败');
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

