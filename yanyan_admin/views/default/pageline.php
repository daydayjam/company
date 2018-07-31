<div class="container-fluid page-box">
    <nav class="page-container" aria-label="Page navigation">
        <div class="totalCountLabel">
            <span>共搜索到<label class="total-num"><?= $data['total'] ?></label>条数据</span>
        </div>
        <ul class="pagination">
            <?php
                $pageTotal = ceil($data['total']/$data['record']);
                $page = $data['page'];
                $url = '/'.$params['ac'].'/'.$params['op'];
            ?>
            <span id="cur-page" class="hidden"><?= $page ?></span>
            <?php if($pageTotal > 8 && $page > 1) { ?>
            <li>
                <a href="<?= $url ?>?page=<?= $page-1 ?>&<?= $params['condition'] ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php } ?>
            <li class="<?php if($page==1){echo 'active';} ?>"><a href="<?= $url ?>?page=1&<?= $params['condition'] ?>">1</a></li>
            <?php if($pageTotal>8) { ?> 
            <?php if($page < 6) { for($i=2; $i<=7; $i++) { ?>
            <li class="<?php if($page==$i){echo 'active';} ?>"><a href="<?= $url ?>?page=<?= $i ?>&<?= $params['condition'] ?>"><?= $i; ?></a></li>
            <?php }?>
            <li><a href="#">...</a></li>
            <li class="<?php if($page==$pageTotal){echo 'active';} ?>"><a href="<?= $url ?>?page=<?= $pageTotal ?>&<?= $params['condition'] ?>"><?= $pageTotal; ?></a></li>
            <?php } ?>
            <?php if($page >= 6) { ?>
            <li><a href="#">...</a></li>
            <?php if($pageTotal - $page > 3) { ?>
            <li><a href="<?= $url ?>?page=<?= $page-2 ?>&<?= $params['condition'] ?>"><?= $page-2 ?></a></li>
            <li><a href="<?= $url ?>?page=<?= $page-1 ?>&<?= $params['condition'] ?>"><?= $page-1 ?></a></li>
            <li class="active"><a href="<?= $url ?>?page=<?= $page ?>&<?= $params['condition'] ?>"><?= $page; ?></a></li>
            <li><a href="<?= $url ?>?page=<?= $page+1 ?>&<?= $params['condition'] ?>"><?= $page+1 ?></a></li>
            <li><a href="#">...</a></li>
            <?php } else { ?>
            <li class="<?php if($page==$pageTotal-5){echo 'active';} ?>"><a href="<?= $url ?>?page=<?= $pageTotal-5 ?>&<?= $params['condition'] ?>"><?= $pageTotal-5 ?></a></li>
            <li class="<?php if($page==$pageTotal-4){echo 'active';} ?>"><a href="<?= $url ?>?page=<?= $pageTotal-4 ?>&<?= $params['condition'] ?>"><?= $pageTotal-4 ?></a></li>
            <li class="<?php if($page==$pageTotal-3){echo 'active';} ?>"><a href="<?= $url ?>?page=<?= $pageTotal-3 ?>&<?= $params['condition'] ?>"><?= $pageTotal-3 ?></a></li>
            <li class="<?php if($page==$pageTotal-2){echo 'active';} ?>"><a href="<?= $url ?>?page=<?= $pageTotal-2 ?>&<?= $params['condition'] ?>"><?= $pageTotal-2 ?></a></li>
            <li class="<?php if($page==$pageTotal-1){echo 'active';} ?>"><a href="<?= $url ?>?page=<?= $pageTotal-1 ?>&<?= $params['condition'] ?>"><?= $pageTotal-1 ?></a></li>
            <?php } ?>
            <li class="<?php if($page==$pageTotal){echo 'active';} ?>"><a href="<?= $url ?>?page=<?= $pageTotal ?>&<?= $params['condition'] ?>"><?= $pageTotal ?></a></li>
            <?php }} ?>
            <?php if($pageTotal==8) { for($i=2; $i<=$pageTotal; $i++) { ?>
            <li class="<?php if($page==$i){echo 'active';} ?>"><a href="<?= $url ?>?page=<?= $i ?>&<?= $params['condition'] ?>">$i</a></li>
            <?php }} ?>
            <?php if($pageTotal<8) { for($i=2; $i<=$pageTotal; $i++) { ?> 
            <li class="<?php if($page==$i){echo 'active';} ?>"><a href="<?= $url ?>?page=<?= $i ?>&<?= $params['condition'] ?>"><?= $i; ?></a></li>
            <?php }} ?>
            <?php if($pageTotal > 8 && $page < $pageTotal) { ?>
            <li>
                <a href="<?= $url ?>?page=<?= $page+1 ?>&<?= $params['condition'] ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
            <?php } ?>
        </ul>
    </nav>
</div>