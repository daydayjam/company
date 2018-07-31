<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<form class="form-horizontal">
    <div class="form-group">
        <label class="col-sm-2 control-label">ID</label>
        <div class="col-sm-8">
            <label class="control-label" id="uid"><?= $data['id'] ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">言言ID</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $data['serial_num'] ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">昵称</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $data['nickname'] ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">头像</label>
        <div class="col-sm-8">
            <img src="<?= $data['avatar'] ?>" width="150" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">手机号</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $data['tel'] ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">当前状态</label>
        <div class="col-sm-8">
            <select class="form-control" id="status" name="status">
                <option value="1" <?php if($data['status']==1){ ?>selected<?php } ?>>正常</option>
                <option value="-1" <?php if($data['status']==-1){ ?>selected<?php } ?>>封号</option>
                <option value="-2" <?php if($data['status']==-2){ ?>selected<?php } ?>>冻结8小时</option>
                <option value="-3" <?php if($data['status']==-3){ ?>selected<?php } ?>>冻结24小时</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">生日</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $data['birth_date'] ?></label>
        </div>
    </div>
    <?php
        $genderArr = [1=>'男',2=>'女',0=>'保密'];
        $emoArr = [1=>'已婚',2=>'未婚',0=>'保密'];
    ?>
    <div class="form-group">
        <label class="col-sm-2 control-label">性别</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $genderArr[$data['gender']] ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">情感状态</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $emoArr[$data['emotion']] ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">家乡</label>
        <div class="col-sm-8">
            <label class="control-label"><?php if($data['hometown']) { echo $data['hometown']; }else { ?>未知<?php } ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">职业兴趣</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $data['signature'] ?></label>
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
        var uid = $('#uid').text();
        var status = $('#status').val();
        var data = {
            uid:uid,
            status:status
        };
        ajax('/user/update', data, function(result) {
            alert(result.msg);
        });
    });
</script>




