<?php
/**
 * 测试控制器类
 * @author ztt
 * @date 2018/03/12
 */

namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Cache;
use app\models\User;
use app\models\Rhesis;

class TestController extends Controller {
    public function actionA() {
        $url = 'https://poetry.leiyu.tv/spider/add';
        $data = [
            'data'=>'{
    "title":"渔歌子·西塞山前白鹭飞",
    "author":"张志和",
    "year":"唐代",
    "author_info":{
        "author_desc":"张志和（730年(庚午年)～810年？），字子同，初名龟龄，汉族，婺州（今浙江金华）人，自号“烟波钓徒”，又号“玄真子”。唐代著名道士、词人和诗人。十六岁参加科举，以明经擢第，授左金吾卫录事参军，唐肃宗赐名为“志和”。因事获罪贬南浦尉，不久赦还。自此看破红尘，浪迹江湖，隐居祁门赤山镇。其兄张鹤龄担心他遁世不归，在越州（今绍兴市）城东筑茅屋让他居住。史载唐肃宗曾赐他奴婢各一人，张志和让他们结婚，取名渔童和樵青。著有《玄真子》集。► 13篇诗文",
        "author_avatar":"https://img.gushiwen.org/authorImg/zhangzhihe.jpg"
    },
    "tag":[
        "宋词三百首",
        "小学古诗",
        "水乡",
        "写景",
        "写人",
        "抒怀"
    ],
    "content":"西塞山前白鹭飞，桃花流水鳜鱼肥。\n\n青箬笠，绿蓑衣，斜风细雨不须归。",
    "translationId":"1212",
    "translation":"西塞山前白鹭在自由地翱翔，江水中，肥美的鳜鱼欢快地游着，漂浮在水中的桃花是那样的鲜艳而饱满。\n江岸一位老翁戴着青色的箬笠，披着绿色的蓑衣，冒着斜风细雨，悠然自得地垂钓，他被美丽的春景迷住了，连下了雨都不回家。",
    "translation2":"译文<br></br>西塞山前白鹭在自由地翱翔，江水中，肥美的鳜鱼欢快地游着，漂浮在水中的桃花是那样的鲜艳而饱满。<br></br>江岸一位老翁戴着青色的箬笠，披着绿色的蓑衣，冒着斜风细雨，悠然自得地垂钓，他被美丽的春景迷住了，连下了雨都不回家。",
    "annotation":"渔歌子：词牌名。此调原为唐教坊名曲。分单调、双调二体。单调二十七字，平韵，以张氏此调最为著名。双调，五十字，仄韵。《渔歌子》又名《渔父》或《渔父乐》，大概是民间的渔歌。据《词林纪事》转引的记载说，张志和曾谒见湖州刺史颜真卿，因为船破旧了，请颜真卿帮助更换，并作《渔歌子》。词牌《渔歌子》即始于张志和写的《渔歌子》而得名。“子”即是“曲子”的简称。\n西塞山：浙江湖州。\n白鹭：一种白色的水鸟。\n桃花流水：桃花盛开的季节正是春水盛涨的时候，俗称桃花汛或桃花水。\n鳜（guì）鱼：淡水鱼，江南又称桂鱼，肉质鲜美。\n箬（ruò）笠：竹叶或竹蔑做的斗笠。\n蓑（suō）衣：用草或棕编制成的雨衣。\n不须：不一定要。",
    "annotation2":"注释<br></br>渔歌子：词牌名。此调原为唐教坊名曲。分单调、双调二体。单调二十七字，平韵，以张氏此调最为著名。双调，五十字，仄韵。《渔歌子》又名《渔父》或《渔父乐》，大概是民间的渔歌。据《词林纪事》转引的记载说，张志和曾谒见湖州刺史颜真卿，因为船破旧了，请颜真卿帮助更换，并作《渔歌子》。词牌《渔歌子》即始于张志和写的《渔歌子》而得名。“子”",
    "appreciationId":"1626",
    "appreciation":""
}'
        ];
        $curl = curl_init(); // 启动一个CURL会话      
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址              
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容      
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转      
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer      
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求      
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包      
//        curl_setopt($curl, CURLOPT_TIMEOUT, 150); // 设置超时限制防止死循环      
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回      
        
        $tmpInfo = curl_exec($curl); // 执行操作      
        if (curl_errno($curl)) {      
           echo 'Errno'.curl_error($curl);      
        }      
//        print_r(curl_getinfo($curl));die;
        curl_close($curl); // 关键CURL会话      
        return $tmpInfo; // 返回数据      
    }
    
    public function actionB() {
        $connomains = array(
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
            'https://dev.poetry.leiyu.tv/index.php?r=/user/sign&token=cde141b244292e4d2eae111f83df0dd8',
        );
        $mh = curl_multi_init();
        foreach ($connomains as $i => $url) {
             $conn[$i]=curl_init($url);
              curl_setopt($conn[$i],CURLOPT_RETURNTRANSFER,1);
              curl_multi_add_handle ($mh,$conn[$i]);
        }
        do { $n=curl_multi_exec($mh,$active); } while ($active);
        foreach ($connomains as $i => $url) {
              $res[$i]=curl_multi_getcontent($conn[$i]);
              curl_close($conn[$i]);
        }
        print_r($res);
        
    }
    
    public function actionC() {
        $options = [
            'hostname' => '127.0.0.1',
            'login'    => 'root',
            'password' => '',
            'port'     => 8983,
            'path'     => 'solr/poetry'
        ];
        
        $client = new \SolrClient($options);
        
        $query = new \SolrDisMaxQuery('title:李白 将进酒 君不见黄河之水天上来 or content:李白 将进酒 君不见黄河之水天上来 or author:李白 将进酒 君不见黄河之水天上来');
        $query
            ->addQueryField('title', 1)
            ->addQueryField('content', 0.6)
            ->addQueryField('author', 0.4);
//        echo $query;die;
//        $dismaxQuery->add
        
        
        

//        $query = new \SolrQuery();
//        
//        $query->setQuery('title:将进酒 君不见黄河之水天上来 or content:将进酒 君不见黄河之水天上来');

        $query->setStart(0);

        $query->setRows(10);

        $query->addField('id')->addField('title')->addField('content')->addField('author_name');

        $query_response = $client->query($query);

        $response = $query_response->getResponse();

        print_r($response['response']);die;
    }
    
    public function actionD() {
      phpinfo();
    }
    
    
}
