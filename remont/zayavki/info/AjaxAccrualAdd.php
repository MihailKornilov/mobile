<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("insert into accrual (
ws_id,
client_id,
zayavki_id,
summa,
prim,
viewer_id_add
) values (
".$vku->ws_id.",
".$_POST['cid'].",
".$_POST['zid'].",
".$_POST['summa'].",
'".win1251(textFormat($_POST['prim']))."',
".$_GET['viewer_id'].")");

$VK->Query("insert into history (ws_id,type,zayav_id,value,viewer_id_add) values (".$vku->ws_id.",5,".$_POST['zid'].",".$_POST['summa'].",".$_GET['viewer_id'].")");

setClientBalans($_POST['cid']);

$status_new = '';
if($_POST['status_new'] == 1) {
  $status_new = ",zayav_status=".$_POST['status'].",zayav_status_dtime=current_timestamp";
  $VK->Query("insert into history (ws_id,type,zayav_id,value,viewer_id_add) values (".$vku->ws_id.",4,".$_POST['zid'].",".$_POST['status'].",".$_GET['viewer_id'].")");
}
$VK->Query("update zayavki set device_status=".$_POST['device_status'].$status_new." where ws_id=".$vku->ws_id." and id=".$_POST['zid']);

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
".$_POST['zid'].",
'".textFormat(win1251($_POST['reminder_txt']))."',
'".$_POST['reminder_day']."',
'".FullDataTime(strftime("%Y-%m-%d %H:%M:%S", time()))." ".$vk_name." добавил напоминание при внесении начисления.',
".$_GET['viewer_id']."
)");
}

$spisok = $VK->QueryObjectArray("select * from accrual where ws_id=".$vku->ws_id." and zayavki_id=".$_POST['zid']." and status=1 order by id");
$send = array();
foreach($spisok as $sp) {
  array_push($send, array(
    'id' => $sp->id,
    'summa' => $sp->summa,
    'prim' => utf8($sp->prim),
    'dtime' => utf8(FullDataTime($sp->dtime_add,1))
  ));
}

echo json_encode($send);
?>
