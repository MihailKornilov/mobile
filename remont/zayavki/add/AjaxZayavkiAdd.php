<?php
require_once('../../../include/AjaxHeader.php');

$modelName = '';
if($_POST['model'] > 0) { $modelName = $VK->QRow("select name from base_model where id=".$_POST['model']); }

$send->id = $VK->Query("insert into zayavki (
ws_id,
nomer,
client_id,
category,

base_device_id,
base_vendor_id,
base_model_id,

imei,
serial,
color_id,

zayav_status,
zayav_status_dtime,

device_status,
device_place,
device_place_other,

viewer_id_add,
find
) values (
".$vku->ws_id.",
".$VK->QRow("select ifnull(max(nomer),0)+1 from zayavki where ws_id=".$vku->ws_id).",
".$_POST['client'].",
".$_POST['category'].",

".$_POST['device'].",
".$_POST['vendor'].",
".$_POST['model'].",

'".textFormat(win1251($_POST['imei']))."',
'".textFormat(win1251($_POST['serial']))."',
".$_POST['color'].",

1,
current_timestamp,

1,
".$_POST['place'].",
'".textFormat(win1251($_POST['place_other']))."',

".$_GET['viewer_id'].",
'".$modelName." ".$_POST['imei']." ".$_POST['serial']."'
)");



$VK->Query("update client set zayav_count=zayav_count+1 where id=".$_POST['client']);
GclientsCreate();

if (strlen($_POST['comm']) > 0) {
  $VK->Query("insert into vk_comment (table_name,table_id,txt,viewer_id_add) values ('zayav',".$send->id.",'".textFormat(win1251($_POST['comm']))."',".$_GET['viewer_id'].")");
}

// Добавление напоминания
if ($_POST['reminder'] == 1) {
  $vk_name = $VK->QRow("select concat(first_name, ' ', last_name) from vk_user where viewer_id=".$_GET['viewer_id']);
  $VK->Query("insert into reminder (
ws_id,
zayav_id,
txt,
day,
history,
viewer_id_add
) values (
".$vku->ws_id.",
".$send->id.",
'".textFormat(win1251($_POST['reminder_txt']))."',
'".$_POST['reminder_day']."',
'".FullDataTime(strftime("%Y-%m-%d %H:%M:%S", time()))." ".$vk_name." добавил напоминание для заявки.',
".$_GET['viewer_id']."
)");
}

$VK->Query("insert into history (ws_id,type,client_id,zayav_id,viewer_id_add) values (".$vku->ws_id.",1,".$_POST['client'].",".$send->id.",".$_GET['viewer_id'].")");

echo json_encode($send);
?>

