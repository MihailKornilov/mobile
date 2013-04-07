// внесение в список балансов клиентов
for (var n = 0; n < G.clients.length; n++) {
  var sp = G.clients[n];
  if (G.balans[sp.id]) { sp.balans = G.balans[sp.id]; }
}


$("#dolg").myCheck({
  title:'Должники',
  func:function () {
    $("#find INPUT:first").val('');
    $("#find H5 DIV:first").show();
    var val = $("#dolg").val();
    var array = [], dolg = 0, dop = '';
    if (val == 1) {
      for (var n = 0; n < G.clients.length; n++) {
        var sp = G.clients[n];
        if (sp.balans && sp.balans < 0) {
          dolg += -sp.balans;
          array.push(sp);
        }
      }
      dop = "<EM>(Общая сумма долга = " + dolg + " руб.)</EM>";
    } else { array = G.clients; }
    G.spisok.json = array;
    G.spisok.result_dop = dop;
    G.spisok.print();
  }
});

$("#find").topSearch({
  width:585,
  focus:1,
  txt:'Начните вводить данные клиента',
  func:function (input) {
    $("#dolg").myCheckVal(0);

    var array = [];
    if (input) {
      var reg = new RegExp(input, "i");
      for (var n = 0; n < G.clients.length; n++) {
        var sp = G.clients[n];
        if (reg.test(sp.fio) || reg.test(sp.telefon)) {
          array.push({
            id:sp.id,
            fio:sp.fio.replace(reg, "<EM>$&</EM>"),
            telefon:sp.telefon ? sp.telefon.replace(reg, "<EM>$&</EM>") : '',
            count:sp.count,
            balans:sp.balans
          });
        }
      }
    } else { array = G.clients; }

    G.spisok.result_dop = '';
    G.spisok.json = array;
    G.spisok.print();
  }
});

$("#buttonCreate").click(function () {
  clientAdd(function (res) { location.href="index.php?" + G.values + "&my_page=remClientInfo&id=" + res.uid; });
});



G.spisok.unit = function (sp) {
  var HTML = '';
  if(sp.balans) HTML += "<DIV class=balans>Баланс: <B style=color:#" + (sp.balans < 0?'A00':'090') + ">" + sp.balans + "</B></DIV>";
  HTML += "<TABLE cellspacing=1 cellpadding=0>";
  HTML += "<TR><TD class=tdAbout>Имя:<TD><A HREF='index.php?" + G.values + "&my_page=remClientInfo&id=" + sp.id + "'>" + sp.fio + "</A>";
  if(sp.telefon) HTML+="<TR><TD class=tdAbout>Телефон:<TD>" + sp.telefon;
  if(sp.count) HTML+="<TR><TD class=tdAbout>Заявки:<TD>" + sp.count;
  HTML+="</TABLE>";
  return HTML;
};

G.spisok.create({
  json:G.clients,
  view:$("#spisok"),
  limit:20,
  result:"Найден$find $count клиент$client",
  ends:{'$client':['', 'а', 'ов'], '$find':['', 'о']},
  result_view:$("#result"),
  next:"Следующие 20 клиентов",
});






$("HEAD").append("<SCRIPT type='text/javascript' src='/remont/client/spisok/client_hint.js?" + G.script_style + "'></SCRIPT>");

