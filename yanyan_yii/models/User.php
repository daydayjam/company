<?php
/**
 * 用户模型类
 * @author ztt
 * @date 2017/10/26
 */
namespace app\models;

use Yii;
use app\models\Safety;
use app\models\Tool;
use app\models\Easemob;
use app\models\Black;
use app\models\Lock;
use app\models\UserHelper;
use app\models\AuthCode;
use app\models\Attachment;
use app\components\UserIdentity;

class User extends UserHelper {
    
    public static $systemAvatar = [1,2,3,4,5,6,7,8];
    public static $thirdPlat = ['qq','sina','wechat'];
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%user}}';
    }

    /**
     * 用户注册
     * @param string $tel 用户手机号，11位手机号
     * @param string $pwd 用户密码， 至少8位字符，非中文
     * @param string $nick 用户昵称
     * @param int $gender 用户性别，1=男，2=女
     * @param string $birthDate 用户出生年月 格式0000-00-00
     */
    public function register($tel, $pwd, $nick, $avatar, $gender = 0, $birthDate = '0000-00-00', $signature = '神秘的小言') {
        if(empty($avatar)) {
            $this->addError('', '-3:头像不可为空');
            return false;
        }
        if(strlen($avatar)==1 && !in_array($avatar, self::$systemAvatar)) {
            $this->addError('avatar', '-4:头像格式不正确');
            return false;
        }
        if(!Tool::isMobile($tel)) {
            $this->addError('', '-4:手机号格式不正确');
            return false;
        }
        if(!Tool::isPwd($pwd)) {
            $this->addError('', '-4:密码格式不正确');
            return false;
        }
        if(empty($nick)) {
            $this->addError('', '-3:昵称不可为空');
            return false;
        }
        if(!in_array($gender, [0, 1, 2])) {
            $this->addError('', '-4:性别格式不正确');
            return false;
        }
        if(!Tool::isDate($birthDate)) {
            $this->addError('', '-4:出生年月日格式不正确');
            return false;
        }
        if(Safety::ipTimes('register_' . $tel) > 20) {
            $this->addError('', '-6:访问次数受限，请稍后再试');
            return false;
        }
//        $key = Yii::$app->session->get('key');
//        if(!isset($key) || (isset($key) && $key != Tool::md5Double(Tool::getUniqueValue($tel, 'user_register')))) {
//            $this->addError('', '-2:您的操作有误，请重试');
//            return false;
//        }
        //判断手机号是否注册过
        $UserRecord = $this->findByCondition(['tel'=>$tel])->one();
        if($UserRecord) {
            if($UserRecord->status == -1) {
                $this->addError('', '-103:该账号已被停封，无法再次注册');
                return false;
            }
            $this->addError('', '-101:该手机号已被注册');
            return false;
        }
        $pwd = Tool::md5Double($pwd);
        //启动事务
        $this->beginTransaction();
        $this->tel = $tel;
        $this->pwd = $pwd;
        $this->nickname = $nick;
        $this->gender = $gender;
        $this->signature = $signature;
        $this->birth_date = $birthDate;
        if(!$this->save()) {
            $this->rollback();//回滚事务
            $this->addError('', '0:注册失败1');
            return false;
        }
        //修改serial_num字段,言言ID
        $serialNum = $this->getSerialNum($this->id);
        $this->serial_num = $serialNum;
        if(!$this->save(false)) {
            $this->rollback();
            $this->addError('', '0:注册失败2');
            return false;
        }
        //上传用户头像
        $avatarUrl = '/system/avatar_default_'.$avatar.'.png';
        if(strlen($avatar) > 1) {
            $Attachment = new Attachment();
            $result = $Attachment->uploadBase64Img($avatar, 'avatar');
            if(!$result) {
                $this->rollback();
                $error = $Attachment->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
            $avatarUrl = $result['path'];
        }
        $this->avatar = $avatarUrl;
        if(!$this->save()) {
            $this->rollback();
            $this->addError('', '0:注册失败3');
            return false;
        }
        //添加用户设置
        $Setting = new Setting();
        if(!$Setting->add($this->id)) {
            $this->rollback();
            $this->addError('', '0:注册失败4');
            return false;
        }
        //注册环信用户
        $easeUname = $this->getEaseUname($this->id);
        $response = Easemob::getInstance()->createUser($easeUname, $this->pwd);
//        print_r($response);die;
        if($response['code'] != '200') {
            $this->rollback();
            $this->addError('', '0:注册失败5');
            return false;
        }
        $this->ease_uid = $easeUname;
        if(!$this->save()) {
            $this->rollback();
            $this->addError('', '0:注册失败6');
            return false;
        }
        //获取用户信息
        $data = $this->getLoginData($this->id);
        $Identity = new UserIdentity($tel, $pwd);
        $data['uinfo'] = $Identity->authenticate();
//        Yii::$app->session->remove('key');
        $this->commit();
        return $data; 
    }
    
    /**
     * 用户登录
     * @param string $tel 用户手机号
     * @param string $pwd 用户密码
     * @return mixed array 登录的用户信息，false=登陆失败
     */
    public function login($tel, $pwd) {
        //限制同意ip登录次数
        if(Safety::ipTimes('login_' . $tel) > 100000) {
            $this->addError('', '-6:访问次数受限，请稍后再试');
            return false;
        }
        if(!Tool::isMobile($tel)) {
            $this->addError('', '-4:手机号格式不正确');
            return false;
        }
        if(!Tool::isPwd($pwd)) {
            $this->addError('', '-4:密码格式不正确');
            return false;
        }
        $pwd = Tool::md5Double($pwd);
        $Identity = new UserIdentity($tel, $pwd, 1);
        $uinfo = $Identity->authenticate();
        if(!$uinfo) {
            $this->addError('', $Identity->errorCode.':'.$Identity->errorMsg);
            return false;
        }
        $data = $this->getLoginData($uinfo['id']);
        $data['uinfo'] = $uinfo;
        $isLoginKey = date('Ymd') . $uinfo['id'];
        $isLogin = Cache::get($isLoginKey);
        if(!isset($isLogin)) {
            Cache::setex($isLoginKey,Yii::$app->params['expire'], 1);
        } else {
            Cache::setex($isLoginKey,Yii::$app->params['expire'], 0);
        }
        Safety::ipClear('login_' . $tel);
        return $data;
    }
    
    /**
     * 检查验证码是否正确以及手机号是否注册过
     * @param string $tel 手机号
     * @param string $vcode 验证码
     * @param string $type 业务逻辑
     * @return boolen true=成功
     */
    public function regCheck($tel, $vcode, $type) {
        if(!Tool::isMobile($tel)) {
            $this->addError('', '-4:手机号格式不正确');
            return false;
        }
        if(!Tool::isCode($vcode)) {
            $this->addError('', '-4:验证码格式不正确');
            return false;
        }
        if(!in_array($type, AuthCode::$actionType)) {
            $this->addError('type', '-4:参数格式不正确');
            return false;
        }
        //检测手机号是否已被使用
        $errMsg = '';
        $isTelUsed = $this->isUsed('tel', $tel);
        if($isTelUsed && in_array($type, ['user_register','user_bindtel'])) {
            $errMsg = '-101:该手机号已被注册';
        }else if(!$isTelUsed && in_array($type, ['user_findpwd'])) {
            $errMsg = '-102:该手机号未被注册';
        }
        if($errMsg) {
            $this->addError('', $errMsg);
            return false;
        }
        
        //检测验证码是否正确
        $AuthCode = new AuthCode();
        $codeResult = $AuthCode->validateCode($tel, $vcode, $type);
        if(!$codeResult) {
            $this->addError('', '-502:验证码不正确');
            return false;
        }
        return true;
    }
    
    /**
     * 第三方注册
     * @param string $plat 第三方名称，qq,sina,wechat
     * @param string $uuid 第三方账号串码
     * @param string $nick 昵称
     * @param string $avatar base64编码图片
     * @param int $gender 性别，0=保密；1=男；2=女
     * @param string $birth 出生年月日，格式为0000-00-00
     * @return array|boolen 成功返回用户信息，失败返回false
     */
    public function thirdRegister($plat, $uuid, $nick, $avatar, $gender = 0, $birth = '0000-00-00', $signature='神秘的小言') {
        if(empty($plat) || empty($uuid)) {
            $this->addError('', '-3:第三方账号不可为空');
            return false;
        }
        if(empty($nick)) {
            $this->addError('', '-3:昵称不可为空');
            return false;
        }
        if(empty($avatar)) {
            $this->addError('', '-3:头像不可为空');
            return false;
        }
        if(strlen($avatar)==1 && !in_array($avatar, self::$systemAvatar)) {
            $this->addError('', '-4:头像格式不正确');
            return false;
        }
        if(!in_array($gender, [0,1,2])) {
            $this->addError('', '-4:性别格式不正确');
            return false;
        }
        if($birth != '0000-00-00' && !Tool::isDate($birth)) {
            $this->addError('', '-4:出生年月日格式不正确');
            return false;
        }
        $lockKey = 'user_thirdRegister_'.$plat.'_'.$uuid;
//        if(Safety::ipTimes($lockKey) > 20) {
//            $this->addError('', '-6:访问次数受限，请稍后再试');
//            return false;
//        }
        if(!Lock::addlock($lockKey)) {
            $this->addError('', '-100:账号临时锁定，请稍后重试');
            return false;
        }
        $this->beginTransaction();
        //判断第三方账号是否被绑定过
        $platName = $plat.'_id';
        $Record = $this->findByCondition([$platName=>$uuid])->one();
        if($Record) {
            Lock::unLock($lockKey);
            $this->rollback();
            if($Record->status == -1) {
                $this->addError('', '-103:该账号已被停封，无法再次注册');
                return false;
            }
            $this->addError('', '-105:第三方账号已经被绑定');
            return false;
        }
        $this->nickname = $nick;
        $this->gender = $gender;
        $this->signature = $signature;
        $this->birth_date = $birth;
        $this->$platName = $uuid;
        if(!$this->save()) {
            Lock::unLock($lockKey);
            $this->rollback();
            $this->addError('', '0：注册失败');
            return false;
        }
        $pwd = Tool::md5Double('pwd_'.$this->id);
        $this->pwd = $pwd;
        if(!$this->save()) {
            Lock::unLock($lockKey);
            $this->rollback();
            $this->addError('', '0：注册失败');
            return false;
        }
        //修改serial_num字段,言言ID
        $serialNum = $this->getSerialNum($this->id);
        $this->serial_num = $serialNum;
        if(!$this->save(false)) {
            Lock::unLock($lockKey);
            $this->rollback();
            $this->addError('', '0:注册失败');
            return false;
        }
        //上传用户头像
        $avatarUrl = '/system/avatar_default_'.$avatar.'.png';
        if(strlen($avatar) > 1) {
            $Attachment = new Attachment();
            if(Tool::startWith($avatar, 'http')) {
                $avatarUrl = $Attachment->saveUrlImg($avatar, 'avatar');
            } else {
                $result = $Attachment->uploadBase64Img($avatar, 'avatar');
                if(!$result) {
                    Lock::unLock($lockKey);
                    $this->rollback();
                    $error = $Attachment->getCodeError();
                    $this->addError('', $error['code'] . ':' . $error['msg']);
                    return false;
                }
                $avatarUrl = $result['path'];
            }
        }
        $this->avatar = $avatarUrl;
        if(!$this->save()) {
            Lock::unLock($lockKey);
            $this->rollback();
            $this->addError('', '0:注册失败');
            return false;
        }
        //添加用户设置
        $Setting = new Setting();
        if(!$Setting->add($this->id)) {
            Lock::unLock($lockKey);
            $this->rollback();
            $this->addError('', '0:注册失败');
            return false;
        }
        //注册环信用户
        $easeUname = $this->getEaseUname($this->id);
        $response = Easemob::getInstance()->createUser($easeUname, $this->pwd);
        if($response['code'] != '200') {
            Lock::unLock($lockKey);
            $this->rollback();
            $this->addError('', '0:注册失败');
            return false;
        }
        $this->ease_uid = $easeUname;
        if(!$this->save()) {
            Lock::unLock($lockKey);
            $this->rollback();
            $this->addError('', '0:注册失败');
            return false;
        }
        //获取用户信息
        $data = $this->getLoginData($this->id);
        $Identity = new UserIdentity($platName, $uuid, 2);
        $data['uinfo'] = $Identity->authenticate();
        Lock::unLock($lockKey);
        $this->commit();
        return $data;
    }
    
    /**
     * 第三方登录
     * @param string $plat 第三方平台名称
     * @param string $uuid 第三方账号ID
     * @return array|boolen 登录成功返回用户信息，登录失败返回false
     */
    public function thirdLogin($plat, $uuid) {
        if(empty($plat) || empty($uuid)) {
            $this->addError('', '-3:第三方账号不可为空');
            return false;
        }
        if(!in_array($plat, self::$thirdPlat)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        //限制同意ip登录次数
//        if(Safety::ipTimes('login_' . $plat . '_' .$uuid) > 20) {
//            $this->addError('', '-6:访问次数受限，请稍后再试');
//            return false;
//        }
        $platFieldName = $plat.'_id';
        $Identity = new UserIdentity($platFieldName, $uuid, 2);
        $uinfo = $Identity->authenticate();
        if(!$uinfo) {
            if($Identity->errorCode == -7) {
                $data = ['is_register'=>0];
                return $data;
            }
            $this->addError('', $Identity->errorMsg);
            return false;
        }
        Safety::ipClear('login_' . $plat . '_' .$uuid);
        $data = $this->getLoginData($uinfo['id']);
        $data['uinfo'] = $uinfo;
        $data['is_register'] = 1;
        $isLoginKey = date('Ymd') . $uinfo['id'];
        $isLogin = Cache::get($isLoginKey);
        if(!isset($isLogin)) {
            Cache::setex($isLoginKey,Yii::$app->params['expire'], 1);
        } else {
            Cache::setex($isLoginKey,Yii::$app->params['expire'], 0);
        }
        return $data;  
    }
    
    
    /**
     * 获取用户详情
     * @param int $uid 用户ID
     * @return array|false 用户详情
     */
    public function getInfo($uid) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($uid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Record = $this->findOne($uid);
        if(!$Record) {
            $this->addError('', '-7:该用户不存在');
            return false;
        }
        if($Record->status == -1) {
            $this->addError('', '-103:该用户已因涉嫌违规已被停封处理');
            return false;
        }
        $Comment = new Comment();
        $cmtList = $Comment->getListByUid($uid);
        
        if(!empty($cmtList)) {
            $CommentHelper = new CommentHelper();
            $CommentHelper->addListIsPraise($cmtList, $loginUid);
            $CommentHelper->addListDomain($cmtList, 'pics');
            $CommentHelper->mergeToListFilm($cmtList);
            $CommentHelper->mergeToListNews($cmtList);
//            $CommentHelper->addListEpTitle($cmtList);
        }
        $userInfo['id'] = $Record->id;
        $userInfo['ease_uid'] = $Record->ease_uid;
        $userInfo['tel'] = $Record->tel;
        $userInfo['serial_num'] = $Record->serial_num;
        $userInfo['avatar'] = Tool::connectPath(Yii::$app->params['image_domain'], $Record->avatar);
        $userInfo['nickname'] = $Record->nickname;
        $userInfo['gender'] = $Record->gender;
        $userInfo['birth_date'] = $Record->birth_date;
        $userInfo['emotion'] = $Record->emotion;
        $userInfo['hometown'] = $Record->hometown;
        $userInfo['intrest'] = $Record->intrest;
        $userInfo['signature'] = $Record->signature;
        $userInfo['status'] = $Record->status;
        $userInfo['img_bg'] = Tool::connectPath(Yii::$app->params['image_domain'], $Record->img_bg);
        $Black = new Black();
        $userInfo['is_black'] = $Black->isInBlack($uid, Yii::$app->user->id);
//        $FollowFilm = new FollowFilm();
//        $followfilmList = $FollowFilm->getFollowFilmList($uid, 0, 10);
        $result = ['uinfo'=>$userInfo, 'comment_list'=>$cmtList, 'page'=>1, 'pagesize'=>10];
        return $result;
    }
    
    
    
    /**
     * 修改用户头像
     * @param string $avatar base64编码图片字符串
     * @return boolen true=修改成功
     */
    public function updateAvatar($avatar) {
        $loginUid = Cache::hget('id');
//        if(!Tool::isBase64Img($avatar)) {
//            $this->addError('avatar', '-4:头像格式不正确');
//            return false;
//        }
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        $Attachment = new Attachment();
        $result = $Attachment->uploadBase64Img($avatar, 'avatar');
        if(!$result) {
            $error = $Attachment->getCodeError();
            $this->addError('', $error['code'] . ':' . $error['msg']);
            return false;
        }
        $Record->avatar = $result['path'];
        if(!$Record->save()) {
            $this->addError('', '0:修改失败');
            return false;
        }
        $data = ['avatar'=>Yii::$app->params['image_domain'] . $result['path']];
        return $data;
    }
    
    /**
     * 修改用户生日
     * @param string $birth 用户生日，格式为YYYY-mm-dd
     * @return boolen true=修改成功
     */
    public function updateBirth($birth) {
        $loginUid = Cache::hget('id');
        if(!Tool::isDate($birth)) {
            $this->addError('', '-4:出生年月日格式不正确');
            return false;
        }
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        if($Record->birth_date == $birth) {
            return true;
        }
        $Record->birth_date = $birth;
        if(!$Record->save()) {
            $this->addError('', '0:修改失败');
            return false;
        }
        return true;
    }
    
    /**
     * 修改用户情感状态
     * @param string $emotion 用户情感状态，0=保密；1=男；2=女
     * @return boolen true=修改成功
     */
    public function updateEmo($emotion) {
        $loginUid = Cache::hget('id');
        if(!in_array($emotion, [0, 1, 2])) {
            $this->addError('', '-4:情感状态格式有误');
            return false;
        }
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        if($Record->emotion == $emotion) {
            return true;
        }
        $Record->emotion = $emotion;
        if(!$Record->save()) {
            $this->addError('', '0:修改失败');
            return false;
        }
        return true;
    }
    
    /**
     * 修改用户家乡
     * @param string $hometown 用户家乡
     * @return boolen true=修改成功
     */
    public function updateHometown($hometown) {
        $loginUid = Cache::hget('id');
        if(empty($hometown)) {
            $this->addError('', '-3:请选择您的家乡');
            return false;
        }
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        if($Record->hometown == $hometown) {
            return true;
        }
        $Record->hometown = $hometown;
        if(!$Record->save()) {
            $this->addError('', '0:修改失败');
            return false;
        }
        return true;
    }
    
    /**
     * 修改用户兴趣爱好
     * @param int $uid 用户ID
     * @param string $intrest 用户兴趣爱好
     * @return boolen true=修改成功
     */
    public function updateIntrest($intrest) {
        $loginUid = Cache::hget('id');
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        if($Record->intrest == $intrest) {
            return true;
        }
        $Record->intrest = $intrest;
        if(!$Record->save()) {
            $this->addError('', '0:修改失败');
            return false;
        }
        return true;
    }
    
    /**
     * 修改用户昵称
     * @param string $nickname 用户昵称
     * @return boolen true=修改成功
     */
    public function updateNick($nickname) {
        $loginUid = Cache::hget('id');
        if(empty($nickname)) {
            $this->addError('', '-3:昵称不可为空');
            return false;
        }
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        if($Record->nickname == $nickname) {
            return true;
        }
        $Record->nickname = $nickname;
        if(!$Record->save()) {
            $this->addError('', '0:修改失败');
            return false;
        }
        return true;
    }
    
    /**
     * 修改用户签名
     * @param int $uid 用户ID
     * @param string $signature 用户签名
     * @return boolen true=修改成功
     */
    public function updateSign($signature) {
        $loginUid = Cache::hget('id');
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        if($Record->signature == $signature) {
            return true;
        }
        $Record->signature = $signature;
        if(!$Record->save()) {
            $this->addError('', '0:修改失败');
            return false;
        }
        return true;
    }
    
    /**
     * 绑定手机号
     * @param string $tel 手机号
     * @param string $vcode 验证码
     * @param string $pwd 密码
     * @return boolen true=绑定成功
     */
    public function bindTel($tel, $vcode, $pwd) {
        $loginUid = Cache::hget('id');
        if(!Tool::isMobile($tel)) {
            $this->addError('', '-4:手机号格式不正确');
            return false;
        }
        if(!Tool::isCode($vcode)) {
            $this->addError('', '-4:验证码格式不正确');
            return false;
        }
        if(!Tool::isPwd($pwd)) {
            $this->addError('', '-4:密码格式不正确');
            return false;
        }
        //检查手机号是否已经被占用
        if($this->isUsed('tel', $tel)) {
            $this->addError('', '-101:该手机号已经被占用');
            return false;
        }
        //检查验证码是否正确
        $AuthCode = new AuthCode();
        $codeResult = $AuthCode->validateCode($tel, $vcode, 'user_bindtel');
        if(!$codeResult) {
            $this->addError('', '-5:验证码不正确');
            return false;
        }
        $AuthCodeRecode = $AuthCode->findByCondition(['mobile'=>$tel])->one();
        if(!$AuthCodeRecode->cstatus) {
            $AuthCodeRecode->cstatus = 1;
            $AuthCodeRecode->save();
        }
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        $this->beginTransaction();
        $pwd = Tool::md5Double($pwd);
        $Record->tel = $tel;
        $Record->pwd = $pwd;
        if(!$Record->save()) {
            $this->rollback();
            $this->addError('', '0:绑定失败');
            return false;
        }
        //更改环信用户密码
        if(!Easemob::getInstance()->resetPassword(Cache::hget('ease_uid'),$pwd)) {
            $this->rollback();
            $this->addError('', '0:绑定失败');
            return false;
        }
        $this->commit();
        return true; 
    }
    
    /**
     * 修改绑定手机号
     * @param string $tel 手机号
     * @param string $vcode 验证码
     * @return boolen true=修改成功
     */
    public function changeTel($tel, $vcode) {
        $loginUid = Cache::hget('id');
        if(!Tool::isMobile($tel)) {
            $this->addError('', '-4:手机号格式不正确');
            return false;
        }
        if(!Tool::isCode($vcode)) {
            $this->addError('', '-4:验证码格式不正确');
            return false;
        }
        if($this->isUsed('tel', $tel)) {
            $this->addError('', '-101:该手机号已经被占用');
            return false;
        }
         //检查验证码是否正确
        $AuthCode = new AuthCode();
        $codeResult = $AuthCode->validateCode($tel, $vcode, 'user_bindtel');
        if(!$codeResult) {
            $this->addError('', '-5:验证码不正确');
            return false;
        }
        $AuthCodeRecode = $AuthCode->findByCondition(['mobile'=>$tel])->one();
        if(!$AuthCodeRecode->cstatus) {
            $AuthCodeRecode->cstatus = 1;
            $AuthCodeRecode->save();
        }
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        $Record->tel = $tel;
        if(!$Record->save()) {
            $this->addError('', '0:绑定失败');
            return false;
        }
        return true;
    }
    
    /**
     * 绑定第三方账号
     * @param string $plat 第三方名称，有qq，sina，wechat
     * @param string $platId 第三方唯一码
     * @return boolen true=绑定成功
     */
    public function bindThird($plat, $platId) {
        $loginUid = Cache::hget('id');
        if(!in_array($plat, self::$thirdPlat)) {
            $this->addError('', '-4:第三方入口不正确');
            return false;
        }
        if(empty($platId)) {
            $this->addError('', '-3:第三方信息不可为空');
            return false;
        }
        $platName = $plat.'_id';
        if($this->isUsed($platName, $platId)) {
            $this->addError('', '-105:当前第三方账号已经被绑定');
            return false;
        }
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        $Record->$platName = $platId;
        if(!$Record->save()) {
            $this->addError('', '0:绑定失败');
            return false;
        }
        $result = ['plat_name'=>$plat, 'plat_id'=>$platId];
        return $result;
    }
    
    /**
     * 解绑第三方账号
     * @param string $plat 第三方名称，有qq，sina，wechat
     * @return boolen true=绑定成功
     */
    public function unBindThird($plat) {
        $loginUid = Cache::hget('id');
        if(!in_array($plat, self::$thirdPlat)) {
            $this->addError('', '-4:第三方入口不正确');
            return false;
        }
        $platName = $plat.'_id';
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        if($Record->$platName) {
            $Record->$platName = '';
            if(!$Record->save()) {
                $this->addError('', '0:解绑失败');
                return false;
            }
        }
        return true;
    }
    
    /**
     * 修改密码
     * @param string $pwd 新密码
     * @param string $oldPwd 旧密码
     * @return boolen true=修改成功
     */
    public function changePwd($pwd, $oldPwd) {
        $loginUid = Cache::hget('id');
        if(!Tool::isPwd($pwd)) {
            $this->addError('', '-4:密码格式不正确');
            return false;
        }
        if(!Tool::isPwd($oldPwd)) {
            $this->addError('', '-4:密码格式不正确');
            return false;
        }
        $Record = $this->findOne($loginUid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        if($Record->pwd != Tool::md5Double($oldPwd) ) {
            $this->addError('', '-5:旧密码不正确');
            return false;
        }
        
        $this->beginTransaction();
        $pwd = Tool::md5Double($pwd);
        $Record->pwd = $pwd;
        if(!$Record->save()) {
            $this->rollback();
            $this->addError('', '0:修改失败');
            return false;
        }
        //更改环信用户密码
        if(!Easemob::getInstance()->resetPassword(Cache::hget('ease_uid'), $pwd)) {
            $this->rollback();
            $this->addError('', '0:绑定失败');
            return false;
        }
        $this->commit();
        return true; 
    }
    
    /**
     * 忘记密码
     * @param string $tel 手机号
     * @param string $vcode 验证码
     * @param string $pwd 密码
     * @return boolen true=成功
     */
    public function findPwd($tel, $vcode, $pwd) {
        if(!Tool::isMobile($tel)) {
            $this->addError('', '-4:手机号格式不正确');
            return false;
        }
        if(!Tool::isCode($vcode)) {
            $this->addError('', '-4:验证码格式不正确');
            return false;
        }
        $Safety = new Safety();
        if($Safety->ipTimes('findpwd_' . $tel) > 20) {
            $this->addError('', '-6:访问次数受限，请稍后再试');
            return false;
        }
        $linkkey = Cache::get('linkkey');
        if(!isset($linkkey) || (isset($linkkey) && $linkkey != Tool::md5Double(Tool::getUniqueValue($tel, $vcode, 'user_findpwd')))) {
            $this->addError('', '-2:您的操作有误，请重试');
            return false;
        }
        $Record = $this->findByCondition(['tel'=>$tel])->one();
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        //注意：为了兼容以前项目，该$vcode保留，由于已经验证key是否合法，合法则必定检查过验证码，
        //如果当前业务逻辑的验证码还有效，也进行检验
        $AuthCode = new AuthCode();
        if($AuthCode->getOldCode($tel, 'user_findpwd')) {
            if(!$AuthCode->validateCode($tel, $vcode, 'user_findpwd')) {
                $this->addError('', '-5:验证码不正确');
                return false;
            }
        }
        $pwd = Tool::md5Double($pwd);
        if($Record->pwd == $pwd) {
            //销毁key,以防多次请求
            Cache::del('linkkey');
            return true;
        }
        $this->beginTransaction();
        $Record->pwd = $pwd;
        if(!$Record->save()) {
            $this->rollback();
            $this->addError('', '0:找回失败');
            return false;
        }
        if(!Easemob::getInstance()->resetPassword($Record->ease_uid,$pwd)) {
            $this->rollback();
            $this->addError('', '0:找回失败');
            return false;
        }
        //销毁key,以防多次请求
        Cache::del('linkkey');
        $this->commit();
        return true;  
    }
    
    /**
     * 判断用户是否被停封
     * @param type $uid
     * @return boolean true=用户未被停封
     */
    public function isOut($uid) {
        if(!is_numeric($uid)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $Record = $this->findOne($uid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        if($Record->status == -1) {
            $this->addError('', '-103:该用户已因涉嫌违规已被停封处理');
            return false;
	}
        return true;
    }
    
    /**
     * 获取注册登录用户信息
     * @param type $uid
     * @return array
     */
    public function getSimpleList($uid) {
        $Record = $this->findOne($uid);
        $userInfo = [];
        $userInfo['id'] = $Record->id;
        $userInfo['ease_uid'] = $Record->ease_uid;
        $userInfo['pwd'] = $Record->pwd;
        $userInfo['tel'] = $Record->tel;
        $userInfo['serial_num'] = $Record->serial_num;
        $userInfo['avatar'] =  Tool::connectPath(Yii::$app->params['image_domain'], $Record->avatar);
        $userInfo['nickname'] = $Record->nickname;
        $userInfo['gender'] = $Record->gender;
        $userInfo['birth_date'] = $Record->birth_date;
        $userInfo['emotion'] = $Record->emotion;
        $userInfo['hometown'] = $Record->hometown;
        $userInfo['intrest'] = $Record->intrest;
        $userInfo['signature'] = $Record->signature;
        $userInfo['qq_id'] = $Record->qq_id;
        $userInfo['sina_id'] = $Record->sina_id;
        $userInfo['wechat_id'] = $Record->wechat_id;
        $userInfo['push_code'] = $this->getUserPushAlias($Record->id);
        $userInfo['verify_code'] = $this->getUserVerifyCode($Record->id);
        return $userInfo;
    }
    
    /**
     * 更新消息角标
     * @param int $uid 用户ID
     * @param int $badge 角标数
     * @param int $lasttime 最后更新时间
     * @return boolean true=更新成功
     */
    public function setBadge($uid, $badge, $lasttime) {
        if(!is_numeric($uid) || !is_numeric($badge)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $Record = $this->findOne($uid);
        if(!$Record) {
            $this->addError('', '-7:用户不存在');
            return false;
        }
        $Setting = new Setting();
        $SettingRecord = $Setting->findOne($uid);
        $SettingRecord->badge = $badge;
        if($lasttime) {
            $SettingRecord->lasttime = $lasttime;
        } else {
            $SettingRecord->lasttime = date('Y-m-d H:i:s');
        }
        if(!$SettingRecord->save()) {
            $this->addError('', '0:更新失败');
            return false;
        }
        return true;
    }
    
    /**
     * 获取正常用户id，包括被冻结用户
     */
    public function getOutUser() {
        $sql = 'select id from ' . $this->tableName() . ' where status=' . Yii::$app->params['state_code']['status_delete'];
        try {
            $records = $this->findBySql($sql)->asArray()->all();
        }catch(Exception $e) {
            $this->addError('', '-11:' . $e->getMessage());
            return false;
        }
        $outUserIds = [];
        foreach($records as $record) {
            $outUserIds[] = $record['id'];
        }
        return $outUserIds;
    }
}

