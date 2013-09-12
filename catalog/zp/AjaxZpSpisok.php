<?php
function zpEnd($count)
	{
	$ost=$count%10;
	$ost10=$count/10%10;

	if($ost10==1) return '��';
	else
		switch($ost)
			{
			case '1': return '�';
			case '2': return '�';
			case '3': return '�';
			case '4': return '�';
			default: return '��';
			}
	}

require_once('../../include/AjaxHeader.php');

$find="where zp_catalog.id";
if($_GET['name_id']>0) $find.=" and zp_catalog.name_id=".$_GET['name_id'];
if($_GET['device_id']>0) $find.=" and zp_catalog.base_device_id=".$_GET['device_id'];
if($_GET['vendor_id']>0) $find.=" and zp_catalog.base_vendor_id=".$_GET['vendor_id'];
if($_GET['model_id']>0) $find.=" and zp_catalog.base_model_id=".$_GET['model_id'];
if($_GET['fast']) $find.=" and zp_catalog.find like '%".$_GET['fast']."%'";

if($_GET['name_id']>0 or $_GET['device_id']>0 or $_GET['vendor_id']>0 or $_GET['model_id']>0 or $_GET['fast']) $msg=1;


$send[0]->page=0;
$send[0]->type=$_GET['type'];

$send[0]->count=$VK->QRow("select count(id) from zp_catalog ".$find);
if($msg) $msg="������".($send[0]->count%10==1?'�':'�')." "; else $msg="� �������� ";
if($send[0]->count>0)
	{
	$spisok=$VK->QueryRowArray("select 
id,
name_id,
base_device_id,
base_vendor_id,
base_model_id,
0,
0,
img,
color_id
from zp_catalog ".$find." order by id desc limit ".(($_GET['page']-1)*20).",20");
	if(count($spisok)==20)
		if($VK->QNumRows("select id from zp_catalog ".$find." limit ".($_GET['page']*20).",20")>0)
			$send[0]->page=$_GET['page']+1;
	}

$send[0]->result=iconv("WINDOWS-1251","UTF-8",$msg.$send[0]->count." ������������ �������".zpEnd($send[0]->count));
$send[0]->add='';

$zpName=$VK->QueryPtPArray("select id,name from setup_zp_name");
$zpColor=$VK->QueryPtPArray("select id,name from setup_color_name");
$devName=$VK->QueryPtPArray("select id,name_rod from base_device");
$vendorName=$VK->QueryPtPArray("select id,name from base_vendor");
$modelName=$VK->QueryPtPArray("select id,name from base_model");

if($send[0]->count>0) 
	foreach($spisok as $n=>$sp)
		{
		$n++;
		if($sp[5]==0) $sp[5]=$VK->QRow("select count from zp_avai where ws_id=".$vku->ws_id." and zp_catalog_id=".$sp[0]);
		if($sp[6]==0) $sp[6]=$VK->QRow("select count from zp_zakaz where ws_id=".$vku->ws_id." and zp_catalog_id=".$sp[0]);
		$send[$n]->id=$sp[0];
		$send[$n]->img=$sp[7];
		$send[$n]->count_avai=($sp[5]?$sp[5]:0);
		$send[$n]->count_zakaz=($sp[6]?$sp[6]:0);
		$send[$n]->zp_name=iconv("WINDOWS-1251","UTF-8",$zpName[$sp[1]]);
		$send[$n]->color=($sp[8]>0?iconv("WINDOWS-1251","UTF-8",$zpColor[$sp[8]]):'');
		$send[$n]->dev_name=iconv("WINDOWS-1251","UTF-8",$vendorName[$sp[3]]." ".$modelName[$sp[4]]);
		$send[$n]->for_dev=iconv("WINDOWS-1251","UTF-8",$devName[$sp[2]]);
		}

echo json_encode($send);
?>
