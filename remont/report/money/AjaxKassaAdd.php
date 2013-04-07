<?php
require_once('../../../../include/AjaxHeader.php');

$send->id = $VK->Query("insert into kassa (
sum,
txt,
viewer_id_add
) values (
".$_POST['sum'].",
'".textFormat(win1251($_POST['txt']))."',
".$_GET['viewer_id']."
)");

echo json_encode($send);;
?>



