function clientAdd(callback) {
  $("#dialog_client").remove();
  $("#frameBody").append("<DIV id=dialog_client></DIV>");
  var HTML="<TABLE cellpadding=0 cellspacing=10>";
  HTML+="<TR><TD class=tdAbout>���:<TD id=al><INPUT TYPE=text id=fio style=width:220px;>";
  HTML+="<TR><TD class=tdAbout>�������:<TD><INPUT TYPE=text id=telefon style=width:220px;>";
  HTML+="</TABLE>";
  var dialog = $("#dialog_client").vkDialog({
    head:"���������� �o���� �������",
    content:"<CENTER>" + HTML + "</CENTER>",
    submit:function () {
      var obj = {
        fio:$("#fio").val(),
        telefon:$("#telefon").val()
      };
      if(!obj.fio) {
        $("#al").alertShow({txt:'<SPAN class=red>�� ������� ��� �������.</SPAN>',top:-43,left:-2});
      }  else  {
        dialog.process();
        $.post("/include/clients/AjaxClientAdd.php?"+G.values, obj, function (res) { callback(res, dialog); }, 'json');
      }
    },
    focus:'#fio'
  }).o;
}







// ����� �������� �� ������ � ���������� ������. ����������� �� �������� ����� ������, � ������� ��������
$.fn.clientSel = function (obj) {
  if (!obj) { var obj = {}; }
  obj.width = obj.width || 240;
  obj.skip = obj.skip || 0; // ���������� ������� � ������
  obj.add = obj.add || null;
  
  var t = $(this);

  var clients = [];
  for (var n=0; n < G.clients.length; n++) {
    var sp = G.clients[n];
    if (obj.skip != sp.id) {
      clients.push({
        uid:sp.id,
        title:sp.fio,
        content:sp.fio + (sp.telefon ? "<DIV class=pole2>" + sp.telefon + "</DIV>" : '')
      });
    }
  }

  if (obj.add) {
    obj.add = function () {
      clientAdd(function (obj, dialog) {
        cl.add(obj).val(obj.uid);
        dialog.close();
      });
    };
  }

  var cl = t.vkSel({
    width:obj.width,
    title0:'������� ������� ������ �������...',
    spisok:clients,
    limit:50,
    ro:0,
    nofind:'�������� �� �������',
    funcAdd:obj.add
  }).o;

  t.o = cl;
  return t;
};




