var zpNameVkSel;

// �������� �������� �� ������� ���������
G.zp.spisok = function () {
  var HTML = "<DIV id=result></DIV>" +
    "<TABLE cellpadding=0 cellspacing=0>" +
    "<TR><TD id=left>" +
      "<TD id=right>" +
        "<DIV id=findFast></DIV>" +

        "<DIV id=menu></DIV>" +

        "<DIV class=findHead>������������</DIV>" +
        "<DIV class=findContent><INPUT TYPE=hidden id=zpName value=" + G.zp.name_id + "></DIV>" +

       "<DIV class=findHead>����������</DIV>" +
       "<DIV class=findContent id=zpDev></DIV>" +
    "</TABLE>" +
    "<DIV id=zp_dialog></DIV>" +
    "</DIV>";
  
  $("#zp").html(HTML);



  G.spisok.unit = function (sp) {
    HTML = "<TABLE cellpadding=0 cellspacing=0 class=tab><TR>" +
      "<TD class=img><IMG src=" + (sp.img ? sp.img + "-small.jpg" : "/img/nofoto.gif") + " val=link_" + sp.num + ">" +

      "<TD class=cont><H1><A val=link_" + sp.num + ">" + G.zp_name_ass[sp.name_id] + " <B val=link_" + sp.num + ">" + G.vendor_ass[sp.vendor_id] + " " + G.model_ass[sp.model_id] + "</B></A></H1>" +
      "<H3>" + sp.name_dop + "</H3>" +
      "<H2>��� " + G.device_rod_ass[sp.device_id] + "</H2>" +
     (sp.color_id > 0 ? "<H6><SPAN>����:</SPAN> " + G.color_ass[sp.color_id] + "</H6>" :'') +

      "<TD class=action>" +
      (sp.avai ? "<A class=avai val=avai_" + sp.num + ">� �������: <B val=avai_" + sp.num + ">" + sp.avai + "</B></A>" : "<A class=hid val=avai_" + sp.num + ">������ �������</A>") +
      (sp.zakaz ? "<A class=zakaz val=zakaz_" + sp.num + ">��������: <B>" + sp.zakaz + "</B></A>" : "<A class=hid val=zakaz_" + sp.num + ">��������</A>") +

      "</TABLE>";
    return HTML;
  };

  G.spisok.create({
    url:"/remont/zp/AjaxZpSpisok.php",
    json:G.zp.data,
    limit:G.zp.data ? G.zp.data.length : 20,
    view:$("#left"),
    result_view:$("#result"),
    result:"� �������� $count �������$zayav",
    result_dop:G.zp.type == 1 ? "<H6><A onclick=G.zp.add(null,G.zp.addAfter);>������ ����� �������� � �������</A></H6>" : '',
    ends:{'$no':['��', '��'],'$zakaz':['�', '�'],'$zayav':['�', '�', '��']},
    next_txt:"��������� 20 ���������",
    nofind:"��������� �� �������.",
  //  a:1,
    imgup:"#menu .sel",
    values:{
      id:G.zp.id,
      fast:'',
      type:G.zp.type,
      name_id:G.zp.name_id,
      device_id:G.zp.device_id,
      vendor_id:G.zp.vendor_id,
      model_id:G.zp.model_id
    },
    callback:function (data) {
      // �������������� ���������� ����� ����������� �� ��������� ��������
      G.spisok.json = null;
      G.spisok.limit = 20;

      // ���� ��� ��������� ������ ��������, ������� �� ��
      if (G.zp.id > 0) {
        G.zp.id = 0;
        G.zp.view(data[0]);
      } else {
        // ��������� ������ ��� ��������
        var val = G.spisok.values;
        VK.callMethod("setLocation","remZp_[" + val.type + "," + val.name_id + "," + val.device_id + "," + val.vendor_id + "," + val.model_id + "]");

        // ���������� ���������� ������� ������ ������
        for (var k in G.spisok.values) { G.zp[k] = G.spisok.values[k]; }

        if (data.length > 0) {
          // ���� ������ ���������� �������, �� ������ ����������� ������, ����� ���������� ������� � �������
          if (data[0].num == 0) { G.zp.data = data; } else { for (var n = 0; n < data.length; G.zp.data.push(data[n]), n++); }
        } else {
          G.zp.data = [];
          // ����������� �������� ����� ��������, ���� ��� ������ ���� ������� ��� �������
          if (G.zp.type == 1 && G.zp.name_id > 0 && G.zp.device_id > 0 && G.zp.vendor_id > 0 && G.zp.model_id > 0) { G.zp.add(G.spisok.values, G.zp.addAfter); }
        }

        // ����� - ������� "�������, ��������"
        $("#left .unit").unbind().bind({
          mouseenter:function () { $(this).find(".hid").css('visibility','visible'); },
          mouseleave:function () { $(this).find(".hid").css('visibility','hidden'); }
        });

        // ������ ��������� ������ ��� ��� ��������� ���������� ������
        $("#left .action")
          .attr('unselectable', 'on')
          .css('user-select', 'none')
          .on('selectstart', false);

        // ���������� ��������
        $("#left .action A").unbind().bind({
          mouseenter:function () {
            var val = $(this).attr('val').split('_');
            if(val[0] == 'zakaz') {
              var sp = G.zp.data[val[1]];
              sp.zakaz_old = sp.zakaz;
              $(this)
                .attr('class', 'zakaz')
                .html("��������: <EM val=minus_" + sp.num + "> � </EM><B>" + sp.zakaz + "</B><EM val=plus_" + sp.num + "> + </EM>");
            } 
          },
          mouseleave:function () {
            var t = $(this);
            var val = t.attr('val').split('_');
            var sp = G.zp.data[val[1]];
            if (val[0] == 'zakaz') {
              var leave = function () {
                t.html(sp.zakaz > 0 ? "��������: <B>" + sp.zakaz + "</B>" : "��������");
                t.attr('class', sp.zakaz > 0 ? 'zakaz' : 'hid').css('visibility','visible');
              };
              if(sp.zakaz != sp.zakaz_old) {
                t.html("��������: <IMG src=/img/upload.gif>");
                $.post("/remont/zp/AjaxZpZakazAdd.php?" + G.values, {zid:sp.id, count:sp.zakaz}, leave, 'json');
              } else { leave(); }
            }
          }
        });
      }
    } // end callback
  });




  // �������� ����
  $("#menu").infoLink({
    spisok:[
      {uid:1,title:'����� �������'},
      {uid:2,title:'�������'},
      {uid:3,title:'��� � �������'},
      {uid:4,title:'�����'}],
    func:function(id) {
      G.spisok.result_dop = (id == '1' ? "<H6><A onclick=G.zp.add(null,G.zp.addAfter);>������ ����� �������� � �������</A></H6>" : '');
      switch (+id) {
      case 1: G.spisok.result = "� �������� $count �������$zayav"; break;
      case 2: G.spisok.result = "� ������� $count �������$zayav"; break;
      case 3: G.spisok.result = "���������$no $count �������$zayav"; break;
      case 4: G.spisok.result = "�������$zakaz $count �������$zayav"; break;
      }
      G.spisok.print({type:id});
    }
  }).infoLinkSet(G.zp.type);


  // ������� �����
  $("#findFast").topSearch({
    width:134,
    focus:1,
    txt:'������� �����...',
    func:function (val) { G.spisok.print({fast:val}); },
    enter:1
  });

  
  // ������������ ��������
  zpNameVkSel = $("#zpName").vkSel({
    width:153,
    title0:'����� ������������',
    spisok:G.zp_name_spisok,
    func:function (id) { G.spisok.print({name_id:id}); },
  }).o;

  // ����������
  $("#zpDev").device({
    width:153,
    type_no:1,
    device_id:G.zp.device_id,
    vendor_id:G.zp.vendor_id,
    model_id:G.zp.model_id,
    device_ids:G.device_ids,
    func:function (dev) { G.spisok.print(dev); },
  });



  // �������� ��� ������� �� ������ ���������
  $("#left").click(function (e) {
    var val = $(e.target).attr('val');
    if(val) {
      val = val.split('_');
      var sp = G.zp.data[val[1]];
      switch (val[0]) {
      case 'link': G.zp.view(sp); break;
      case 'plus': G.zp.zakazChange(sp, 1); break;
      case 'minus': G.zp.zakazChange(sp, -1); break;
      case 'avai': G.zp.avaiInsert(sp, function () {
          $("#unit_" + sp.id + " .action A:first").attr('class','avai').html("� �������: <B val=avai_" + sp.num + ">" + sp.avai + "</B>").css('visibility','visible');
          $("#unit_" + sp.id).css('background-color','#FFC').delay(2000).animate({'background-color':'#FFF'},3000);
        }); break;
      }
    }
  });
};



