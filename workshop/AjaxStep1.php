<?php
require_once('../include/AjaxHeader.php');

$send->id = $VK->Query("insert into workshop (
admin_id,
org_name,
country_id,
country_name,
city_id,
city_name,
devs
) values (
".$vku->viewer_id.",
'".win1251(textFormat($_POST['org_name']))."',
".$_POST['country_id'].",
'".win1251($_POST['country_name'])."',
".$_POST['city_id'].",
'".win1251($_POST['city_name'])."',
'".$_POST['devs']."'
)");

$VK->Query("update vk_user set ws_id=".$send->id.",admin=1 where viewer_id=".$vku->viewer_id);

$send->time = getTime($T);

echo json_encode($send);;
?>



