<?php
/**
 * 反馈模型类
 * @author ztt
 * @date 2017/11/30
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class Feedback extends ActiveRecord {
    public $type = [1,2];    //反馈类型,1=意见&bug反馈，2=封停申诉
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%feedback}}';
    }
    
    /**
     * 添加反馈信息
     * @param int $uid 用户ID
     * @param int $type 反馈类型,1=意见&bug反馈，2=封停申诉
     * @param string $content 反馈内容
     * @param string $contact 联系方式
     * @return boolen true=添加成功
     */
    public function add($uid, $type, $content, $contact) {
        if($uid && !is_numeric($uid)) {
            $this->addError('', '-4:请求参数有误');
            return false;
        }
        if(!in_array($type, $this->type)) {
            $this->addError('', '-4:反馈类型有误');
            return false;
        }
        if(empty($content)) {
            $this->addError('', '-3:反馈内容不可为空');
            return false;
        }
        if($uid > 0) {
            $User = new User();
            $Record = $User->findOne($uid);
            if(!$Record) {
                $this->addError('', '-7:请求信息不存在');
                return false;
            }
        }
        $this->uid = $uid;
        $this->type = $type;
        $this->content = $content;
        $this->contact = $contact;
        if(!$this->save()) {
            $this->addError('', '0:反馈失败');
            return false;
        }
        return true;
    }
    
}

