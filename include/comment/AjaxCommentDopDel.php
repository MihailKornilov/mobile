<?php
require_once('../AjaxHeader.php');

$VK->Query("update vk_comment set status=0,viewer_id_del=".$_GET['viewer_id'].",dtime_del=current_timestamp where id=".$_POST['del']);
$parent=$VK->QRow("select parent_id from vk_comment where id=".$_POST['del']);
$VK->QRow("update vk_comment set child_count=child_count-1 where id=".$parent);

echo 1;
?>
