<?php
if(!isset($SA[$_GET['viewer_id']])) header("Location:".$URL);

include('incHeader.php');
?>
<DIV class=path><?php include('superadmin/incBack.php'); ?>�����������������</DIV>

<DIV style="padding:20px 0px 100px 20px">
  <DIV><B>���������� � ����������:</B></DIV>
  <A href="<?php echo $URL; ?>&my_page=saVkUser">������������ (<?php echo $VK->QRow("select count(viewer_id) from vk_user"); ?>)</A><BR>
  <A href="<?php echo $URL; ?>&my_page=saWS">���������� (<?php echo $VK->QRow("select count(id) from workshop"); ?>)</A><BR>
  <BR>
  <DIV><B>���������� � ��������:</B></DIV>
  <A href="<?php echo $URL; ?>&my_page=saFault">���� ��������������</A><BR>
  <BR>
  <A href="<?php echo $URL; ?>&my_page=saDevice">����������</A><BR>
  <A href="<?php echo $URL; ?>&my_page=saDevSpec">�������������� ��������� ��� ����������</A><BR>
  <A href="<?php echo $URL; ?>&my_page=saDevStatus">������� ��������� � �������</A><BR>
  <A href="<?php echo $URL; ?>&my_page=saDevPlace">��������������� ��������� � �������</A><BR>
  <BR>
  <A href="<?php echo $URL; ?>&my_page=saColor">����� ��� ��������� � ���������</A><BR>
  <BR>
  <A href="<?php echo $URL; ?>&my_page=saZp">������������ ���������</A><BR>
</DIV>




<?php include('incFooter.php'); ?>
</SCRIPT>