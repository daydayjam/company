<?php
/**
 * 控制器总类
 */
namespace app\components;

use Yii;
use app\models\Tool;
use app\models\Cache;


class Controller extends \yii\web\Controller {
    public $layout = false;
    
    public $enableCsrfValidation = false;
    
    /**
     * 当前页
     * @var type 
     */
    public $page = '';
    
    /**
     * 每页显示多少条
     * @var type 
     */
    public $size = '';
    
    /**
     * 查询条件起始值
     */
    public $from = 0;
    
    /**
     * 构造方法，用于初始化所有控制器
     * @param string $id
     * @param string $module
     * @return void
     */
    public function __construct($id, $module) {
        if($id != 'spider') { 
            $this->writeLog(); 
        }
        parent::__construct($id, $module);
        $this->initController();
    }
    
    /**
     * 初始化方法,所有控制器都会调用此方法
     * @return void
     */
    final function initController() {
        $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
        $routes = require Yii::$app->basePath . '/config/access.php';
        $route1 = filter_input(INPUT_GET, 'r');//精准匹配
        if(strpos($route1, '/') === 0) {
            $route1 = substr($route1, 1);
        }
        $route2 = $this->getUniqueId() . '/*';//全匹配
        if(in_array($route1, $routes['guest']) || in_array($route2, $routes['guest'])) {
            if(!empty($code)) {
                Cache::expire($code, Yii::$app->params['expire']);
            }
            return;
        }
        if(empty($code)) {//如果code为空，用户未登录，验证访问路由是否为非登录用户可访问
            if(in_array($route1, $routes['guest']) || in_array($route2, $routes['guest'])) {
                return;
            }
            $this->show(-2, '您没有权限');
        }
        if(!Cache::exists($code)) {    //缓存不存在则说明身份认证已过期，需要重新登录
            $this->show(-1, '请重新登录');
        }
        //用户状态判断
        $status = Cache::hget('status');
        if($status == 1) {   //用户为正常用户
            //每次访问成功更新code过期时间
            Cache::expire($code, Yii::$app->params['expire']);
            return;
        }else if($status < -1) { //用户为冻结用户
            if(in_array($route1, $routes['freeze']) || in_array($route2, $routes['freeze'])) {
                //每次访问成功更新code过期时间
                Cache::expire($code, Yii::$app->params['expire']);
                return;
            }
            $hourType = $status == -2 ? 8 : 24;
            $timeDiff = Tool::getTimeDiff(Cache::hget('unfreeze_time'), date('Y-m-d H:i:s'));
            $hour = floor($timeDiff/3600);
            $second = floor(($timeDiff/3600 - $hour)*60);
            $this->show(-104, '您因违规被处以冻结'.$hourType.'小时处罚，距离解封还剩'.$hour.'小时'.$second.'分');
        }else { //用户为封号用户
            $this->show(-103, '您的账号已因涉嫌违规已被停封处理');
        }
    }
    
    /**
     * 输出正确JSON消息，调用此方法，程序会退出
     * @param int $status 状态码
     * @param string $msg 错误信息，默认为空字符串
     * @param array $data 输出的其他信息，默认为null
     */
    public function show($status, $msg, $data = null) {
        $result = [
            'code' => $status,
            'msg' => $msg
        ];
        if(is_array($data)) {
            $result = array_merge($result, $data);
        }
        echo json_encode($result);
        die;
    }
    
    /**
     * 输出正确JSON消息，调用此方法，程序会退出
     * @param array $data 输出的其他信息，默认为null
     */
    public function showOk($data = null) {
        $result = [
            'code' => 1,
            'msg' => '操作成功'
        ];
        if($data !== null) {
            $data = ['data' => $data];
            $result = array_merge($result, $data);
        }
        echo json_encode($result);
        die;
    }
    
    /**
     * 输出正确JSON消息，调用此方法，程序会退出
     * @param array $data 输出的其他信息，默认为null
     */
    public function showOkIos($data = null) {
        $result = [
            'code' => 1,
            'msg' => '操作成功',
            'is_play' => Yii::$app->params['version']['ios']['is_play']
        ];
        if($data !== null) {
            $data = ['data' => $data];
            $result = array_merge($result, $data);
        }
        echo json_encode($result);
        die;
    }
    
