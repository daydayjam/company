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
use app\models\TagPoetry;
use app\models\Helper;
use app\models\Author;
use app\models\Collection;
use app\models\Rhesis;

class Poetry extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%poetry}}';
    }
    
    /**
     * 获取诗词详情
     * @param int $id 诗词ID
     * @return array 诗词详情
     */
    public function getInfo($id) {
        $loginUserId = Cache::hget('user_id') ? Cache::hget('user_id') : 0;
        if(!is_numeric($id) || $id < 1) {
            return $this->addError('', '300:参数格式有误');
        }
        $Record = $this->findOne($id);
        if(!$Record || $Record->status == Yii::$app->params['code']['poetry_delete']) {
            return $this->addError('', '402:您要查看的诗词尚未入库或已被删除');
        }
        $isCollect = 0;
        $Collection = new Collection();
        $CollectionRecord = $Collection->findByCondition(['user_id'=>$loginUserId, 'poetry_id'=>$id, 'status'=>Yii::$app->params['code']['collect_yes']])->one();
        if($CollectionRecord) {
            $isCollect = 1;
        }
        $result = [];
        $result['id'] = $id;
        $result['navbar_title'] = Yii::$app->params['text']['navbar_title_poetry'];
        $result['title'] = $Record->title;
        $result['is_collect'] = $isCollect;
        $content = preg_replace('/(\()|(（)/', '۞(', preg_replace('/(\))|(）)/', ')۞', $Record->content));
        $result['content'] = preg_replace('/(\)۞。)/', ')。', $content);
        $result['translation'] = $Record->translation;
        $result['annotation'] = $Record->annotation;
        $result['appreciation'] = $Record->appreciation;
        $result['year'] = $Record->year;
        $result['author'] = $Record->author_name;
        $result['author_id'] = $Record->author_id;
        Helper::removeDai($result);
        Helper::addEmptyAuthorItem($result);
        Helper::mergeIsReciteToItem($result);
        Helper::mergeRecommendToItem($result);
        return $result;
    }
    
    /**
     * 获取搜索首页诗词列表
     * @param string $keyword 搜索框输入的词
     * @return array 诗词列表信息
     */
    public function getSearchIndexList($keyword) {
        if(empty($keyword)) {
            return $this->addError('', '300:搜索关键词不可为空');
        }
        $Author = new Author();
        $authorList = $Author->getList($keyword);
        $Tag = new Tag();
        $tagList = $Tag->getList(Yii::$app->params['document']['poetry_tag_from'], $keyword);
        $result = [
            'author' => $authorList['rows'],
            'tag'    => $tagList,
            'other'  => $this->getListByTitleOrContent($keyword, 1, 10)
        ];
        return $result;  
    }
    
    /**
     * 获取列表信息
     * @param int $searchBy 查询类别：1=searchByTag;2=searchByAuthor;3=searchByOther
     * @param string $keyword 关键字
     * @param int $relId 查询的标签ID
     * @param int $page 当前页码
     * @param int $pagesize 每页显示记录数
     * @return array 列表数组
     */
    public function getSearchList($searchBy = 1, $keyword = '', $relId = 0, $page = 1, $pagesize = 10) {
        if(!is_numeric($page) || !is_numeric($pagesize) || $page < 1 || $pagesize < 1) {
            return $this->addError('', '300:参数格式有误，请重试');
        }
//        $keyword = explode(' ', $keyword);
//        $keyword = Tool::scwsWord($keyword);
        switch ($searchBy) {
            case 1:
            case '':
                if(empty($relId)) {
                    return $this->addError('', '301:请选择您要查询的标签');
                }
                if(!is_numeric($relId)) {
                    return $this->addError('', '300:参数格式有误');
                }
                $result = $this->getListByTag($relId, $page, $pagesize);
                break;
            case 2:
                if(empty($relId)) {
                    return $this->addError('', '301:请选择您要查询的作者');
                }
                $result = $this->getListByAuthor($relId, $page, $pagesize);
                break;
            case 3:
                if(empty($keyword)) {
                    return $this->addError('', '301:请输入关键词');
                }
                $result = $this->getListByTitleOrContent($keyword, $page, $pagesize);
                break;
            default:
                return $this->addError('', '300:参数格式有误');
        }
        return $result;
    }
    
    /**
     * 获取诗词列表，根据标题和内容
     * @param string $keyword 搜索框输入的词，关键字，可以是标题、内容、诗人、年代
     * @param int $page 当前页码
     * @param int $pagesize 每页显示记录数
     * @return type
     */
