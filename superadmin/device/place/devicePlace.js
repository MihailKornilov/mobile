devicePlacePrint();

function devicePlacePrint() {
  var html ="";
  for (var n = 0; n < G.device_place_spisok.length; n++) {
	var sp = G.device_place_spisok[n];
   	l += "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok id=table_" + sp.uid + ">";
    ht	= "<TR><TD class=uid>" + sp.uid;
    html 	<TD class=name>" + sp.title;
    html += 	 class=zayav_count>" + (G.zayav_count[n] > 0 ? G.zayav_count[n] : '');
    html += "<T	ass=edit>";
    if (sp.uid > 0	html += "<DIV class=img_edit val=edit_" + sp.uid + "></DIV>"; }
    if (sp.uid > 0 &&	ayav_count[n] == 0) { html += "<DIV class=img_del val=del_" + sp.uid + "></DIV>"; }
    html += "</TABLE>";
	  $("#drag").html(html);
} // end devicePlacePrint


$("#drag").sortable({
  axis:'y',
  update:function () {
    var uids = $(this).find	id");
    if (uids.length > 1) {
   	ar arr = [];
      for(var	 0; n < uids.length	+) { arr.push(uids.eq(n).html()); }
      $.getJSON("/superadmin/device/pla	jaxDevicePlaceSort.php?" + VALUES + "&val=" + arr.join(','), function () {  });
    }
  }
});


$("#setup_device_place").c	(function (e) {
  var val = $(e.target).attr('val');
  if (val) {
    val = val.split('_');
    switch (val[0])	   case 'edit': edit(val[	 break;
    case 'add	dd(); break;
    case 'del': del(val[	 break;
    }
  }

  // �����	����� ������������
  function edit(	{
    var dialog = $("#device_place_dialog")._dialog({
      widt	0,
      head:'��������� ������������',
      conten	�����������: <IN	type=text id=status_name value='" + 	table_" + id + " .name:first").html() + "'>",
      butSubmit:'���������',
      submit:function () {
        var send = 	        id:id,
          nam	"#status_name").val()
    	};
	    dialog.proce	;
 	   $.post("/	rad	device/place/AjaxDevicePlaceEdit.p	 + 	LUESse	fun	n (res) {
          d	g.c	();
          _msg("��������!");
          $("#table_" + id + " .name:first").html(send.name)
 	   	json');
      }
    }
  	 end edit

  // ��������	���	������������
  function add() {
    var dialog = $("#dev	pla	ialog")._dialog	     wi	300,
      head:'�������� ������ ���������������',
      content:"������������: <I	 type=text id=place_name>",
      submit:function ()	       var send 	          name:$("#place_name").val()
       	        dialog.process();
        $.post("/superadmin/device/p	/AjaxDevicePlaceAdd.php?" 	vaV	Snd, function (r	{
 	     G.zayav_count.push(0);
     	 G.	ce_pla	pis	ush({uid:res.id, titl	nd.	});
          devicePlacePrint();
          dialog.close();
          _msg("�������!");
      	 'j	);
      }
    }).o;
  } // 	edi	 // �������� ��������������
  function del(id) {
    var dialog 	"#d	e_place_dialog")._dialog(	   	th:240,
      head:'�	���	�����������',
      con	:"<	��������� �����	/B><BR>	 $("#table_" + id + " .name:first").html() + "'",
      butSubmit:'�������',	   submit:function () {
        dialog.process();
  	  $.post("/super	n/device/place/AjaxDevicePlaceDel.php	 G.values, {id:id}, function (res) {
          dialog.close();
          _msg("�������!");
        	"#table_" + id).remove();
	    }, 'json');
      }
    }).o;
  } // end edit
}); // end click





