<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<form id="myform" class="form-horizontal">
    <div class="form-group">
        <label class="col-sm-2 control-label">ID</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $data['id'] ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">分类</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $data['ctype'] ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">关联影视</label>
        <div class="col-sm-8">
            <label class="control-label"><?php if(!$data['title']) { echo '无';} else { ?><a href="/film/info?id=<?= $data['assoc_id']?>"><?= $data['title'] ?></a><?php } ?></label>
        </div>
    </div>
    <div id="oldCover" class="form-group">
        <label class="col-sm-2 control-label">配图</label>
        <div class="col-sm-8">
            <?php if(!$data['pics']) { echo '无';} else { foreach($data['pics'] as $item) { ?>
            <img src="<?= $item['path'] ?>" width="150" />
            <?php }}?>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">评论时间</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $data['create_time'] ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">评论内容</label>
        <div class="col-sm-8">
            <textarea class="form-control" readonly rows="8" name="comment"><?= $data['comment'] ?></textarea>
        </div>
    </div>    
</form>
<?php include ROOT_DIR.'/views/default/pagefooter.php' ?>
<script type="text/javascript">
    
</script>




