<?php
require_once('../AjaxHeader.php');

$user=$VK->QueryObjectOne("select * from vk_user where viewer_id=".$_GET['viewer_id']);
$send[0]->autor_viewer_id=$_GET['viewer_id'];
$send[0]->autor_first_name=iconv("WINDOWS-1251","UTF-8",$user->first_name);
$send[0]->autor_last_name=iconv("WINDOWS-1251","UTF-8",$user->last_name);
$send[0]->autor_photo=$user->photo;

$spisok=$VK->QueryObjectArray("select * from vk_user where ws_id=".$vku->ws_id);
foreach($spisok as $sp)
	{
	$vkUs[$sp->viewer_id]->first_name=iconv("WINDOWS-1251","UTF-8",$sp->first_name);
	$vkUs[$sp->viewer_id]->last_name=iconv("WINDOWS-1251","UTF-8",$sp->last_name);
	$vkUs[$sp->viewer_id]->photo=$sp->photo;
	}

$spisok=$VK->QueryObjectArray("select * from vk_comment where parent_id=0 and status=1 and table_name='".$_GET['table_name']."' and table_id=".$_GET['table_id']." order by id desc");
$send[0]->count=count($spisok);
if($send[0]->count>0)
	foreach($spisok as $n=>$sp)
		{
		$send[$n]->id=$sp->id;
		$send[$n]->viewer_id=$sp->viewer_id_add;
		$send[$n]->first_name=$vkUs[$sp->viewer_id_add]->first_name;
		$send[$n]->last_name=$vkUs[$sp->viewer_id_add]->last_name;
		$send[$n]->photo=$vkUs[$sp->viewer_id_add]->photo;
		$send[$n]->txt=iconv("WINDOWS-1251","UTF-8",$sp->txt);
		$send[$n]->child=$sp->child_count;
		$send[$n]->dtime_add=iconv("WINDOWS-1251","UTF-8",FullDataTime($sp->dtime_add));
		}

echo json_encode($send);
?>
