<?php
require_once('../../../include/AjaxHeader.php');

$img = $VK->QRow("select img from base_model where id=".$_GET['model_id']);
if($img) {
	$file_id = $VK->QRow("select id from files where table_name='base_model' and table_id=".$_GET['model_id']." and main=1");
	$send->img = "<IMG src=/files/images/base_model/".$img."s.jpg onclick=fotoShow(".$file_id."); style=cursor:pointer;>";
} else {
	$send->img = "<IMG src=/img/nofoto.gif>";
}

echo json_encode($send);
?>



