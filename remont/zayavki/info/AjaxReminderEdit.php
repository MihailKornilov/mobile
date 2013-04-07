<?php
require_once('../../../include/AjaxHeader.php');

$vk_name = $VK->QRow("select concat(first_name, ' ', last_name) from vk_user where viewer_id=".$_GET['viewer_id']);

$action = '';
switch($_POST['action']) {
  case 1: $action = " указал новую дату: ".FullData($_POST['day']).". Причина: ".win1251($_POST['history']); break;
  case 2: $action = " выполнил задание.".($_POST['history'] ? " (".win1251($_POST['history']).")" : ''); break;
  case 3: $action = " отменил задание. Причина: ".win1251($_POST['history']); break;
}

$VK->Query("update reminder set
day='".$_POST['day']."',
status=".$_POST['status'].",
history=concat(history,'<BR>".FullDataTime(strftime("%Y-%m-%d %H:%M:%S", time()))." ".$vk_name.$action."')
where id=".$_POST['id']);

$send->reminder = array();
$spisok = $VK->QueryObjectArray("select * from reminder where ws_id=".$vku->ws_id." and zayav_id=".$_POST['zayav_id']." and status=1 and (private=0 or private=1 and viewer_id_add=".$vku->viewer_id.")");
if (count($spisok) > 0) {
  $today = strtotime(strftime("%Y-%m-%d", time()));
  foreach($spisok as $sp) {
    array_push($send->reminder, array(
      'id' => $sp->id,
      'txt' => utf8($sp->txt),
      'day' => utf8(FullData($sp->day, 1)),
      'day_real' => $sp->day,
      'day_leave' => (strtotime($sp->day) - $today) / 3600 / 24,
      'history' => utf8($sp->history),
      'private' => $sp->private,
      'dtime' => utf8(FullDataTime($sp->dtime_add, 1)),
      'viewer_id' => $sp->viewer_id_add
    ));
  }
}


echo json_encode($send);;
?>



