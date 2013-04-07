<?php
require_once('../AjaxHeader.php');

$send->id = $VK->Query("insert into base_vendor (device_id,name,viewer_id_add) values (".$_POST['device_id'].",'".win1251($_POST['name'])."',".$vku->viewer_id.")");

GvaluesCreate();

echo json_encode($send);
?>



