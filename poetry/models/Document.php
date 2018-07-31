<?php

/**
 * 诗词操作类
 * @date 2018/06/06
 * @author ztt
 */
namespace app\models;
use Yii;
use app\components\ActiveRecord;
use yii\db\Query;
use app\models\Poetry;
use app\models\Userdefined;

class Document extends ActiveRecord {
    
    /**
     * 获取诗词详情
     * @param int $id 诗词ID
     * @param int $docType 文档类型：1=诗词；2=自定义文档
     * @param int $isShare 是否是分享的内容：1=是；0=否
     * @return array 诗词详情
     */
    public function getInfo($id, $docType = 1, $isShare = 0) {
        if(!is_numeric($id) || $id < 1) {
            return $this->addError('', '300:参数格式有误');
        }
        if(!in_array($docType, Yii::$app->params['document']['doc_type'])) {
            return $this->addError('', '300:暂无该文档类型');
        }
        if($isShare != Yii::$app->params['document']['share_yes'] && $isShare != Yii::$app->params['document']['share_no']) {
            return $this->addError('', '300:参数格式有误');
        }
        $result = [];
        switch ($docType) {
            case Yii::$app->params['document']['doc_type_poetry']:
                $Poetry = new Poetry();
                $result = $Poetry->getInfo($id);
                if($result === false) {
                    $error = $Poetry->getCodeError();
                    return $this->addError('', $error['code'] . ':' . $error['msg']);
                }
                break;
            case Yii::$app->params['document']['doc_type_user_defined']:
                $Userdefined = new Userdefined();
                $result = $Userdefined->getInfo($id, $isShare);
                if($result === false) {
                    $error = $Userdefined->getCodeError();
                    return $this->addError('', $error['code'] . ':' . $error['msg']);
                }
                break;
            default:
                return $this->addError('', '300:暂无该文档类型');
        }
        return $result;
    }
    
    /**
     * 
     * @param type $id
     * @param type $docType
     */
    public function share($id = 0, $docType = 1) {
        $loginUserId = Cache::hget('user_id');
        if(!empty($id)) {
            if($docType == Yii::$app->params['document']['doc_type_user_defined']) { // 自定义文档
                $Userdefined = new Userdefined();
                $UserdefinedRecord = $Userdefined->findOne($id);
                if(!$UserdefinedRecord) {
                    return $this->addError('', '402:自定义文档不存在');
                }
                if($UserdefinedRecord->is_share != Yii::$app->params['document']['share_yes']) {
                    $UserdefinedRecord->is_share = Yii::$app->params['document']['share_yes'];
                    if(!$UserdefinedRecord->save()) {
                        return $this->addError('', '400:分享失败，请稍后重试');
                    }
                }
            }
        }
        
        $ocrMaxKey = 'ocr_times_' . $loginUserId . '_' . date('Ymd');
       
        Cache::set($ocrMaxKey, Yii::$app->params['document']['ocr_max']);
        $expire = strtotime(date('Y-m-d 00:00:00') . "+1 day") - time();
        
        Cache::setex($ocrMaxKey, $expire, Yii::$app->params['document']['ocr_max']);
        return true;
    }
}

