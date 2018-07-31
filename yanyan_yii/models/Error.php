<?php
/**
 * 错误定义类
 * @author ztt
 * @date 2017/10/27
 */
namespace app\models;

class Error {
    private static $data = [
        'success' => ['code' => 1, 'msg' => '操作成功'],
        'fail' => ['code' => 0, 'msg' => '请求失败'],
        'verifyError' => ['code' => -1, 'msg' => '请重新登录'],
        'invalidArgument' => ['code' => -2, 'msg' => '参数错误'],
        'noPermission' => ['code' => -3, 'msg' => '没有权限'],
        'uploadImageError' => ['code' => -4, 'msg' => '图片上传失败'],
        'lowLevel' => ['code' => -5, 'msg' => '等级不足'],
        'noUser' => ['code' => -6, 'msg' => '未找到该用户'],
        'uploadImageFail' => ['code' => -7, 'msg' => '图片上传失败'],
        'dataError' => ['code' => -8, 'msg' => '数据错误'],
        'inBlack' => ['code' => -9, 'msg' => '被对方拉黑了'],
        'telIsUsed' => ['code' => -10, 'msg' => '该手机号已经注册过了'],
        'emptyArgument' => ['code' => -11, 'msg' => '此参数必填'],
        'frozen' => ['code' => -12, 'msg' => '该用户已因涉嫌违规已被停封处理'],
        'telNotRegistered' => ['code' => -100, 'msg' => '该手机号尚未注册'],
        'pwdError' => ['code' => -101, 'msg' => '密码错误'],
        'oldPwdError' => ['code' => -102, 'msg' => '当前密码不正确'],
        'thirdIsUsed' => ['code' => -103, 'msg' => '该{param1}账号已经被绑定'],
        'thirdIsBind' => ['code' => -104, 'msg' => '未绑定第三方账号'],
        'limitedAccess' => ['code' => -105,'msg' => '访问次数受限,请稍后重试'],
        'telInvalid' => ['code' => -106, 'msg' => '手机号格式有误'],
        'pwdInvalid' => ['code' => -107, 'msg' => '密码格式有误'],
        'avatarInvalid' => ['code' => -108, 'msg' => '头像格式有误'],
        'genderInvalid' => ['code' => -109, 'msg' => '性别格式有误'],
        'dateInvalid' => ['code' => -110, 'msg' => '出生日期有误'],
        'codeInvalid' => ['code' => -111, 'msg' => '验证码格式有误'],
        'logicInvalid' => ['code' => -111, 'msg' => '业务逻辑有误'],
        'telOrPwdError' => ['code' => -112, 'msg' => '手机号或密码有误'],
        'emptyNick' => ['code' => -113, 'msg' => '昵称不可为空'],
        'chatroomExpired' => ['code' => -200,'msg' => '聊天室已过期'],
        'noRoom' => ['code' => -201,'msg' => '未找到该聊天室'],
        'alreadForbid' => ['code' => -202,'msg' => '该用户已经被禁言了'],
        'frequentlyForbid' => ['code' => -203,'msg' => '频繁举报'],
        'noTipoff' => ['code' => -204,'msg' => '未找到该举报信息'],
        'tipoffTimeOut' => ['code' => -205,'msg' => '该举报已失效'],
        'fromUserOperate' => ['code' => -206,'msg' => '举报发起人不能处理'],
        'toUserOperate' => ['code' => -206,'msg' => '举报人不能处理'],
        'alreadyAgree' => ['code' => -207,'msg' => '已经同意了'],
        'noFilm' => ['code' => -208,'msg' => '未找到该聊天室'],
        'noComment' => ['code' => -300,'msg' => '未找到该评论'],
        'isReport' => ['code' => -400,'msg' => '您已举报过该信息'],
        'timeOutCode' => ['code' => -500,'msg' => '该验证码超时'],
        'codeUsed' => ['code' => -501,'msg' => '该验证码已经被使用过'],
        'errorCode' => ['code' => -502,'msg' => '该验证码不正确'],
        'timeClose' => ['code' => -503, 'msg' => '时间过于接近，请稍后再试'],
        'locking' => ['code' => -600,'msg' => '账号临时锁定,请稍后重试'],
        'inYourBlack' => ['code' => -700,'msg' => '对方已在您的黑名单中'],
        'inTheirBlack' => ['code' => 701,'msg' => '您已被对方拉入黑名单'],
        'notInYourBlack' => ['code' => -702,'msg' => '对方不在您的黑名单中']
    ];

    /**
     * 获取错误信息
     */
    public static function getErrorInfo($errorDesc, $paramValues = []) {
        if(!array_key_exists($errorDesc, self::$data)) {
            return false;
        }
        $info = self::$data[$errorDesc];
        if(count($paramValues) > 0){
            $info['msg'] = self::replaceParams($info['msg'], $paramValues);
        }
        return $info;
    }
    
    /**
     * 替换msg里面的参数
     * @param string $str 待替换的字符串
     * @param string $values 
     * @return string 
     */
    public function replaceParams($str, $values){
        $pattern = array();
        for($i = 0; $i < count($values); $i++){
            array_push($pattern, '({param'.($i+1)."})");
        }
        $data = preg_replace($pattern, $values, $str , 1);
        return $data;
    } 
}
