<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<div class="row-fluid">
    <form class="form-inline">
        <input type="hidden" name="ac_name" value="<?= $params['ac_name'] ?>" />
        <input type="hidden" name="op_name" value="<?= $params['op_name'] ?>" />
        <div class="form-group">
            <img src="/img/icon-search.gif" />
        </div>
        <div class="form-group">
            <div class="form-group">
                <label for="exampleInputName2">电影/电视剧</label>
                <select class="form-control" name="kind">
                    <option value="">全部</option>
                    <option value="1" <?php if($params['kind']==1){ ?>selected<?php } ?>>电影</option>
                    <option value="2" <?php if($params['kind']==2){ ?>selected<?php } ?>>电视剧</option>
                    <option value="3" <?php if($params['kind']==3){ ?>selected<?php } ?>>综艺</option>
                    <option value="4" <?php if($params['kind']==4){ ?>selected<?php } ?>>动漫</option>
                </select>
            </div>
            <div class="form-group">
                <label for="exampleInputName2">是否热门</label>
                <select class="form-control" name="is_hot">
                    <option value="">全部</option>
                    <option value="1" <?php if($params['is_hot']==1){ ?>selected<?php } ?>>是</option>
                    <option value="0" <?php if($params['is_hot']==='0'){ ?>selected<?php } ?>>否</option>
                </select>
            </div>
            <div class="form-group">
                <label for="exampleInputEmail2">ID</label>
                <input type="text" value="<?= $params['id'] ?>" class="form-control" id="id" name="id" placeholder="ID">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail2">TITLE</label>
                <input type="text" value="<?= $params['title'] ?>" class="form-control" id="title" name="title" placeholder="名称">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail2">类型</label>
                <input type="text" value="<?= $params['genre'] ?>" class="form-control" id="genre" name="genre" placeholder="类型">
            </div>
        </div>
        <div class="form-group" style="margin-top:5px;">
            <div class="form-group">
                <label for="exampleInputEmail2">国家/地区</label>
                <input type="text" value="<?= $params['area'] ?>" class="form-control" id="area" name="area" placeholder="国家/地区">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail2">年代</label>
                <input type="text" value="<?= $params['year'] ?>" class="form-control" id="year" name="year" placeholder="年代">
            </div>
        </div>
        
        <button type="submit" class="btn btn-default">查询</button>
    </form>
</div>
<table class="table table-hover">
    <tr>
        <th>ID</th>
        <th>名称</th>
        <th>电影/电视剧</th>
        <th>类型</th>
        <th>标签</th>
        <th>国家/地区</th>
        <th>上映时间</th>
        <th>操作</th>
    </tr>
    <?php foreach($data['rows'] as $key=>$item) {
        $kindArr = [1=>'电影',2=>'电视剧',3=>'综艺',4=>'动漫'];
    ?>
    <tr>
        <td><?= $item['id'] ?></td>
        <td><a href="/film/info?id=<?= $item['id'] ?>&ac_name=影视剧管理&op_name=影视剧详情"><?= $item['title'] ?></a></td>
        <td><?= $kindArr[$item['kind']] ?></td>
        <td><?= $item['genre'] ?></td>
        <td><?= $item['area'] ?></td>
        <td><?= $item['year'] ?></td>
        <td>
            <a id="openSwitch<?= $item['id'] ?>" href="javascript:void(0)" onclick="changeSwitch(<?= $item['id'] ?>, 0)" style="color:red" <?php if(!$item['is_hot']) { ?>hidden<?php } ?> >热&nbsp;&nbsp;&nbsp;&nbsp;门</a>
            <a id="closeSwitch<?= $item['id'] ?>" href="javascript:void(0)" onclick="changeSwitch(<?= $item['id'] ?>, 1)" style="color:#646464" <?php if($item['is_hot']) { ?>hidden<?php } ?> >非热门</a>
            |<a href="/motto/add?fid=<?= $item['id'] ?>&ftitle=<?= $item['title'] ?>&ac_name=寄语管理&op_name=添加寄语">寄语</a>
            |<a href="/comment/add?fid=<?= $item['id'] ?>&ftitle=<?= $item['title'] ?>&ac_name=评论管理&op_name=添加评论">碎碎念</a>
            |<a href="javascript:void(0)" onclick="del(<?= $item['id'] ?>, this)">删除</a>
        </td>
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




