faultPrint();

function faultPrint() {
  var html ="";
  for (var n = 0; n < G.fault_spisok.length; n++) {
    var sp = G.fault_spisok[n];
    html += "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok id=table_" + sp.uid + ">";
    html += "<TR><TD class=uid>" + sp.uid;
    html += "<TD class=name>" + sp.title;
    html += "<TD class=edit>";
      html += "<DIV class=img_edit val=edit_" + sp.uid + "></DIV>";
      html += "<DIV class=img_del val=del_" + sp.uid + "></DIV>";
    html += "</TABLE>";
  }
  $("#drag").html(html);
} // end faultPrint


$("#drag").sortable({
  axis:'y',
  update:function () {
    var uids = $(this).find(".uid");
    if (uids.length > 1) {
      var arr = [];
      for(var n = 0; n < uids.length; n++) { arr.push(uids.eq(n).html()); }
      $.getJSON("/superadmin/fault/AjaxFaultSort.php?" + VALUES + "&val=" + arr.join(','), function () {  });
    }
  }
});


$("#setup_fault").click(function (e) {
  var val = $(e.target).attr('val');
  if (val) {
    val = val.split('_');
    switch (val[0]) {
    case 'edit': edit(val[1]); break;
    case 'add': add(); break;
    case 'del': del(val[1]); break;
    }
  }

  // �������������� ������������
  function edit(id) {
    var dialog = $("#fault_dialog")._dialog({
      width:240,
      head:'��������� ������������',
      content:"������������: <INPUT type=text id=fault_name value='" + $("#table_" + id + " .name:first").html() + "'>",
      butSubmit:'���������',
      submit:function () {
        var send = {
          id:id,
          name:$("#fault_name").val()
        };
        dialog.process();
        $.post("/superadmin/fault/AjaxFaultEdit.php?" + G.VALUESsend, function (res) {
          dialog.close();
          _msg("��������!");
          $("#table_" + id + " .name:first").html(send.name)
        }, 'json');
      }
    }).o;
  } // end edit

  // �������� ����� �������������
  function add() {
    var dialog = $("#fault_dialog")._dialog({
      width:300,
      head:'�������� ����� �������������',
      content:"������������: <INPUT type=text id=fault_name>",
      submit:function () {
        var send = {
          name:$("#fault_name").val()
        };
        dialog.process();
        $.post("/superadmin/fault/AjaxFaultAdd.php?" + G.vaVALUESnd, function (res) {
          G.fault_spisok.push({uid:res.id, title:send.name});
          faultPrint();
          dialog.close();
          _msg("�������!");
        }, 'json');
      }
    }).o;
  } // end edit

  // �������� �������������
  function del(id) {
    var dialog = $("#fault_dialog")._dialog({
      width:240,
      head:'�������� �������������',
      content:"<B>����������� ��������</B><BR>'" + $("#table_" + id + " .name:first").html() + "'",
      butSubmit:'�������',
      submit:function () {
        dialog.process();
        $.post("/superadmin/fault/AjaxFaultDel.php?" + G.values, {id:id}, function (res) {
          dialog.close();
          _msg("�������!");
          $("#table_" + id).remove();
        }, 'json');
      }
    }).o;
  } // end edit
}); // end click


