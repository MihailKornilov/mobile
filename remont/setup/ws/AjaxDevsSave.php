<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("update workshop set devs='".$_POST['devs']."' where id=".$vku->ws_id);

$send->time = getTime($T);

echo json_encode($send);
?>
