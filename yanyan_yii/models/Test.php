<?php
/**
 * 测试类
 * @author ztt
 * @date 2017/11/13
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class Test extends ActiveRecord {
    
    public function a() {
        $this->addError('', '不好');
        return false;
    }
    
    public function b() {
        if($this->a()) {
            $error = $this->getCodeError();
            $this->addError('', $error['code']);
        }
        
    }
    
}


