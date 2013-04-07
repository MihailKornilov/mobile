function clientAdd(callback) {
  $("#dialog_client").remove();
  $("#frameBody").append("<DIV id=dialog_client></DIV>");
  var HTML="<TABLE cellpadding=0 cellspacing=10>";
  HTML+="<TR><TD class=tdAbout>Имя:<TD id=al><INPUT TYPE=text id=fio style=width:220px;>";
  HTML+="<TR><TD class=tdAbout>Телефон:<TD><INPUT TYPE=text id=telefon style=width:220px;>";
  HTML+="</TABLE>";
  var dialog = $("#dialog_client").vkDialog({
    head:"Добавление нoвого клиента",
    content:"<CENTER>" + HTML + "</CENTER>",
    submit:function () {
      var obj = {
        fio:$("#fio").val(),
        telefon:$("#telefon").val()
      };
      if(!obj.fio) {
        $("#al").alertShow({txt:'<SPAN class=red>Не указано имя клиента.</SPAN>',top:-43,left:-2});
      }  else  {
        dialog.process();
        $.post("/include/clients/AjaxClientAdd.php?"+G.values, obj, function (res) { callback(res, dialog); }, 'json');
      }
    },
    focus:'#fio'
  }).o;
}







// выбор клиентов из списка и добавление нового. Применяется во внесении новой заявки, в продаже запчасти
$.fn.clientSel = function (obj) {
  if (!obj) { var obj = {}; }
  obj.width = obj.width || 240;
  obj.skip = obj.skip || 0; // пропустить клиента в списке
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
    title0:'Начните вводить данные клиента...',
    spisok:clients,
    limit:50,
    ro:0,
    nofind:'Клиентов не найдено',
    funcAdd:obj.add
  }).o;

  t.o = cl;
  return t;
};




