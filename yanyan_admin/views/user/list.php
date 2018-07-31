<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<div class="row-fluid">
    <form class="form-inline">
        <input type="hidden" name="ac_name" value="<?= $params['ac_name'] ?>" />
        <input type="hidden" name="op_name" value="<?= $params['op_name'] ?>" />
        <div class="form-group">
            <img src="/img/icon-search.gif" />
        </div>
        <div class="form-group">
            <label for="exampleInputName2">用户状态</label>
            <select class="form-control" name="status">
                <option value="">全部</option>
                <option value="1" <?php if($params['status']==1){ ?>selected<?php } ?>>正常</option>
                <option value="-1" <?php if($params['status']==-1){ ?>selected<?php } ?>>封号</option>
                <option value="-2" <?php if($params['status']==-2){ ?>selected<?php } ?>>冻结8小时</option>
                <option value="-3" <?php if($params['status']==-3){ ?>selected<?php } ?>>冻结24小时</option>
            </select>
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">ID</label>
            <input type="text" value="<?= $params['uid'] ?>" class="form-control" id="uid" name="uid" placeholder="用户ID">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">TEL</label>
            <input type="tel" value="<?= $params['tel'] ?>" class="form-control" id="tel" name="tel" placeholder="手机号">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">昵称</label>
            <input type="text" value="<?= $params['nick'] ?>" class="form-control" id="nick" name="nick" placeholder="昵称">
        </div>
        <button type="submit" class="btn btn-default">查询</button>
    </form>
</div>
<table class="table table-hover">
    <tr>
        <th>ID</th>
        <th>言言ID</th>
        <th>手机号码</th>
        <th>昵称</th>
        <th>头像</th>
        <th>性别</th>
        <th>婚否</th>
        <th>状态</th>
        <th>注册日期</th>
    </tr>
    <?php foreach($data['rows'] as $key=>$item) { 
        $genderArr = [1=>'男',2=>'女',0=>'保密'];
        $emoArr = [1=>'已婚',2=>'未婚',0=>'保密'];
        $statusArr = [1=>'正常',-1=>'封号',-2=>'冻结8小时',-3=>'冻结24小时'];
    ?>
    <tr>
        <td><?= $item['id'] ?></td>
        <td><?= $item['serial_num'] ?></td>
        <td><?= $item['tel'] ?></td>
        <td><a href="/user/info?uid=<?= $item['id'] ?>&ac_name=用户管理&op_name=用户详情"><?= $item['nickname'] ?></a></td>
        <td><img src="<?= $item['avatar'] ?>" width="50" /></td>
        <td><?= $genderArr[$item['gender']] ?></td>
        <td><?= $emoArr[$item['emotion']] ?></td>
        <td><?= $statusArr[$item['status']] ?></td>
        <td><?= $item['create_time'] ?></td>
    </tr>
    <?php } ?>
</table>
<?php include ROOT_DIR.'/views/default/pageline.php' ?>
<?php include ROOT_DIR.'/views/default/pagefooter.php' ?>




