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

class Tag extends ActiveRecord {
    
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
    public function getList($type = 1, $title = []) {
        if(!is_numeric($type) || ($type != Yii::$app->params['document']['poetry_tag_from'] && $type != Yii::$app->params['document']['poetry_tag_sort'])) {
            return $this->addError('', '300:参数格式有误');
        }
        $query = (new Query())->select(['id', 'title'])
                        ->from($this->tableName())
                        ->where(['type' => $type])
                        ->andWhere('listorder > 0 and listorder < 10');
        if(!empty($title)) {
            $query->andWhere(['or like', 'title', $title]);
        }
        $result = $query->orderBy('tagorder')->all();
        return $result;
    }
    
    /**
     * 获取最新事件
     * @return int
     */
    public function getLastTime() {
        $query = (new Query())->select(['create_time'])
                        ->from($this->tableName())
                        ->orderBy('create_time desc')
                        ->limit(1)
                        ->one();
        return $query ? $query['create_time'] : 0;
    }
}

