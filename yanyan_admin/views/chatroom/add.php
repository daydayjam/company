<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<form id="myform" class="form-horizontal">
    <div class="form-group">
        <label class="col-sm-2 control-label">名称</label>
        <div class="col-sm-8">
            <label class="control-label"><?= $data['name'] ?></label>
        </div>
    </div>
    <div id="imgTop" class="form-group">
        <label class="col-sm-2 control-label">图片</label>
        <div class="col-sm-8">
            <input type="file" class="file">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">开始时间</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="st" name="st" placeholder="开始时间">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">结束时间</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="et" name="et" placeholder="结束时间">
        </div>
    </div>
    <div id="imgQue" class="form-group">
        <label class="col-sm-2 control-label">问题图片</label>
        <div class="col-sm-8">
            <input type="file" class="file">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">问题</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="question" name="question" placeholder="聊天室问题">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">选项</label>
        <div class="col-sm-8 radio">
            <label>
                <input type="radio" name="ans_right" id="options1" value="1" checked>
                <input type="text" class="form-control" id="ans1" name="ans_1" placeholder="选项一">
            </label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label"></label>
        <div class="col-sm-8 radio">
            <label>
                <input type="radio" name="ans_right" id="options2" value="2">
                <input type="text" class="form-control" id="ans2" name="ans_2" placeholder="选项二">
            </label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label"></label>
        <div class="col-sm-8 radio">
            <label>
                <input type="radio" name="ans_right" id="options3" value="3">
                <input type="text" class="form-control" id="ans3" name="ans_3" placeholder="选项三">
            </label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label"></label>
        <div class="col-sm-8 radio">
            <label>
                <input type="radio" name="ans_right" id="options4" value="4">
                <input type="text" class="form-control" id="ans4" name="ans_4" placeholder="选项四">
            </label>
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
    $('#st').datetimepicker({
        lang:"ch", //语言选择中文 注：旧版本 新版方法：$.datetimepicker.setLocale('ch');
        format:"Y-m-d H:i:s",      //格式化日期
        timepicker:true,    //关闭时间选项
        yearStart:2000,     //设置最小年份
        yearEnd:2050,        //设置最大年份
        todayButton:true    //关闭选择今天按钮
    });
    $('#et').datetimepicker({
        lang:"ch", //语言选择中文 注：旧版本 新版方法：$.datetimepicker.setLocale('ch');
        format:"Y-m-d H:i:s",      //格式化日期
        timepicker:true,    //关闭时间选项
        yearStart:2000,     //设置最小年份
        yearEnd:2050,        //设置最大年份
        todayButton:true    //关闭选择今天按钮
    });
    $('#submitBtn').click(function() {     
        var data = getFormData();
        data['fid'] = <?= $data['fid'] ?>;
        
        var ansList = [
            {
                pos:1,
                answer:data['ans_1']
            },
            {
                pos:2,
                answer:data['ans_2']
            },
            {
                pos:3,
                answer:data['ans_3']
            },
            {
                pos:4,
                answer:data['ans_4']
            }
        ];
        data['ans_list'] = JSON.stringify(ansList);
        var imgTop = encodeURIComponent($('#imgTop .file-preview-image.kv-preview-data').attr('src'));
        var imgQue = encodeURIComponent($('#imgQue .file-preview-image.kv-preview-data').attr('src'));
        data['img_top'] = imgTop == 'undefined' ? '' : imgTop;
        data['img_que'] = imgQue == 'undefined' ? '' : imgQue;
        ajax('/chatroom/save', data, function(result) {
            alert(result.msg);
            if(result.code == 1) {
                window.location.href = '/chatroom/list';
            }
        });
    });
</script>




