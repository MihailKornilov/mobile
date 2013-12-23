<?php
require_once('../../include/AjaxHeader.php');

$vendor=$VK->QRow("select count(id) from base_vendor where device_id=".$_POST['id']);
$model=$VK->QRow("select count(id) from base_model where device_id=".$_POST['id']);
$zayav=$VK->QRow("select count(id) from zayav where zayav_status>0 and base_device_id=".$_POST['id']);

if($vendor == 0 and $model == 0 and $zayav == 0) {
  $VK->Query("delete from base_device where id=".$_POST['id']);
  $send->result = 1;
} else {
  $send = $VK->QueryObjectOne("select id,name from base_device where id=".$_POST['id']);
  $send->result = 0;
  $send->vendor = $vendor;
  $send->model = $model;
  $send->zayav = $zayav;
}

GvaluesCreate();

echo json_encode($send);
?>



