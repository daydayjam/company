<?php
$data = '111';
$newData = iconv('UTF-8', 'GBK', $data);
echo json_encode($newData);