<?php

/**
 * 微信操作类
 * @date 2018/06/05
 * @author ztt
 */
namespace app\models;
use Yii;
use app\components\ActiveRecord;
use app\models\Weixin;

class User extends ActiveRecord {
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%user}}';
    }
    
    /**
     * 微信用户登录
     * 通过code获取session_key 和 openid等
     * @param string $code 临时登录凭证code
     */
    public function login($code, $nickname = '') {
        if(empty($code)) {
            return $this->addError('', '101:微信登录临时凭证不可为空');
        }
        $Weixin = new Weixin();
        $result = $Weixin->codeToInfo($code);
//        return $result;
        if(!$result) {
            return $this->addError('', '102:微信登录临时凭证无效');
        }
        // 通过openid判断用户是否已经入库，
        // 这个微信账号登录态生成一个session id并维护在我们自己的session机制中，然后把这个session id派发到小程序客户端作为session标识来使用。
        // 否则，先入库，在走上述步骤
        $openId = $result['openid'];
        $sessionKey = $result['session_key'];
        $userId = 0;
        try {
            $Record = $this->findByCondition(['openid'=>$openId])->one();
        }catch(\Exception $e) {
            return $this->addError('', '500:' . $e->getMessage());
        }
        if(!$Record) {
            $this->nickname = $nickname;
            $this->openid = $openId;
            $this->token = '';
            $this->last_login_time = time();
            $this->last_login_ip = ip2long(Yii::$app->request->userIP);
            try {
                if(!$this->save()) {
                    return $this->addError('', '103:用户登录失败');
                }
            }catch(\Exception $e) {
                return $this->addError('', '500:' . $e->getMessage());
            }
            $userId = $this->id;
        }else {
            if($Record->nickname !== $nickname) {
                $Record->nickname = $nickname;
                $Record->save();
            }
            $userId = $Record->id;
        }
        // 生成token
        $token = Tool::md5Sign($openId, Yii::$app->params['weixin']['PRIVATE_KEY']);
        $tokenValue = $openId . $sessionKey;
        // 存入redis
        Cache::hset('user_id', $userId, $token);
        Cache::hset('ocr_times', 0, $token);
        Cache::hset('token_value', $tokenValue, $token);
        Cache::hset('open_id', $openId, $token);
        Cache::expire($token, Yii::$app->params['weixin']['EXPIRE_TIME']);
        $SignLogRecord = SignLog::find()->where(['user_id'=>$userId])->orderBy('create_time desc')->limit(1)->one();
        $isSign = $SignLogRecord && Tool::isToday($SignLogRecord->create_time) ? 1 : 0;    // 今日是否已经签到
        $signDay = $SignLogRecord ? $SignLogRecord->sign_day : 0;
        
        $Collection = new Collection();
        $collectTotal = $Collection->getTotal($userId) ? $Collection->getTotal($userId) : 0;     // 收藏是次数
        $recitingTotal = (new Recite())->getTotal(0, $userId) ? (new Recite())->getTotal(0, $userId) : 0;  //在背诗词数
        $recitedTotal = (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited'], $userId) ? (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited'], $userId) : 0; //已背诗词数
        
        return ['token'=>$token, 'is_sign'=>$isSign, 'sign_day'=>$signDay, 'collect_total'=>$collectTotal, 'reciting_total'=>$recitingTotal, 'recited_total'=>$recitedTotal];
    }
}

