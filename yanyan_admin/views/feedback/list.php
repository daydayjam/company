<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<table class="table table-hover">
    <tr>
        <th>ID</th>
        <th>发布人</th>
        <th>联系方式</th>
        <th>反馈类型</th>
        <th>内容</th>
        <th>发布时间</th>
    </tr>
    <?php foreach($data['rows'] as $key=>$item) {
    ?>
    <tr>
        <td><?= $item['id'] ?></td>
        <td>
            <?php if($item['uid'] && isset($item['nickname'])) { ?>
                <a href="/user/info?uid=<?= $item['uid'] ?>ac_name=反馈管理&op_name=反馈详情"><?= $item['nickname'] ?></a>
            <?php } ?>
        </td>
        <td><?= $item['contact'] ?></td>
        <td><?= $item['type'] ?></td>
        <td><?= $item['content'] ?></td>
        <td><?= $item['create_time'] ?></td>
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




