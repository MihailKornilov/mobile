<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("update vk_user set ws_id=0,admin=0 where viewer_id=".$_POST['viewer_id']);
echo 1;
?>
