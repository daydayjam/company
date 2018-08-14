<?php
/**
 * 推送设置控制器类
 * @author ztt
 * @date 2017/11/20
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Setting;

class SettingController extends Controller {
    
    /**
     * 修改推送设置
     * @return void
     */
    public function actionUpdate() {
        $isDisturb = $this->getParam('is_disturb');
        $isShowmsg = $this->getParam('is_showmsg', 1);
        $isSound = $this->getParam('is_sound', 1);
        $isShock = $this->getParam('is_shock', 1);
        $Setting = new Setting();
        $result = $Setting->updateSetting($isDisturb, $isShowmsg, $isSound, $isShock);
        if(!$result) {
            $this->showError($Setting);
        }
        $this->showOk();
    }
    
    
}

