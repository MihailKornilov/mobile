<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("update workshop set org_name='".win1251(textFormat($_POST['org_name']))."' where id=".$vku->ws_id);

echo 1;
?>
