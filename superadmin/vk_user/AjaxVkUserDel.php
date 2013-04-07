<?php
require_once('../../include/AjaxHeader.php');

$send->id = $VK->Query("delete from vk_user where viewer_id=".$_POST['viewer_id']." and dtime_add='".$_POST['dtime']."'");

echo json_encode($send);
?>



