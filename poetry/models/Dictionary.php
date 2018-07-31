<?php

/**
 * 字典操作类
 * @date 2018/06/06
 * @author ztt
 */
namespace app\models;
use Yii;
use app\components\ActiveRecord;
use yii\db\Query;

class Dictionary extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%dictionary}}';
    }
    
    /**
     * 获取标签列表
     * @param int $type 标签分类：1= 出处标签；2=分类标签
     * @param string $title 标签名称
     * @return array
     */
    public function getInfo($word) {
        $result = [];
        if(empty($word)) {
            return $this->addError('', '301:请选择您要查看的汉字');
        }
        $Record = $this->findByCondition(['word'=>$word])->one();
        if(!$Record) {
            return $this->addError('', '402:暂未收录该汉字');
        }
        $result['word'] = $Record->word;
        $pinyinArr = explode(',', $Record->pinyin);
        $briefIntro = $Record->brief_intro;
        $intro = [];
        $introArr = preg_split('/('.implode('|', $pinyinArr).')/', $briefIntro,  -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach($introArr as $key=>$item) {
            if(in_array($item, $pinyinArr)) {
                $intro[$key]['pinyin'] = $item;
                $introCovent = str_replace('<br>', '\n', str_replace('<br>'.$word.'<br>', '', $introArr[$key+1]));
                $introCovent = preg_replace('/^\\\n|(\\\n){1,}$/', '', $introCovent);
                $intro[$key]['intro'] = $introCovent;
            }
        }
        $result['intro'] = array_values($intro);
        return $result;
    }
}

