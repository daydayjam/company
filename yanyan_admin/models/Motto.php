<?php
/**
 * 寄语模型类
 * @author ztt
 * @date 2017/12/27
 */
namespace app\models;

use Yii;
use yii\db\Query;
use app\components\ActiveRecord;


class Motto extends ActiveRecord {
    private $_Transaction = null;  //事务处理对象
//    public static $prefix = '小言说:';
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%motto}}';
    }
    
    /**
     * 获取列表
     * @param type $params
     * @param int $page
     * @param int $record
     * @return array|boolen
     */
    public function getList($params, $page = 1, $record = 20) {
        $andParams = [];
        if($params['id']) {
            $andParams['id'] = $params['id'];
        }
        if($params['fname']) {
            $andParams['film_name'] = [
                'val'=>'%' . $params['fname'] . '%',
                'op' =>'like'
            ];
        }
        if($params['content']) {
            $andParams['content'] = [
                'val'=>'%' . $params['content'] . '%',
                'op' =>'like'
            ];
        }
        $select = 'id,film_id,film_name,content';
        $result = $this->getListData($select, $page, $record, $andParams);
        $result['page'] = $page;
        $result['record'] = $record;
        return $result;
    }
    
    /**
     * 添加寄语
     * @param int $fid
     * @param string $content
     * @return boolean
     */
    public function saveMotto($content, $fid = 0) {
        if($fid && !is_numeric($fid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!$content) {
            $this->addError('', '-3:内容不能为空');
            return false;
        }
        if($fid) {
            $Film = new Film();
            $FilmRecord = $Film->findOne($fid);
            if(!$FilmRecord) {
                $this->addError('', '-7:相关影视不存在');
                return false;
            }
        }
        
        $this->film_id = $fid ? $fid : 0;
        $this->film_name = $fid ? $FilmRecord->title : '';
        $this->content = $content;
        if(!$this->save()) {
            $this->addError('', '0:创建失败');
            return false;
        }
        return true;
    }
    
    /**
     * 删除
     * @param type $ids
     * @return boolean
     */
    public function del($ids) {
        $idArr = explode(',', $ids);
        $this->beginTransaction();
        foreach($idArr as $item) {
            if(!$item) {
                continue;
            }
            $Record = $this->findOne($item);
            if(!$Record) {
                $this->rollback();
                $this->addError('', '-7:该寄语不存在');
                return false;
            }
            if(!$Record->delete()) {
                $this->rollback();
                $this->addError('', '0:删除失败');
                return false;
            }
        }
        $this->commit();
        return true;
        
    }
    
    /**
     * 启动事务
     * @return void 无返回值
     */
    protected function beginTransaction() {
        $this->_Transaction = Yii::$app->db->beginTransaction();
    }
    
    /**
     * 回滚事务
     * @return void 无返回值
     */
    protected function rollback() {
        if($this->_Transaction != null) {
            $this->_Transaction->rollBack();
        }
    }
    
    /**
     * 提交事务
     * @return void 无返回值
     */
    protected function commit() {
        if($this->_Transaction != null) {
            $this->_Transaction->commit();
        }
    }
    
    

    
}

