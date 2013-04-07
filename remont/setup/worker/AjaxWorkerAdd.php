<?php
require_once('../../../include/AjaxHeader.php');

$us = $VK->QueryObjectOne("select viewer_id,ws_id from vk_user where viewer_id=".$_POST['viewer_id']);

if ($us->viewer_id) {
  if ($us->ws_id > 0) {
    $send->ws_id = $us->ws_id;
    $send->res = 'no';
  } else {
    $VK->Query("update vk_user set ws_id=".$vku->ws_id." where viewer_id=".$us->viewer_id);
    $send->res = 'upd';
  }
} else {
  $VK->Query("insert into vk_user (
ws_id,
viewer_id,
first_name,
last_name,
sex,
photo,
country_id,
country_name,
city_id,
city_name
) values (
".$vku->ws_id.",
".$_POST['viewer_id'].",
'".win1251($_POST['first_name'])."',
'".win1251($_POST['last_name'])."',
".$_POST['sex'].",
'".$_POST['photo']."',
".$_POST['country'].",
'".win1251($_POST['country_name'])."',
".$_POST['city'].",
'".win1251($_POST['city_name'])."'
)");
  $send->res = 'add';
}
echo json_encode($send);
?>
