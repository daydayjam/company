<?php
/**
 * 验证控制器
 * @author ztt
 * @date 2017/10/30
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\AuthCode;

class AuthcodeController extends Controller {
    
    /**
     * 发送短信验证码
     * @return void
     */
    public function actionGet() {
        $mobile = $this->getParam('mobile');
        $type = $this->getParam('type', 'user_register');
        $AuthCode = new AuthCode();
        $result = $AuthCode->send($mobile, $type);
        if(!$result) {
            $this->showError($AuthCode);
        }
        $this->showOk();
    }
    
    
}

