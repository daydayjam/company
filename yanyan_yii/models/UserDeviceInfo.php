<?php
/**
 * 用户设备模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;

class UserDeviceInfo extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%user_device_info}}';
    }
    
    /**
     * 更新用户设备信息
     * @param string $deviceId 设备ID
     * @param string $deviceName 设备名
     * @param string $deviceImei 设备IMEI
     * @param int $clientType 设备类型：1010=安卓；1001=IOS
     * @return boolen
     */
    public function updateDeviceInfo($deviceId, $deviceName, $deviceImei, $clientType = 1) {
        $loginUserId = Cache::hget('id') ? Cache::hget('id') : 0;
        if(empty($deviceId) || empty($deviceName)) {
            return $this->addError('', '-3:参数不可为空');
        }
        if(!in_array($clientType, Yii::$app->params['state_code']['client_type'])) {
            return $this->addError('', '-4:参数格式不正确');
        }
        $Record = $this->findByCondition(['device_id'=>$deviceId, 'client_type'=>$clientType, 'user_id'=>$loginUserId])->one();
        if($Record) {
            if($Record->user_id == 0 && $loginUserId) {
                $Record->user_id = $loginUserId;
                if(!$Record->save()) {
                    return $this->addError('', '-11:设备信息更新失败');
                }
                return true;
            }else {
                $Record->update_time = date('Y-m-d H:i:s');
                if(!$Record->save(true, null, 0)) {
                    return $this->addError('', '-11:设备信息更新失败');
                }
                return true;
            }
        }else {
            $this->device_id = $deviceId;
            $this->device_name = $deviceName;
            $this->device_imei = $deviceImei;
            $this->client_type = $clientType;
            $this->user_id = $loginUserId;
            if(!$this->save()) {
                return $this->addError('', '-11:设备信息新增失败');
            }
            return true;
        }
        
    }
    
    
    
    
    
}

