<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<form id="myform" class="form-horizontal">
    <div class="form-group">
        <label class="col-sm-2 control-label">名称</label>
        <div class="col-sm-8">
            <input type="text" value="<?= $data['role_name'] ?>" class="form-control" id="role-name" name="role_name" placeholder="名称">
        </div>
    </div>
    <div id="avatar" class="form-group">
        <label class="col-sm-2 control-label">头像</label>
        <div class="col-sm-8">
            <input type="file" class="file">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">原头像</label>
        <div class="col-sm-8">
            <img src="<?= $data['avatar'] ?>" width="150" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">描述</label>
        <div class="col-sm-8">
            <textarea class="form-control" name="rdesc" rows="8"><?= $data['rdesc'] ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-5 col-sm-7">
            <button type="button" id="submitBtn" class="btn btn-default">保存</button>
        </div>
    </div>
</form>
<?php include ROOT_DIR.'/views/default/pagefooter.php' ?>
<script type="text/javascript">
    $('#submitBtn').click(function() {     
        var data = getFormData();
        data['id'] = <?= $data['id'] ?>;
        var avatar = encodeURIComponent($('#avatar .file-preview-image.kv-preview-data').attr('src'));
        data['avatar'] = avatar == 'undefined' ? '' : avatar;
        ajax('/role/update', data, function(result) {
            alert(result.msg);
            if(result.code == 1) {
                window.location.reload();
            }
        });
    });
</script>




