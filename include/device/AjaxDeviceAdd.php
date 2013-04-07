<?php
require_once('../AjaxHeader.php');

$name = win1251($_POST['name']);
$send->id = $VK->Query("insert into base_device (name,name_rod,name_mn,viewer_id_add) values ('".$name."','".$name."','".$name."',".$vku->viewer_id.")");

GvaluesCreate();

echo json_encode($send);
?>



