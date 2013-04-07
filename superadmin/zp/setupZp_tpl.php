<?php
if($_POST['zpName']) $VK->Query("insert into setup_zp_name (name) values ('".$_POST['zpName']."')");

$spisok=$VK->QueryObjectArray("select * from setup_zp_name order by name");
if(count($spisok)>0)
	{
	$zpName="<TR><TH>Наименование запчасти<TH>В каталоге<TH>Редактирование";
	foreach($spisok as $n)
		{
		$cZp=$VK->QRow("select count(id) from zp_catalog where name_id=".$n->id);
		$zpName.="<TR id=tr".$n->id."><TD>".$n->name."<TD align=center>".($cZp>0?$cZp:'&nbsp;')."<TD align=center><DIV class=delete onclick=zpNameDel(".$n->id.");>&nbsp;</DIV>";
		}
	}
include('incHeader.php');
?>

<SCRIPT type="text/javascript">
function zpNameAddGo()
	{
	if(!formZpName.zpName.value)
		alert('Введите наименование запчасти');
	else formZpName.submit();
	}

function zpNameDel(id)
	{
	$.ajax({
		url:"/superadmin/zp/AjaxZpNameDel.php?id="+id,
		success:function(data){
			$("#tr"+id).html("<TD colspan=3 align=center>Удалено.");	
			}
		});
	}
</SCRIPT>

<DIV class=path>
  <?php include('superadmin/incBack.php'); ?>
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">Администрирование</A> » 
  Наименования запчастей
</DIV>

<DIV id=setupZpName>
	<DIV class=headName>Добавление нового наименования для запчастей</DIV>
	<TABLE cellpadding=0 cellspacing=10>
	<TR><TD class=tdAbout>Наименование:<TD><FORM method=post action="<?php echo $URL; ?>&my_page=saZp" name=formZpName><INPUT type=text name=zpName></FORM>
	</TABLE>
  <DIV class=vkButton ><BUTTON onclick=zpNameAddGo();>Внести</BUTTON></DIV>

<BR><BR>

	<DIV class=headName>Список</DIV>
	<TABLE cellpadding=0 cellspacing=0 class=tabSpisok>
	<?php echo $zpName; ?>
	</TABLE> 
</DIV>



<?php include('incFooter.php'); ?>


