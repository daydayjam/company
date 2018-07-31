<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<div class="row-fluid">
    <form class="form-inline">
        <input type="hidden" name="ac_name" value="<?= $params['ac_name'] ?>" />
        <input type="hidden" name="op_name" value="<?= $params['op_name'] ?>" />
        <div class="form-group">
            <img src="/img/icon-search.gif" />
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">ID</label>
            <input type="text" value="<?= $params['id'] ?>" class="form-control" id="id" name="id" placeholder="ID">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">相关影视</label>
            <input type="text" value="<?= $params['fname'] ?>" class="form-control" id="fname" name="fname" placeholder="相关影视">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">内容</label>
            <input type="text" value="<?= $params['content'] ?>" class="form-control" id="content" name="content" placeholder="内容">
        </div>
        <button type="submit" class="btn btn-default">查询</button>
        <button type="button" id="delBtn" class="btn btn-default pull-right" style="margin-right: 10px">删除</button>
    </form>
</div>
<table class="table table-hover">
    <tr>
        <th><input type="checkbox" id="allcheck" value=""></th>
        <th>ID</th>
        <th>相关影视</th>
        <th>寄语内容</th>
    </tr>
    <?php foreach($data['rows'] as $key=>$item) {
    ?>
    <tr>
        <td width="50"><input type="checkbox" value="<?= $item['id'] ?>"></td>
        <td><?= $item['id'] ?></td>
        <td><a href="/film/info?id=<?= $item['film_id'] ?>&ac_name=影视剧管理&op_name=影视剧详情"><?= $item['film_name'] ?></a></td>
        <td><?= $item['content'] ?></td>
    </tr>
    <?php } ?>
</table>
<?php include ROOT_DIR.'/views/default/pageline.php' ?>
<?php include ROOT_DIR.'/views/default/pagefooter.php' ?>
<script type="text/javascript">
    $('#allcheck').click(function() {
        if($(this).is(":checked")) {
            $('body').find('input[type="checkbox"]').prop('checked', true);
        } else {
            $('body').find('input[type="checkbox"]').prop('checked', false);
        }
        
    });
    
    $('#delBtn').click(function() {
        var result = new Array();
        $('input[type="checkbox"]').each(function () {
            if ($(this).is(":checked")) {
                result.push($(this).attr("value"));
            }
        });
        ajax('/motto/del', {ids:result.join(',')}, function(result) {
            alert(result.msg);
            if(result.code == 1) {
                window.location.reload();
            }
        });
    });
</script>




