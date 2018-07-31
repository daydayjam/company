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
use app\models\Helper;

class Author extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%author}}';
    }
    
    /**
     * 模糊查询所有作者
     * @param string $author 作者名称
     * @return array
     */
    public function getList($author = [], $page = 1, $pagesize = 10) {
        if(empty($author)) {
            return $this->addError('', '301:请输入作者名称');
        }
        if(!is_numeric($page) || !is_numeric($pagesize) || $page < 1 || $pagesize < 1) {
            return $this->addError('', '300:参数格式有误，请重试');
        }
        if(!is_array($author)) {
            $author = Tool::scwsWord($author);
        }
//        print_r($author);die;
        $rows = (new Query())
                        ->select(['id', 'name', 'description', 'year'])
                        ->from(self::tableName())
                        ->where(['or like', 'name', $author])
                        ->orderBy('create_time')
                        ->limit($pagesize)
                        ->offset(($page-1)*$pagesize)
                        ->all();
        $countRow = (new Query())
                        ->select('count(*) as count')
                        ->from(self::tableName())
                        ->where(['or like', 'name', $author])
                        ->one();
        $newResult = [];
        foreach($rows as $key=>$value) {
            $newResult[$key]['id'] = $value['id'];
            $newResult[$key]['name'] = $value['name'];
            $newResult[$key]['year'] = $value['year'];
            $newResult[$key]['description'] = $value['description'];
        }
        Helper::removeListDai($newResult);
        Helper::addEmptyAuthorList($newResult, 'name');
        $result = [
            'total' => $countRow['count'],
            'rows' => $newResult
        ];
        return $result;
    }
    
    /**
     * 获取作者信息
     * @param int $id 作者ID
     * @return array
     */
    public function getInfo($id) {
        $Record = $this->findOne($id);
        if(!$Record) {
            return $this->addError('', '402:作者信息不存在');
        }
        $result = [];
        $result['id'] = $Record->id;
        $result['name'] = $Record->name;
        $result['year'] = $Record->year;
        $result['description'] = $Record->description;
        $result['avatar'] = $Record->avatar;
        Helper::removeDai($result);
        return $result;
    }
}

