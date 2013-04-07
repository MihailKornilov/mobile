<?php
require_once('../../include/AjaxHeader.php');

$max = $VK->QRow("select max(sort)+1 from base_vendor where device_id=".$_POST['device_id']);

$send->id = $VK->Query("insert into base_vendor (

device_id,
name,
bold,
sort,
viewer_id_add

) values (

".$_POST['device_id'].",
'".iconv("UTF-8","WINDOWS-1251",$_POST['name'])."',
".$_POST['bold'].",
".$max.",
".$_GET['viewer_id']."

)");

GvaluesCreate();

echo json_encode($send);
?>



