<?php
/**
 * 影视剧模型类
 * @author ztt
 * @date 2017/11/10
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\Tool;
use app\models\FilmSourceSpider;

class FilmSpider extends ActiveRecord {
    public static $cover = '/system/movie_nopic@3x.png';    //无封面时显示的默认图片
    public static $flag = [1=>'综艺', 2=>'动画'];   //影视剧标签：1=综艺；2=动画
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%film}}';
    }
    
//    public static function getDb() {
//        return Yii::$app->get('db2');
//    }
    
    /**
     * 影视剧入库
     * @param type $cmdCode
     * @param type $sign
     * @param type $url
     * @param type $data
     * @return boolean
     */
    public function add($data) {
        $data = json_decode($data, true);
        if(empty($data)) {
           die(); 
        }
        // 无双影视
        if(empty($data['main_actor']) && empty($data['director']) && empty($data['area']) && empty($data['summary']) && empty($data['year'])) {
            return false;
        }
        if(empty($data['year'])) {
            return false;
        }
        $resource = $data['resource'];  //来源
        $params = [];
        $params['title'] = $this->dealTitle($data['title']);
        $params['title_no_mark'] = Tool::trimMark($params['title']);
        $params['kind'] = $data['kind'];
        $params['type'] = $data['kind'] == 1 ? 1 : 2;
        $params['kind_extra'] = 0;
        
        if(count($data['main_actor']) == 1) {
            $mainActor = array_filter(preg_split('/,|((&#8194;))|\//', $data['main_actor'][0]));
            if(count($mainActor) > 1) {
                $params['main_actor'] = implode('/', $mainActor);
            }else {
                $params['main_actor'] = $data['main_actor'][0];
            }
        }else {
            $params['main_actor'] = implode('/', $data['main_actor']);
        }
        
        
        foreach($data['director'] as $key=>$director) {
            $data['director'][$key] = preg_replace('/(&#8194;)/', '', $director);
        }
        $params['director'] = implode('/', $data['director']);
        $params['cover'] = $data['cover'];
        
        $area = $data['area'];
        $areaArr = Tool::splitByMark($area);
        
        
        $data['area'] = $areaArr ? ($areaArr[0] == '内地' || $area[0] == '中国大陆' ? '大陆' : $areaArr[0]) : '';

        $params['area'] =  $data['area'];
        $params['year'] = preg_match('/\d{4}/', $data['year']) ? $data['year'] : 0;
        $params['summary'] = $data['summary'];
        
        $genreArr = $data['genre'];
        foreach($genreArr as $key=>$item) {
            $item = str_replace('片', '', $item);
            $item = str_replace('动漫', '', $item);
            $item = str_replace('综艺', '', $item);
            if($item != '喜剧' && $item != '剧情' && $item != '网剧' && $item != '悲剧') {
                $item = str_replace('剧', '', $item);
            }
            
            $genreArr[$key] = $item;
        }
        $params['genre'] = implode('/', $genreArr);
        
        $class = $data['kind'];
        $classExtra = 0;
        
        if($data['kind'] == 4) { // 如果是动漫，判断电影中是否已存在，存在则设置type=1,否则为2
	try {
            $OldFilmRecord = $this->findByCondition(['kind'=>1, 'title_no_mark'=>$params['title_no_mark'], 'year'=>$params['year']])->one();
	} catch(\Exception $e) {
	    return true;
	}
            if($OldFilmRecord) {
                $params['kind'] = 1;
                $params['kind_extra'] = 4;
                $params['type'] = 1;
                $class = 1;
                $classExtra = 4;
            }else {
                $params['kind'] = 4;
                $params['kind_extra'] = 0;
                $class = 4;
                $classExtra = 0;
                $params['type'] = 2;
            }
        }else if($data['kind'] == 1) {
	try {
	  $OldFilmRecord = $this->findByCondition(['kind'=>4, 'title_no_mark'=>$params['title_no_mark'], 'year'=>$params['year']])->one();
	}catch(\Exception $e) {
	   return true;
	}
            if($OldFilmRecord) {
                $params['kind'] = 4;
                $params['kind_extra'] = 1;
                $params['type'] = 1;
                $class = 1;
                $classExtra = 1;
            }else {
                $params['kind'] = 1;
                $params['kind_extra'] = 0;
                $class = 1;
                $classExtra = 0;
                $params['type'] = 1;
            }

	}

        try{
        $OldFilmRecord = $this->findByCondition(['kind'=>$class, 'kind_extra'=>$classExtra, 'title_no_mark'=>$params['title_no_mark'], 'year'=>$params['year']])->one();
	}catch(\Exception $e) {
	    return true;
	}
        $oldEpUpdate = 0;
        if($OldFilmRecord) {
            $oldEpUpdate = $OldFilmRecord->episode_number;
        }
	try {
        $filmId = $this->addFilm($params);
	}catch(\Exception $e) {
	    return true;
	}
        if($filmId) {
            // 整合播放线路和播放源
            $playArr = array_combine($data['play_route'], $data['play_source']);
            $newPlayArr = [];
            foreach($playArr as $key=>$item) {
                $newPlayArr[$key] = $item['way'];
            }
            if($data['kind'] == 1) {
                $this->film($newPlayArr);
            }
            $FilmSource = new FilmSourceSpider();
            if($newPlayArr) {
		try{
                $FilmSource->add($filmId, $newPlayArr, $resource);
		}catch(\Exception $e) {
		    return true;
		}
            }
            if($params['type'] == 2) {
                $updateEp = $this->getUpdateEp($newPlayArr);
            }else {
                $updateEp = 1;
            }
            $params['episode_number'] = $updateEp;
            // 更新片源信息更新状态
            try{
            $this->updateProgress($filmId, $oldEpUpdate, $params);
	    }catch(\Exception $e) {
		return true;
		}	
        }
        return true;
    }
    
    /**
     * 
     * @param type $filmId
     * @param type $epUpdate
     * @param type $type
     */
    public function updateProgress($filmId, $oldUpdate, $params) {
        $epUpdate = $params['episode_number'];
        $type = $params['type'];
        $epToday = '';
        $epUpdateNum = 0;
        $FilmRecord = $this->findOne($filmId);
        if($FilmRecord->type == 1) { // 电影
            $FilmSource = new FilmSourceSpider();
            $oldSql = 'select * from ' . $FilmSource->tableName() . ' where film_id=:film_id and unix_timestamp(create_time) < unix_timestamp(DATE_FORMAT(NOW(),"%Y-%m-%d"))';
            $OldFilmSourceRecord = $FilmSource->findBySql($oldSql, [':film_id'=>$filmId])->one();
            if($OldFilmSourceRecord) {
                $newSql = 'select * from ' . $FilmSource->tableName() . ' where film_id=:film_id and unix_timestamp(create_time) > unix_timestamp(DATE_FORMAT(NOW(),"%Y-%m-%d"))';
                $newFilmSourceRecords = $FilmSource->findBySql($newSql, [':film_id'=>$filmId])->all();                
                $epUpdate = 1;
                foreach($newFilmSourceRecords as $SourceRecord) {
                    $epToday .= $SourceRecord->route_id . '/';
                }
            }
        }else { // 电视剧
            if($oldUpdate !=0 && $oldUpdate != $epUpdate && $epUpdate > $oldUpdate) {
                $epUpdateNum = $epUpdate - $oldUpdate;
                for($i = 1; $i <= $epUpdateNum; $i ++) {
                    $epToday .= ($oldUpdate + $i) . '/';
                }
            }
        }
        if($epUpdate > $FilmRecord->episode_number || $epToday) {
            $FilmRecord->episode_number = $epUpdate;
            if($FilmRecord->is_hot == 1) {
                $result = [];
                $result['id'] = $FilmRecord->id;
                $result['kind'] = $FilmRecord->kind;
                $result['title'] = $FilmRecord->title;
                $result['cover'] = $FilmRecord->cover;
                $result['year'] = $FilmRecord->year;
                $result['tag'] = $FilmRecord->genre;
                $result['main_actor'] = $FilmRecord->main_actor;
                $result['episode_number'] = $epUpdate;
                $result['type'] = $FilmRecord->type;
                $result['update_time'] = date('Y-m-d H:i:s');
                $orderBy = $FilmRecord->year + strtotime($FilmRecord->update_time);
                $CacheKey = 'FILM_HOT';
                Yii::$app->redis->zadd($CacheKey . $FilmRecord->kind, $orderBy, $FilmRecord->id);
                Yii::$app->redis->hset($CacheKey . $FilmRecord->kind . '_CONTENT', $FilmRecord->id, json_encode($result));
            }
            if($FilmRecord->type == 1 || ($FilmRecord->type == 2 && $epUpdateNum < 10)) {
                $FilmRecord->episode_today = $epToday ? rtrim($epToday, '/') : $epToday;
            }
            return $FilmRecord->save();
        }
        
    }
    
    /**
     * 连续剧和动漫
     * @param type $data
     */
    public function tvAndAnimation(&$data) {
        foreach($data as $key1=>$arr) {
            foreach($arr as $key2=>$value) {
                // 去除预告片
                if(strpos($value['num'], '预告') > -1) {    
                    unset($data[$key1][$key2]);
                }
            }
            $arr = array_values($arr);
            foreach($arr as $key3=>$item) {
                // 提取纯数字为剧集编号
                if(count($arr) == 1) {
                    $data[$key1][$key3]['num'] = 1;
                }else {
                    $data[$key1][$key3]['num'] = $key3+1;
                }
                $data[$key1][$key3]['title'] = $item['num'];
            }
        }
    }
    
    /**
     * 电影
     * @param type $data
     */
    public function film(&$data) {
        $newData = [];
        foreach($data as $key1=>$arr) {
            if(count($arr) > 1) {
                $arr[0]['num'] = 1;
                $newData[$key1][0] = $arr[0];
                break;
            }else {
                $data[$key1][0]['num'] = 1;
            }
        }
        if($newData) {
            $data = $newData;
        }
    }
    
    /**
     * 综艺
     * @param type $data
     */
    public function variety(&$data) {
        foreach($data as $key1=>$arr) {
            foreach($arr as $key2=>$value) {
                $data[$key1][$key2]['num'] = $key2 + 1;
                $data[$key1][$key2]['title'] = $value['num'];
            }
        }
    }
    
    /**
    * 
    * @param type $params
    * @return type
    */
   public function addFilm($params){
       // 如果年代没有，判断去掉标点符号的名称一致则判断演员是否有同样的，有的认为是同一部，没有则不认为是同一部
       if(!preg_match('/\d{4}/', $params['year'])) {
           $Record = $this->findByCondition(['title_no_mark'=>$params['title_no_mark']])->one();
           if($Record) {
               $oldMainActor = explode('/', $Record->main_actor);
               $mainActor = explode('/', $Record->main_actor);
               foreach($mainActor as $key=>$value) {
                   if(in_array($value, $oldMainActor)) {
                       return $Record->id;
                   }
               }
           }
       }
//	if($params['kind'] == 4) {// 如果是动漫，这查看电影里面有没有相同的
//	  $Record = $this->findByCondition(['kind'=>1, 'title_no_mark'=>$params['title_no_mark'], 'year'=>$params['year']])->one();
  //	  if($Record) {
//	    $Record->kind_extra = 4;
//	    $Record->save();
//	    return $Record->id;
//	  }
//	}
//	if($params['kind'] == 1) {// 如果是动漫，这查看电影里面有没有相同的
  //        $Record = $this->findByCondition(['kind'=>4, 'title_no_mark'=>$params['title_no_mark'], 'year'=>$params['year']])->one();
    //      if($Record) {
      //      $Record->kind_extra = 1;
        //    $Record->save();
          //  return $Record->id;
         // }
       // }

        $sql = 'insert into ' . $this->tableName() 
                . '(`type`, `kind`, `title`,`title_no_mark`,`cover`,`director`,`main_actor`,`area`,`year`,`genre`,`summary`,`kind_extra`,`create_time`) '
                . 'values'
                . '(:type, :kind, :title, :title_no_mark, :cover, :director, :main_actor, :area, :year, :genre, :summary, :kind_extra, now()) '
                . 'on duplicate key update kind=values(kind),cover=if(cover="",values(cover),cover),director=if(director="",values(director),director),main_actor=if(main_actor="",values(main_actor),main_actor),area=if(area="",values(area),area),'
                . 'year=if(year="",values(year),year),genre=if(genre="",values(genre),genre),summary=if(summary="",values(summary),summary),kind_extra=values(kind_extra),type=values(type)';
        $binds = [];
        foreach($params as $key=>$item) {
            $binds[':' . $key] = $item;
        }
        $conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql, $binds);
        if($cmd->execute()) {
            $filmId = Yii::$app->db->getLastInsertID();
            return $filmId;
        }else {
            $Record = $this->findByCondition(['kind'=>$params['kind'], 'title'=>$params['title_no_mark'], 'year'=>$params['year']])->one();
            if(!$Record) {
               return false;
            }
            return $Record->id;
        }
    }
    
