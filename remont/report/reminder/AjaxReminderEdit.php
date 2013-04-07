<?php
require_once('../../../include/AjaxHeader.php');

$vk_name = $VK->QRow("select concat(first_name, ' ', last_name) from vk_user where viewer_id=".$_GET['viewer_id']);

$action = '';
switch($_POST['action']) {
  case 1: $action = " указал новую дату: ".FullData($_POST['day']).". Причина: ".win1251($_POST['history']); break;
  case 2: $action = " выполнил задание.".($_POST['history'] ? " (".win1251($_POST['history']).")" : ''); break;
  case 3: $action = " отменил задание. Причина: ".win1251($_POST['history']); break;
}

$send->id = $VK->Query("update reminder set
day='".$_POST['day']."',
status=".$_POST['status'].",
history=concat(history,'<BR>".FullDataTime(strftime("%Y-%m-%d %H:%M:%S", time()))." ".$vk_name.$action."')
where id=".$_POST['id']);

echo json_encode($send);;
?>



