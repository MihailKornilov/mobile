G.report = {};
G.report.type = {
  1:'������ ����� ������ $zayav ��� ������� $client.',
  2:'������ ������ �$value.',
  3:'��� � ���� ������ ������� $client.',
  4:'������� ������ ������ $zayav �� $value.',
  5:'������� ���������� �� ����� $value ���. ��� ������ $zayav.',
  6:'��� ����� �� ����� $value ���. ������ $zayav.',
  7:'�������������� ������ ������ $zayav.',
  8:'������ ���������� �� ����� $value ���. � ������ $zayav.',
  9:'������ ����� �� ����� $value ���. � ������ $zayav.',
  10:'�������������� ������ ������� $client.',
  11:'������� ����������� ��������. ���������: $client.',
  12:'��������� �������� � �����: $value ���.',
  13:'������� ��������� �������� $zp �� ������ $zayav',
  14:'������ �������� $zp �� ����� $value ���.',
  15:'������� �������� �������� $zp',
  16:'������� ������� �������� $zp',
  17:'���������� ������� $zp',
  18:'��� ������� �������� $zp � ���������� $value ��.'
};




$("#menu").infoLink({
  spisok:[
    {uid:1,title:'������� ��������'},
    {uid:2,title:'�������' + G.remindActive},
    {uid:3,title:'������'}
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










// ������ �������
function historySpisok() {
  G.spisok.unit = function (sp) {
    var txt = G.report.type[sp.type];
    if (sp.client_id) { txt = txt.replace('$client', "<A href='/index.php?" + G.values + "&my_page=remClientInfo&id=" + sp.client_id + "'>" + sp.client_fio + "</A>"); }
    if (sp.zayav_id) { txt = txt.replace('$zayav', "<A href='/index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + sp.zayav_id + "'>�" + sp.zayav_nomer + "</A>"); }
    if (sp.zp_id) {
      txt = txt.replace('$zp',
        "<A href='/index.php?" + G.values + "&my_page=remZp&id=" + sp.zp_id + "'>" +
        "<B>" + G.zp_name_ass[sp.zp_name] + "</B>" +
        " ��� " + G.device_rod_ass[sp.zp_device] +
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
    nofind:"������� ���.",
 //   a:1,
    values:{},
    callback:function (data) {}
  });
} // end historySpisok






