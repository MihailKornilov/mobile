<?php
function zEnd($count)
	{
	$ost=$count%10;
	$ost10=$count/10%10;

	if($ost10==1) return '';
	else
		switch($ost)
			{
			case '1': return 'о';
			case '2': return 'а';
			case '3': return 'а';
			case '4': return 'а';
			default: return '';
			}
	}

require_once('../../include/AjaxHeader.php');

$find="where id";
if($_GET['device']>0) $find.=" and device_id=".$_GET['device'];
if($_GET['vendor']>0) $find.=" and vendor_id=".$_GET['vendor'];
if($_GET['model']>0) $find.=" and id=".$_GET['model'];
if($_GET['empty']>0) $find.=" and viewer_id_edit=0 and hide=0";

$send[0]->count=$VK->QRow("select count(id) from base_model ".$find);
if($_GET['device']>0 or $_GET['vendor']>0 or $_GET['model']>0) $fCount="Найдено "; else $fCount="В каталоге ";
$send[0]->result=iconv("WINDOWS-1251","UTF-8",$fCount.$send[0]->count." устройств".zEnd($send[0]->count));
$send[0]->page=0;

$spisok=$VK->QueryObjectArray("select * from base_model ".$find." order by id ".($_GET['desc']==0?'desc':'')." limit ".(($_GET['page']-1)*20).",20");
if(count($spisok)>0)
	{
	$device=$VK->QueryPtPArray("select id,name from base_device");
	$vendor=$VK->QueryPtPArray("select id,name from base_vendor");

	foreach($spisok as $n=>$sp)
		{
		$send[$n]->id=$sp->id;
		$send[$n]->dev=iconv("WINDOWS-1251","UTF-8",$device[$sp->device_id]);
		$send[$n]->dev_name=iconv("WINDOWS-1251","UTF-8",$vendor[$sp->vendor_id]." ".$sp->name);
		$send[$n]->img=$sp->img;
		$send[$n]->hide=$sp->hide;
		}
	if(count($spisok)==20)
		{
		$count=$VK->QNumRows("select id from base_model ".$find." limit ".($_GET['page']*20).",20");
		$_GET['page']++;
		if($count>0) $send[0]->page=$_GET['page'];
		}
	}

echo json_encode($send);
?>



