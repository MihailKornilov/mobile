<?php
if ($VK->QRow("select count(id) from workshop where status=1 and admin_id=".$_GET['viewer_id']) > 0) header("Location: ".$URL."&my_page=remClient");
include('incHeader.php');
?>

<DIV style="background-color:#F7F7F7;padding:10px;">
	<DIV class=infoTxt><H3>����� ���������� � ���������� Hi-Tech Service!</H3> 
	������ ���������� �������� ���������� ��� ����� ������� ��������� ���������, ���, ���������, ����������� � ������ ���������������� ���������� � ������� �������.<BR>
	<BR>
	<U>��� ������ ��������� �����:</U><BR>
	 - ����� ���������� ���� (�������, �������� ���������� � ��������, ������� ����� ���������� � ������);<BR>
	 - ����� ���� ���������, �������� � ������;<BR>
	 - ��������� ������ �� ����������� ������;<BR>
	 - ��������� �������;<BR>
	 - ��������, �������� ���������� � ���������.<BR>
	 <!-- - �������� ���������� � ��������������� ������ ����������,<BR>
	 - ���������, ��������� �����, ��������, ������-�������,<BR> -->
	<BR>
	����� ���������� ������������ ������� <A href="http://vk.com/page-28447634_39893509" target="_blank">������������</A> ����������.<BR>
	<BR>
	��� ����, ����� ������ ������������ �����������, ���������� ������� ���� ����������.</DIV>

	<CENTER>
		<DIV class=vkButton><BUTTON onclick="location.href='<?php echo $URL; ?>&my_page=wsStep1'"; id=wsCreate>���������� � �������� ����������</BUTTON></DIV>&nbsp;
		<!-- <DIV class=vkCancel><BUTTON onclick="location.href='<?php echo $URL; ?>&my_page=catalog'">��������� � �������</BUTTON></DIV> -->
	</CENTER>
</DIV>

<?php include('incFooter.php'); ?>


