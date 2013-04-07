G.report = {};
G.report.type = {
  1:'создал новую заявку $zayav для клиента $client.',
  2:'удалил заявку №$value.',
  3:'внёс в базу нового клиента $client.',
  4:'изменил статус заявки $zayav на $value.',
  5:'произвёл начисление на сумму $value руб. для заявки $zayav.',
  6:'внёс платёж на сумму $value руб. заявке $zayav.',
  7:'отредактировал данные заявки $zayav.',
  8:'удалил начисление на сумму $value руб. у заявки $zayav.',
  9:'удалил платёж на сумму $value руб. у заявки $zayav.',
  10:'отдерактировал данные клиента $client.',
  11:'произвёл объединение клиентов. Результат: $client.',
  12:'установил значение в кассе: $value руб.',
  13:'произвёл установку запчасти $zp по заявке $zayav',
  14:'продал запчасть $zp на сумму $value руб.',
  15:'произвёл списание запчасти $zp',
  16:'произвёл возврат запчасти $zp',
  17:'забраковал запчась $zp',
  18:'внёс наличие запчасти $zp в количестве $value шт.'
};




$("#menu").infoLink({
  spisok:[
    {uid:1,title:'История действий'},
    {uid:2,title:'Задания' + G.remindActive},
    {uid:3,title:'Деньги'}
  ],
  func:function (id) {
    $("#content").html('');
    $("#podmenu").html('');
    $("#menu #remind_add_but").remove();
    if (id == 1) { historySpisok(); }
    if (id == 2) {
      $("#menu .sel").append("<DIV id=remind_add_but></DIV>");
      $("#remind_add_but").on('click', reminderAdd);
      reminderSpisok();
    }
    if (id == 3) { moneySpisok(); }
  }
}).infoLinkSet(1);


//moneySpisok();
historySpisok();










// Список истории
function historySpisok() {
  G.spisok.unit = function (sp) {
    var txt = G.report.type[sp.type];
    if (sp.client_id) { txt = txt.replace('$client', "<A href='/index.php?" + G.values + "&my_page=remClientInfo&id=" + sp.client_id + "'>" + sp.client_fio + "</A>"); }
    if (sp.zayav_id) { txt = txt.replace('$zayav', "<A href='/index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + sp.zayav_id + "'>№" + sp.zayav_nomer + "</A>"); }
    if (sp.zp_id) {
      txt = txt.replace('$zp',
        "<A href='/index.php?" + G.values + "&my_page=remZp&id=" + sp.zp_id + "'>" +
        "<B>" + G.zp_name_ass[sp.zp_name] + "</B>" +
        " для " + G.device_rod_ass[sp.zp_device] +
        " " + G.vendor_ass[sp.zp_vendor] +
        " " + G.model_ass[sp.zp_model] +
        "</A>");
    }
    if (sp.value) {
      if (sp.type == 4) {
        txt = txt.replace('$value', "'" + G.status_ass[sp.value] + "'");
      } else {
        txt = txt.replace('$value', sp.value);
      }
    }
    return "<TABLE cellpadding=0 cellspacing=0 class=history><TR><TD class=dtime>" + sp.dtime + "<TD><A href='http://vk.com/id" + sp.viewer_id + "'>" + G.vkusers[sp.viewer_id] + "</A> " + txt + "</TABLE>";
  };

  G.spisok.create({
    url:"/remont/report/history/AjaxHistoryGet.php",
    limit:20,
    view:$("#content"),
    nofind:"Истории нет.",
 //   a:1,
    values:{},
    callback:function (data) {}
  });
} // end historySpisok






