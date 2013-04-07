<?php
header("Location: ". $URL."&my_page=remZayavki");
$model = $VK->QueryObjectOne("select * from base_model where id=".(preg_match("|^[\d]+$|",$_GET['id'])?$_GET['id']:0));
if(!$model->id) header("Location: ". $URL."&my_page=nopage&parent=remDevice");

$devName=$VK->QRow("select name from base_device where id=".$model->device_id);
$vendorName=$VK->QRow("select name from base_vendor where id=".$model->vendor_id);

$devNameFull=$vendorName." ".$model->name;

$razdel=$VK->QueryObjectArray("select * from setup_device_specific_razdel where base_device_id=".$model->device_id." order by id");
if(count($razdel)>0)
	{
	$specArr=$VK->QueryPtPArray("select specific_id,value from device_specific where base_model_id=".$model->id);
	foreach($razdel as $n=>$raz)
		{
		$specTab='';
		$spec=$VK->QueryObjectArray("select * from setup_device_specific_item where razdel_id=".$raz->id." order by id");
		if(count($spec)>0)
			foreach($spec as $m=>$s)
				if($specArr[$s->id])
					$specTab.="<TR><TD class=tdAbout>".$s->name.":<TD>".$specArr[$s->id];
		if($specTab) $specific.="<H2>".$raz->name."</H2><TABLE cellpadding=0 cellspacing=2 class=specific>".$specTab."</TABLE>";
		
		}
	}

include('incHeader.php');
$sel = 'remDevice'; include('remont/mainLinks.php');
$dLink1='Sel'; include('remont/device/view/dopLinks.php');
?>


<TABLE cellpadding=0 cellspacing=0 class=remDeviceView>
	<TR><TD class=td1><DIV id=foto></DIV>
	<TD class=td2>
		<H1><?php echo $devNameFull; ?></H1>
		<TABLE cellpadding=0 cellspacing=2 class=specific><TR><TD class=tdAbout>Тип устройства:<TD><?php echo $devName; ?></TABLE>
		<?php echo $specific; ?>
</TABLE>


<INPUT type=hidden id=table_name value='base_model'>
<INPUT type=hidden id=table_id value=<?php echo $model->id; ?>>
<SCRIPT type="text/javascript" src="/include/foto/foto.js?<?php echo $G->script_style; ?>"></SCRIPT>


<?php include('incFooter.php'); ?>

