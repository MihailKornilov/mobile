<?php
require_once('../../../include/AjaxHeader.php');

$send->id = $VK->Query("insert into setup_device_place (name) values ('".win1251($_POST['name'])."')");

GvaluesCreate();

echo json_encode($send);
?>



