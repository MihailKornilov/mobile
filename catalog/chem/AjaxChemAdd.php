<?php
require_once('../../include/AjaxHeader.php');

$send->id=$VK->Query("insert into chem_catalog (
name,
about,
base_device_id,
base_vendor_id,
base_model_id,
link,
viewer_id_add
) values (
'".iconv("UTF-8","WINDOWS-1251",$_POST['name'])."',
'".iconv("UTF-8","WINDOWS-1251",$_POST['about'])."',
".$_POST['device'].",
".$_POST['vendor'].",
".$_POST['model'].",
'".$_POST['link']."',
".$_GET['viewer_id'].")");

echo json_encode($send);
?>



