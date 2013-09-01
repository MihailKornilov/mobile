function menu(val) {
  $("#dopMenu A.linkSel").attr('class','link');
  $("#dopMenu A.link:eq(" + val + ")").attr('class','linkSel');
  $("#zDop").css('display', val == 0 ? 'block' : 'none');
  $("#zayav").css('display', val == 0 ? 'block' : 'none');
  $("#result").css('display', val == 0 ? 'block' : 'none');
  $("#client_money").css('display', val == 1 ? 'block' : 'none');
  $("#client_comment").css('display', val == 2 ? 'block' : 'none');
}





// список-меню статусов
G.status_spisok.unshift({uid:0, title:'Любой статус'});
$("#status").infoLink({
  spisok:G.status_spisok,
  func:function (id) { G.spisok.print({status:id}); }
});


// вывод устройств
$("#dev").device({
  width:146,
  type_no:1,
  device_ids:G.device_ids,
  vendor_ids:G.vendor_ids,
  model_ids:G.model_ids,
  func:function (res) { G.spisok.print(res); }
});





G.spisok.condition = function (obj) {
  if (obj.status > 0 || obj.device_id > 0 || obj.vendor_id > 0 || obj.model_id > 0) {
    var spisok = [];
    for (var n = 0; n < G.zayavki.length; n++) {
      var sp = G.zayavki[n];
      if (obj.status > 0 && sp.status != obj.status) continue;
      if (obj.device_id > 0 && sp.device_id != obj.device_id) continue;
      if (obj.vendor_id > 0 && sp.vendor_id != obj.vendor_id) continue;
      if (obj.model_id > 0 && sp.model_id != obj.model_id) continue;
      spisok.push(sp);
    }
    G.spisok.json = spisok;
  } else { G.spisok.json = G.zayavki; }
};


G.spisok.unit = function (sp) {
  var HTML = "<TABLE cellpadding=0 cellspacing=0 width=100%><TR><TD valign=top>";
  HTML += "<h2>#" + sp.nomer + "</h2><H1>" + G.category_ass[sp.category] + " <A HREF='javascript:'>" + G.device_ass[sp.device_id] + " <B>" + (G.vendor_ass[sp.vendor_id] || '') + " " + (G.model_ass[sp.model_id] || '') + "</B></A></H1>";
  HTML += "<TABLE cellpadding=0 cellspacing=2 class=tabInfo>";
  HTML += "<TR><TD class=tdAbout>Дата подачи:<TD>"+sp.dtime;
  HTML += "</TABLE>";
  HTML += "<TD class=image><IMG src="+(sp.img ? sp.img + "-small.jpg" : "/img/nofoto.gif")+">";
  HTML += "</TABLE>";
  return HTML;
};

G.articles = [];
G.spisok.create({
  json:G.zayavki,
  view:$("#zayav"),
  limit:10,
  result_view:$("#result"),
  result:"Показан$show $count заяв$zayav",
  ends:{'$show':['а', 'о'], '$zayav':['ка', 'ки', 'ок']},
  next:"Следующие 20 заявок",
  nofind:"Заявок не найдено.",
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
          top:5,
          left:483,
          indent:5,
          delayShow:400
        });
    }
  }
});









// ПРОСМОТР ПЛАТЕЖЕЙ
function oplataShow() {
  $(".headBlue").html("<DIV id=zResult><IMG src=/img/upload.gif></DIV>Платежи").show();
  $("#zDop").hide();
  $("#zSpisok").html('');
  var URL="&page=1&client=" + G.client.id;
  $.getJSON("/remont/client/info/AjaxOplataSpisok.php?" + G.values + URL,function(data){
    if(data[0].count>0)
      {
      var HTML="<TABLE cellpadding=0 cellspacing=0 class=tabSpisok>";
      HTML+="<TR><TH>Сумма<TH>Примечание<TH>Дата<TH>Принял";
      for(var n=0;n<data[0].count;n++)
        HTML+="<TR><TD align=center width=40><B>"+data[n].summa+"</B><TD>"+data[n].prim+"<TD class=dtime>"+data[n].dtime+"<TD width=90><A href=''>Корнилов Михаил</A>";
      HTML+="</TABLE>";
      $("#zResult").html(data[0].result);
      $("#zSpisok").html(HTML);
      frameBodyHeightSet();
      }
    else $("#zResult").html("Платежей нет");
    });
  }



// вывод заметок
$("#client_comment").vkComment({
  width:444,
  table_name:'client',
  table_id:G.client.id
});

