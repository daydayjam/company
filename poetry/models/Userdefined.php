<?php

/**
 * 用户自定义文档操作类
 * @date 2018/06/07
 * @author ztt
 */
namespace app\models;
use Yii;
use app\components\ActiveRecord;
use app\models\Helper;

class Userdefined extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%user_defined}}';
    }
    
    /**
     * 上传文档
     * @param string $title 标题
     * @param string $content 内容
     * @param int $from 来源：1=自创；2=文字扫描；3=分享下载
     * @return boolen true=上传成功
     */
    public function add($title = '', $content = '', $from = 1) {
        $loginUserId = Cache::hget('user_id');
        if(empty($title) && empty($content)) {
            return $this->addError('', '301:标题和内容不可都为空');
        }
        if(!in_array($from, Yii::$app->params['document']['document_from'])) {
            return $this->addError('', '300:暂无该创建方式');
        }
        if(mb_strlen($title) > Yii::$app->params['document']['title_max']) {
            return $this->addError('', '302:标题最多为' . Yii::$app->params['document']['title_max'] . '个字');
        }
        if(mb_strlen($content) > Yii::$app->params['document']['content_max']) {
            return $this->addError('', '302:内容最多为' . Yii::$app->params['document']['content_max'] . '个字');
        }
        $number = 0;
        if(empty($title)) {
            $number = Helper::getNumber() + 1;
            $title = Yii::$app->params['text']['new_document'] . $number;
        }
        $this->user_id = $loginUserId;
        $this->from = $from;
        $this->title = $title;
        $this->content = $content;
        $this->number = $number;
        try {
            if(!$this->save()) {
                return $this->addError('', '400:创建文档失败');
            }
        }catch(\Exception $e) {
            return $this->addError('', '500:' . $e->getMessage());
        }
        return $this->id;
    }
    
    /**
     * 编辑文档
     * @param int $id 自定义文档编号
     * @param string $title 标题
     * @param string $content 内容
     * @return boolen true=修改成功
     */
    public function edit($id, $title, $content) {
        $loginUserId = Cache::hget('user_id');
        if(!is_numeric($id)) {
            return $this->addError('', '300:参数格式有误');
        }
        if(empty($title) && empty($content)) {
            return $this->addError('', '301:标题和内容不可都为空');
        }
        if(mb_strlen($title) >= Yii::$app->params['document']['title_max']) {
            return $this->addError('', '302:标题最多为' . Yii::$app->params['document']['title_max'] . '个字');
        }
        if(mb_strlen($content) >= Yii::$app->params['document']['content_max']) {
            return $this->addError('', '302:内容最多为' . Yii::$app->params['document']['content_max'] . '个字');
        }
        $Record = $this->findOne($id);
        if(!$Record || $Record->user_id != $loginUserId) {
            return $this->addError('', '402:您要编辑的文档不存在或已被删除');
        }
        $number = 0;
        if(empty($title)) {
            if($Record->number > 0) {
                $number = $Record->number;
                $title = $Record->title;
            }else {
                $number = $this->getNumber() + 1;
                $title = Yii::$app->params['text']['new_document'] . $number;
            }
        }
        if($title == $Record->title && $content == $Record->content) {
            return true;
        }
        if($title != $Record->title) {
            $Record->title = $title;
        }
        if($content != $Record->content) {
            $Record->content = $content;
        }
        if($number != $Record->number) {
            $Record->number = $number;
        }
        if(!$Record->save()) {
            return $this->addError('', '400:保存修改失败，请稍后重试');
        }
        return true;
    }
    
    /**
     * 获取当前登录用户的自定义文档列表及总数
     * @param int $page 当前页码 1=第一页
     * @param int $pagesize 每页显示记录数
     * @return array 自定义文档列表结果集
     */
    public function getList($page = 1, $pagesize = 10) {
        $loginUserId = Cache::hget('user_id');
        if(!is_numeric($page) || !is_numeric($pagesize) || $page < 1 || $pagesize < 1) {
            return $this->addError('', '300:参数格式有误，请重试');
        }
        $select = "id, title, content, update_time";
        $params = ['user_id'=>$loginUserId];
        $result = $this->getListData($select, $page, $pagesize, $params);
        if($result['total'] == 0) {
            return $result;
        }
        foreach($result['rows'] as $key=>$value) {
            $result['rows'][$key]['update_time'] = date('Y-m-d', $value['update_time']);
        }
        Helper::removeN($result['rows']);
        return $result;
    }
    
    /**
     * 删除自定义文档
     * @param int $id 自定义文档ID
     * @return boolen true=删除成功
     */
    public function remove($id) {
        $loginUserId = Cache::hget('user_id');
        if(!is_numeric($id)) {
            return $this->addError('', '300:参数格式有误');
        }
        $Record = $this->findOne($id);
        if(!$Record || $Record->user_id != $loginUserId) {
            return $this->addError('', '402:您要编辑的文档不存在或已被删除');
        }
        if(!$Record->delete()) {
            return $this->addError('', '403:文档删除失败，请稍后重试');
        }
        return true;
    }
    
    /**
     * 获取自定义文档详情
     * @param int $id 文档ID
     * @param int $isShare 是否是分享的内容：1=是；-1=否
     * @return array 文档详情
     */
    public function getInfo($id, $isShare) {
        $loginUserId = Cache::hget('user_id');
        if(!is_numeric($id) || $id < 1) {
            return $this->addError('', '300:参数格式有误');
        }
        if($isShare != Yii::$app->params['document']['share_yes'] && $isShare != Yii::$app->params['document']['share_no']) {
            return $this->addError('', '300:参数格式有误');
        }
        $Record = $this->findOne($id);
        if(!$Record) {
            return $this->addError('', '402:您要查看的诗词尚未入库或已被删除');
        }
        if($Record->is_share == Yii::$app->params['document']['share_no'] && $Record->user_id != $loginUserId) {
            return $this->addError('', '404:您无法查看他人未分享的内容');
        }
        $result = [];
        $result['navbar_title'] = Yii::$app->params['text']['navbar_title_user_defined'];
        $content = stripcslashes($Record->content);
        $result['content'] = preg_replace('/(\\n)/', '۞', $content);
        $result['title'] = stripcslashes($Record->title);
        $result['is_share'] = $Record->is_share;
        return $result;
    }
    
}

