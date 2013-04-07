<?php
require_once('../AjaxHeader.php');

$txt=str_replace("'","&#039;",iconv("UTF-8", "WINDOWS-1251",$_POST['txt']));
$txt=str_replace("<","&lt;",$txt);
$txt=str_replace(">","&gt;",$txt);
$txt=str_replace("\n","<BR>",trim($txt));

$send->id=$VK->Query("insert into vk_comment (
table_name,
table_id,
parent_id,
txt,
viewer_id_add
) values (
'".$_POST['table_name']."',
".$_POST['table_id'].",
".$_POST['parent_id'].",
'".$txt."',
".$_POST['viewer_id'].")");

if($_POST['parent_id']>0)
	{
	$count=$VK->QRow("select count(id) from vk_comment where status=1 and parent_id=".$_POST['parent_id']);
	$VK->Query("update vk_comment set child_count=".$count." where id=".$_POST['parent_id']);
	}
else $send->count=$VK->QRow("select count(id) from vk_comment where parent_id=0 and status=1 and table_name='".$_POST['table_name']."' and table_id=".$_POST['table_id']);

$send->txt=iconv("WINDOWS-1251","UTF-8",$txt);
$send->dtime_add=iconv("WINDOWS-1251","UTF-8",FullDataTime(strftime("%Y-%m-%d %H:%M:%S",time())));

echo json_encode($send);
?>



