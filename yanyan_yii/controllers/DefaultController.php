<?php
/**
 * 验证控制器
 * @author ztt
 * @date 2017/10/30
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Crash;
use app\models\UserDeviceInfo;

class DefaultController extends Controller {
    
    /**
     * 发送短信验证码
     * @return void
     */
    public function actionException() {
        $source = $this->getParam('source', 'ios');
        $ex = $this->getParam('ex');
        $ex = str_replace("'","\"",$ex);
        $Crash = new Crash();
        $result = $Crash->add($source, $ex);
        if(!$result) {
            $this->showError($Crash);
        }
        $this->showOk();
    }
    
    /**
     * 获取崩溃信息
     * @return void
     */
    public function actionGetcrash() {
        $Crash = new Crash();
        $result = $Crash->getList(1, 10);
        if($result === false) {
            $this->showError($Crash);
        }
        $this->showOk($result);
    }
    
    /**
     * 更新用户设备信息
     * @return void
     */
    public function actionUpdatedevice() {
        $deviceId = $this->getParam('device_id');
        $deviceName = $this->getParam('device_name');
        $deviceImei = $this->getParam('device_imei');
        $clientType = $this->getParam('client_type', 1010);
        $UserDeviceInfo = new UserDeviceInfo();
        if(!$UserDeviceInfo->updateDeviceInfo($deviceId, $deviceName, $deviceImei, $clientType)) {
            $this->showError($UserDeviceInfo);
        }
        $this->showOk();
    }
    
    /**
     * 安卓版本更新
     * @return void
     */
    public function actionUpgrade() {
        $oldVersion = $this->getParam('old_version_number');
        $currentVersion = Yii::$app->params['version']['ANDROID']['CURRENT_VERSION'];
        if($oldVersion < $currentVersion) {
            $this->showOk(['url'=>Yii::$app->params['version']['ANDROID']['UPGRADE_URL'], 'is_force'=>1]);
        }else {
            $this->showOk();
        }
    }
    
    
}

