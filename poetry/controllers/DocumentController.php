<?php
/**
 * 文档控制器
 * @date 2018/06/07
 * @author ztt
 */

namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Document;
use app\models\Cache;
use app\models\Attachment;

class DocumentController extends Controller {
    
    /**
     * 获取文档详情
     * @return void
     */
    public function actionInfo() {
        $id = $this->getParam('id');
        $docType = $this->getParam('doc_type', 1);
        $isShare = $this->getParam('is_share', 0);
        $Document = new Document();
        $result = $Document->getInfo($id, $docType, $isShare);
        if(!$result) {
            $this->showError($Document);
        }
        $this->showOk($result);
    }
    
    /**
     * 分享增加扫描次数
     * @return void
     */
    public function actionShare() {
        $id = $this->getParam('id', 0);
        $docType = $this->getParam('doc_type', 1);
        $Document = new Document();
        $result = $Document->share($id, $docType);
        if(!$result) {
            $this->showError($Document);
        }
        $this->showOk();
    }
    
    /**
     * 图片文字扫描
     * @return void
     */
    public function actionOcr() {
        $loginUserId = Cache::hget('user_id') ? Cache::hget('user_id') : 0;
        // 判断今日已扫描次数是否超限
        $ocrMaxKey = 'ocr_times_' . $loginUserId . '_' . date('Ymd');
        $ocrTimes = Cache::get($ocrMaxKey);
        if($ocrTimes >= Yii::$app->params['document']['ocr_max']) {
            $this->show(302, '每日扫描最多3次，分享可额外获得3次扫描次数');
        }
        $inputName = $this->getParam('name');
        $inputName = 'file';
        $Attachment = new Attachment();
        $uploadImage = $Attachment->uploadImg($inputName);
        if(!$uploadImage) {
            $error = $Attachment->getCodeError();
            $this->show($error['code'], $error['msg']);
        }
        
        require_once EXT_DIR . '/baidu/AipOcr.php';

        // 你的 APPID AK SK
        $appId = Yii::$app->params['baidu']['API_ID'];
        $apiKey = Yii::$app->params['baidu']['API_KEY'];
        $secretKey = Yii::$app->params['baidu']['SECRET_KEY'];
        
        $image = file_get_contents($uploadImage);
        
        $client = new \AipOcr($appId, $apiKey, $secretKey);
        
        $ocrResult = $client->basicAccurate($image);
        $resultArr = [];
        if($ocrResult['words_result_num'] > 0) {
            foreach($ocrResult['words_result'] as $value) {
                $resultArr[] = $value['words'];
            }
        }
        $result = implode('\n', $resultArr);
        // 增加缓存中的扫描次数
        Cache::set($ocrMaxKey, $ocrTimes + 1);
        $this->showOkN($result);
    }
}


