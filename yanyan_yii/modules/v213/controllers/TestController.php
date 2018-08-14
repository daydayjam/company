<?php
/**
 * 测试控制器类
 * @author ztt
 * @date 2017/10/30
 */
namespace app\controllers;

use Yii;
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
        $url = 'http://yy.leiyu.tv/spider/savefilm';
        $data = [
            'data'=>'{
    "resource":"嗨.哆咪影视",
    "title":"西虹市首富",
    "kind":"1",
    "status":"HC1080P中字",
    "main_actor":[
        "沈腾",
        "张一鸣"
    ],
    "director":[
        "闫非",
        "彭大魔"
    ],
    "genre":[
        "电影"
    ],
    "cover":"https://pic.china-gif.com/pic/upload/vod/2018-07/15327734738.jpg",
    "area":"大陆",
    "year":"2018",
    "summary":"讲述了王多鱼（沈腾饰）意外获得十亿资金，却必需在一个月内花光的搞笑故事。",
    "play_route":[
        "云播放②",
        "云播放①",
        "云播放③",
        "云播放④",
        "哆咪云播①",
        "云播放⑯"
    ],
    "play_source":[
        {
            "way":[
                {
                    "title":"HD",
                    "num":"1",
                    "url":"http://m.haiduomi.com/play/27990-1-1.html"
                }
            ]
        },
        {
            "way":[
                {
                    "title":"HC1080P中字",
                    "num":"1",
                    "url":"http://m.haiduomi.com/play/27990-2-1.html"
                }
            ]
        },
        {
            "way":[
                {
                    "title":"HD1280高清无水印中字",
                    "num":"1",
                    "url":"http://m.haiduomi.com/play/27990-3-1.html"
                }
            ]
        },
        {
            "way":[
                {
                    "title":"HD1280高清无水印中字",
                    "num":"1",
                    "url":"http://m.haiduomi.com/play/27990-4-1.html"
                }
            ]
        },
        {
            "way":[
                {
                    "title":"HDTC高清版",
                    "num":"1",
                    "url":"http://m.haiduomi.com/play/27990-5-1.html"
                }
            ]
        },
        {
            "way":[
                {
                    "title":"高清",
                    "num":"1",
                    "url":"http://m.haiduomi.com/play/27990-6-1.html"
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
        $result = ['name'=>111];
        $result[] = ['age'=>22];
        print_r($result);
    }
    
    
    
}

