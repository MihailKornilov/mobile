<?php
if ($_POST['colorName']) {
  $VK->Query("insert into setup_color_name (name,viewer_id_add) values ('".$_POST['colorName']."',".$vku->viewer_id.")");
  GvaluesCreate();
}

$spisok=$VK->QueryObjectArray("select * from setup_color_name order by name");
if(count($spisok)>0)
	{
	$colorName="<TR><TH>����<TH>��������������";
	foreach($spisok as $n)
		$colorName.="<TR id=tr".$n->id."><TD>".$n->name."<TD align=center><DIV class=delete onclick=colorNameDel(".$n->id.");>&nbsp;</DIV>";
	}
include('incHeader.php');
?>

<SCRIPT type="text/javascript">
function colorNameAddGo()
	{
	if(!formColorName.colorName.value)
		alert('������� ������������ ��������');
	else formColorName.submit();
	}

function colorNameDel(id)
	{
	$.ajax({
		url:"/superadmin/color/AjaxColorNameDel.php?color_id="+id,
		success:function(data){
			$("#tr"+id).html("<TD colspan=3 align=center>�������.");	
			}
		});
	}
</SCRIPT>

<DIV class=path>
  <?php include('superadmin/incBack.php'); ?>
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">�����������������</A> � 
  ����� ��� ��������� � ���������
</DIV>

<DIV id=setupZpName>
	<DIV class=headName>���������� ������ �����</DIV>
	<TABLE cellpadding=0 cellspacing=10>
	<TR><TD class=tdAbout>����:<TD><FORM method=post action="<?php echo $URL; ?>&my_page=saColor" name=formColorName><INPUT type=text name=colorName></FORM>
	</TABLE>
  <DIV class=vkButton ><BUTTON onclick=colorNameAddGo();>������</BUTTON></DIV>

<BR><BR>

	<DIV class=headName>������</DIV>
	<TABLE cellpadding=0 cellspacing=0 class=tabSpisok>
	<?php echo $colorName; ?>
	</TABLE> 
</DIV>



<?php include('incFooter.php'); ?>


