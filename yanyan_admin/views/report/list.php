<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<table class="table table-hover">
    <tr>
        <th>ID</th>
        <th>举报人</th>
        <th>被举报信息或人</th>
        <th>类型</th>
        <th>原因</th>
        <th>状态</th>
        <th>举报时间</th>
        <th>处理时间</th>
        <th>操作</th>
    </tr>
    <?php foreach($data['rows'] as $key=>$item) {
        $reasonArr = [0=>'其他',1=>'暴力色情',2=>'人身攻击',3=>'广告骚扰',4=>'谣言及虚假信息',5=>'政治敏感'];
        $typeArr = [0=>'举报信息',1=>'举报人'];
        $statusArr = [0=>'未处理',1=>'已处理'];
    ?>
    <tr>
        <td><?= $item['id'] ?></td>
        <td>
            <?php if($item['uid'] && isset($item['nick'])) { ?>
                <a href="/user/info?uid=<?= $item['uid'] ?>"><?= $item['nick'] ?></a>
            <?php } ?>
        </td>
        <td style="width: 300px;">
            <?php if($item['type']) { ?>
                <a href="/user/info?uid=<?= $item['assoc_id'] ?>&ac_name=举报管理&op_name=举报详情"><?= $item['to_info'] ?></a>
            <?php } else { ?>
                <a href="/comment/info?id=<?= $item['assoc_id'] ?>&ac_name=举报管理&op_name=举报详情"><?= $item['to_info'] ?></a>
            <?php } ?>
        </td>
        <td><?= $typeArr[$item['type']] ?></td>
        <td><?= $reasonArr[$item['reason']] ?></td>
        <td><?= $statusArr[$item['status']] ?></td>
        <td><?= $item['create_time'] ?></td>
        <td><?= $item['deal_time'] ?></td>
        <td>操作</td>
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




