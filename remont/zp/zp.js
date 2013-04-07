var zpNameVkSel;

// создание страницы со списком запчастей
G.zp.spisok = function () {
  var HTML = "<DIV id=result></DIV>" +
    "<TABLE cellpadding=0 cellspacing=0>" +
    "<TR><TD id=left>" +
      "<TD id=right>" +
        "<DIV id=findFast></DIV>" +

        "<DIV id=menu></DIV>" +

        "<DIV class=findHead>Наименование</DIV>" +
        "<DIV class=findContent><INPUT TYPE=hidden id=zpName value=" + G.zp.name_id + "></DIV>" +

       "<DIV class=findHead>Устройство</DIV>" +
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
      "<H2>для " + G.device_rod_ass[sp.device_id] + "</H2>" +
     (sp.color_id > 0 ? "<H6><SPAN>Цвет:</SPAN> " + G.color_ass[sp.color_id] + "</H6>" :'') +

      "<TD class=action>" +
      (sp.avai ? "<A class=avai val=avai_" + sp.num + ">В наличии: <B val=avai_" + sp.num + ">" + sp.avai + "</B></A>" : "<A class=hid val=avai_" + sp.num + ">Внести наличие</A>") +
      (sp.zakaz ? "<A class=zakaz val=zakaz_" + sp.num + ">Заказано: <B>" + sp.zakaz + "</B></A>" : "<A class=hid val=zakaz_" + sp.num + ">Заказать</A>") +

      "</TABLE>";
    return HTML;
  };

  G.spisok.create({
    url:"/remont/zp/AjaxZpSpisok.php",
    json:G.zp.data,
    limit:G.zp.data ? G.zp.data.length : 20,
    view:$("#left"),
    result_view:$("#result"),
    result:"В каталоге $count запчаст$zayav",
    result_dop:G.zp.type == 1 ? "<H6><A onclick=G.zp.add(null,G.zp.addAfter);>Внести новую запчасть в каталог</A></H6>" : '',
    ends:{'$no':['ет', 'ют'],'$zakaz':['а', 'о'],'$zayav':['ь', 'и', 'ей']},
    next_txt:"Следующие 20 запчастей",
    nofind:"Запчастей не найдено.",
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
      // восстановление переменных после возвращения из просмотра запчасти
      G.spisok.json = null;
      G.spisok.limit = 20;

      // если был произведён запрос запчасти, переход на неё
      if (G.zp.id > 0) {
        G.zp.id = 0;
        G.zp.view(data[0]);
      } else {
        // установка ссылки для контакта
        var val = G.spisok.values;
        VK.callMethod("setLocation","remZp_[" + val.type + "," + val.name_id + "," + val.device_id + "," + val.vendor_id + "," + val.model_id + "]");

        // обновление переменных условий вывода списка
        for (var k in G.spisok.values) { G.zp[k] = G.spisok.values[k]; }

        if (data.length > 0) {
          // если список начинается сначала, то просто копирование ссылки, иначе пополнение массива с данными
          if (data[0].num == 0) { G.zp.data = data; } else { for (var n = 0; n < data.length; G.zp.data.push(data[n]), n++); }
        } else {
          G.zp.data = [];
          // предложение добавить новую запчасть, если при поиске были указаны все условия
          if (G.zp.type == 1 && G.zp.name_id > 0 && G.zp.device_id > 0 && G.zp.vendor_id > 0 && G.zp.model_id > 0) { G.zp.add(G.spisok.values, G.zp.addAfter); }
        }

        // показ - скрытие "Наличие, Заказать"
        $("#left .unit").unbind().bind({
          mouseenter:function () { $(this).find(".hid").css('visibility','visible'); },
          mouseleave:function () { $(this).find(".hid").css('visibility','hidden'); }
        });

        // запрет выделения текста при при изменении количества заказа
        $("#left .action")
          .attr('unselectable', 'on')
          .css('user-select', 'none')
          .on('selectstart', false);

        // управление заказами
        $("#left .action A").unbind().bind({
          mouseenter:function () {
            var val = $(this).attr('val').split('_');
            if(val[0] == 'zakaz') {
              var sp = G.zp.data[val[1]];
              sp.zakaz_old = sp.zakaz;
              $(this)
                .attr('class', 'zakaz')
                .html("Заказано: <EM val=minus_" + sp.num + "> — </EM><B>" + sp.zakaz + "</B><EM val=plus_" + sp.num + "> + </EM>");
            } 
          },
          mouseleave:function () {
            var t = $(this);
            var val = t.attr('val').split('_');
            var sp = G.zp.data[val[1]];
            if (val[0] == 'zakaz') {
              var leave = function () {
                t.html(sp.zakaz > 0 ? "Заказано: <B>" + sp.zakaz + "</B>" : "Заказать");
                t.attr('class', sp.zakaz > 0 ? 'zakaz' : 'hid').css('visibility','visible');
              };
              if(sp.zakaz != sp.zakaz_old) {
                t.html("Заказано: <IMG src=/img/upload.gif>");
                $.post("/remont/zp/AjaxZpZakazAdd.php?" + G.values, {zid:sp.id, count:sp.zakaz}, leave, 'json');
              } else { leave(); }
            }
          }
        });
      }
    } // end callback
  });




  // создание меню
  $("#menu").infoLink({
    spisok:[
      {uid:1,title:'Общий каталог'},
      {uid:2,title:'Наличие'},
      {uid:3,title:'Нет в наличии'},
      {uid:4,title:'Заказ'}],
    func:function(id) {
      G.spisok.result_dop = (id == '1' ? "<H6><A onclick=G.zp.add(null,G.zp.addAfter);>Внести новую запчасть в каталог</A></H6>" : '');
      switch (+id) {
      case 1: G.spisok.result = "В каталоге $count запчаст$zayav"; break;
      case 2: G.spisok.result = "В наличии $count запчаст$zayav"; break;
      case 3: G.spisok.result = "Отсутству$no $count запчаст$zayav"; break;
      case 4: G.spisok.result = "Заказан$zakaz $count запчаст$zayav"; break;
      }
      G.spisok.print({type:id});
    }
  }).infoLinkSet(G.zp.type);


  // быстрый поиск
  $("#findFast").topSearch({
    width:134,
    focus:1,
    txt:'Быстрый поиск...',
    func:function (val) { G.spisok.print({fast:val}); },
    enter:1
  });

  
  // наименование запчасти
  zpNameVkSel = $("#zpName").vkSel({
    width:153,
    title0:'Любое наименование',
    spisok:G.zp_name_spisok,
    func:function (id) { G.spisok.print({name_id:id}); },
  }).o;

  // устройства
  $("#zpDev").device({
    width:153,
    type_no:1,
    device_id:G.zp.device_id,
    vendor_id:G.zp.vendor_id,
    model_id:G.zp.model_id,
    device_ids:G.device_ids,
    func:function (dev) { G.spisok.print(dev); },
  });



  // действия при нажатии на список запчастей
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
          $("#unit_" + sp.id + " .action A:first").attr('class','avai').html("В наличии: <B val=avai_" + sp.num + ">" + sp.avai + "</B>").css('visibility','visible');
          $("#unit_" + sp.id).css('background-color','#FFC').delay(2000).animate({'background-color':'#FFF'},3000);
        }); break;
      }
    }
  });
};



