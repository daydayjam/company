<?php
/**
 * ActiveRecode基类
 * @author ztt
 * @date 2017/10/27
 */
namespace app\components;

use app\models\Error;
use app\models\Cache;

class ActiveRecord extends \yii\db\ActiveRecord {
    /**
     * 获取带验证码的错误信息
     * @return mixed 如果没有错误，则返回false，否则返货array，格式为array('code'=>100, 'msg'=>'error msg...')
     */
    public function getCodeError() {
        if(!$this->hasErrors()) {
            return false;
        }
        $errors = $this->getErrors();
        $error = current($errors);//格式 success
        $splitIndex = strpos($error[0], ':');
        if($splitIndex) {
            $code = trim(substr($error[0], 0, $splitIndex));
            $msg = trim(substr($error[0], $splitIndex + 1));
            if(is_numeric($code)) {
                return ['code' => $code, 'msg' => $msg];
            }
        }
        return ['code' => '', 'msg' => $error[0]];
    }
    
    
    /**
     * 获取翻页列表
     * @param string $select列表字段 如：id,name
     * @param int $page 页码，第一页为1
     * @param int $record 每页显示的行数
     * @param array $params 查询条件 全部用and连接, 如：array('id'=>1,'name'=>'asdf')
     * @param string $order 排序方式
     * @return array 数组 格式为：
     * array(
     * 'total'=>int //满足条件的总记录数
     * 'rows' =>array(
     * 			0=>item0,//每项表示一条记录
     * 			1=>item2
     * 		)
     * 
     * )
     */
    public function getListData($select = '*', $page = 1, $record = 10, $andParams = null, $orParams = null, $leftJoin = null, $order = 'id DESC') {
        $condition = [];
        $bind = [];
        if (is_array($andParams) && count($andParams) > 0) {
            $condition[] = 'and';
            foreach ($andParams as $key => $val) {
                $op = '=';
                if(is_array($val)) {
                    $op = $val['op'];
                    $val = $val['val'];
                }
                $condition[] = $key . ' ' . $op . ' :' . $key;
                $bind[':' . $key] = $val;
            }
        }
        
        $orCondition = [];
        $orBind = [];
        if (is_array($orParams) && count($orParams) > 0) {
            $orCondition[] = 'or';
            foreach ($orParams as $key => $val) {
                $op = '=';
                if(is_array($val)) {
                    $op = $val['op'];
                    $val = $val['val'];
                }
                $orCondition[] = $key . ' ' . $op . ' :' . $key;
                $orBind[':' . $key] = $val;
            }
        }
        
        $rows = (new \yii\db\Query())
                ->select($select)
                ->from($this->tableName());
        if($leftJoin) {
            $rows = $rows->leftJoin($leftJoin['tbname'], $leftJoin['on']);
        }
        $rows = $rows->where($condition, $bind)
                    ->orWhere($orCondition, $orBind)
                    ->orderBy($order)
                    ->limit($record)
                    ->offset($page * $record)
                    ->all();
        $countRow = (new \yii\db\Query())
                    ->select('count(*) as count')
                    ->from($this->tableName());
        if($leftJoin) {
            $countRow = $countRow->leftJoin($leftJoin['tbname'], $leftJoin['on']);
        }
        $countRow = $countRow->where($condition, $bind)
                ->orWhere($orCondition, $orBind)
                ->one();
        return array(
            'total' => $countRow['count'],
            'rows' => $rows
        );
    }
    
    /**
     * 重写save方法，让更新时间有数据库自动更新
     */
    public function save($runValidation = true, $attributeNames = null) {
        if(isset($this->update_time)) {
            unset($this->update_time);
            if($attributeNames != null && isset($attributeNames['update_time'])) {
                unset($attributeNames['update_time']);
            }
        }
        return parent::save($runValidation, $attributeNames);
    }
    
    /**
     * 重写insert方法，调用方法时自动生成创建时间
     */
    public function insert($runValidation = true, $attributeNames = null) {
//        if(!isset($this->update_time) || $this->update_time == '0000-00-00') {
//            $this->update_time = date('Y-m-d H:i:s');
//        }
        if($attributeNames == null){
            if(!isset($this->create_time) || $this->create_time=='0000-00-00 00:00:00'){
                $this->create_time = date('Y-m-d H:i:s');
            }
        } else if(!isset($attributeNames['create_time']) || $this->create_time=='0000-00-00 00:00:00'){
            $attributeNames['create_time'] = date('Y-m-d H:i:s');
        }
        return parent::insert($runValidation, $attributeNames);
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

