<?php
require_once('../../../include/AjaxHeader.php');

$modelName = '';
if($_POST['model'] > 0) { $modelName = $VK->QRow("select name from base_model where id=".$_POST['model']); }

$VK->Query("update zayavki set
client_id=".$_POST['client_id'].",
category=".$_POST['category'].",
base_device_id=".$_POST['device'].",
base_vendor_id=".$_POST['vendor'].",
base_model_id=".$_POST['model'].",
imei='".textFormat(win1251($_POST['imei']))."',
serial='".textFormat(win1251($_POST['serial']))."',
color_id=".$_POST['color'].",
find='".$modelName." ".$_POST['imei']." ".$_POST['serial']."'
where id=".$_POST['id']);

$VK->Query("insert into history (ws_id,type,zayav_id,viewer_id_add) values (".$vku->ws_id.",7,".$_POST['id'].",".$_GET['viewer_id'].")");


$send = getTime($T);

echo json_encode($send);
?>

