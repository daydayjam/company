<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<div class="row-fluid">
    <div class="metro-nav">
        <div class="metro-nav-block nav-block-orange">
            <a data-original-title="" href="/user/list?ac_name=用户管理&op_name=用户列表">
                <i class="fa fa-user"></i>
                <div class="info">+<?= $data['user_cnt'] ?></div>
                <div class="status">新增用户</div>
            </a>
        </div>
        <div class="metro-nav-block nav-olive">
            <a data-original-title="" href="/comment/list?ac_name=评论管理&op_name=评论列表">
                <i class="fa fa-comments-o"></i>
                <div class="info">+<?= $data['cmt_cnt'] ?></div>
                <div class="status">新增评论</div>
            </a>
        </div>
        <div class="metro-nav-block nav-block-yellow">
            <a data-original-title="" href="/feedback/list?ac_name=反馈管理&op_name=反馈列表">
                <i class="fa fa-question"></i>
                <div class="info">+<?= $data['feedback_cnt'] ?></div>
                <div class="status">新增反馈</div>
            </a>
        </div>
        <div class="metro-nav-block nav-block-red">
            <a data-original-title="" href="/report/list?ac_name=举报管理&op_name=举报列表">
                <i class="fa fa-meh-o"></i>
                <div class="info">+<?= $data['report_cnt'] ?></div>
                <div class="status">新增举报</div>
            </a>
        </div>
    </div>
    <div class="metro-nav">
        <div class="metro-nav-block nav-light-purple double">
            <a data-original-title="" href="/film/list?ac_name=影视剧管理&op_name=影视剧列表">
                <i class="fa fa-film"></i>
                <div class="info"><?= $data['film_cnt'] ?></div>
                <div class="status">影视剧总数</div>
            </a>
        </div>
        <div class="metro-nav-block nav-light-green">
            <a data-original-title="" href="/motto/list?ac_name=寄语管理&op_name=寄语列表">
                <i class="fa fa-tag"></i>
                <div class="info"><?= $data['motto_cnt'] ?></div>
                <div class="status">寄语总数</div>
            </a>
        </div>
    </div>
</div>
<?php include ROOT_DIR.'/views/default/pagefooter.php' ?>

