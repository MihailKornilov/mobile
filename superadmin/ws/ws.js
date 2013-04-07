G.spisok.unit = function (sp) {
  return "<A class='org_name" + (sp.status == 0 ? ' deleted' : '') + "'>" + sp.org_name + "</A>" + (sp.status == 0 ? "<EM class=dtime_del>" + sp.dtime_del + "</EM>" : '') +
    "<DIV class=img_del val=del_" + sp.num + "></DIV>" +
    "<DIV class=dtime>" + sp.dtime_add + "</DIV>" +
    "<DIV class=city>" + (sp.country_name ? sp.country_name : sp.country_id) + ", " + (sp.city_name ? sp.city_name : sp.city_id) + "</DIV>" +
    "<DIV class=admin>�����: <A href='http://vk.com/id" + sp.admin_id + "' target=_blank>" + sp.admin_name + "</A></DIV>" +
    "<DIV class=counts><A val=counts_" + sp.id + ">���� ������</A></DIV>";
};

G.spisok.create({
  json:G.sa.ws,
  view:$("#spisok"),
  result:"�������$show $count ��������$ws",
  ends:{'$show':['�', 'o'], '$ws':['��', '��']},
  result_view:$("#result")
});



$("#spisok").click(function (e) {
  var val = $(e.target).attr('val');
  if (val) {
    val = val.split('_');
    switch (val[0]) {
    case 'del': del(val[1]); break;
    case 'counts': counts(val[1], e); break;
    }
  }
});



// �������� ����������
function del(n) {
  var sp = G.spisok.json[n];
  var dialog = $("#ws_dialog").vkDialog({
    head:'�������� ����������',
    content:"����������� ���������� �������� ���������� '<B>" + sp.org_name + "</B>' � ���� � ������.",
    butSubmit:'�������',
    submit:function () {
      dialog.process();
      $.post("/superadmin/ws/AjaxWsDel.php?" + G.values, {ws_id:sp.id}, function (res) {
        $("#unit_" + sp.id).hide();
        dialog.close();
        frameBodyHeightSet();
      }, 'json');    
    }
  }).o;
}



function counts(id, e) {
  var target = $(e.target).parent();
  target.html("<IMG src=/img/upload.gif>");
  $.post("/superadmin/ws/AjaxWsCounts.php?" + G.values, {ws_id:id}, function (res) {
    var html = '';
    for (var n = 0; n < res.length; n++) {
      var sp = res[n];
      if (sp.count > 0) { html +=  sp.about + ": <B>" + sp.count + "</B><EM>" + sp.table + "</EM><BR>"; }
    }
    if (!html) { html = "�����."; }
    target.html(html);
    frameBodyHeightSet();
  }, 'json');
}

