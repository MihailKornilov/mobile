<?php
require_once('../include/AjaxHeader.php');

$VK->Query("update setup_global set script_style=script_style+1");

$send->time = getTime($T);

echo json_encode($send);
?>



