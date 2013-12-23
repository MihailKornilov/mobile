<?php
if(!isset($SA[$_GET['viewer_id']])) header("Location:".$URL);

include('incHeader.php');

$zayav_count = array();
$spisok = $VK->QueryRowArray("select id from setup_device_status order by sort");
foreach($spisok as $sp) {
  array_push($zayav_count, $VK->QRow("select count(id) from zayav where device_place=".$sp[0]));
}

?>

<DIV class=path>
  <?php include('superadmin/incBack.php'); ?>
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">Администрирование</A> » 
  Местонахождения устройств в заявках
</DIV>

<DIV id=setup_device_place>
  <DIV class=headName>Местонахождения устройств в заявках<A class=add val=add_>Новое местонахождение</A></DIV>
  <TABLE cellpadding=0 cellspacing=0 class=tabSpisok>
	 <TR><TH class=uid>id
   	   	<TH	ss=name>Наименование
            	H c	=za	count>Количество<BR>заявок
              <TH class=edit>
  </TABLE>
  <DL id=drag></DL>
  <DL id=device_place_dialog></DL>
</DIV>


<SCRIPT type="text/javascript">G.zayav_count = [<?php echo implode(',', $zayav_count); ?>]</SCRIPT>
<SCRIPT type="text/javascript" src="/superadmin/device/place/devicePlace.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>