G.zp.spisok();












// �������� ������� ��������
G.zp.avaiInsert = function (sp, func) {
  var HTML="<TABLE cellpadding=0 cellspacing=0 class=avaiInsert>" +
    "<TR><TD class=td1>" + G.zp_name_ass[sp.name_id] + " <B val=link>" + G.vendor_ass[sp.vendor_id] + " " + G.model_ass[sp.model_id] + "</B>" +
    "<P>��� " + G.device_rod_ass[sp.device_id] +
    "<DIV class=nal>������� �������: <B>" + sp.avai + "</B> ��.</DIV>" +
    "<DIV class=input>����������: <INPUT type=text id=kolvo maxlength=5></DIV>" +
    "<DIV class=input>���� �� ��.: <INPUT type=text id=cena maxlength=10><SPAN>�� �����������</SPAN></DIV>" +
    "<TD valign=top><IMG src=" + (sp.img ? sp.img + "-small.jpg" : "/img/nofoto.gif") + ">" +
    "</TABLE>";
  var dialog = $("#zp_dialog").vkDialog({
    head:'�������� ������� ��������',
    content:HTML,
    focus:'#kolvo',
    submit:submit
  }).o;
  
  function submit() {
    var send = {
      zp_id:sp.id,
      kolvo:$("#kolvo").val(),
      cena:$("#cena").val()
    };
    if (!send.cena) { send.cena = 0; }

    var msg;
    if (!/^\d+$/.test(send.kolvo) || send.kolvo == 0) { msg = "����������� ������� ����������."; $("#kolvo").focus(); }
    else if (send.cena != 0 && !/^[\d.]+$/.test(send.cena)) { msg = "����������� ������� ����."; $("#cena").focus(); }
    else {
      dialog.process();
      $.post("/remont/zp/AjaxZpAvaiAdd.php?" + G.values, send, function (res) {
        sp.avai = res.count;
        dialog.close();
        vkMsgOk("�������� ������� ��� �������� " + G.zp_name_ass[sp.name_id] + " " + G.vendor_ass[sp.vendor_id] + " " + G.model_ass[sp.model_id] + " �����������. ������� ����������: "+res.count+" ��.");
        func(res);          
      }, 'json');
    }
  if (msg) { $("#zp_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:95}); }
  }
}; // end avaiInsert






// ��������� ���������� ������ ��������
G.zp.zakazChange = function(sp, c) {
  sp.zakaz = sp.zakaz * 1 + c;
  var A = $("#unit_" + sp.id + " .action A:eq(1)");
  if(sp.zakaz <= 0) { sp.zakaz = 0; }
  A.find("B:first").html(sp.zakaz + '');
};





// ����������/�������������� ��������
G.zp.add = function (obj, func) {
  var obj = $.extend({
    id:0,
    name_id:0,
    name_dop:'',
    color_id:0,
    device_id:0,
    vendor_id:0,
    model_id:0,
    compat_id:0
  },obj);

  var HTML = "<TABLE cellpadding=0 cellspacing=10>";
  HTML += "<TR><TD class=tdAbout>������������ ��������:<TD><INPUT TYPE=hidden id=zpAdd_name_id value=" + obj.name_id + ">";
  if (obj.compat_id > 0) HTML += "<B style=position:relative;top:2px;>" + G.zp_name_ass[obj.name_id] + "</B>";
  HTML += "<TR><TD class=tdAbout>�������������� ����������:<TD><INPUT TYPE=text id=zpAdd_name_dop maxlength=30 style=width:188px; value='" + obj.name_dop + "'>";
  HTML += "<TR><TD class=tdAbout>����:<TD><INPUT TYPE=hidden id=zpAdd_color_id value=" + obj.color_id + ">";
  HTML += "<TR><TD class=tdAbout>����������:<TD id=zpAdd_dev>";
  HTML += "</TABLE>";

  dialogShow({
    top:70,
    width:440,
    head:obj.id ? '�������������� ��������' : '�������� ����� �������� � �������',
    content:HTML,
    butSubmit: obj.id ? '���������' : '������',
    submit:submit
  });

  $("#dialog .tdAbout").css({
    width:'155px',
    'text-align':'right',
    'padding-top':'4px'
  });

  if (obj.compat_id == 0) {
    $("#zpAdd_name_id").vkSel({
      width:200,
      title0:'������������ �� �������',
      spisok:G.zp_name_spisok
    });
  }

  $("#zpAdd_color_id").vkSel({
    width:130,
    title0:'���� �� ������',
    spisok:G.color_spisok
  });
 
  $("#zpAdd_dev").device({
    width:200,
    device_id:obj.device_id,
    vendor_id:obj.vendor_id,
    model_id:obj.model_id
  });

  function submit() {
    var msg = '';
    var save = {
      id:obj.id,
      name_id:$("#zpAdd_name_id").val(),
      name_dop:$("#zpAdd_name_dop").val(),
      color_id:$("#zpAdd_color_id").val(),
      device_id:$("#zpAdd_dev_device").val(),
      vendor_id:$("#zpAdd_dev_vendor").val(),
      model_id:$("#zpAdd_dev_model").val()
    };
    if (save.name_id == 0) { msg = '�� ������� ������������ ��������.'; }
    else if (save.device_id == 0) { msg = '�� ������� ����������'; }
    else if (save.vendor_id == 0) { msg = '�� ������ �������������'; }
    else if (save.model_id == 0) { msg = '�� ������� ������'; }
    else {
      $("#butDialog").butProcess();
      $.post("/remont/zp/AjaxZpAdd.php?" + G.values, save, function (res) { func(save); dialogHide(); },'json');
    }

    if (msg) { $("#dialog H3").alertShow({txt:"<SPAN class=red>" + msg + "</SPAN>",left:135,top:-43}); }
  }
};

// �������� ����� �������� ����� ��������
G.zp.addAfter = function (save) {
  zpNameVkSel.val(save.name_id);
  $("#zpDev").device({
    width:153,
    type_no:1,
    device_id:save.device_id,
    vendor_id:save.vendor_id,
    model_id:save.model_id,
    func:G.spisok.print
  });
  G.spisok.print({
    name_id:save.name_id,
    device_id:save.device_id,
    vendor_id:save.vendor_id,
    model_id:save.model_id
  });
};
