<?php
require_once('../../../include/AjaxHeader.php');

$vk_name = $VK->QRow("select concat(first_name, ' ', last_name) from vk_user where viewer_id=".$_GET['viewer_id']);

$send->id = $VK->Query("insert into reminder (
ws_id,
client_id,
zayav_id,
txt,
day,
private,
history,
viewer_id_add
) values (
".$vku->ws_id.",
".$_POST['client_id'].",
".$_POST['zayav_id'].",
'".textFormat(win1251($_POST['txt']))."',
'".$_POST['day']."',
".$_POST['private'].",
'".FullDataTime(strftime("%Y-%m-%d %H:%M:%S", time()))." ".$vk_name." создал задание.',
".$_GET['viewer_id']."
)");

echo json_encode($send);;
?>



