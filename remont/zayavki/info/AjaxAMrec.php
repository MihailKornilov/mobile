<?php
require_once('../../../include/AjaxHeader.php');

 $VK->Query("update ".$_POST['table']." set status=1,viewer_id_del=0,dtime_del='0000-00-00 00:00:00' where ws_id=".$vku->ws_id." and id=".$_POST['id']." and status=0");

setClientBalans($_GET['client_id']);

echo "&nbsp;";
?>
