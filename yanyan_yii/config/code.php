<?php
/**
 * 和状态码相关的文案配置
 */
return [
    // 删除
    'status_delete' => -1,  
    // 正常
    'status_normal' => 1,   
    // 待审
    'status_padding'=> 0,
    
    // 影视剧类型：电影类型
    'film_movie' => 1,
    // 影视剧类型：电视剧类型
    'film_tv' => 2,
    // 综艺
    'film_variety' => 3,
    // 动漫
    'film_animation' => 4,
    // 已追剧
    'follow_yes' => 1,
    // 未追剧
    'follow_no' => 0,
    
    // 已满赞
    'full_praise_yes' => 1,
    // 未满赞
    'full_praise_no' => 0,
    // 满赞数量
    'full_praise_cnt' => 10,
    
    // 已点赞
    'praise_yes' => 1,
    // 未点赞
    'praise_no' => 0,
    
    // 关联业务，0=影视剧评论；1=资讯评论；11=转发的资讯评论; 110=转发的资讯评论的评论
    'comment_film' => 0,
    'comment_news' => 1,
    'comment_trans_news' => 11,
    'comment_trans_news_comment' => 110,
    
    // 年代未知
    'year_unknown' => 0,
    
    // 片源报错3人
    'film_source_feedback' => 3,
    
    // 未读
    'read_no' => 0,
    
    // 已读
    'read_yes' => 1,
    
    // 设备类型
    'client_type' => [1001, 1010], //1001=IOS， 1010=安卓
    
    
];

