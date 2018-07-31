<?php
/**
 * 文章模型类
 * @author ztt
 * @date 2018/03/09
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\Tool;
use yii\db\Query;

class Rhesis extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%rhesis}}';
    }
    
    /**
     * 获取随机名句及背景图
     * @return array
     */
    public function getDailyInfo() {
        $key = 'RHESIS_'.date('Ymd');
        $rhesisStr = Cache::get($key);
        if($rhesisStr) {
            return json_decode($rhesisStr, true);
        } else {
            $sql = 'SELECT poetry_id,poetry_title,content,year,author_name FROM '.self::tableName().' order by rand() limit 1;';
            $Record = $this->findBySql($sql)->one();
            $content = Tool::verticalRow($Record->content);
            $result = [
                'poetry_id' => $Record->poetry_id,
                'content' => $content,
                'from' => $Record->author_name ? $Record->author_name : '佚名',
                'backimage' => Yii::$app->params['image_domain'] . '/system/backimage/'.rand(1, 21).'@3x.jpg'
            ];
            Cache::setex($key, 86400, json_encode($result));
        }
        return $result;
    }
    
}
