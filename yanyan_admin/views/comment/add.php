<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<?php if(!$data['sys_users']) { ?>
   还没有虚拟用户，<a href="/user/add">创建虚拟用户</a> 
<?php } else { ?>
<form id="myform" class="form-horizontal">
    <div class="form-group">
        <label class="col-sm-2 control-label">选择评论人</label>
        <div class="col-sm-8">
            <select class="form-control" id="uid" name="uid">
                <option value="" class="">选择评论人</option>
                <?php foreach($data['sys_users'] as $user) { ?>
                <option value="<?= $user['id'] ?>"><?= $user['nickname'] ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">关联影视</label>
        <div class="col-sm-8">
            <label class="control-label"><?php if(!$data['fid']) { echo '无';} else { ?><a href="/film/info?id=<?= $data['fid']?>"><?= $data['ftitle'] ?></a><?php } ?></label>
        </div>
    </div>
    <div id="pic" class="form-group">
        <label class="col-sm-2 control-label">配图</label>
        <div class="col-sm-8">
            <input type="file" class="file" multiple>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">评论内容</label>
        <div class="col-sm-8">
            <textarea class="form-control" rows="8" name="cmt"></textarea>
        </div>
    </div>    
    <div class="form-group">
        <div class="col-sm-offset-5 col-sm-7">
            <button type="button" id="submitBtn" class="btn btn-default">保存</button>
        </div>
    </div>
</form>
<?php } ?>
<?php include ROOT_DIR.'/views/default/pagefooter.php' ?>
<script type="text/javascript">
    //提交表单
    $('#submitBtn').click(function() {     
        var data = getFormData();
        data['fid'] = <?php if($data['fid']) {echo $data['fid'];}else {echo 0;} ?>;
        var img1 = encodeURIComponent($('#pic .kv-preview-thumb .file-preview-image.kv-preview-data').eq(0).attr('src'));
        var img2 = encodeURIComponent($('#pic .kv-preview-thumb .file-preview-image.kv-preview-data').eq(1).attr('src'));
        var img3 = encodeURIComponent($('#pic .kv-preview-thumb .file-preview-image.kv-preview-data').eq(2).attr('src'));
        data['pic_1'] = img1 == 'undefined' ? '' : img1;
        data['pic_2'] = img2 == 'undefined' ? '' : img2;
        data['pic_3'] = img3 == 'undefined' ? '' : img3;
        ajax('/comment/save', data, function(result) {
            alert(result.msg);
            if(result.code == 1) {
                window.location.reload();
            }
        });
        
    });
</script>




