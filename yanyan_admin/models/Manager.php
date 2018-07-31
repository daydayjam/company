<?php
/**
 * 用户模型类
 * @author ztt
 * @date 2017/01/16
 */
namespace app\models;

use Yii;
use app\models\UserHelper;
use yii\db\Query;
use app\models\Tool;
use app\models\Setting;
use app\models\Easemob;
use app\models\CmdHelper;
use app\models\Cache;

class Manager extends UserHelper {
    
    public static $gender = [1,2,0];
    public static $systemAvatar = [1,2,3,4,5,6,7,8];
    public static $status = [1,-1,-2,-3];
    public static $thirdPlat = ['qq','sina','wechat'];
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%manager}}';
    }
    
    /**
     * 获取信息
     * @param int $uid
     * @return array
     */
    public function getInfo($uid) {
        if(!is_numeric($uid)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $Query = new Query();
        $result = $Query->select('id,uname,nickname,email,reg_dt,last_dt')
                        ->from($this->tableName())
                        ->where(['id'=>$uid])
                        ->one();
        return $result;
    }
    
    /**
     * 管理员登录
     */
    public function signin($uname, $pwd) {
        if(empty($uname) || empty($pwd)) {
            $this->addError('', '-3:用户名或密码不能为空');
            return false;
        }
        $pwd = Tool::md5Double($pwd);
        $Record = $this->findByCondition(['uname'=>$uname, 'password'=>$pwd])->one();
        if(!$Record) {
            $this->addError('', '-5:用户名或密码错误');
            return false;
        }
        $time= time();
        $verifyCode = md5($uname.'_'.$pwd.'_'.$time.'_'.Yii::$app->params['unique_token']);
        $Record->sn = $verifyCode;
        if(!$Record->save()) {
            $this->addError('', '0:登录失败');
            return false;
        }
        setcookie('code', $verifyCode, time()+7*24*3600,'/');
//        Cache::hset('id', $Record->id, $verifyCode);
        return $Record->id;
    }
    
    
}