//    public function getListByTitleOrContent($keyword = [], $page = 1, $pagesize = 10) {
//        $find = ['author.name', 'poetry.title',  'poetry.content'];
//        $weight = [4, 2, 1];
//        // sign(LOCATE('中国',a.`title`))+sign(LOCATE('北京',a.`title`))+sign(LOCATE('天安门',a.`title`)) as 匹配数
//        $score = [];
//        for($i=0; $i<count($find); $i++) {
//            for($n=0; $n<count($keyword); $n++) {
//                $score[] = 'if(LOCATE("' . $keyword[$n] . '",' . $find[$i] . '), ' . $weight[$i] . ', 0)';
//            }
//        }
//        $score = '(' . implode('+', $score) . ') as score';
//        
//        
//        $query = (new Query())->select(['poetry.id', 'poetry.title', 'poetry.content', 'poetry.author_name as author', 'poetry.year', $score])
//                        ->from($this->tableName() . ' as poetry')
//                        ->leftJoin(Author::tableName() . ' as author', 'author.id = poetry.author_id')
//                        ->leftJoin(TagPoetry::tableName() . ' as tag_poetry', 'tag_poetry.poetry_id = poetry.id')
//                        ->leftJoin(Tag::tableName() . ' as tag', 'tag.id = tag_poetry.tag_id')
//                        ->where(['poetry.status'=>Yii::$app->params['code']['poetry_normal']]);
//     
//        // $sql = "MATCH(poetry.title_space,poetry.content_space,poetry.author_name) AGAINST ('".implode(' ', $keyword)."' IN BOOLEAN MODE)";
//        $sql = "MATCH(poetry.title_space,poetry.content_space) AGAINST ('".implode(' ', $keyword)."' IN BOOLEAN MODE) or MATCH(author.name) AGAINST ('".implode(' ', $keyword)."' IN BOOLEAN MODE)";
//
//        $query->andWhere($sql);
// 
//        $query->groupBy('poetry.title,poetry.year');
//        $total = $query->count();
//        $rows = $query->orderBy('score desc,tag.listorder, poetry.id')
//                        ->limit($pagesize)
//                        ->offset(($page-1)*$pagesize)
////                ->createCommand()->sql;
////        echo $rows;die;
//                        ->all();
//        Helper::removeN($rows);
//        Helper::removeListDai($rows);
//        Helper::addEmptyAuthorList($rows);
//        $result = [
//            'total' => $total,
//            'rows'  => $rows
//        ];
//        return $result;
//    }
    
    public function getListByTitleOrContent($keyword, $page = 1, $pagesize = 10) {
        $options = [
            'hostname' => Yii::$app->params['solr']['HOSTNAME'],
            'login'    => Yii::$app->params['solr']['LOGIN'],
            'password' => Yii::$app->params['solr']['PASSWORD'],
            'port'     => Yii::$app->params['solr']['PORT'],
            'path'     => Yii::$app->params['solr']['PATH']
        ];
        $client = new \SolrClient($options);
        $query = new \SolrDisMaxQuery();
        
        $query->setPhraseFields('content^100')
              ->setMinimumMatch('100%')
              ->addQueryField('searchText');
//              ->addQueryField('title', 0.3)
//              ->addQueryField('author', 0.1);
        
        $query->setQuery($keyword);
        $query->setStart(($page-1)*$pagesize);
        $query->setRows($pagesize);
        $query->addField('id')->addField('title')->addField('content')->addField('year')->addField('author');
        $query_response = $client->query($query);
        $response = $query_response->getResponse();
        $response = $response['response'];
        $rows = $response['docs'];
        Helper::removeN($rows);
        Helper::removeListDai($rows);
        Helper::addEmptyAuthorList($rows);
        $result = [
            'total' => $response['numFound'],
            'rows'  => $rows
        ];
        return $result;
    }
    
    /**
     * 获取诗词列表，根据作者
     * @param string $authorId 诗人名字
     * @param int $page 当前页码
     * @param int $pagesize 每页显示记录数
     * @return type
     */
    public function getListByAuthor($authorId, $page = 1, $pagesize = 10) {
        $Author = new Author();
        $authorInfo = $Author->getInfo($authorId);
        if(!$authorInfo) {
            $error = $Author->getCodeError();
            return $this->addError('', $error['code'] . ':' . $error['msg']);
        }
        $query = (new Query())->select(['poetry.id', 'poetry.title', 'poetry.content', 'poetry.author_name as author', 'poetry.year'])
                        ->from($this->tableName() . ' as poetry')
                        ->leftJoin(Author::tableName() . ' as author', 'author.id = poetry.author_id')
                        ->leftJoin(TagPoetry::tableName() . ' as tag_poetry', 'tag_poetry.poetry_id = poetry.id')
                        ->leftJoin(Tag::tableName() . ' as tag', 'tag.id = tag_poetry.tag_id')
                        ->where(['status'=>Yii::$app->params['code']['poetry_normal'], 'poetry.author_id'=>$authorId]);
        $total = $query->count();
        $rows = $query->groupBy('poetry.title,poetry.year')
                        ->orderBy('tag.listorder, poetry.id')
                        ->limit($pagesize)
                        ->offset(($page-1)*$pagesize)
                        ->all();
        Helper::removeN($rows);
        Helper::removeListDai($rows);
        Helper::addEmptyAuthorList($rows);
        $result = [
            'total'       => $total,
            'rows'        => $rows,
            'author_info' => $authorInfo
        ];
        return $result;
    }
    
    /**
     * 获取诗词列表，根据标签
     * @param int $tagId 标签ID
     * @param int $page 当前页码
     * @param int $pagesize 每页显示记录数
     * @return type
     */
    public function getListByTag($tagId, $page = 1, $pagesize = 10) {
        $query = (new Query())->select(['poetry.id', 'poetry.title', 'poetry.content', 'poetry.author_name as author', 'poetry.year'])
                        ->from($this->tableName() . ' as poetry')
                        ->leftJoin(Author::tableName() . ' as author', 'author.id = poetry.author_id')
                        ->leftJoin(TagPoetry::tableName() . ' as tag_poetry', 'tag_poetry.poetry_id = poetry.id')
                        ->leftJoin(Tag::tableName() . ' as tag', 'tag.id = tag_poetry.tag_id');
        $params = ['poetry.status'=>Yii::$app->params['code']['poetry_normal']];
        if(!empty($tagId)) {
            $params['tag_poetry.tag_id'] = $tagId;
        }
        $query->where($params);
        $total = $query->count();
        $rows = $query->groupBy('poetry.title,poetry.year')
                        ->orderBy('tag.listorder, poetry.id')
                        ->limit($pagesize)
                        ->offset(($page-1)*$pagesize)
                        ->all();
        Helper::removeN($rows);
        Helper::removeListDai($rows);
        Helper::addEmptyAuthorList($rows);
        $Collection = new Collection();
        $Tag = new Tag();        
        $result = [
            'total'         => $total,
            'collect_total' => $Collection->getTotal() ? $Collection->getTotal() : 0,     // 收藏是次数
            'reciting_total' => (new Recite())->getTotal() ? (new Recite())->getTotal() : 0,  //在背诗词数
            'recited_total'  => (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited']) ? (new Recite())->getTotal(Yii::$app->params['code']['recite_status']['recited']) : 0, //已背诗词数
            'rows'          => $rows,   
            'tag_changed'   => $Tag->getLastTime(),  //标签最后新增or修改时间
            'daily_rhesis'  => (new Rhesis())->getDailyInfo()
               
        ];
        return $result;
    }
    
    /**
     * 获取诗词字数，除去标点及注释
     * @param int $poetryId 诗词ID
     * return int 诗词字数
     */
    public function getWordNum($poetryId) {
        $Record = $this->findOne($poetryId);
        if(!$Record) {
            return 0;
        }
        return mb_strlen(Tool::trimMarkAndAnno($Record->content));
    }
}

