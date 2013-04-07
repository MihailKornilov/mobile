<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("update setup_device_place set name='".win1251($_POST['name'])."' where id=".$_POST['id']);

$send->id = $_POST['id'];

GvaluesCreate();

echo json_encode($send);
?>



