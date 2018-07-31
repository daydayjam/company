<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<form id="myform" class="form-horizontal" enctype='multipart/form-data'>
    <div class="form-group">
        <label class="col-sm-2 control-label">用户名</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-addon" id="basic-addon1">926</span>
                <input type="text" class="form-control" id="tel" name="tel" placeholder="请输入剩余8位数字">
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">密码</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="pwd" name="pwd" placeholder="至少8个字符，区分大小写">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">昵称</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="nick" name="nick" placeholder="昵称">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">头像</label>
        <div class="col-sm-8">
            <input type="file" class="file" id="avatar" name="avatar" placeholder="请输入剩余8位数字">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">性别</label>
        <div class="col-sm-8">
            <select class="form-control" id="gender" name="gender">
                <option value="1">男</option>
                <option value="2">女</option>
                <option value="0">保密</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">生日</label>
        <div class="col-sm-8">
            <input type="date" class="form-control" id="birth" name="birth" placeholder="生日">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">职业兴趣</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="signature" name="signature" placeholder="职业兴趣">
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-5 col-sm-7">
            <button type="button" id="submitBtn" class="btn btn-default">创建</button>
        </div>
    </div>
</form>
<?php include ROOT_DIR.'/views/default/pagefooter.php' ?>
<script type="text/javascript">
    $('#submitBtn').click(function() {      
        var data = getFormData();
        if(!data['tel']) {
            alert('手机号不能为空');
        }
        if(!data['pwd']) {
            alert('密码不能为空');
        }
        data['tel'] = '926' + data['tel'];
        data['avatar'] = $('.file-preview-image.kv-preview-data').attr('src') ? encodeURIComponent($('.file-preview-image.kv-preview-data').attr('src')) : '';
        ajax('/user/save', data, function(result) {
            alert(result.msg);
            if(result.code == 1) {
                window.location.href = '/user/list';
            }
        });
    });
</script>




