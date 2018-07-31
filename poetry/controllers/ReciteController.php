<?php
/**
 * 背诵控制器
 * @date 2018/06/26
 * @author ztt
 */

namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Recite;
use app\models\Network;
use app\models\Cache;
use app\models\Poetry;

class ReciteController extends Controller {
    
    /**
     * 添加背诵
     * @return void
     */
    public function actionAdd() {
        $poetryId = $this->getParam('poetry_id');
        $formId = $this->getParam('form_id');
        $status = $this->getParam('status', 0);
        $Recite = new Recite();
        $result = $Recite->add($poetryId, $formId, $status);
        if(!$result) {
            $this->showError($Recite);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取背诵列表
     * @return void
     */
    public function actionList() {
        $status = $this->getParam('status', 0);
        $page = $this->getParam('page', Yii::$app->params['page']);
        $pagesize = $this->getParam('pagesize', Yii::$app->params['pagesize']);
        $Recite = new Recite();
        $result = $Recite->getList($status, $page, $pagesize);
        if($result === false) {
            $this->showError($Recite);
        }
        $this->showOk($result);
    }
    
    /**
     * 发送
     */
    public function actionSend() {
        $formId = $this->getParam('form_id');
        $userOpenId = $this->getParam('open_id');
        $poetryId = $this->getParam('poetry_id');
        $recitetotal = $this->getParam('recite_total', 0);
        $isHaveAccessToken = Cache::get(Yii::$app->params['weixin']['ACCESS_TOKEN_KEY']);
        $accessToken = ''; // 默认access_token为空
        if($isHaveAccessToken) {
            $accessToken = $isHaveAccessToken;
        }else {
            $accessTokenParams = [
                'grant_type' => Yii::$app->params['weixin']['ACCESS_TOKEN_GRANT_TYPE'],
                'appid' => Yii::$app->params['weixin']['APP_ID'],
                'secret' => Yii::$app->params['weixin']['SECRET']
            ];
            $accessTokenResult = Network::makeRequest(Yii::$app->params['weixin']['ACCESS_TOKEN_URL'], $accessTokenParams, '', 'get', 'https');
            if($accessTokenResult['result'] == 1) {
                $accessTokenResult = json_decode($accessTokenResult['msg'], 'true');
                $accessToken = $accessTokenResult['access_token'];
                Cache::setex(Yii::$app->params['weixin']['ACCESS_TOKEN_KEY'], $accessTokenResult['expires_in'], $accessToken);
            }
        }
        if(empty($accessToken)) {
            return false;
        }
	$Poetry = new Poetry();
	$PoetryRecord = $Poetry->findOne($poetryId);
	$poetryName = $PoetryRecord ? $PoetryRecord->title : '';
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $accessToken;
        $params = [
            'touser' => $userOpenId,
            'template_id' => Yii::$app->params['weixin']['TEMPLATE_ID'],
            'form_id' => $formId,
            'page' => 'pages/backPoems/poemsDetail?poemsId='.$poetryId,
            'data' => [
                'keyword1' => [
                    'value' => $poetryName
                ],
                'keyword2' => [
                    'value' => '您的《' . $poetryName . '》还在背诵中，需要温习巩固哦'
                ]
            ]
        ];
        $params = json_encode($params);
        $result = Network::makeRequest($url, $params, '', 'post', 'https');
        print_r($result);
    }
    
}


