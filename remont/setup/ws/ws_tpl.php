<?php
if ($vku->admin == 0) header("Location:".$URL);
/*
if($_GET['action']=='delete' and $_COOKIE['wsAction']=='del')
	{
	setcookie("wsAction","",time()-1,"/");
	$VK->Query("update workshop set status=0,dtime_del=current_timestamp where id=".$vku->ws_id);
//	$VK->Query("update worker set active=0,dtime_del=current_timestamp where ws_id=".$vku->ws_id);
	header("Location:".$URL);
	}

if($_POST['ws_id'])
	{
	$VK->Query("update workshop set org_name='".$_POST['org_name']."' where id=".$_POST['ws_id']);
	setcookie("save","ok",time()+10,"/");
	header("Location:".$URL."&my_page=remSetup");
	}

if($_COOKIE['save'])
	{
	setcookie("save","",time()-1,"/");
//	$msg="<DIV class=msgOk>Изменения сохранены.</DIV>";	
	}
*/
include('incHeader.php');

$sel = 'remSetup'; include('remont/mainLinks.php');
$dLink1 = 'Sel'; include('remont/setup/dopLinks.php');
?>

<DIV id=setupMain>
	<DIV class=headName id=headName>Информация о мастерской</DIV>
	<TABLE cellpadding=0 cellspacing=8 class=infoTab>
	<TR><TD class=tdAbout>Название организации:<TD><INPUT type=text id=org_name maxlength=50 value='<?php echo $WS->org_name; ?>'><SPAN id=org_name_save></SPAN>
	<TR><TD class=tdAbout>Город:<TD><?php echo $WS->city_name.", ".$WS->country_name; ?>
	<TR><TD class=tdAbout>Главный администратор:<TD><B><?php echo $vku->first_name." ".$vku->last_name; ?></B>
	</TABLE>

	<DIV class=headName>Категории ремонтируемых устройств</DIV><DIV id=devs></DIV>

	<DIV class=headName>Удаление мастерской</DIV>
	<H3>Мастерская, а также все данные удаляются без возможности восстановления.</H3>
	<DIV class=vkButton id=ws_del><BUTTON>Удалить мастерскую</BUTTON></DIV>

  <DIV id=ws_dialog></DIV>
</DIV>



<SCRIPT type="text/javascript">
G.ws = {
  devs:[<?php echo $WS->devs; ?>],
  org_name:"<?php echo $WS->org_name; ?>"
};
</SCRIPT>
<SCRIPT type="text/javascript" src="/remont/setup/ws/ws.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>

