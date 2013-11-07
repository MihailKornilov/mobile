<?php
include('incHeader.php');
?>

<SCRIPT type="text/javascript">
$(document).ready(function(){
	VK.callMethod('setLocation','saDevSpec');

	$("#device").vkSel({
		width:220,
		spisok:<?php echo $VK->vkSelJson("select id,name from base_device order by name"); ?>,
		title:'�������� ����������',
		func:function(id){
			if(id>0)
				{
				$("#razdelAdd").show();
				specSpisokGet(id);
				}
			else
				$("#razdelAdd").hide();
			}
		});

	/* �������� ������ ������� ������������� */
	$("#razdelAdd").click(function(){
		var HTML="	<TABLE cellpadding=0 cellspacing=8>";
		HTML+="<TR><TD class=tdAbout>������������:<TD><INPUT type=text id=name maxlength=100 onkeydown='if(event.keyCode==13)specRazdelAdd();' style=width:200px;>";
		HTML+="</TABLE>";
		dialogShow({
			head:"���������� ������ ������� �������������",
			content:HTML,
			submit:specRazdelAdd,
			focus:'#name'
			});
		});

	frameBodyHeightSet(350);
	});

/* ���������� ������� ��� ������������� */
function specRazdelAdd()
	{
	NAME=$("#name").val();
	if(!NAME)
		$("#name").css('border','#F66 solid 1px').focus();
	else
		{
		var DEVID=$("#device").val();
		$("#butDialog").butProcess();
		$.post("/superadmin/device/specific/AjaxSpecRazdelAdd.php?"+VALUES,{device_id:DEVID,name:NAME},function(res){
			dialogHide();
			_msg("���������� ������ ������� ������� �����������!");
			specSpisokGet(DEVID);
			},'json');
		}
	}


/* ���������� ����� �������������� � ������˨���� ������ */
function specificAdd(ID,OBJ)
	{
	var HTML="	<TABLE cellpadding=0 cellspacing=8>";
	HTML+="<TR><TD class=tdAbout>������:<TD>"+$(OBJ).next().html();
	HTML+="<TR><TD class=tdAbout>������������:<TD><INPUT type=text id=name maxlength=100 onkeydown='if(event.keyCode==13)specificAddGo();' style=width:250px;>";
	HTML+="<TR><TD class=tdAbout>��������:<TD><TEXTAREA id=info onkeydown='if(event.keyCode==13)specificAddGo();' style=width:250px;height:60px;></TEXTAREA>";
	HTML+="</TABLE>";
	dialogShow({
		width:400,
		head:"���������� ����� ��������������",
		content:HTML,
		submit:function(){ specificAddGo(ID); },
		focus:'#name'
		});
	}
function specificAddGo(RAZID)
	{
	NAME=$("#name").val();
	if(!NAME)
		$("#name").css('border','#F66 solid 1px').focus();
	else
		{
		$("#butDialog").butProcess();
		var DEVID=$("#device").val();
		$.post("/superadmin/device/specific/AjaxSpecificAdd.php?"+VALUES,{device_id:DEVID,razdel_id:RAZID,name:NAME,info:$("#info").val()},function(res){
			dialogHide();
			vkM_msg������� ����� �������������� ������� �����������!");
			specSpisokGet(DEVID);
			},'json');
		}
	}


/* ����� ������ �������� */
function specSpisokGet(ID)
	{
	$("#specificSpisok").load("/superadmin/device/specific/AjaxSpecSpisok.php?"+VALUES+"&id="+ID,function(){
		var H=document.getElementById('specificSpisok').offsetHeight+100;
		if(H<350) H=350;
		frameBodyHeightSet(H);
		});
	}

/* �������� �������� */
function specRazdelDel(ID,OBJ)
	{
	dialogShow({
		head:"�������� �������",
		content:"	<CENTER>������� ������ <B>"+$(OBJ).next().next().html()+"</B>?</CENTER>",
		submit:function(){ specRazdelDelGo(ID); },
		butSubmit:'OK'
		});
	}
function specRazdelDelGo(ID)
	{
	$.getJSON("/superadmin/device/specific/AjaxSpecRazdelDel.php?"+VALUES+"&id="+ID,function(){
		dialogHide();
		specSpisokGet($("#device").val());
		vkMsgOk("������ �����!");
		});
	}

</SCRIPT>

<DIV class=path>
  <?php include('superadmin/incBack.php'); ?>
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">�����������������</A> � 
  �������������� ���������
</DIV>

<DIV id=devSpecific>
	
	<TABLE cellspacing=10 cellpadding=0>
	<TR>
		<TD><INPUT type=hidden id=device>
		<TD><A href="#" id=razdelAdd>�������� ����� ������</A>
	</TABLE> 
	
	<DIV id=specificSpisok></DIV>
</DIV>



<?php include('incFooter.php'); ?>


