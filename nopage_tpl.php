<?php include('incHeader.php'); ?>

<SCRIPT type="text/javascript">
$(document).ready(function(){
	VK.callMethod('setLocation','nopage');
	frameBodyHeightSet();
	});
</SCRIPT>


<DIV class=path>Ошибка</DIV>

<DIV class=nopage>
	Ошибка: несуществующая страница.<BR><BR>
	<DIV class=vkButton onclick="location.href='<?=$URL."&my_page=".$_GET['parent']; ?>';"><BUTTON>Назад</BUTTON></DIV>
</DIV>


<?php include('incFooter.php'); ?>