    /**
     * 输出正确JSON消息，调用此方法，程序会退出
     * @param array $data 输出的其他信息，默认为null
     */
    public function showOkN($data = null) {
        $result = [
            'code' => 1,
            'msg' => '操作成功'
        ];
        if($data !== null) {
            $data = ['data' => $data];
            $result = array_merge($result, $data);
        }
        $result = preg_replace('/\\\n/', 'n', json_encode($result));
//        $result = json_encode(stripslashes($result));
        echo $result;
        die;
    }
    
    /**
     * 输出错误JSON消息，调用此方法，程序会退出
     * @param object $object 出错对象
     */
    public function showError($object = null) {
        $result = [];
        $error = $object ?$object->getCodeError() : '';
        if($error) {
             $result = [
                'code' => (int)$error['code'],
                'msg' => $error['msg']
            ];
        }
        echo json_encode($result);
        die;
    }
    
    /**
     * 获取参数
     * @param string $fielId 字段名称
     * @param string $default 默认值
     * @return mixed 获取的对应字段的值
     */
    public function getParam($fielId, $default = '') {
        $result = '';
        if (Yii::$app->request->isGet) {
            $result = trim(Yii::$app->request->get($fielId, $default));
        } else {
            $result = trim(Yii::$app->request->post($fielId, $default));
        }
        if($result) {
            $result = addslashes(urldecode($result));
        }
        return $result;
    }
    
    /**
     * 获取参数集
     * @param string $fieldIds 字段集合字符串，格式为 username,password,age,...
     * @return mixed 获取的对应字段集的值
     */
    public function getParams($fieldIds) {
        $result = [];
        $fieldIdsArr = explode(',', $fieldIds);
        foreach($fieldIdsArr as $value) {
            $result[$value] = addslashes(Yii::$app->request->get($value));
        }
        return $result;
    }
    
    /**
     * 将字符串里的emoji表情转化为unicode,
     * text为可能包含二进制emoji表情的字符串,
     * 由客户端自行解码
     * @param string $text 参数
     * @return string
     */
    public function encodeEmojiStr($text){
        $tmpStr = json_encode($text); //暴露出unicode
        $tmpStr = @preg_replace("#(\\\u[de]{1}[0-9a-f]{3})#ie","addslashes('\\1')",$tmpStr); //将emoji的unicode留下，其他不动
        $text = json_decode($tmpStr);
        return $text;
    }
    
    /**
     * 写入日志
     * @param $socket  socket 可以为空
     * @param $code string 错误代码
     * @param $msg  消息
     * @return void
     */
    public function writeLog($msg = '', $code = '') {
        $logFile = 'access.html';
        $logTime = 30; //重写时间
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . urldecode(http_build_query($_REQUEST));
        if (file_exists($logFile)) {
            if(!Cache::get('access_ctime')) {
                @unlink($logFile);
            }
        }
        if (!file_exists($logFile)) {
            Cache::setex('access_ctime', 30, 1);
            $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <title>言言日志文件-预删除日期：' . date('Y-m-d H:i:s', time() + $logTime) . '</title>
                    </head>
                    <span style="color:red">The log file was created at ' . date('Y-m-d H:i:s') . ', and  would be rebuilt at ' . date('Y-m-d H:i:s', time() + $logTime) . '.</span>';
            file_put_contents($logFile, $html);
        }
        $error = array();
        $error['msg'] = print_r($msg, true);
        $text = '<br /><br />';
        $text .= date('Y-m-d H:i:s')
        	  .'\nURL:'.$url
        	  .'\nMSG:'.$error['msg'];
        $fp = fopen($logFile, 'a');
        fwrite($fp, str_replace('\n', '\n<br />', $text));
        fclose($fp);
    }
    
    /**
    * 获取分页内容
    */
   public function getPageAndSize($page, $pagesize) {
       $this->page = !empty($page) ? $page : 1;
       $this->size = !empty($pagesize) ? $pagesize : Yii::$app->params['page_size'];
       $this->from = ($this->page - 1) * $this->size;
   }
}

