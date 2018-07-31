<?php

/**
 * 诗词操作类
 * @date 2018/06/06
 * @author ztt
 */
namespace app\models;
use Yii;
use app\components\ActiveRecord;
use yii\db\Query;

class TagSpider extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%tag}}';
    }
    
    /**
     * 获取标签列表
     * @param int $type 标签分类：1= 出处标签；2=分类标签
     * @param string $title 标签名称
     * @return array
     */
    public function getList($type = 1, $title = '') {
        if(!is_numeric($type) || ($type != Yii::$app->params['document']['poetry_tag_from'] && $type != Yii::$app->params['document']['poetry_tag_sort'])) {
            return $this->addError('', '300:参数格式有误');
        }
        $query = (new Query())->select(['id', 'title'])
                        ->from($this->tableName())
                        ->where(['type' => $type]);
        if(!empty($title)) {
            $query->orWhere(['like', 'title', $title]);
        }
        $result = $query->all();
        return $result;
    }
}

