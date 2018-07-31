<?php
/**
 * 演员模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;
use yii\web\IdentityInterface;
use app\models\Safety;
use app\models\Tool;
use app\models\Praise;

class Actor extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%actors}}';
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
        if($params['name']) {
            $andParams['act_name'] = [
                'val'=>'%' . $params['name'] . '%',
                'op' =>'like'
            ];
        }
        if($params['en_name']) {
            $andParams['en_name'] = [
                'val'=>'%' . $params['en_name'] . '%',
                'op' =>'like'
            ];
        }
        if($params['country']) {
            $andParams['country'] = [
                'val'=>'%' . $params['country'] . '%',
                'op' =>'like'
            ];
        }
        $select = 'id,act_name,en_name,avatar,country';
        $result = $this->getListData($select, $page, $record, $andParams,[],[],'id asc');
        $result['page'] = $page;
        $result['record'] = $record;
        return $result;
    }
    
    

    
}

