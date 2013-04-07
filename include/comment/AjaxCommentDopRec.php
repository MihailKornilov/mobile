<?php
require_once('../AjaxHeader.php');

$VK->Query("update vk_comment set status=1,viewer_id_del=0,dtime_del='0000-00-00 00:00:00' where id=".$_POST['rec']);
$parent=$VK->QRow("select parent_id from vk_comment where id=".$_POST['rec']);
$VK->QRow("update vk_comment set child_count=child_count+1 where id=".$parent);

echo 1;
?>
