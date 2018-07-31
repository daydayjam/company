<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<form id="myform" class="form-horizontal">
    <div class="form-group">
        <label class="col-sm-2 control-label">名称</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="role-name" name="role_name" placeholder="名称">
        </div>
    </div>
    <div id="avatar" class="form-group">
        <label class="col-sm-2 control-label">头像</label>
        <div class="col-sm-8">
            <input type="file" class="file">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">描述</label>
        <div class="col-sm-8">
            <textarea class="form-control" name="rdesc" rows="8"></textarea>
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
        data['fid'] = <?= $data['fid'] ?>;
        var avatar = encodeURIComponent($('#avatar .file-preview-image.kv-preview-data').attr('src'));
        data['avatar'] = avatar == 'undefined' ? '' : avatar;
        ajax('/role/save', data, function(result) {
            if(result.code == 1) {
                if (confirm('已添加成功，是否继续添加？')==true){
                    window.location.reload();
                }else{
                     window.location.href = '/role/list?fid=<?= $data['fid'] ?>';
                }  
            }else {
                alert(result.msg);
            }
        });
    });
</script>




