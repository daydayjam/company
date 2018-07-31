<?php
/**
 * 测试控制器类
 * @author ztt
 * @date 2017/10/30
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Black;
use app\models\CommentT;
use app\models\FilmSpider;

class TestController extends Controller {
    public function actionA() {
        $connomains = array(
        'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
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
        $url = 'http://yytest.leiyu.tv/spider/savefilm';
        $data = [
            'data'=>'{
    "resource":"100视频",
    "title":"两天一夜2017",
    "kind":"3",
    "status":"",
    "main_actor":[
        "姜虎东&#8194;金C&#8194;殷志源&#8194;MC梦&#8194;"
    ],
    "director":[
        "罗英锡&#8194;"
    ],
    "genre":[
        "综艺娱乐"
    ],
    "cover":"https://img.100sp.com/pic/uploadimg/2017-9/20179420261455255.jpg",
    "area":"",
    "year":"2017",
    "summary":"&nbsp;时隔5年，人气MC姜虎东回归KBS，担任名为《Happy Sunday - 准备好了》韩国新综艺节目的主持人，此节目由七名韩国艺人出演，其中殷志源、卢洪哲、金钟民、李秀根、池尚烈为固定嘉宾，由节目组提出挑战，艺人和MC共同完成任务。后2007年8月5日正式更名播出2天1夜，以体验野生为宗旨的娱乐节目，但是由于池尚烈拍摄电视剧离开由金C进入顶替，卢洪哲的因故离开李胜基顶替，金钟民服兵役MC梦进入顶替，迎来了全新的两天一夜正式出发！在同时段节目中2天1夜的收视率一直居高不下，甚至在09年1月11日创下收视记录！",
    "play_route":[
        "云播",
        "m3u8"
    ],
    "play_source":[
        {
            "way":[
                {
                    "title":"20170903",
                    "num":"1",
                    "url":"https://m.100sp.com/bofang/46923-0-0.html"
                },
                {
                    "title":"20170910",
                    "num":"2",
                    "url":"https://m.100sp.com/bofang/46923-0-1.html"
                },
                {
                    "title":"20170917",
                    "num":"3",
                    "url":"https://m.100sp.com/bofang/46923-0-2.html"
                },
                {
                    "title":"20170924",
                    "num":"4",
                    "url":"https://m.100sp.com/bofang/46923-0-3.html"
                },
                {
                    "title":"20171001",
                    "num":"5",
                    "url":"https://m.100sp.com/bofang/46923-0-4.html"
                },
                {
                    "title":"20171008",
                    "num":"6",
                    "url":"https://m.100sp.com/bofang/46923-0-5.html"
                },
                {
                    "title":"20171015",
                    "num":"7",
                    "url":"https://m.100sp.com/bofang/46923-0-6.html"
                },
                {
                    "title":"20171022",
                    "num":"8",
                    "url":"https://m.100sp.com/bofang/46923-0-7.html"
                },
                {
                    "title":"20171029",
                    "num":"9",
                    "url":"https://m.100sp.com/bofang/46923-0-8.html"
                },
                {
                    "title":"20171105",
                    "num":"10",
                    "url":"https://m.100sp.com/bofang/46923-0-9.html"
                },
                {
                    "title":"20171112",
                    "num":"11",
                    "url":"https://m.100sp.com/bofang/46923-0-10.html"
                },
                {
                    "title":"20171119",
                    "num":"12",
                    "url":"https://m.100sp.com/bofang/46923-0-11.html"
                },
                {
                    "title":"20171126",
                    "num":"13",
                    "url":"https://m.100sp.com/bofang/46923-0-12.html"
                },
                {
                    "title":"20171203",
                    "num":"14",
                    "url":"https://m.100sp.com/bofang/46923-0-13.html"
                },
                {
                    "title":"20171210",
                    "num":"15",
                    "url":"https://m.100sp.com/bofang/46923-0-14.html"
                },
                {
                    "title":"20171217",
                    "num":"16",
                    "url":"https://m.100sp.com/bofang/46923-0-15.html"
                },
                {
                    "title":"20171224",
                    "num":"17",
                    "url":"https://m.100sp.com/bofang/46923-0-16.html"
                },
                {
                    "title":"20171231",
                    "num":"18",
                    "url":"https://m.100sp.com/bofang/46923-0-17.html"
                }
            ]
        },
        {
            "way":[
                {
                    "title":"20170903",
                    "num":"1",
                    "url":"https://m.100sp.com/bofang/46923-1-0.html"
                },
                {
                    "title":"20170910",
                    "num":"2",
                    "url":"https://m.100sp.com/bofang/46923-1-1.html"
                },
                {
                    "title":"20170917",
                    "num":"3",
                    "url":"https://m.100sp.com/bofang/46923-1-2.html"
                },
                {
                    "title":"20170924",
                    "num":"4",
                    "url":"https://m.100sp.com/bofang/46923-1-3.html"
                },
                {
                    "title":"20171001",
                    "num":"5",
                    "url":"https://m.100sp.com/bofang/46923-1-4.html"
                },
                {
                    "title":"20171008",
                    "num":"6",
                    "url":"https://m.100sp.com/bofang/46923-1-5.html"
                },
                {
                    "title":"20171015",
                    "num":"7",
                    "url":"https://m.100sp.com/bofang/46923-1-6.html"
                },
                {
                    "title":"20171022",
                    "num":"8",
                    "url":"https://m.100sp.com/bofang/46923-1-7.html"
                },
                {
                    "title":"20171029",
                    "num":"9",
                    "url":"https://m.100sp.com/bofang/46923-1-8.html"
                },
                {
                    "title":"20171105",
                    "num":"10",
                    "url":"https://m.100sp.com/bofang/46923-1-9.html"
                },
                {
                    "title":"20171112",
                    "num":"11",
                    "url":"https://m.100sp.com/bofang/46923-1-10.html"
                },
                {
                    "title":"20171119",
                    "num":"12",
                    "url":"https://m.100sp.com/bofang/46923-1-11.html"
                },
                {
                    "title":"20171126",
                    "num":"13",
                    "url":"https://m.100sp.com/bofang/46923-1-12.html"
                },
                {
                    "title":"20171203",
                    "num":"14",
                    "url":"https://m.100sp.com/bofang/46923-1-13.html"
                },
                {
                    "title":"20171210",
                    "num":"15",
                    "url":"https://m.100sp.com/bofang/46923-1-14.html"
                },
                {
                    "title":"20171217",
                    "num":"16",
                    "url":"https://m.100sp.com/bofang/46923-1-15.html"
                },
                {
                    "title":"20171224",
                    "num":"17",
                    "url":"https://m.100sp.com/bofang/46923-1-16.html"
                },
                {
                    "title":"20171231",
                    "num":"18",
                    "url":"https://m.100sp.com/bofang/46923-1-17.html"
                }
            ]
        }
    ]
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
        $str = $this->getParam('str');
        $arr = explode(',', $str);
        $kind = 0;
        $areaArr = [];
        $year = 0;
        $genre = '';
        foreach($arr as $key=>$item) {
            if($item == '电影') {
                $kind = 1;
            }else if($item == '电视剧') {
                $kind = 2;
            }else if($item == '综艺') {
                $kind = 3;
            }else if($item == '动画') {
                $kind = 4;
            }else if($item == '港台') {
                $areaArr = ['香港', '台湾'];
            }else if($item == '日韩') {
                $areaArr = ['日本', '韩国'];
            }else if($item == '欧美') {
                $areaArr = ['德国', '法国', '英国', '西班牙', '瑞典', '瑞士', '挪威', '奥地利', '意大利', '芬兰', '美国', '加拿大'];
            }else if(is_numeric($item)) {
                $year = $item;
            }else {
                $genre = $item;
            }
        }
        $result = [
            'area' => $areaArr,
            'kind' => $kind,
            'year' => $year,
            'genre' => $genre
        ];
        print_r($result);
    }
    
    public function actionE() {
        $result = mb_strpos('罗英锡&#8194;', '&#8194;');
        print_r($result);
    }
    
    
    
}

