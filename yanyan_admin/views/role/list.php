<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<div class="row-fluid">
    <button type="button" id="delBtn" class="btn btn-default" style="margin-bottom: 10px">删除</button>
</div>
<table class="table table-hover">
    <tr>
        <th>
            <input type="checkbox" id="allcheck" value="">
        </th>
        <th>名称</th>
        <th>头像</th>
        <th>描述</th>
    </tr>
    <?php foreach($data['rows'] as $key=>$item) { ?>
    <tr>
        <td width="50"><input type="checkbox" value="<?= $item['id'] ?>"></td>
        <td width="100"><a href="/role/info?id=<?= $item['id'] ?>"><?= $item['role_name'] ?></a></td>
        <td><img src="<?= $item['avatar'] ?>" width="100" /></td>
        <td><?= $item['rdesc'] ?></td>
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
        ajax('/role/del', {ids:result.join(',')}, function(result) {
            alert(result.msg);
            if(result.code == 1) {
                window.location.reload();
            }
        });
    });
</script>




