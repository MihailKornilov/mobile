<?php
require_once('../../include/AjaxHeader.php');

$send->id = $VK->Query("insert into setup_fault (name,viewer_id_add) values ('".win1251($_POST['name'])."',".$_GET['viewer_id'].")");

GvaluesCreate();

echo json_encode($send);
?>



