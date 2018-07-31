<?php include ROOT_DIR.'/views/default/pageheader.php' ?>
<style>
    .sub-table {width: 100%;}
    /*.sub-list td{background-color:#F4FAFB}*/
    .sub-list .sub-top{display:table}
    .sub-list .sub-top .top-left,.sub-list .sub-top .top-right{float:left;}
    .sub-list .sub-top .top-right{margin-top:10px;font-size:14px;margin-left:10px;color:#646464}
    .sub-list .sub-footer{margin-left:45px;font-size:14px;padding: 5px;}
    .sub-list .sub-footer .rnick{color:#ED4A4B;}
    .sub-list .sub-wrap{
        display: -webkit-box;      
        display: -moz-box;         
        display: -ms-flexbox;      
        display: -webkit-flex;     
        display: flex;             
    }
    .sub-list .sub-left{
        -webkit-box-flex: 12;  
        -moz-box-flex: 12;             
        -webkit-flex: 12;      
        -ms-flex: 12;            
        flex: 12;                
    }
    .sub-list .sub-right{
        -webkit-box-flex: 1;  
        -moz-box-flex: 1;             
        -webkit-flex: 1;      
        -ms-flex: 1;            
        flex: 1;                
    }
    .sub-list .sub-right div {
        display: inline-block;           
    }
    .sub-list a:link{text-decoration:none;}
    .has-cmt{background:#ccc;box-shadow: 5px 5px 5px;}
</style>
<div class="row-fluid">
    <form class="form-inline">
        <input type="hidden" name="ac_name" value="<?= $params['ac_name'] ?>" />
        <input type="hidden" name="op_name" value="<?= $params['op_name'] ?>" />
        <div class="form-group">
            <img src="/img/icon-search.gif" />
        </div>
        <div class="form-group">
            <label for="exampleInputName2">来源</label>
            <select class="form-control" name="source_type">
                <option value="">全部</option>
                <option value="1" <?php if($params['source_type']==1){ ?>selected<?php } ?>>微信公众号</option>
                <option value="2" <?php if($params['source_type']==2){ ?>selected<?php } ?>>哔哩哔哩</option>
                <option value="3" <?php if($params['source_type']==2){ ?>selected<?php } ?>>新浪微博</option>
            </select>
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">标题</label>
            <input type="text" value="<?= $params['title'] ?>" class="form-control" id="title" name="title" placeholder="TITLE">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">描述</label>
            <input type="text" value="<?= $params['description'] ?>" class="form-control" id="description" name="description" placeholder="描述">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">作者</label>
            <input type="text" value="<?= $params['author'] ?>" class="form-control" id="author" name="author" placeholder="AUTHOR">
        </div>
        <button type="submit" class="btn btn-default">查询</button>
    </form>
</div>
<table class="table table-hover">
    <tr>
        <th>ID</th>
        <th>标题</th>
        <th>来源</th>
        <th>作者</th>
        <th>描述</th>
        <th>原文链接</th>
        <th>发布时间</th>
        <th>操作</th>
    </tr>
    <?php foreach($data['rows'] as $key=>$item) {
        $stypeArr = [1=>'微信公众号',2=>'哔哩哔哩',3=>'新浪微博'];
        $hasCmt = $item['comment_cnt']>0 ? 'has-cmt' : '';
    ?>
    <tr id="mainCmt<?= $item['id'] ?>" class="<?= $hasCmt ?>" <?php if($item['comment_cnt']){ ?>onclick="toggle(<?= $item['id'] ?>)"<?php } ?>>
        <td><?= $item['id'] ?></td>
        <td><?= $item['title'] ?></td>
        <td><?= $stypeArr[$item['source_type']] ?></td>
        <td><?= $item['author']['name'] ?></td>
        <td><?= $item['description'] ?></td>
        <td><a href="<?= $item['source_url'] ?>">原文</a></td>
        <td><?= $item['pubdate'] ?></td>
        <td>
            <a href="javascript:void(0)" onclick="del(<?= $item['id'] ?>, this, 1)">删除</a>
        </td>
    </tr>
    <tr id="subCmt<?= $item['id'] ?>" class="sub-list" style="display:none;">
        <td colspan="10">
            <table id="subCmtTable<?= $item['id'] ?>" class="sub-table">

            </table>
        </td>
    </tr>
    <?php } ?>
</table>
<?php include ROOT_DIR.'/views/default/pageline.php' ?>
<?php include ROOT_DIR.'/views/default/pagefooter.php' ?>
<script type="text/javascript">
    //影片删除
    function del(id, object, assoc_type) {
        var data = {'id':id,'assoc_type':assoc_type};
        ajax('/comment/del', data, function(result) {
            alert(result.msg);
            if(result.code == 1) {
                if($(object).parent().parent().attr('class')!='sub-right') {
                    $(object).parent().parent().hide('slow');
                } else {
                    $('#mainCmt'+id).hide('slow');
                    $(object).parent().parent().parent().parent().parent().hide('slow');
                    if(result.data.mcid !== 0 && result.data.cmt_cnt === 0) {
                        $('#mainCmt'+result.data.mcid).removeClass('has-cmt');
                        $('#mainCmt'+result.data.mcid).prop("onclick",null).off("click");
                        $('.sub-list').remove();
                    }
                }
                
            }
        });
    }
    
    //切换
    var subCmtTotal = 0;
    function toggle(id) {
        var subCmt = $('#subCmt'+id);
        if(subCmt.css('display') == 'none') {
            //获取追加评论列表
            ajax('/comment/getadd',{'cid':id,'assoc_type':1},function(result){
                var data = result.data;
                subCmtTotal = data.length;
                var subHtml = '';
                for(var i=0; i<data.length; i++) {
                    var cmtHtml = '';
                    if(data[i].reply_uid>0) {cmtHtml = '回复<a href="/admin/user/edit?id='+data[i].reply_uid+'"><span class="rnick">'+data[i].reply_nick+'</span></a>：'+data[i].comment;}
                    else{cmtHtml = data[i].comment;}
                    subHtml += '<tr>';
                    subHtml += '    <td>';
                    subHtml += '        <div class="sub-wrap">';
                    subHtml += '            <div class="sub-left">';
                    subHtml += '                <div class="sub-top">';
                    subHtml += '                    <div class="top-left">';
                    subHtml += '                        <img src="'+data[i].avatar+'" width="35" />';
                    subHtml += '                    </div>';
                    subHtml += '                    <div class="top-right"><a href="/admin/user/edit?id='+data[i].uid+'">';
                    subHtml +=                           data[i].nickname;
                    subHtml += '                    </a></div>';
                    subHtml += '                </div>';
                    subHtml += '                <div class="sub-footer">';
                    subHtml +=                     cmtHtml;
                    subHtml += '                </div>';
                    subHtml += '            </div>';
                    subHtml += '            <div class="sub-right">';
                    subHtml += '                <div style="margin-top:10px;text-align:center;">';
                    subHtml += '                    <a href="javascript:void(0)" onclick="del('+data[i].id+',this)" title="删除">';
                    subHtml += '                        <input type="button" value="删除" class="button new_btn" />';
                    subHtml += '                    </a>';
                    subHtml += '                </div>';
                    subHtml += '            </div>';
                    subHtml += '        </div>';
                    subHtml += '    </td>';
                    subHtml += '</tr>';
                }
                $('#subCmtTable'+id).html(subHtml);
                if(data.length > 0) {subCmt.show();}
            });
        } else {
            subCmt.hide();
        }
    }
</script>




