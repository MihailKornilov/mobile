<?php
require_once('../../../include/AjaxHeader.php');

$money_id = $VK->Query("insert into money (
ws_id,
client_id,
zayav_id,
summa,
kassa,
prim,
viewer_id_add
) values (
".$vku->ws_id.",
".$_POST['client_id'].",
".$_POST['zayav_id'].",
".$_POST['summa'].",
".$_POST['kassa'].",
'".win1251(textFormat($_POST['prim']))."',
".$_GET['viewer_id'].")");

$VK->Query("insert into history (ws_id,type,zayav_id,value,viewer_id_add) values (".$vku->ws_id.",6,".$_POST['zayav_id'].",".$_POST['summa'].",".$_GET['viewer_id'].")");

// Внесение в кассу
if ($_POST['kassa'] == 1) {
  $VK->Query("insert into kassa (
type,
ws_id,
sum,
zayav_id,
money_id,
viewer_id_add
) values (
1,
".$vku->ws_id.",
".$_POST['summa'].",
".$_POST['zayav_id'].",
".$money_id.",
".$_GET['viewer_id']."
)");
}


setClientBalans($_POST['client_id']);

$VK->Query("update zayavki set device_place=".$_POST['device_place'].",device_place_other='' where ws_id=".$vku->ws_id." and id=".$_POST['zayav_id']);

$spisok = $VK->QueryObjectArray("select * from money where ws_id=".$vku->ws_id." and zayav_id=".$_POST['zayav_id']." and status=1 order by id");
if(count($spisok) > 0) {
  foreach($spisok as $n => $sp) {
    $send[$n]->id = $sp->id;
    $send[$n]->summa = $sp->summa;
    $send[$n]->prim = utf8($sp->prim);
    $send[$n]->dtime = utf8(FullDataTime($sp->dtime_add, 1));
  }
}

echo json_encode($send);
?>
