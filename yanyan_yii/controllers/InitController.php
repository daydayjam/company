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

class InitController extends Controller {
    
    /**
     * 安卓版本更新
     * @return void
     */
    public function actionUpgrade() {
        $oldVersion = $this->getParam('version_number');
        $currentVersion = Yii::$app->params['version']['android']['no'];
        if($oldVersion < $currentVersion) {
            $this->showOkN(Yii::$app->params['version']['android']);
        }else {
            $this->show(-12, '无需更新');
        }
    }
    
    
}

