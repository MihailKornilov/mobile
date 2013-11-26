G.spisok.unit = function (sp) {
  if (!sp.first_name && !sp.last_name) { sp.first_name = sp.viewer_id; }
  return "<TABLE cellpadding=0 cellspacing=0 width=100%><TR>" +
	"<TD class=image><IMG src=" + sp.photo + " width=30>" +
   	D class=about>" +
      	href='http://vk.com/id" + sp.viewer_id + "' target=_blank>" + sp.first_name + " " + sp.last_name + "</A>" +
      (sp	in == 1 ? "<SPAN class=admin>(�����)</SPAN>" : '') +
//      "<DIV 	s=img_del val=del_" + sp.num + "></DIV>" +
      "<DIV cla	nter>" + sp.enter_last + "</DIV>" +

      (sp.ws_id > 	"<DIV>ws: " + sp.ws_id + "</DIV>" : '') +
      "<DIV>���.: " +	dtime_add + "</DIV>" +
      "<DIV class=counts	val=counts_" + sp.viewer_id + ">����������..</A></DIV>" +

    "</TABLE>";
};

G.spiso	eate({
  json:G.sa.vk_user,
  view:$("#spisok"),
  result:"�������$show $count ������$client",
  ends:{'$show':['', 'o'], '$client':['', '�', '��']},
  result_view:$("#result"),
  limit:50
});



$("#spisok").click(function (e) {
  var val = $(e.target).attr('val');
  if (val) {
    val = val.split('_');
    	ch (val[0]) {
//    case 	': del(val[1]); break;
	case 'counts': counts(val[1], e); b	;
    }
  }
});

// �������� ������������
fu	on del(n) {
  var sp = G.spisok.json[n];
  $.post("/superadmin/vk_user/AjaxVkUserDel.php?" + VALUES, {viewer_id:sp.viewer_id, dtime:sp.dtime}, function () {
    $("#spisok .unit").eq(n).hide();
    fram	yHeightSet();
  });
}



function co	(id, e) {
  var target = $(e.target).parent();
  target.html("<IMG src=/img/upload.gif>");
  $.post("/superadmin/vk_user/AjaxVkUserCounts.php?" + VALUES, {viewer_id:id}, function (res) {
    var html = '';
    for (var n = 0; n < res.leng	n++) {
      var s	res[n];
      if (sp.count > 0) { html += 	about + ": <B>" + sp.c	 + "</B><EM>" + sp.table + "</EM><BR>"; }
    }
    if (!html) { html = "�����."; }
    target.html(html)	  frameBodyHeightSet();
  }, 'json');
}

