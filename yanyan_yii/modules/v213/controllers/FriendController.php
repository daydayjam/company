<?php
/**
 * 好友控制器类
 * @author ztt
 * @date 2017/11/24
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Cache;
use app\models\Friend;
use app\models\Shield;

class FriendController extends Controller {
    
    /**
     * 关注用户
     * @return void
     */
    public function actionConcern() {
        $fuid = $this->getParam('uid');
        $Friend = new Friend();
        $result = $Friend->follow($fuid);
        if(!$result) {
            $this->showError($Friend);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取关注列表
     * @return void
     */
    public function actionConcernlist() {
        $uid = Cache::hget('id');
        $Friend = new Friend();
        $result = $Friend->getList($uid, true);
        if(!$result) {
            $this->showError($Friend);
        }
        $this->showOk($result);
    }
    
    /**
     * 取消关注
     * @return void
     */
    public function actionDelconcern() {
        $fuid = $this->getParam('uid');
        $Friend = new Friend();
        $result = $Friend->unfollow($fuid);
        if(!$result) {
            $this->showError($Friend);
        }
        $this->showOk($result);
    }
    
    /**
     * 屏蔽用户
     * @return void
     */
    public function actionShield() {
        $suid = $this->getParam('uid');
        $Shield = new Shield();
        $result = $Shield->add($suid);
        if(!$result) {
            $this->showError($Shield);
        }
        $this->showOk();
    }
    
  /**
     * 取消屏蔽用户
     * @return void
     */
    public function actionDelshield() {
        $suid = $this->getParam('uid');
        $Shield = new Shield();
        $result = $Shield->del($suid);
        if(!$result) {
            $this->showError($Shield);
        }
        $this->showOk();
    }  
    
    
}

