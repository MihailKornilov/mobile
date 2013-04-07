<?php
require_once('../../../include/AjaxHeader.php');

$send->id=0;

$zayav=$VK->QueryObjectOne("select * from zayavki where ws_id=".$vku->ws_id." and zayav_status>0 and nomer=".number($_GET['nomer']));

if (isset($zayav->id)) {
  $send->id = $zayav->id;
  $send->category = $zayav->category;
  $send->device_id = $zayav->base_device_id;
  $send->vendor_id = $zayav->base_vendor_id;
	$send->model_id = $zayav->base_model_id;;
  $link = $VK->QRow("select link from images where owner='zayav".$zayav->id."' and sort=0");
  if (!$link) { $link = $VK->QRow("select link from images where owner='dev".$zayav->base_model_id."' and sort=0"); }
	$send->img = $link ? $link."-small.jpg" : '/img/nofoto.gif';
}

echo json_encode($send);
?>
