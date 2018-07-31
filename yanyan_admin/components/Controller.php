<?php
/**
 * 控制器总类
 */
namespace app\components;

use Yii;
use app\models\Tool;
use app\models\Cache;
use app\models\Manager;


class Controller extends \yii\web\Controller {
    public $layout = false;
    
    public $enableCsrfValidation = false;
    
    /**
     * 构造方法，用于初始化所有控制器
     * @param string $id
     * @param string $module
     * @return void
     */
    public function __construct($id, $module) {
//        $this->writeLog();
        parent::__construct($id, $module);
        $this->initController();
    }
    
    /**
     * 初始化方法,所有控制器都会调用此方法
     * @return void
     */
    final function initController() {
        $code = isset($_COOKIE['code']) ? $_COOKIE['code'] : '';
        $routes = require Yii::$app->basePath . '/config/access.php';
        $route1 = filter_input(INPUT_GET, 'r'); //精准匹配
	if(strpos($route1, '/') === 0) {
            $route1 = substr($route1, 1);
        }
        $route2 = $this->getUniqueId() . '/*';  //全匹配
//        print_r($code);die;
        if(empty($code)) {  //如果code为空，用户未登录，验证访问路由是否为非登录用户可访问
            if(in_array($route1, $routes['guest']) || in_array($route2, $routes['guest'])) {
                return;
            }
            $this->redirect('/admin/login');
        }
        $Manager = new Manager();
        $sql = 'select * from ' . $Manager->tableName() . ' where sn=:sn';
        $ManagerRecord = $Manager->findBySql($sql, [':sn'=>$code])->one();
        if(!$ManagerRecord) {   
            $this->redirect('/admin/login');
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
     * 输出错误JSON消息，调用此方法，程序会退出
     * @param object $object 出错对象
     */
    public function showError($object) {
        $result = [];
        $error = $object->getCodeError();
        if($error) {
             $result = [
                'code' => $error['code'],
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
     * 重写render
     * @param type $view
     * @param type $params
     */
    public function render($view, $data = array(), $params = array()) {
        $params['ac'] = Yii::$app->controller->id;
        $params['op'] = Yii::$app->controller->action->id;
        if(!isset($params['ac_name']) && !isset($params['op_name'])) {
            $params['ac_name'] = $this->getParam('ac_name');
            $params['op_name'] = $this->getParam('op_name');
        }
        $condition = '';
        foreach($params as $key=>$item) {
            $condition .= '&'.$key.'='.$item;
        }
        $params['condition'] = $condition;
        
        $result = ['data'=>$data, 'params'=>$params];
        return parent::render($view, $result);
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
                unlink($logFile);
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
}