G.zp.spisok();












// Внесение наличия запчасти
G.zp.avaiInsert = function (sp, func) {
  var HTML="<TABLE cellpadding=0 cellspacing=0 class=avaiInsert>" +
    "<TR><TD class=td1>" + G.zp_name_ass[sp.name_id] + " <B val=link>" + G.vendor_ass[sp.vendor_id] + " " + G.model_ass[sp.model_id] + "</B>" +
    "<P>для " + G.device_rod_ass[sp.device_id] +
    "<DIV class=nal>Текущее наличие: <B>" + sp.avai + "</B> шт.</DIV>" +
    "<DIV class=input>Количество: <INPUT type=text id=kolvo maxlength=5></DIV>" +
    "<DIV class=input>Цена за ед.: <INPUT type=text id=cena maxlength=10><SPAN>не обязательно</SPAN></DIV>" +
    "<TD valign=top><IMG src=" + (sp.img ? sp.img + "-small.jpg" : "/img/nofoto.gif") + ">" +
    "</TABLE>";
  var dialog = $("#zp_dialog").vkDialog({
    head:'Внесение наличия запчасти',
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
    if (!/^\d+$/.test(send.kolvo) || send.kolvo == 0) { msg = "Некорректно указано количество."; $("#kolvo").focus(); }
    else if (send.cena != 0 && !/^[\d.]+$/.test(send.cena)) { msg = "Некорректно указана цена."; $("#cena").focus(); }
    else {
      dialog.process();
      $.post("/remont/zp/AjaxZpAvaiAdd.php?" + G.values, send, function (res) {
        sp.avai = res.count;
        dialog.close();
        vkMsgOk("Внесение наличия для запчасти " + G.zp_name_ass[sp.name_id] + " " + G.vendor_ass[sp.vendor_id] + " " + G.model_ass[sp.model_id] + " произведено. Текущее количество: "+res.count+" шт.");
        func(res);          
      }, 'json');
    }
  if (msg) { $("#zp_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:95}); }
  }
}; // end avaiInsert






// изменение количества заказа запчасти
G.zp.zakazChange = function(sp, c) {
  sp.zakaz = sp.zakaz * 1 + c;
  var A = $("#unit_" + sp.id + " .action A:eq(1)");
  if(sp.zakaz <= 0) { sp.zakaz = 0; }
  A.find("B:first").html(sp.zakaz + '');
};





// добавление/редактирование запчасти
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
  HTML += "<TR><TD class=tdAbout>Наименование запчасти:<TD><INPUT TYPE=hidden id=zpAdd_name_id value=" + obj.name_id + ">";
  if (obj.compat_id > 0) HTML += "<B style=position:relative;top:2px;>" + G.zp_name_ass[obj.name_id] + "</B>";
  HTML += "<TR><TD class=tdAbout>Дополнительная информация:<TD><INPUT TYPE=text id=zpAdd_name_dop maxlength=30 style=width:188px; value='" + obj.name_dop + "'>";
  HTML += "<TR><TD class=tdAbout>Цвет:<TD><INPUT TYPE=hidden id=zpAdd_color_id value=" + obj.color_id + ">";
  HTML += "<TR><TD class=tdAbout>Устройство:<TD id=zpAdd_dev>";
  HTML += "</TABLE>";

  dialogShow({
    top:70,
    width:440,
    head:obj.id ? 'Редактирование запчасти' : 'Внесение новой запчасти в каталог',
    content:HTML,
    butSubmit: obj.id ? 'Сохранить' : 'Внести',
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
      title0:'Наименование не выбрано',
      spisok:G.zp_name_spisok
    });
  }

  $("#zpAdd_color_id").vkSel({
    width:130,
    title0:'Цвет не указан',
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
    if (save.name_id == 0) { msg = 'Не указано наименование запчасти.'; }
    else if (save.device_id == 0) { msg = 'Не выбрано устройство'; }
    else if (save.vendor_id == 0) { msg = 'Не выбран производитель'; }
    else if (save.model_id == 0) { msg = 'Не выбрана модель'; }
    else {
      $("#butDialog").butProcess();
      $.post("/remont/zp/AjaxZpAdd.php?" + G.values, save, function (res) { func(save); dialogHide(); },'json');
    }

    if (msg) { $("#dialog H3").alertShow({txt:"<SPAN class=red>" + msg + "</SPAN>",left:135,top:-43}); }
  }
};

// действия после внесения новой запчасти
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
