<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("delete from setup_device_specific_razdel where id=".$_GET['id']);
$VK->Query("delete from setup_device_specific_item where razdel_id=".$_GET['id']);

$send=1;
echo json_encode($send);
?>



