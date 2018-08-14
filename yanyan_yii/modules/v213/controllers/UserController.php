<?php
/**
 * 用户控制器类
 * @author ztt
 * @date 2017/10/26
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\User;    
use app\models\Tool;
use app\models\Cache;
use app\models\FilmFollow;

class UserController extends Controller {
    /**
     * 用户注册
     * @return void
     */
    public function actionRegister() {
        $tel = $this->getParam('tel');
        $pwd = $this->getParam('pwd');
        $nick = $this->getParam('nick');
        $avatar = $this->getParam('avatar');
        $gender = $this->getParam('gender', 0);
        $birthDate = $this->getParam('birth', '1999-01-01');
        $signature = $this->getParam('signature', '神秘的小言');
        $User = new User();
        $result = $User->register($tel, $pwd, $nick, $avatar, $gender, $birthDate, $signature);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk($result);
    }
    
    /**
     * 用户登录
     * @return void
     */
    public function actionLogin() {
        $tel = $this->getParam('tel');
        $pwd = $this->getParam('pwd');
        $User = new User();
        $result = $User->login($tel, $pwd);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk($result);
    }
    
    /**
     * 检查验证码是否正确以及手机号是否注册
     * @return void
     */
    public function actionRegcheck() {
        $tel = $this->getParam('tel');
        $vcode = $this->getParam('vcode');
        $type = $this->getParam('type', 'user_register');
        $User = new User();
        $result = $User->regCheck($tel, $vcode, $type);
        if(!$result) {
            $this->showError($User);
        }
        //设置一个key，保证下一页面由此而来
        $uniqueValue = Tool::getUniqueValue($tel, $vcode, $type);
        Cache::set('linkkey', Tool::md5Double($uniqueValue));
        $this->showOk();
    }
    
    /**
     * 第三方注册
     * @return void
     */
    public function actionThirdregister() {
        $plat = $this->getParam('plat');
        $uuid = $this->getParam('uuid');
        $nick = $this->getParam('nick');
        $gender = $this->getParam('gender', 0);
        $birth = $this->getParam('birth', '1999-01-01');
        $avatar = $this->getParam('avatar');
        $signature = $this->getParam('signature', '神秘的小言');
        $User = new User();
        $result = $User->thirdRegister($plat, $uuid, $nick, $avatar, $gender, $birth, $signature);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk($result);
    }
    
    /**
     * 第三方登录
     * @return void
     */
    public function actionThirdlogin() {
        $plat = $this->getParam('plat');
        $uuid = $this->getParam('uuid');
        $User = new User();
        $result = $User->thirdLogin($plat, $uuid);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取用户信息
     * @return void
     */
    public function actionInfo() {
        $uid = $this->getParam('uid');
        $User = new User();
        $result = $User->getInfo($uid);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk($result);
    }
    
    /**
     * 更新用户头像
     * @return void
     */
    public function actionUpdateavatar() {
        $avatar = $this->getParam('avatar');
        $User = new User();
        $result = $User->updateAvatar($avatar);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk($result);
    }
    
    /**
     * 更改用户生日
     * @return void
     */
    public function actionUpdatebirth() {
        $birth = $this->getParam('birth');
        $User = new User();
        $result = $User->updateBirth($birth);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 更改用户感情状态
     * @return void
     */
    public function actionUpdateemo() {
        $emotion = $this->getParam('emotion', 0);
        $User = new User();
        $result = $User->updateEmo($emotion);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 更改用户家乡
     * @return void
     */
    public function actionUpdatehome() {
        $hometown = $this->getParam('hometown');
        $User = new User();
        $result = $User->updateHometown($hometown);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 更改用户爱好
     * @return void
     */
    public function actionUpdateintrest() {
        $intrest = $this->getParam('intrest');
        $User = new User();
        $result = $User->updateIntrest($intrest);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 更改用户昵称
     * @return void
     */
    public function actionUpdatenick() {
        $nickname = $this->getParam('nickname');
        $User = new User();
        $result = $User->updateNick($nickname);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 更改用户签名
     * @return void
     */
    public function actionUpdatesign() {
        $signature = $this->getParam('signature');
        $User = new User();
        $result = $User->updateSign($signature);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 绑定手机号
     * @return void
     */
    public function actionBindtel() {
        $tel = $this->getParam('tel');
        $vcode = $this->getParam('vcode');
        $pwd = $this->getParam('pwd');
        $User = new User();
        $result = $User->bindTel($tel, $vcode, $pwd);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 修改手机号
     * @return void
     */
    public function actionChangetel() {
        $tel = $this->getParam('tel');
        $vcode = $this->getParam('vcode');
        $User = new User();
        $result = $User->changeTel($tel, $vcode);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 绑定第三方
     * @return void
     */
    public function actionBindthird() {
        $plat = $this->getParam('plat_name');
        $platId = $this->getParam('plat_id');
        $User = new User();
        $result = $User->bindThird($plat, $platId);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk($result);
    }
    
    /**
     * 绑定第三方
     * @return void
     */
    public function actionUnbindthird() {
        $plat = $this->getParam('plat_name');
        $User = new User();
        $result = $User->unBindThird($plat);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 修改密码
     * @return void
     */
    public function actionChangepwd() {
        $pwd = $this->getParam('pwd');
        $oldPwd = $this->getParam('oldpwd');
        $User = new User();
        $result = $User->changePwd($pwd, $oldPwd);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 找回密码
     * @return void
     */
    public function actionFindpwd() {
        $tel = $this->getParam('tel');
        $vcode = $this->getParam('vcode');
        $pwd = $this->getParam('pwd');
        $User = new User();
        $result = $User->findPwd($tel, $vcode, $pwd);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 判断用户是否被封号
     * @return void
     */
    public function actionIsout() {
        $uid = $this->getParam('uid');
        $User = new User();
        $result = $User->isOut($uid);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 修改用户消息角标
     * @return void
     */
    public function actionSetbadge() {
        $uid = $this->getParam('uid');
        $badge = $this->getParam('badge');
        $lasttime = $this->getParam('lasttime');
        $User = new User();
        $result = $User->setBadge($uid, $badge, $lasttime);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 判断是否当日第一次登录
     * @return void
     */
    public function actionIsfirstlogin() {
//        $isLoginKey = Yii::$app->params['unique_token'] . date('Ymd') . '_' . Cache::hget('id');
//        $isLogin = Cache::get($isLoginKey);
//        if(isset($isLogin) && Cache::get($isLoginKey) == 0) {
            $this->show(-108, '非用户首次登录');
//        }
//        Cache::setex($isLoginKey,Yii::$app->params['expire'], 0);
//        $this->show(1, '用户首次登录');
    }
    
    /**
     * 获取我的追剧
     * @return void
     */
    public function actionGetfollow() {
        $page = $this->getParam('page', 1);
        $pagesize = $this->getParam('pagesize', 10);
        $order = $this->getParam('order', 'update_time');
        $FilmFollow = new FilmFollow();
        $result = $FilmFollow->getList($page, $pagesize, $order);
        if($result === false) {
            $this->showError($FilmFollow);
        }
        $this->showOk($result);
    }
    
}

