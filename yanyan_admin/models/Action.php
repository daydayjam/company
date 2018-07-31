<?php
/**
 * 菜单模型类
 * @author ztt
 * @date 2018/01/09
 */
namespace app\models;

use Yii;
use yii\db\Query;
use app\components\ActiveRecord;


class Action extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%admin_action}}';
    }
    
    /**
     * 获取菜单项
     * @return array
     */
    public function getMenu() {
        $result = [];
        $sql = 'select * from ' . $this->tableName() . ' where pid=0';
        $parentMenus = $this->findBySql($sql)->asArray()->all();
        foreach($parentMenus as $key=>$menu) {
            $result[$key] = $menu;
            $sql = 'select * from ' . $this->tableName() . ' where pid=' . $menu['id'];
            $sonMenus = $this->findBySql($sql)->asArray()->all();
            $result[$key]['s_menu'] = $sonMenus;
        }
        return $result;
    }
    
    

    
}

