<?php
/**
 * 评论控制器类
 * @author ztt
 * @date 2018/01/15
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Comment;
use app\models\User;

class CommentController extends Controller {
    
    /**
     * 渲染评论列表
     * @return 
     */
    public function actionList() {
        $params = [];
        $params['ctype'] = $this->getParam('ctype');
        $params['ftitle'] = $this->getParam('ftitle');
        $params['cmt'] = $this->getParam('cmt');
        $page = $this->getParam('page', 1);
        $record = 20;
        $Comment = new Comment();
        $result = $Comment->getList($params, $page, $record);
        return $this->render('list', $result, $params);
    }
    
    /**
     * 获取自评论列表
     * @return void
     */
    public function actionGetadd() {
        $cid = $this->getParam('cid');
        $assocType = $this->getParam('assoc_type');
        $Comment = new Comment();
        $result = $Comment->getAdd($cid, $assocType);
        if(!$result) {
            $this->showError($Comment);
        }
        $this->showOk($result);
    }
    
    /**
     * 删除评论
     * @return void
     */
    public function actionDel() {
        $id = $this->getParam('id');
        $assocType = $this->getParam('assoc_type');
        $Comment = new Comment();
        $result = $Comment->del($id, $assocType);
        if(!$result) {
            $this->showError($Comment);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取详情
     * @return void
     */
    public function actionInfo() {
        $id = $this->getParam('id');
        $Comment = new Comment();
        $result = $Comment->getInfo($id);
        return $this->render('info', $result);
    }
    
    /**
     * 渲染添加评论界面
     * @return void
     */
    public function actionAdd() {
        $fid = $this->getParam('fid');
        $ftitle = $this->getParam('ftitle');
        $User = new User();
        $sysUsers = $User->getSysUsers();
        $result = [
            'fid'      =>$fid,
            'ftitle'    =>$ftitle,
            'sys_users'=>$sysUsers
        ];
        return $this->render('add', $result);
    }
    
    /**
     * 保存评论
     * @return void
     */
    public function actionSave() {
        $fid = $this->getParam('fid', 0);
        $uid = $this->getParam('uid', 0);
        $cmt = $this->getParam('cmt');
        $pic1 = $this->getParam('pic_1');
        $pic2 = $this->getParam('pic_2');
        $pic3 = $this->getParam('pic_3');
        $picArr = [$pic1, $pic2, $pic3];
        $Comment = new Comment();
        $result = $Comment->saveCmt($uid, $cmt, $fid, $picArr);
        if(!$result) {
            $this->showError($Comment);
        }
        $this->showOk();
    }
    
    
    
}

