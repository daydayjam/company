<?php
/**
 * 反馈控制器类
 * @author ztt
 * @date 2017/11/30
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Feedback;
use app\models\Cache;

class FeedbackController extends Controller {
    
    /**
     * 添加反馈信息
     * @return void
     */
    public function actionAdd() {
        $uid = Cache::hget('id');
        $type = $this->getParam('type', 1);
        $content = $this->getParam('content');
        $contact = $this->getParam('contact');
        $Feedback = new Feedback();
        $result = $Feedback->add($uid, $type, $content, $contact);
        if(!$result) {
            $this->showError($Feedback);
        }
        $this->showOk();
    }
    
    
}

