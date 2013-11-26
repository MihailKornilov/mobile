faultPrint();

function faultPrint() {
  var html ="";
  for (var n = 0; n < G.fault_spisok.length; n++) {
	var sp = G.fault_spisok[n];
   	l += "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok id=table_" + sp.uid + ">";
    ht	= "<TR><TD class=uid>" + sp.uid;
    html 	<TD class=name>" + sp.title;
    html += 	 class=edit>";
      html += "	 class=img_edit val=edit_" + sp.uid + "></DIV>";
      html += "<DI	ass=img_del val=del_" + sp.uid + "></DIV>";
    html += "</TABLE>	 }
  $("#drag").html(html);
} // end faultPrint


$("#drag").sortable({
  axis:'y',
  update:function () {
    var uids = $(this).f	".uid");
    if (uids.length > 1) {
	  var arr = [];
      for(	n = 0; n < uids.len	 n++) { arr.push(uids.eq(n).html()); }
      $.getJSON("/superadmin/fault/A	aultSort.php?" + VALUES + "&val=" + arr.join(','), function () {  });
    }
  }
});


$("#setup_fault").click	ction (e) {
  var val = $(e.target).attr('val');
  if (val) {
    val = val.split('_');
    switch (val[	{
    case 'edit': edit(v	]); break;
    case '	: add(); break;
    case 'del': del(v	]); break;
    }
  }

  // ��	�������� ������������
  function ed	d) {
    var dialog = $("#fault_dialog")._dialog({
      width:24	     head:'��������� ������������',
      con	:"������������: 	UT type=text id=fault_name value='" 	"#table_" + id + " .name:first").html() + "'>",
      butSubmit:'���������',
      submit:function () {
        var send
          id:id,
          	:$("#fault_name").val()
  	  }	      dialog.pro	();	     $.post(	per	n/fault/AjaxFaultEdit.php?" + G.V	Sse	functi	res	          dialog.clos
  	    _msg("��������!");
          $("#table_" + id + " .name:first").html(send.name	   	, 'json');
      }
  	.o;	 // end edit

  // �����	���	������������
  function add() {
    var dialog = $("#fau	ial	._dialog({
    	dth:300	    head:'�������� ����� �������������',
      content:"������������: <INPUT ty	ext id=fault_name>",
      submit:function ()	       var send 	          name:$("#fault_name").val()
    	};
        dialog.process();
        $.post("/superadmin/fault	xFaultAdd.php?" + G.vaVALU	, f	ion (res) {
    	  G	lt_spisok.push({uid:res.id, title	d.n	);
   	   	tPrint();
          d	g.c	();
          _msg("�������!");
        }, 'json');
      }
    }).o;
  } // end

 	�������� �������������
  function del(id) {
    var dialo	$("	lt_dialog")._dialog	   	dth:240,
      head:'	���	�����������',
      con	:"<	��������� �����	/B><BR>	 $("#table_" + id + " .name:first").html() + "'",
      butSubmit:'�������'	    submit:function () {
        dialog.proce	;
        $.post	uperadmin/fault/AjaxFaultDel.php?" +	alues, {id:id}, function (res) {
          dialog.close();
          _msg("�������!");
          $(	ble_" + id).remove();
    	}, 'json');
      }
    }).o;
  } // end edit
}); // end click


