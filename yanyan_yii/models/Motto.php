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
     * 获取随机电影寄语
     * @param int $filmId 电影ID
     * @return boolean|string 随机寄语内容
     */
    public function getRandom($filmId = 0) {
        if($filmId && !is_numeric($filmId)) {
            $this->addError('', '-4:参数有误');
            return false;
        }
        if($filmId) {
            $sql = 'select content from ' . $this->tableName() . ' where film_id=:filmId';
            $records = $this->findBySql($sql, [':filmId'=>$filmId])->asArray()->all();
            if($records) {
                $key = array_rand($records);
                return $records[$key]['content'];
            }
        }
        $sql = 'select content from ' . $this->tableName() . ' where film_id=0';
        $records = $this->findBySql($sql)->asArray()->all();
        $key = array_rand($records);
        return $records[$key]['content'];
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

