<?php
/**
 * 点赞模型类
 * @author ztt
 * @date 2017/11/9
 */
namespace app\models;

use Yii;
use yii\db\Query;
use app\components\ActiveRecord;
use yii\web\IdentityInterface;
use app\models\Safety;
use app\models\Tool;

class Praise extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%praise}}';
    }
    
    /**
     * 获取点赞次数
     * @param type $assocId
     * @param type $assocType 默认0,0=评论
     * @return boolean|int
     */
    public function addAndGetPraiseCnt($assocId, $assocType = 0) {
        $loginUid = Cache::hget('id');
        $Record = $this->findByCondition(['uid'=>$loginUid, 'assoc_type'=>$assocType, 'comment_id'=>$assocId])->one();
        if(!$Record) {
            $this->uid = $loginUid;
            $this->assoc_type = $assocType;
            $this->comment_id = $assocId;
            $this->cnt = 1;
            if(!$this->save()) {
                $this->addError('', '0:点赞失败');
                return false;
            }
            return 1;
        }
        if($Record->cnt >= 10) {
            $this->addError('', '-6:点满了哟');
            return false;
        }
        $Record->cnt = $Record->cnt + 1;
        if(!$Record->save()) {
            $this->addError('', '0:点赞失败');
            return false;
        }
        return $Record->cnt;
    }
   
    
    
    
    
}

