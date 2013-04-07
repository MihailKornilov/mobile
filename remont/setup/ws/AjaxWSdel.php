<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("update workshop set status=0,dtime_del=current_timestamp where id=".$vku->ws_id);
$VK->Query("update vk_user set ws_id=0,admin=0 where viewer_id=".$vku->viewer_id);

echo 1;
?>
