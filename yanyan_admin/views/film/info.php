<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<form id="myform" class="form-horizontal">
    <input type="hidden" name="ac_name" value="<?= $params['ac_name'] ?>" />
    <input type="hidden" name="op_name" value="<?= $params['op_name'] ?>" />
    <div class="form-group">
        <label class="col-sm-2 control-label">名称</label>
        <div class="col-sm-8">
            <input type="text" value="<?= $data['title'] ?>" class="form-control" id="title" name="title" placeholder="影视剧中文名称">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">影视剧类型</label>
        <div class="col-sm-8">
            <select class="form-control" id="kind" name="kind">
                <option value="1" <?php if($data['kind']==1){ ?>selected<?php } ?>>电影</option>
                <option value="2" <?php if($data['kind']==2){ ?>selected<?php } ?>>电视剧</option>
                <option value="3" <?php if($data['kind']==3){ ?>selected<?php } ?>>综艺</option>
                <option value="4" <?php if($data['kind']==4){ ?>selected<?php } ?>>动漫</option>
            </select>
        </div>
    </div>
    <div id="cover" class="form-group">
        <label class="col-sm-2 control-label">封面图</label>
        <div class="col-sm-8">
            <input type="file" class="file">
        </div>
    </div>
    <div id="oldCover" class="form-group">
        <label class="col-sm-2 control-label">原封面图</label>
        <div class="col-sm-8">
            <img src="<?= $data['cover'] ?>" width="150" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">体裁</label>
        <div class="col-sm-8" id="genre-box"></div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">上映时间</label>
        <div class="col-sm-8" id="flags-box">
            <input type="text" class="form-control" id="year" name="year" value="<?= $data['year'] ?>" placeholder="上映时间,格式：年-月-日">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">国家</label>
        <div class="col-sm-8" id="flags-box">
            <input type="text" class="form-control" id="area" name="area" value="<?= $data['area'] ?>" placeholder="国家">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">导演</label>
        <div class="col-sm-8">
            <label class="control-label">
                <?php if($data['director']) { foreach($data['director'] as $key=>$director) { ?>
                <?php if(isset($director['name'])) echo $director['name']; ?>
                <?php }}else {echo '暂无';} ?>
            </label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">主要演员</label>
        <div class="col-sm-8">
            <label class="control-label"><?php echo $data['main_actor'] ? $data['main_actor'] : '暂无' ?></label>
        </div>
    </div>
<!--    <div class="form-group">
        <label class="col-sm-2 control-label">所有演员</label>
        <div class="col-sm-8">
            <label class="control-label"><a href="/actor/list">点击查看</a></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">相关海报</label>
        <div class="col-sm-8">
            <label class="control-label"><a href="/poster/list">点击查看</a></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">相关评论</label>
        <div class="col-sm-8">
            <label class="control-label"><a href="/comment/list">点击查看</a></label>
        </div>
    </div>-->
    <div class="form-group">
        <label class="col-sm-2 control-label">简介</label>
        <div class="col-sm-8">
            <textarea class="form-control" rows="8" name="summary"><?= $data['summary'] ?></textarea>
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
    //体裁
    var genreArr = new Array();
    $.getJSON ('/json/film-genre.json', function (data) {
        console.log(data);
        var genreHtml = '';
        $.each(data, function (i, item) {
            var isCheckedArr = new Array();
            <?php foreach($data['genre'] as $genre) { ?>
            var oldGenre = '<?= $genre ?>';
            if(oldGenre === item.genre) {
                genreArr.push(item.genre);
                isCheckedArr.push(item.id);
            }
            <?php } ?>
            var isChecked = ''; 
            if($.inArray(item.id, isCheckedArr) > -1) {
                isChecked = 'checked';
            }
            genreHtml += '<div class="checkbox col-sm-2 col-xs-4 col-md-4">'
                        +    '<label>'
                        +      '<input type="checkbox" value="'+item.genre+'" '+isChecked+'>'+item.genre
                        +    '</label>'
                        +'</div>';

        });
        $('#genre-box').html(genreHtml);
    });
    
    //定义体裁修改值
    $('#genre-box').on('click', 'label', function () {
        var checkInput = $(this).find('input');
        var index = $.inArray(checkInput.val(), genreArr);
        if(checkInput.is(':checked') && index === -1) {
            genreArr.push(checkInput.val());
        } else {
            if(index > -1) {
                genreArr.splice(index, 1);
            }
        }
    });
    
    //定义体裁修改值
    $('#flags-box').on('click', 'label', function () {
        var checkInput = $(this).find('input');
        var index = $.inArray(checkInput.val(), flagArr);
        if(checkInput.is(':checked') && index === -1) {
            flagArr.push(checkInput.val());
        } else {
            if(index > -1) {
                flagArr.splice(index, 1);
            }
        }
    });
    
    //提交表单
    $('#submitBtn').click(function() {     
        var data = getFormData();
        data['id'] = <?= $data['id'] ?>;
        data['genre'] = genreArr.join('/');
        var cover = encodeURIComponent($('#cover .file-preview-image.kv-preview-data').attr('src'));
        data['cover'] = cover == 'undefined' ? '' : cover;
        ajax('/film/update', data, function(result) {
            alert(result.msg);
            if(result.code == 1) {
                window.location.reload();
            }
        });
        
    });
</script>