//    public function addToFilm($params) {
//        $Record = $this->findByCondition(['kind'=>$params['kind'], 'title'=>$params['title'], 'year'=>$params['year']])->one();
//        if($Record) {
//            $mainActor = $params['main_actor'];
//            $oldMainActor = explode('/', $Record->main_actor);
//            $isExist = false;
//            foreach($mainActor as $actor) {
//                if(in_array($actor, $oldMainActor)) {
//                    $isExist = true;
//                    break;
//                }
//            }
//        }
//    }
    
    /**
     * 获取最新集数
     * @param array $playSource 
     * @return int 
     */
    public function getUpdateEp($playSource) {
//        echo $title;die;
        $max = 0;
        foreach($playSource as $item) {
            $currCount = count($item);
            if($currCount > $max) {
                $max = $currCount;
            } 
        }
        return $max;
    }
    
    /**
     * 处理标题
     * @param type $title
     */
    public function dealTitle($title) {
        $title = Tool::trimAll($title);
        $keyword = [
            '动画版',
            '普通话','普通话版','普通版',
            '闽南语版','闽南语',
            '粤语','粤语版','粵语',
            '日语','日语版','日剧',
            '英语版','国语+英语','英语中字','英文版',
            '国语中字','国语','.国英双语','国英双语','国语林峰版',
            '韩语版', '韩剧','韩版','韩国版',
            '泰语','泰剧国语版','泰剧',
            '多语言版',
            '台语版','台剧','台湾剧',
            '微电影','网剧',
            '1080P','720P','1080p','720p',
            '电视剧','电影',
            '定制版','未删减版',
            'DVD版','DVD',
            '大陆版',
	    '中国版',
	    '完整版','奥斯卡版','内地版'
        ];
        // 去/前面的名字
        $title = explode('/', $title);
        $title = $title[0];
        // 去除[]
        $title = preg_replace('/(\[((?!上|下).)*\])/', '', $title);
        // 去除中文括号（）
        $title = preg_replace('/（(?!上|下).*）/', '', $title);
        // 去除英文括号
        $title = preg_replace('/\((?!上|下).*\)/', '', $title);
        // 去除上述keyword中有的词
        foreach($keyword as $item) {
            if(($index = mb_strpos($title, '-'.$item)) > 0) {
                $title = mb_substr($title, 0, $index);
                break;
            }
            if(($index = mb_strpos($title, $item)) > 0) {
                $title = mb_substr($title, 0, $index);
                break;
            }
        }
        // 去除跟的年份版
        $title = preg_replace('/(\d{2,4}版)$/', '', $title);
        // 去除最后跟的年份
        $title = !preg_match('/^\d{4}$/', $title) ? preg_replace('/(\d{4})$/', '', $title) : $title;
        // 再去除上述keyword中有的词，防止位置颠倒
        foreach($keyword as $item) {
            if(($index = mb_strpos($title, '-'.$item)) > 0) {
                $title = mb_substr($title, 0, $index);
                break;
            }
            if(($index = mb_strpos($title, $item)) > 0) {
                $title = mb_substr($title, 0, $index);
                break;
            }
        }
        if(preg_match('/(\d{4})第((零|一|二|三|四|五|六|七|八|九|十)?(百|十|零)?(一|二|三|四|五|六|七|八|九)?(百|十|零)?(一|二|三|四|五|六|七|八|九))(季|部|章)$/', $title, $match)) {
            $title = str_replace($match[1], '', $title);
            if($match[8] != '章') {
                $title = mb_substr($title, 0, mb_strlen($title)-1) . '季';
            }
        }
        
        if(preg_match('/(\d{4})(\[(上|下)\]|（(上|下)）|\((上|下)\))$/', $title, $match)) {
            $title = str_replace($match[1], '', $title);
        }
        
        // 将第几季前面加空格
        if(preg_match('/第((零|一|二|三|四|五|六|七|八|九|十)?(百|十|零)?(一|二|三|四|五|六|七|八|九)?(百|十|零)?(一|二|三|四|五|六|七|八|九))(季|部|章)$/', $title, $match)) {
            $index = mb_strpos($title, $match[0]);
            $title = mb_substr($title, 0, $index) . ' ' . $match[0];
            if($match[7] != '章') {
                $title = mb_substr($title, 0, mb_strlen($title)-1) . '季';
            }
        }
        // 将数字的第几季变成中文的第几季
        if(preg_match('/(第)?(\d+|Ⅰ|Ⅱ|II|Ⅲ|III|Ⅳ|Ⅴ|Ⅵ|Ⅶ|Ⅷ|Ⅸ|Ⅹ|Ⅺ|Ⅻ)(季|部)?$/', $title, $match)) {
            $index = $match[1] ? mb_strpos($title, $match[1]) : mb_strpos($title, $match[2]);
            if($match[2]=='Ⅰ' || $match[2] == 1) {
                $title = mb_substr($title, 0, $index) . ' 第一季';
            }else if($match[2]=='Ⅱ' || $match[2]=='II' || $match[2] == 2) {
                $title = mb_substr($title, 0, $index) . ' 第二季';
            }else if($match[2]=='Ⅲ' || $match[2]=='III' || $match[2] == 3) {
                $title = mb_substr($title, 0, $index) . ' 第三季';
            }else if($match[2]=='Ⅳ' || $match[2] == 4) {
                $title = mb_substr($title, 0, $index) . ' 第四季';
            }else if($match[2]=='Ⅴ' || $match[2] == 5) {
                $title = mb_substr($title, 0, $index) . ' 第五季';
            }else if($match[2]=='Ⅵ' || $match[2] == 6) {
                $title = mb_substr($title, 0, $index) . ' 第六季';
            }else if($match[2]=='Ⅶ' || $match[2] == 7) {
                $title = mb_substr($title, 0, $index) . ' 第七季';
            }else if($match[2]=='Ⅷ' || $match[2] == 8) {
                $title = mb_substr($title, 0, $index) . ' 第八季';
            }else if($match[2]=='Ⅸ' || $match[2] == 9) {
                $title = mb_substr($title, 0, $index) . ' 第九季';
            }else if($match[2]=='Ⅹ' || $match[2] == 10) {
                $title = mb_substr($title, 0, $index) . ' 第十季';
            }else if($match[2]=='Ⅺ' || $match[2] == 11) {
                $title = mb_substr($title, 0, $index) . ' 第十一季';
            }else if($match[2]=='Ⅻ' || $match[2] == 12) {
                $title = mb_substr($title, 0, $index) . ' 第十二季';
            }
        }
        // 去除第一季
        $title = str_replace(' 第一季', '', $title);
        // 将番外前面加空格
        if(preg_match('/番外$/', $title, $match)) {
            $index = mb_strpos($title, $match[0]);
            $title = mb_substr($title, 0, $index) . ' ' . $match[0];
        }
        $title = str_replace('最终章', ' 最终章', $title);
        // 这样的格式 "武当-前章"改为"武当 前章"
        $title = str_replace('-', ' ', $title);
        return $title;
    }
    
    
}


