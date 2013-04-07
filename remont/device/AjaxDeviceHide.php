<?php
require_once('../../include/AjaxHeader.php');

$VK->Query("update base_model set hide=".$_GET['hide']." where id=".$_GET['id']);
$send=0;
echo json_encode($send);
?>



