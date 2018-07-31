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
            <label for="exampleInputEmail2">中文名</label>
            <input type="text" value="<?= $params['name'] ?>" class="form-control" id="name" name="name" placeholder="中文名">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">英文名</label>
            <input type="text" value="<?= $params['en_name'] ?>" class="form-control" id="en_name" name="en_name" placeholder="英文名">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">国籍</label>
            <input type="text" value="<?= $params['country'] ?>" class="form-control" id="country" name="country" placeholder="国籍">
        </div>
        <button type="submit" class="btn btn-default">查询</button>
    </form>
</div>
<table class="table table-hover">
    <tr>
        <th>ID</th>
        <th>中文名</th>
        <th>英文名</th>
        <th>头像</th>
        <th>国籍</th>
    </tr>
    <?php foreach($data['rows'] as $key=>$item) {
    ?>
    <tr>
        <td><?= $item['id'] ?></td>
        <td><?= $item['act_name'] ?></td>
        <!--<td><a href="/actor/info?id=<?= $item['id'] ?>"><?= $item['act_name'] ?></a></td>-->
        <td><?= $item['en_name'] ?></td>
        <td><img src="<?= $item['avatar'] ?>" onerror="/img/default_avatar.png" width="50" /></td>
        <td><?= $item['country'] ?></td>
    </tr>
    <?php } ?>
</table>
<?php include ROOT_DIR.'/views/default/pageline.php' ?>
<?php include ROOT_DIR.'/views/default/pagefooter.php' ?>
<script type="text/javascript">
    function changeSwitch(id, isHot) {
        var data = {
            id:id,
            is_hot:isHot
        };
        ajax('/film/setishot', data, function(result) {
            if(result.code == 1) {
                if(isHot) {
                    $('#openSwitch'+id).show();
                    $('#closeSwitch'+id).hide();
                } else {
                    $('#openSwitch'+id).hide();
                    $('#closeSwitch'+id).show();
                }
            }
        });
    }
    
    //影片删除
    function del(fid, object) {
        var data = {id:fid};
        ajax('/film/del', data, function(result) {
            alert(result.msg);
            if(result.code == 1) {
                $(object).parent().parent().hide('slow');
            }
        });
    }
</script>




