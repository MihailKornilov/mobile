<?php
if ($VK->QRow("select count(id) from workshop where status=1 and admin_id=".$_GET['viewer_id']) > 0) header("Location: ".$URL."&my_page=remClient");
include('incHeader.php');
?>

<DIV style="background-color:#F7F7F7;padding:10px;">
	<DIV class=infoTxt><H3>Добро пожаловать в приложение Hi-Tech Service!</H3> 
	Данное приложение является программой для учёта ремонта мобильных телефонов, КПК, ноутбуков, телевизоров и другой радиоэлектронной аппаратуры и бытовой техники.<BR>
	<BR>
	<U>При помощи программы можно:</U><BR>
	 - вести клиентскую базу (хранить, изменять информацию о клиентах, которые сдают устройства в ремонт);<BR>
	 - вести учёт устройств, принятых в ремонт;<BR>
	 - начислять оплату за выполненную работу;<BR>
	 - принимать платежи;<BR>
	 - получать, изменять информацию о запчастях.<BR>
	 <!-- - получать информацию о характеристиках любого устройства,<BR>
	 - скачивать, загружать схемы, прошивки, сервис-мануалы,<BR> -->
	<BR>
	Более подробного ознакомления читайте <A href="http://vk.com/page-28447634_39893509" target="_blank">документацию</A> приложения.<BR>
	<BR>
	Для того, чтобы начать пользоваться приложением, необходимо создать свою мастерскую.</DIV>

	<CENTER>
		<DIV class=vkButton><BUTTON onclick="location.href='<?php echo $URL; ?>&my_page=wsStep1'"; id=wsCreate>Приступить к созданию мастерской</BUTTON></DIV>&nbsp;
		<!-- <DIV class=vkCancel><BUTTON onclick="location.href='<?php echo $URL; ?>&my_page=catalog'">Вернуться в каталог</BUTTON></DIV> -->
	</CENTER>
</DIV>

<?php include('incFooter.php'); ?>


