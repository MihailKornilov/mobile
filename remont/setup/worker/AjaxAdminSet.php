<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("update vk_user set admin=".$_POST['admin']." where viewer_id=".$_POST['viewer_id']);
echo 1;
?>
