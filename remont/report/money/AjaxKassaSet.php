<?php
require_once('../../../../../include/AjaxHeader.php');

$kassa_sum = $VK->QRow("select sum(sum) from gazeta_kassa");

$send->id = $VK->Query("update setup_global set kassa_start=".($_POST['summa'] - $kassa_sum));

//$VK->Query("insert into history (ws_id,type,value,viewer_id_add) values (".$vku->ws_id.",12,".$_POST['summa'].",".$_GET['viewer_id'].")");

echo json_encode($send);;
?>



