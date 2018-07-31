<?php
/**
 * 用户控制器
 * @date 2018/0605
 * @author ztt
 */

namespace app\controllers;

use app\components\Controller;
use app\models\SignLog;
use app\models\User;
use app\models\Cache;

class UserController extends Controller {
    
    /**
     * 用户登录
     * @return void
     */
    public function actionLogin() {
        $code = $this->getParam('code');
        $nickname = $this->getParam('nickname');
        // 通过code向微信服务器置换 session_key 和 openid 等
        $User = new User();
        $result = $User->login($code, $nickname);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk($result);
    }
    
    /**
     * 打卡签到
     * @return void
     */
    public function actionSign() {
        $SignLog = new SignLog();
        $result = $SignLog->add();
        if(!$result) {
            $error = $SignLog->getCodeError();
            if($error['code'] == 401) {
                $Record = $SignLog->find()->where(['user_id'=>Cache::hget('user_id')])->orderBy('create_time desc')->limit(1)->one();
                $this->showError($SignLog, $Record->sign_day);
            }
            $this->showError($SignLog);
        }
        $this->showOk($result);
    }
}


