<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<form id="myform" class="form-horizontal">
    <div class="form-group">
        <label class="col-sm-2 control-label">关联影视</label>
        <div class="col-sm-8">
            <label class="control-label"><?php if(!$data['fid']) { echo '无';} else { ?><a href="/film/info?id=<?= $data['fid']?>"><?= $data['fname'] ?></a><?php } ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">寄语内容</label>
        <div class="col-sm-8">
            <textarea class="form-control" rows="8" name="content"></textarea>
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
    //提交表单
    $('#submitBtn').click(function() {     
        var data = getFormData();
        data['fid'] = <?php if($data['fid']) {echo $data['fid'];}else {echo 0;} ?>;
        ajax('/motto/save', data, function(result) {
            alert(result.msg);
            if(result.code == 1) {
                window.location.reload();
            }
        });
        
    });
</script>




