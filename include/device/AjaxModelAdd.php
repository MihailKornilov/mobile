<?php
require_once('../AjaxHeader.php');

$send->id = $VK->Query("insert into base_model (device_id,vendor_id,name,viewer_id_add) values (".$_POST['device_id'].",".$_POST['vendor_id'].",'".win1251($_POST['name'])."',".$vku->viewer_id.")");

GvaluesCreate();

echo json_encode($send);
?>



