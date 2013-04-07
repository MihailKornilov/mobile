<?php
if($VK->QRow("select count(id) from workshop where status=1 and admin_id=".$_GET['viewer_id'])>0) header("Location: ".$URL."&my_page=remClient");

include('incHeader.php');
?>

<DIV id=wCreate1>
  <DIV class=infoTxt>
    Для начала необходимо указать название Вашей мастерской и город, в котором Вы находитесь.<BR>
    Сотрудников и категории устройств можно будет добавить или изменить позднее.
  </DIV>
  <DIV class=headName>Создание мастерской</DIV>
  <TABLE cellpadding=0 cellspacing=8 class=tab>
  <TR><TD class=tdAbout>Название организации:<TD><INPUT type=text id=org_name maxlength=100>
  <TR><TD class=tdAbout>Страна:<TD><INPUT type=hidden id=countries value=<?php echo $vku->country_id; ?>>
  <TR><TD class=tdAbout>Город:<TD><INPUT type=hidden id=cities value=0>
  <TR><TD class=tdAbout>Главный администратор:<TD id=adm><?php echo $vku->first_name." ".$vku->last_name; ?>
  <TR><TD class=tdAbout valign=top>Категории устройств,<BR>ремонтом которых<BR>Вы занимаетесь:<TD id=devs>
  </TABLE>

  <DIV class=vkButton><BUTTON>Готово</BUTTON></DIV><DIV class=vkCancel><BUTTON>Отмена</BUTTON></DIV>
</DIV>


<SCRIPT type="text/javascript" src="/workshop/step1.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>
