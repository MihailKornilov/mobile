<?php include('incHeader.php'); ?>

<SCRIPT type="text/javascript">
$(document).ready(function(){
	VK.callMethod('setLocation','nopage');
	frameBodyHeightSet();
	});
</SCRIPT>


<DIV class=path>������</DIV>

<DIV class=nopage>
	������: �������������� ��������.<BR><BR>
	<DIV class=vkButton onclick="location.href='<?=$URL."&my_page=".$_GET['parent']; ?>';"><BUTTON>�����</BUTTON></DIV>
</DIV>


<?php include('incFooter.php'); ?>

