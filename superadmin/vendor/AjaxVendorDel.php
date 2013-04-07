<?php
require_once('../../include/AjaxHeader.php');

$model=$VK->QRow("select count(id) from base_model where vendor_id=".$_POST['id']);
$zayav=$VK->QRow("select count(id) from zayavki where zayav_status>0 and base_vendor_id=".$_POST['id']);

if($model == 0 and $zayav == 0) {
  $VK->Query("delete from base_vendor where id=".$_POST['id']);
  $send->result = 1;
} else {
  $send = $VK->QueryObjectOne("select id,name,bold from base_vendor where id=".$_POST['id']);
  $send->result = 0;
  $send->model = $model;
  $send->zayav = $zayav;
}

GvaluesCreate();

echo json_encode($send);
?>



