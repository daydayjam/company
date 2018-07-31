<?php
/**
 * 评论控制器类
 * @author ztt
 * @date 2017/11/30
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Comment;

class CommentController extends Controller {
    
    /**
     * 添加评论信息
     * @return void
     */
    public function actionAdd() {
        $assoctype = $this->getParam('assoc_type', 0);
        $ctype = $this->getParam('ctype', 1);
        $filmId = $this->getParam('film_id', 0);
        $epNum = $this->getParam('ep_num', 0);
        $mcid = $this->getParam('mcid', 0);
        $cmtId = $this->getParam('comment_id', 0);
        $comment = $this->getParam('comment');
        $from = $this->getParam('from', 0);
        $pic1 = $this->getParam('pic_1');
        $pic2 = $this->getParam('pic_2');
        $pic3 = $this->getParam('pic_3');
        $picArr = [$pic1, $pic2, $pic3];
        $Comment = new Comment();
        $result = $Comment->add($comment, $filmId, $epNum, $ctype, $mcid, $cmtId, $assoctype, $from, $picArr);
        if(!$result) {
            $this->showError($Comment);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取评论详情
     * @return void
     */
    public function actionInfo() {        
        $id = $this->getParam('id');
        $Comment = new Comment();
        $result = $Comment->getInfo($id);
        if(!$result) {
            $this->showError($Comment);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取某个用户的影评列表
     * @return void
     */
    public function actionUserlist() {
        $uid = $this->getParam('user_id');
        $page = $this->getParam('page', 1);
        $pageSize = $this->getParam('pagesize', 10);
        $Comment = new Comment();
        $result = $Comment->getUserCmtList($uid, $page, $pageSize);
        if($result === false) {
            $this->showError($Comment);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取最新评论列表
     * @return void
     */
    public function actionNewlist() {
        $page = $this->getParam('page', 1);
        $pageSize = $this->getParam('pagesize', 10);
        $Comment = new Comment();
        $result = $Comment->getNewCmtList($page, $pageSize);
        if($result === false) {
            $this->showError($Comment);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取关注的用户的评论列表
     * @return void
     */
    public function actionConcernlist() {
        $page = $this->getParam('page', 1);
        $pageSize = $this->getParam('pagesize', 10);
        $Comment = new Comment();
        $result = $Comment->getConcernCmtList($page, $pageSize);
        if($result === false) {
            $this->showError($Comment);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取评论相关回复列表
     * @return void
     */
    public function actionCmtlist() {
        $assocType = $this->getParam('assoc_type', 0);
        $cmtId = $this->getParam('cmt_id');
        $page = $this->getParam('page', 1);
        $pageSize = $this->getParam('pagesize', 10);
        $Comment = new Comment();
        $result = $Comment->getCmtList($cmtId, $assocType, $page, $pageSize);
        if($result === false) {
            $this->showError($Comment);
        }
        $result = ['page'=>$page, 'pagesize'=>$pageSize, 'rows'=>$result];
        $this->showOk($result);
    }
    
    /**
     * 点赞
     * @return void
     */
    public function actionPraise() {
        $assocType = $this->getParam('assoc_type', 0);
        $from = $this->getParam('from', 0);
        $id = $this->getParam('id');
        $Comment = new Comment();
        $result = $Comment->praise($id, $assocType, $from);
        if(!$result) {
            $this->showError($Comment);
        }
        $this->showOk($result);
    }
    
    /**
     * 赞同
     * @return void
     */
    public function actionAgree() {
        $id = $this->getParam('cid');
        $Comment = new Comment();
        $result = $Comment->agree($id);
        if(!$result) {
            $this->showError($Comment);
        }
        $this->showOk($result);
    }
    
    /**
     * 取消赞同
     * @return void
     */
    public function actionDelagree() {
        $id = $this->getParam('cid');
        $Comment = new Comment();
        $result = $Comment->delAgree($id);
        if(!$result) {
            $this->showError($Comment);
        }
        $this->showOk();
    }
    
    /**
     * 删除评论,可以删除自己的评论，也可以分别删除别人的评论
     * @return void
     */
    public function actionDel() {
        $cid = $this->getParam('cid');
        $from = $this->getParam('from', 0);
        $Comment = new Comment();
        $result = $Comment->del($cid, $from);
        if(!$result) {
            $this->showError($Comment);
        }
        $this->showOk();
    }
    
    /**
     * 获取剧集评论列表
     * @return void
     */
    public function actionEpcmtlist() {
        $epNum = $this->getParam('ep_num');
        $filmId = $this->getParam('film_id');
        $page = $this->getParam('page', 1);
        $pageSize = $this->getParam('pagesize', 10);
        $Comment = new Comment();
        $result = $Comment->getListByEp($filmId, $epNum, $page, $pageSize);
        if($result === false) {
            $this->showError($Comment);
        }
        $this->showOk($result);
    }
    
    /**
     * 根据影视剧ID查找评论列表
     */
    public function actionFilmlist() {
        $filmId = $this->getParam('film_id');
        $page = $this->getParam('page', 1);
        $pagesize = $this->getParam('pagesize', 10);
        $Comment = new Comment();
        $result = $Comment->getListByFilm($filmId, $page, $pagesize);
        if($result === false) {
            $this->showError($Comment);
        }
        $this->showOk($result);
        
    }
    
    
    
}

