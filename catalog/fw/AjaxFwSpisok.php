<?php
function zEnd($count)
	{
	$ost=$count%10;
	$ost10=$count/10%10;

	if($ost10==1) return 'ок';
	else
		switch($ost)
			{
			case '1': return 'а';
			case '2': return 'и';
			case '3': return 'и';
			case '4': return 'и';
			default: return 'ок';
			}
	}

require_once('../../include/AjaxHeader.php');

$find="where id";
if($_GET['device']>0) $find.=" and device_id=".$_GET['device'];
if($_GET['vendor']>0) $find.=" and vendor_id=".$_GET['vendor'];
if($_GET['model']>0) $find.=" and id=".$_GET['model'];

$send[0]->count=$VK->QRow("select count(id) from fw_catalog ".$find);
if($_GET['device']>0 or $_GET['vendor']>0 or $_GET['model']>0) $fCount="Найдено "; else $fCount="В каталоге ";
$fCount="<H6><A href='javascript:' onclick=fwAdd();>Внести новую прошивку в каталог</A></H6>".$fCount;
$send[0]->result=iconv("WINDOWS-1251","UTF-8",$fCount.$send[0]->count." прошивк".zEnd($send[0]->count));
$send[0]->page=0;

$spisok=$VK->QueryObjectArray("select * from fw_catalog ".$find." order by id ".($_GET['desc']==0?'desc':'')." limit ".(($_GET['page']-1)*20).",20");
if(count($spisok)>0)
	{
	$device=$VK->QueryPtPArray("select id,name from base_device");
	$vendor=$VK->QueryPtPArray("select id,name from base_vendor");
	$model=$VK->QueryPtPArray("select id,name from base_model");

	foreach($spisok as $n=>$sp)
		{
		$send[$n]->id=$sp->id;
		$send[$n]->name=$sp->name;
		$send[$n]->link=$sp->link;
		$send[$n]->about=iconv("WINDOWS-1251","UTF-8",$sp->about);
		$send[$n]->dev=iconv("WINDOWS-1251","UTF-8",$device[$sp->base_device_id]);
		$send[$n]->dev_name=iconv("WINDOWS-1251","UTF-8",$vendor[$sp->base_vendor_id]." ".$model[$sp->base_model_id]);
		}
	if(count($spisok)==20)
		{
		$count=$VK->QNumRows("select id from fw_catalog ".$find." limit ".($_GET['page']*20).",20");
		$_GET['page']++;
		if($count>0) $send[0]->page=$_GET['page'];
		}
	}

echo json_encode($send);
?>



