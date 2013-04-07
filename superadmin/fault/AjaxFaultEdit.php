<?php
require_once('../../include/AjaxHeader.php');

$VK->Query("update setup_fault set name='".win1251($_POST['name'])."' where id=".$_POST['id']);

$send->id = $_POST['id'];

GvaluesCreate();

echo json_encode($send);
?>



