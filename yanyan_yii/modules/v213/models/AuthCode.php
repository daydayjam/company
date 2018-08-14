<?php
/**
 * 验证类
 * @author ztt
 * @date 2017/10/30
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\Cache;
use app\models\Rest;

class AuthCode extends ActiveRecord {
    //业务类型：用户注册,用户绑定手机号,用户找回密码
    public static $actionType = ['user_register', 'user_bindtel', 'user_findpwd'];
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%authcode}}';
    }
    
    /**
     * 验证规则
     */
    public function rules() {
        return [
            [['mobile', 'code'], 'required'],
            ['mobile', 'match', 'pattern' => '/^1[3|4|5|7|8]\d{9}$/', 'message' => 'telInvalid'],
            ['code', 'match', 'pattern' => '/^\d{4}$/', 'message' => 'codeInvalid']
            
        ];
    }
    
    /**
     * 发送验证码
     * @param string $moile 手机号
     * @param string $type 业务类型
     * @return boolen true=发送成功
     */
    public function send($mobile, $type = 'user_register') {
        if(!in_array($type, self::$actionType)) {
            $this->addError('type', '-4:业务类型有误');
            return false;
        }
        if(!Tool::isMobile($mobile)) {
            $this->addError('mobile', '-4:手机格式不正确');
            return false;
        }
        //限制同一ip访问次数
        if(Safety::ipTimes($mobile . '_' . $type) > 100000) {
            $this->addError('', '-6:访问次数受限，请稍后再试');
            return false;
        }
        //60秒内只能请求一次
        $Record = $this->findOne(['mobile' => $mobile]);
        if($Record) {
            $currentTime = date('Y-m-d H:i:s');
            if(Tool::getTimeDiff($currentTime, $Record->send_time) < 60) {
                $this->addError('', '-8:请求时间过于接近，请稍后再试');
                return false;
            }
        }
        $code = $this->getOldCode($mobile, $type);
        if($code === '') {
            $code = $this->createCode($mobile, $type);
        }
        //调用发送验证码的API
	$datas = [$code, 30];
	$tempId = 148301;//短信模板
        if(!$this->sendTemplateSMS($mobile, $datas, $tempId)) {
            $this->addError('', '0:发送失败');
            return false;
        }
        $sql = 'insert into ' . $this->tableName() . '(mobile, code, send_time) values(:mobile, :code, now()) on duplicate key update code=:code1,send_time=now(),cstatus=0';
        $params = [':mobile' => $mobile, ':code' =>$code, ':code1' =>$code];
        $result = Yii::$app->db->createCommand($sql, $params)->execute();
        if(!$result) {
            $this->addError('', '0:发送失败');
            return false;
        }
        return true;
    }
    
    /**
     * 验证验证码是否正确
     * @param string $mobile 手机号
     * @param string $code 验证码
     * @param string $type 
     * @param boolen $strict 是否区分大小写，true=区分
     * @return boolen true=正确
     */
    public function validateCode($mobile, $code, $type, $strict = false) {
        $authCode = $this->getOldCode($mobile, $type);
        if($strict) {
            $code = strtolower($code);
            $authCode = strtolower($authCode);
        }
        $this->writeLog('验证码：'.$authCode);
        if($authCode != '' && $code == $authCode) {
            $cacheKey = $this->getCodeKey($mobile, $type);
            Cache::del($cacheKey);
            return true;
        }
        return false;
    }
    
    /**
     * 发送模板短信
     * @param to 手机号码集合,用英文逗号分开
     * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
     * @param $tempId 模板Id
     * @return boolen true=成功
     */
    public function sendTemplateSMS($to, $datas, $tempId) {
        // 初始化REST SDK
        $sms = require Yii::$app->basePath . '/config/sms.php';
        REST::getInstance()->setAccount($sms['account_sid'], $sms['account_token']);
        REST::getInstance()->setAppId($sms['app_id']);

        // 发送模板短信
        $result = REST::getInstance()->sendTemplateSMS($to, $datas, $tempId);
        if($result != null && $result->statusCode == 0){
            return true;
        }
        return false;
    }
    
    /**
     * 创建验证码
     * @param string $mobile 手机号码
     * @param string $actionType 业务类型
     * @return string 短信验证码
     */
    public function createCode($mobile, $actionType) {
        $cacheKey = $this->getCodeKey($mobile, $actionType);
        $code = Tool::getRandom();
        Cache::setex($cacheKey, 1800, $code);
        return $code;
    }
    
    /**
     * 获取旧的验证码
     * @param string $mobile 手机号码
     * @param string $actionType 业务类型
     * @return string 旧的短信验证码，没有时返回空字符串
     */
    public function getOldCode($mobile, $actionType) {
        $cacheKey = $this->getCodeKey($mobile, $actionType);
        $code = Cache::get($cacheKey);
        return $code == false ? '' : $code;
    }
    
    /**
     * 获取验证码键名
     * @param string $mobile 手机号码
     * @param string $actionType 业务类型
     * @return string 键名
     */
    public function getCodeKey($mobile, $actionType) {
        return __CLASS__ . '_auth_code_' . $mobile . '_' . $actionType;
    }
    
    
    
    
}

