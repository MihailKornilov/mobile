<?php
if(!isset($SA[$_GET['viewer_id']])) header("Location:".$URL);

include('incHeader.php');

$zayav_count = array();
array_push($zayav_count, $VK->QRow("select count(id) from zayavki where device_status=0"));
$spisok = $VK->QueryRowArray("select id from setup_device_status order by sort");
foreach($spisok as $sp) {
  array_push($zayav_count, $VK->QRow("select count(id) from zayavki where device_status=".$sp[0]));
}
?>

<DIV class=path>
  <?php include('superadmin/incBack.php'); ?>
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">�����������������</A> � 
  ������� ��������� � �������
</DIV>

<DIV id=setup_device_status>
  <DIV class=headName>������� ��������� � �������<A class=add val=add_>����� ������</A></DIV>
  <TABLE cellpadding=0 cellspacing=0 class=tabSpisok>
	 <TR><TH class=uid>id
   	   	<TH	ss=name>������������
            	H c	=za	count>����������<BR>������
              <TH class=edit>
  </TABLE>
  <DL id=drag></DL>
  <DL id=device_status_dialog></DL>
</DIV>


<SCRIPT type="text/javascript">G.zayav_count = [<?php echo implode(',', $zayav_count); ?>]</SCRIPT>
<SCRIPT type="text/javascript" src="/superadmin/device/status/deviceStatus.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>
