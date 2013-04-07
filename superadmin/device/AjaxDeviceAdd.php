<?php
require_once('../../include/AjaxHeader.php');

$max = $VK->QRow("select max(sort)+1 from base_device");

$send->id = $VK->Query("insert into base_device (

name,
name_rod,
name_mn,
sort,
viewer_id_add

) values (

'".iconv("UTF-8","WINDOWS-1251",$_POST['name'])."',
'".iconv("UTF-8","WINDOWS-1251",$_POST['name_rod'])."',
'".iconv("UTF-8","WINDOWS-1251",$_POST['name_mn'])."',
".$max.",
".$_GET['viewer_id']."

)");

GvaluesCreate();

echo json_encode($send);
?>



