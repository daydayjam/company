<?php

/**
 * 背诵操作类
 * @date 2018/06/26
 * @author ztt
 */
namespace app\models;
use Yii;
use app\components\ActiveRecord;

class Recite extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%recite}}';
    }
    
    /**
     * 获取背诵诗词数
     * @param int $status 在背或已背；1=已背；0=在背
     * @return int
     */
    public function getTotal($status = 0, $userId = 0) {
        $loginUserId = $userId ? $userId : (Cache::hget('user_id') ? Cache::hget('user_id') : 0);
        return $this->find()->where(['user_id'=>$loginUserId, 'status'=>$status])->count();
    }
    
    /**
     * 添加背诵
     * @param int $poetryId  文章ID
     * @param int $status   操作类型：1=已背；0=在背；-1=未背诵
     * @return boolen true=成功
     */
    public function add($poetryId, $formId = '', $status = 0) {
        $loginUserId = Cache::hget('user_id') ? Cache::hget('user_id') : 0;
        if(!is_numeric($poetryId)) {
            return $this->addError('', '300:参数格式有误');
        }
        if(!in_array($status, Yii::$app->params['code']['recite_status'])) {
            return $this->addError('', '300:操作类型有误');
        }
        $Poetry = new Poetry();
        $PoetryRecord = $Poetry->findOne($poetryId);
        if(!$PoetryRecord) {
            return $this->addError('', '402:该诗词不存在');
        }
        $openId = Cache::hget('open_id');
        $poetryName = $PoetryRecord->title;
        $reciteTotal = ((new Recite())->getTotal() ? (new Recite())->getTotal() : 0) + 1;
        $Record = $this->findByCondition(['user_id'=>$loginUserId, 'poetry_id'=>$poetryId])->one();
        if(!$Record) {
            $this->user_id = $loginUserId;
            $this->poetry_id = $poetryId;
            $this->word_number = (new Poetry())->getWordNum($poetryId);
            $this->status = $status;
            if(!$this->save()) {
                return $this->addError('', '400:添加背诵失败，请稍后重试');
            }
            // todo 服务提醒
            if(!Cache::hget('TEMPLATE_TODAY_' . date('Ymd').$loginUserId)) {
                system("echo 'sh ../commands/shell/recite_notice.sh ".$formId." ".$openId." ".$poetryId." ".$poetryName." ".$reciteTotal."' | at 21:00 tomorrow");
//                system("echo 'sh ../commands/shell/recite_notice.sh ".$formId." ".$openId." ".$poetryId." ".$reciteTotal."' | at now + 1 minutes");
                Cache::hset('TEMPLATE_TODAY' . date('Ymd').$loginUserId, 1);
            }
$result = [];
$result['reciting_total'] = (new Recite())->getTotal(0) ? (new Recite())->getTotal(0) : 0;  //在背诗词数
        $result['recited_total'] = (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited']) ? (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited']) : 0; //已背诗词数

            return $result;
        }
        if($Record->status == Yii::$app->params['code']['recite_status']['recited']) {
            return $this->addError('', '401:您已添加到已背中，请勿重复操作');
        }
        if($status == $Record->status && $status == Yii::$app->params['code']['recite_status']['reciting']) {
            return $this->addError('', '401:您已添加到在背中，请勿重复操作');
        }
        if($status == $Record->status && $status == Yii::$app->params['code']['recite_status']['unrecite']) {
            return $this->addError('', '401:您已取消在背，请勿重复操作');
        }
        $Record->status = $status;
        if(!$Record->save()) {
            return $this->addError('', '400:添加背诵失败，请稍后重试');
        }    
        if($status == Yii::$app->params['code']['recite_status']['reciting']) {
            // todo 服务提醒
            if(!Cache::hget('TEMPLATE_TODAY_' . date('Ymd').$loginUserId)) {
                system("echo 'sh ../commands/shell/recite_notice.sh ".$formId." ".$openId." ".$poetryId." ".$poetryName." ".$reciteTotal."' | at 21:00 tomorrow");
//                system("echo 'sh ../commands/shell/recite_notice.sh ".$formId." ".$openId." ".$poetryId." ".$reciteTotal."' | at now + 1 minutes");
                Cache::hset('TEMPLATE_TODAY' . date('Ymd').$loginUserId, 1);
            }
            $result = [];
$result['reciting_total'] = (new Recite())->getTotal() ? (new Recite())->getTotal() : 0;  //在背诗词数
        $result['recited_total'] = (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited']) ? (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited']) : 0; //已背诗词数

            return $result;

        }else if($status == Yii::$app->params['code']['recite_status']['recited']) {
            $result = [];
$result['reciting_total'] = (new Recite())->getTotal() ? (new Recite())->getTotal() : 0;  //在背诗词数
        $result['recited_total'] = (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited']) ? (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited']) : 0; //已背诗词数

            return $result;

        }
        $result = [];
$result['reciting_total'] = (new Recite())->getTotal() ? (new Recite())->getTotal() : 0;  //在背诗词数
        $result['recited_total'] = (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited']) ? (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited']) : 0; //已背诗词数

            return $result;

    }
    
    /**
     * 获取背诵列表
     * @param int $status 在背=0；已背=1，默认在背=0
     * @param int $page 当前页码，默认1=第一页
     * @param int $pagesize 每页显示记录条数，默认10
     * @return array
     */
    public function getList($status = 0, $page = 1, $pagesize = 10) {
        $loginUserId = Cache::hget('user_id');
        if(!in_array($status, Yii::$app->params['code']['recite_status'])) {
            return $this->addError('', '300:操作类型有误');
        }
        if(!is_numeric($page) || !is_numeric($pagesize) || $page < 1 || $pagesize < 1) {
            return $this->addError('', '300:参数格式有误，请重试');
        }
        $select = 'poetry_id';
        $params = ['status'=>$status, 'user_id'=>$loginUserId];
        $order = 'update_time desc, create_time desc';
        $result = $this->getListData($select, $page, $pagesize, $params, $order);
        if($result['total'] == 0) {
            return $result;
        }
        // 整合诗词
        Helper::mergePoetryInfoToList($result['rows']);
        return $result;
    }
}

