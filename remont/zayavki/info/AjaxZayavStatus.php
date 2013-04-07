<?php
require_once('../../../include/AjaxHeader.php');

$status_new = '';
if($_POST['zayav_status_new'] == 1) {
  $status_new = ",zayav_status=".$_POST['zayav_status'].",zayav_status_dtime=current_timestamp";
  $send->status_dtime = utf8(FullDataTime(strftime("%Y-%m-%d %H:%M:%S", time()), 1));
  $VK->Query("insert into history (ws_id,type,zayav_id,value,viewer_id_add) values (".$vku->ws_id.",4,".$_POST['zayav_id'].",".$_POST['zayav_status'].",".$_GET['viewer_id'].")");
}

$VK->Query("update zayavki set
device_status=".$_POST['device_status'].",
device_place=".$_POST['device_place'].",
device_place_other='".textFormat(win1251($_POST['device_place_other']))."'
".$status_new." where id=".$_POST['zayav_id']);

$send->time = getTime($T);

echo json_encode($send);
?>

