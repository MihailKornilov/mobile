G.spisok.unit = function (sp) {
  var HTML = "<TABLE cellpadding=0 cellspacing=0 width=100%><TR><TD valign=top>";
  HTML += "<h2>#" + sp.nomer + "</h2><H1>" + G.category_ass[sp.category] + " <A HREF='javascript:'>" + G.device_ass[sp.device_id] + " <B>" + (G.vendor_ass[sp.vendor_id] || '') + " " + (G.model_ass[sp.model_id] || '') + "</B></A></H1>";
  HTML += "<TABLE cellpadding=0 cellspacing=2 class=tabInfo>";
  HTML += "<TR><TD class=tdAbout>Клиент:<TD><A HREF='index.php?" + G.values + "&my_page=remClientInfo&id="+sp.client_id+"'>"+sp.fio+"</A>";
  HTML += "<TR><TD class=tdAbout>Дата подачи:<TD>"+sp.dtime;
  HTML += "</TABLE>";
  HTML += "<TD class=image><IMG src="+(sp.img ? sp.img + "-small.jpg" : "/img/nofoto.gif")+">";
  HTML += "</TABLE>";
  return HTML;
};

G.articles = [];
G.spisok.create({
  url:"/remont/zayavki/spisok/AjaxZayavkiSpisok.php",
  limit:20,
  view:$("#spisok"),
  result_view:$("#findResult"),
  result:"Показан$show $count заяв$zayav",
  result_dop:"<H6><A HREF='javascript:' onclick=findDrop();>Сбросить условия поиска</A></H6>",
  ends:{'$show':['а', 'о'], '$zayav':['ка', 'ки', 'ок']},
  next:"Следующие 20 заявок",
  nofind:"Заявок не найдено.",
//  a:1,
  imgup:$("#findResult"),
  values:{
    fast:getCookie('zFindFast') || '',
    sort:getCookie('zFindSort') || 1,
    desc:getCookie('zFindDesc') || 0,
    status:getCookie('zFindStatus') || 0,
    device_id:getCookie('device_id') || 0,
    vendor_id:getCookie('vendor_id') || 0,
    model_id:getCookie('model_id') || 0,
    device_place:getCookie('device_place') || 0,
    device_status:getCookie('zFindDeviceStatus') || 0
  },
  callback:function (data) {
    for (var n = 0; n < data.length; n++) {
      var sp = data[n];
      $("#unit_" + sp.id)
        .css('background-color','#' + G.status_color_ass[sp.status])
        .click(function () { location.href = 'index.php?' + G.values + '&my_page=remZayavkiInfo&id=' + this.id.split('unit_')[1]; })
        .vkHint({
          width:150,
          msg:sp.article,
          ugol:'left',
          top:6,
          left:484,
          indent:5,
          delayShow:400
        });
    }
  }
});









$("#fast").topSearch({
  width:134,
  focus:1,
  txt:'Быстрый поиск...',
  enter:1,
  func:function (val) {
    setCookie('zFindFast', val);
    G.spisok.print({fast:val});
  }
}).topSearchSet(G.spisok.values.fast);

$("#sort")
  .val(G.spisok.values.sort)
  .myRadio({
    spisok:[{uid:1,title:'По дате добавления'},{uid:2,title:'По обновлению статуса'}],
    bottom:5,
    func:function (id) {
      setCookie('zFindSort', id);
      G.spisok.print({sort:id});
    }
  });

$("#desc").myCheck({
  title:"Обратный порядок",
  value:G.spisok.values.desc,
  func:function (id) {
    setCookie('zFindDesc', id);
    G.spisok.print({desc:id});
  }
});



// список-меню статусов
G.status_spisok.unshift({uid:0, title:'Любой статус'});
$("#status").infoLink({
  spisok:G.status_spisok,
  func:function (id) {
    setCookie('zFindStatus', id);
    G.spisok.print({status:id});
  }
}).infoLinkSet(G.spisok.values.status);




deviceSet();


// Нахождение устройства
for (var n = 0; n < G.device_place_other.length; n++) {
  var sp = G.device_place_other[n];
  G.device_place_spisok.push({uid:encodeURI(sp), title:sp});
}
G.device_place_spisok.push({uid:-1, title:'не известно', content:'<B>не известно</B>'});
G.vkSel_device_place = $("#device_place").vkSel({
  width:155,
  title0:'Любое местонахождение',
  value:G.spisok.values.device_place,
  spisok:G.device_place_spisok,
  func:function (id) {
    setCookie('device_place', id);
    G.spisok.print({device_place:id});
  }
}).o;

// Состояние устройства
G.device_status_spisok.splice(0, 1);
G.device_status_spisok.push({uid:-1, title:'не известно', content:'<B>не известно</B>'});
G.vkSel_device_status = $("#device_status").vkSel({
  width:155,
  title0:'Любое состояние',
  value:G.spisok.values.device_status,
  spisok:G.device_status_spisok,
  func:function (id) {
    setCookie('zFindDeviceStatus', id);
    G.spisok.print({device_status:id});
  }
}).o;










// Сброс условий поиска
function findDrop() {
  cookieDef();
  deviceSet();
  $("#desc").myCheckVal();
  $("#sort").myRadioSet(1);
  $("#fast").topSearchSet('');
  $("#status").infoLinkSet(0);
  G.vkSel_device_place.val(0);
  G.vkSel_device_status.val(0);
  G.spisok.print();
  }
function cookieDef() {
  G.spisok.values.fast = ''; setCookie('zFindFast','');
  G.spisok.values.sort = 1; setCookie('zFindSort',1);
  G.spisok.values.desc = 0; setCookie('zFindDesc',0);
  G.spisok.values.status = 0; setCookie('zFindStatus',0);
  G.spisok.values.device_id = 0; setCookie('device_id',0);
  G.spisok.values.vendor_id = 0; setCookie('vendor_id',0);
  G.spisok.values.model_id = 0; setCookie('model_id', 0);
  G.spisok.values.device_place = 0; setCookie('device_place', 0);
  G.spisok.values.device_status = 0; setCookie('zFindDeviceStatus', 0);
}





// установка списка устройств
function deviceSet() {
  $("#dev").device({
    width:155,
    type_no:1,
    device_id:G.spisok.values.device_id,
    vendor_id:G.spisok.values.vendor_id,
    model_id:G.spisok.values.model_id,
    device_ids:G.device_ids,
    vendor_ids:G.vendor_ids,
    model_ids:G.model_ids,
    func:function (res) {
      setCookie('device_id', res.device_id);
      setCookie('vendor_id', res.vendor_id);
      setCookie('model_id', res.model_id);
      G.spisok.print(res);
    }
  });
}



