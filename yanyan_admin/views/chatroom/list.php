<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<div class="row-fluid">
    <form class="form-inline">
        <input type="hidden" name="ac_name" value="<?= $params['ac_name'] ?>" />
        <input type="hidden" name="op_name" value="<?= $params['op_name'] ?>" />
        <div class="form-group">
            <img src="/img/icon-search.gif" />
        </div>
        <div class="form-group">
            <label for="exampleInputName2">状态</label>
            <select class="form-control" name="is_open">
                <option value="">全部</option>
                <option value="1" <?php if($params['is_open']==1){ ?>selected<?php } ?>>开放</option>
                <option value="0" <?php if($params['is_open']==='0'){ ?>selected<?php } ?>>关闭</option>
            </select>
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">影视剧ID</label>
            <input type="text" value="<?= $params['fid'] ?>" class="form-control" id="fid" name="fid" placeholder="影视剧ID">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">名称</label>
            <input type="text" value="<?= $params['name'] ?>" class="form-control" id="name" name="name" placeholder="影视剧名称">
        </div>
        <button type="submit" class="btn btn-default">查询</button>
    </form>
</div>
<table class="table table-hover">
    <tr>
        <th>ID</th>
        <th>名称</th>
        <th>是否到期</th>
        <th>操作</th>
    </tr>
    <?php foreach($data['rows'] as $key=>$item) { 
        $overTimeArr = [0=>'未到期', 1=>'已到期'];
    ?>
    <tr>
        <td><?= $item['id'] ?></td>
        <td><a href="/chatroom/info?fid=<?= $item['id'] ?>&ac_name=聊天室管理&op_name=聊天室详情"><?= $item['name'] ?></a></td>
        <td><?= $overTimeArr[$item['is_over_time']] ?></td>
        <td>
            <a id="openSwitch<?= $item['id'] ?>" href="javascript:void(0)" onclick="changeSwitch(<?= $item['id'] ?>, 0)" <?php if($item['is_open']) { ?>hidden<?php } ?> >开放</a>
            <a id="closeSwitch<?= $item['id'] ?>" href="javascript:void(0)" onclick="changeSwitch(<?= $item['id'] ?>, 1)" style="color:#646464" <?php if(!$item['is_open']) { ?>hidden<?php } ?> >关闭</a>
            |<a href="/role/list?fid=<?= $item['id'] ?>">查看角色</a>|
            <a href="/role/add?fid=<?= $item['id'] ?>">添加角色</a>|
            <a href="javascript:void(0)" onclick="del(this, <?= $item['id'] ?>)">删除</a>
        </td>
    </tr>
    <?php } ?>
</table>
<?php include ROOT_DIR.'/views/default/pageline.php' ?>
<?php include ROOT_DIR.'/views/default/pagefooter.php' ?>
<script type="text/javascript">
    function changeSwitch(fid, isOpen) {
        var data = {
            fid:fid,
            is_open:isOpen
        };
        ajax('/chatroom/switch', data, function(result) {
            if(result.code == 1) {
                if(isOpen) {
                    $('#openSwitch'+fid).show();
                    $('#closeSwitch'+fid).hide();
                } else {
                    $('#openSwitch'+fid).hide();
                    $('#closeSwitch'+fid).show();
                }
            }
        });
    }
    
    //删除
    function del(object, fid) {
        var data = {
            fid:fid
        };
        ajax('/chatroom/del', data, function(result) {
           if(result.code == 1) {
               $(object).parent().parent().hide('slow');
           } 
        });
    }
</script>




