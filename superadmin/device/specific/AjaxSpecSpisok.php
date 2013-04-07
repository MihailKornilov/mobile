<?php
require_once('../../../include/AjaxHeader.php');

$razdel=$VK->QueryObjectArray("select * from setup_device_specific_razdel where base_device_id=".$_GET['id']." order by id");
if(count($razdel)>0)
	foreach($razdel as $n=>$raz)
		{
		$send.="<DIV class=headBlue>";
		$send.="<A href='javascript:' onclick=specRazdelDel(".$raz->id.",this);>Удалить</A>";
		$send.="<A href='javascript:' onclick=specificAdd(".$raz->id.",this);>Новая характеристика</A>";
		$send.="<SPAN>".$raz->name."</SPAN></DIV>";
		$spec=$VK->QueryObjectArray("select * from setup_device_specific_item where razdel_id=".$raz->id." order by id");
		if(count($spec)>0)
			{
			$send.="<TABLE cellpadding=0 cellspacing=0 class=tab>";
			foreach($spec as $m=>$s)
				{
				$send.="<TR><TD class=name>".$s->name."<TD>".$s->info;
				}
			$send.="</TABLE>";
			}
		}


echo $send;
?>



