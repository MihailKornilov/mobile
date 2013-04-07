<?php
/*
 * Вывод списка напоминаний
*/

require_once('../../../include/AjaxHeader.php');

$find="where ws_id=".$vku->ws_id." and status=".$_GET['status'];
if ($_GET['private'] == 1) {
  $find .= " and private=1 and viewer_id_add=".$vku->viewer_id;
} else {
  $find .= " and (private=0 or private=1 and viewer_id_add=".$vku->viewer_id.")";
}

$send->all = $VK->QRow("select count(id) from reminder ".$find);
$send->next = 0;
$send->spisok = array();

$spisok = $VK->QueryObjectArray("select * from reminder ".$find." order by day limit ".$_GET['start'].",".$_GET['limit']);
if(count($spisok) > 0) {
  $fio = array();
  $zayav_nomer = array();
  foreach ($spisok as $sp) {
    if ($sp->client_id > 0) { array_push($fio, $sp->client_id); }
    if ($sp->zayav_id > 0) { array_push($zayav_nomer, $sp->zayav_id); }
  }

  if (count($fio) > 0) { $fio = $VK->QueryPtPArray("select id,fio from client where id in (".implode(',', array_unique($fio)).")"); }
  if (count($zayav_nomer) > 0) { $zayav_nomer = $VK->QueryPtPArray("select id,nomer from zayavki where id in (".implode(',', array_unique($zayav_nomer)).")"); }

  $today = strtotime(strftime("%Y-%m-%d", time()));
  foreach ($spisok as $sp) {
    $unit = array(
      'id' => $sp->id,
      'txt' => utf8($sp->txt),
      'day' => utf8(FullData($sp->day, 1)),
      'day_real' => $sp->day,
      'day_leave' => (strtotime($sp->day) - $today) / 3600 / 24,
      'history' => utf8($sp->history),
      'private' => $sp->private,
      'status' => $sp->status,
      'dtime' => utf8(FullDataTime($sp->dtime_add, 1)),
      'viewer_id' => $sp->viewer_id_add
    );

    if ($sp->client_id > 0) { $unit['client_id'] = $sp->client_id; }
    if(isset($fio[$sp->client_id])) { $unit['client_fio'] =  utf8($fio[$sp->client_id]); }
    if ($sp->zayav_id > 0) { $unit['zayav_id'] = $sp->zayav_id; }
    if(isset($zayav_nomer[$sp->zayav_id])) { $unit['zayav_nomer'] =  utf8($zayav_nomer[$sp->zayav_id]); }

    array_push($send->spisok, $unit);
  }
  if (count($spisok) == $_GET['limit']) {
    if ($VK->QNumRows("select id from reminder ".$find." limit ".($_GET['start'] + $_GET['limit']).",".$_GET['limit']) > 0) {
      $send->next = 1;
    }
  }
}

$send->time = getTime($T);

echo json_encode($send);;
?>



