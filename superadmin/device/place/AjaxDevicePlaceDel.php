<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("delete from setup_device_place where id=".$_POST['id']);

GvaluesCreate();

$send->id = $_POST['id'];

echo json_encode($send);
?>



