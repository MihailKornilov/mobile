<?php
if(!isset($SA[$_GET['viewer_id']])) header("Location:".$URL);

include('incHeader.php');
?>
<DIV class=path><?php include('superadmin/incBack.php'); ?>Администрирование</DIV>

<DIV style="padding:20px 0px 100px 20px">
  <DIV><B>Мастерские и сотрудники:</B></DIV>
  <A href="<?php echo $URL; ?>&my_page=saVkUser">Пользователи (<?php echo $VK->QRow("select count(viewer_id) from vk_user"); ?>)</A><BR>
  <A href="<?php echo $URL; ?>&my_page=saWS">Мастерские (<?php echo $VK->QRow("select count(id) from workshop"); ?>)</A><BR>
  <BR>
  <DIV><B>Устройства и запчасти:</B></DIV>
  <A href="<?php echo $URL; ?>&my_page=saFault">Виды неисправностей</A><BR>
  <BR>
  <A href="<?php echo $URL; ?>&my_page=saDevice">Устройства</A><BR>
  <A href="<?php echo $URL; ?>&my_page=saDevSpec">Характеристики устройств для информации</A><BR>
  <A href="<?php echo $URL; ?>&my_page=saDevStatus">Статусы устройств в заявках</A><BR>
  <A href="<?php echo $URL; ?>&my_page=saDevPlace">Местонахождения устройств в заявках</A><BR>
  <BR>
  <A href="<?php echo $URL; ?>&my_page=saColor">Цвета для устройств и запчастей</A><BR>
  <BR>
  <A href="<?php echo $URL; ?>&my_page=saZp">Наименования запчастей</A><BR>
</DIV>




<?php include('incFooter.php'); ?>
</SCRIPT>