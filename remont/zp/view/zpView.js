// подмена события вкладки 'Запчасти' для возврата
G.zp.mLink = $("#mainLinks .sel").attr('href'); // сохранение исходного значения
G.zp.mLinkSet = function (sp) {
    var mLinks = $("#mainLinks .sel");
    if (mLinks.attr('href') != 'javascript:') { mLinks.attr('href','javascript:'); }
    mLinks.off().on('click', function () { G.zp.back(sp); });
};



// возвращение к списку запчастей
G.zp.back = function (sp) {
    G.zp.spisok();
    $("#mainLinks .sel").off().on('click', function () { location.href = G.zp.mLink; });
    if (sp) {
        $("#unit_" + sp.id).css('background-color','#FFC'); // подсветка запчасти в списке, просмотр которой производился
        VK.callMethod('scrollWindow', sp.num * 95);          // прокрутка окна, чтобы запчасть была в поле зрения
    }
};



/*
 структура объекта запчасть:

 + поля, которые совпадают при совместимости

 num - порядковый номер в массиве (для compatSpisok), не содержится в базе данных
 id - идентификатор
 + name_id - id имени setup_zp_name
 + name_dop - дополнительная информация
 device_id - id устройства
 vendor_id - id производителя
 model_id - id модели
 + color_id - цвет
 + zakaz - количество заказано
 + avai - количество в наличии
 dtime - дата внесения в каталог
 + move - массив данных о движении
 + compat_id - номер группировки совместимости
 compatSpisok - массив совместимых запчастей
 + foto - массив с изображениями
 */



G.zp.view = function (sp) {
    VK.callMethod('scrollWindow', 0);

    G.zp.mLinkSet(sp);

    var dopMenu = "<DIV id=dopMenu>" +
        "<A class=del>К списку</A>" +
        "<A class=linkSel><I></I><B></B><DIV>Просмотр</DIV><B></B><I></I></A>" +
        "<A class=link><I></I><B></B><DIV>Редактирование</DIV><B></B><I></I></A>" +
        "<A class=link><I></I><B></B><DIV>Внести приход</DIV><B></B><I></I></A>" +
        "<A class=link><I></I><B></B><DIV>Заказа" + (sp.zakaz > 0 ? "но: " + sp.zakaz : "ть") + "</DIV><B></B><I></I></A>" +
        "<A class=link style=display:none;><I></I><B></B><DIV>Удалить</DIV><B></B><I></I></A>" +
        "<DIV style=clear:both;></DIV></DIV>";

    var html = "<TABLE cellpadding=0 cellspacing=0 class=tab><TR>" +
        "<TD class=td1>" +
        "<H1>" + G.zp_name_ass[sp.name_id] + "<EM>" + sp.name_dop + "</EM></H1>" +
        "<H2>для " + G.device_rod_ass[sp.device_id] + " <A href='/index.php?" + G.values + "&my_page=remDeviceView&id=" + sp.model_id + "'>" + G.vendor_ass[sp.vendor_id] + " " + G.model_ass[sp.model_id] + "</A></H2>" +
        "<H3>" + (sp.color_id > 0 ? "<EM>Цвет:</EM>" + G.color_ass[sp.color_id] : '') + "</H3>" +

        "<TABLE cellpadding=0 cellspacing=0 class=tabnal><TR><TD class=nal><TD><INPUT type=hidden id=actA></TABLE>" +

        "<DIV class=add>Добавлено в каталог " + sp.dtime + "</DIV>" +

        "<DIV id=compat><IMG src=/img/upload.gif></DIV>" +

        "<DIV id=zpMove></DIV>" +
        "<TD class=foto><DIV id=foto></DIV><DIV id=foto_upload></DIV>" +
        "</TABLE>" +
        "<DIV id=zp_dialog></DIV>";

    $("#zp").html(dopMenu + "<DIV id=view>" + html + "</DIV>");

    VK.callMethod("setLocation","remZp_" + sp.id);

    avaiPrint();

    // запрет выделения текста при при изменении количества заказа
    $("#dopMenu .link:eq(2)")
        .attr('unselectable', 'on')
        .css('user-select', 'none')
        .on('selectstart', false);




    // вывод движения запчастей и совместимости
    if(sp.move === undefined) {
        $.getJSON("/remont/zp/view/AjaxMoveSpisok.php?" + G.values + "&zid=" + sp.id, function (res) {
            sp.move = res.spisok;
            for (var n = 0; n < sp.move.length; n++) {
                var i = sp.move[n];
                i.w_name = res.w_name[i.w_id];
                i.w_photo = res.w_photo[i.w_id];
            }
            sp.compatSpisok = res.compatSpisok;
            sp.foto = res.foto;
            fotoPrint();
            compatPrint();
            movePrint();
        });
    } else {
        fotoPrint();
        compatPrint();
        movePrint();
    }
/*
    $("#foto_upload").fotoUpload({
        owner:'zp' + sp.id,
        max_x:200,
        max_y:320,
        func:function (obj) {
            sp.foto.push(obj);
            sp.img = obj.link;
            fotoPrint();
        }
    });
*/
    // вывод фотографий запчасти
    function fotoPrint() {
        return;
        if (sp.foto.length > 0) {
            $("#foto").fotoSet({foto:sp.foto[0], max_x:200, click:function () { G.fotoView({spisok:sp.foto}); }});
        }
    }

    // движение запчастей
    function movePrint() {
        if (sp.move.length > 0) {
            $("#dopMenu A:last").hide();
            type = {
                '':'Приход',
                set:'Установка',
                sale:'Продажа',
                defect:'Брак',
                return:'Возврат',
                'write-off':'Списание'
            };
            var html = "<DIV class=headBlue>Движение</DIV>";
            for (var n = 0; n < sp.move.length; n++) {
                var move = sp.move[n];
                var zayav = '';
                var client = '';
                if (move.zayav_id > 0) { zayav = "по заявке <A href='index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + move.zayav_id + "'>№" + move.nomer + "</A>"; }
                if (move.client_id > 0) { client = "клиенту <A href='index.php?" + G.values + "&my_page=remClientInfo&id=" + move.client_id + "'>" + move.fio + "</A>"; }
                var del = n == 0 ? "<DIV class=img_del val=" + move.id + ">" : '';
                html += "<DIV class=mov>" +
                    "<TABLE cellspacing=0 cellpadding=0><TR>" +
                    "<TH><A href='http://vk.com/id" + move.w_id + "' target='_blank'><IMG src=" + move.w_photo + " width=30></A>" +
                    "<TD>" + del + "</DIV><A href='http://vk.com/id" + move.w_id + "' target='_blank'>" + move.w_name + "</A>" +
                    "<H2>" + type[move.action] + " <B>" + move.count + "</B> шт. " + zayav + client + (move.summa > 0 ? " на сумму " + move.summa + " руб." : '') + "</H2>" +
                    (move.prim ? "<H4>" + move.prim + "</H4>" : '') +
                    "<H3>" + move.dtime +"</H3>" +
                    "</TABLE></DIV>";
            }
            $("#zpMove")
                .html(html)
                .find(".img_del").click(function () {
                    var id = $(this).attr('val');
                    var dialog = $("#zp_dialog").vkDialog({
                        width:300,
                        head:'Удаление записи о движении запчасти',
                        content:"<CENTER>Подтвердите удаление записи.</CENTER>",
                        butSubmit:'Удалить',
                        submit:function () {
                            dialog.process();
                            $.getJSON("/remont/zp/view/AjaxMoveDel.php?" + G.values + "&id=" + id, function (res) {
                                dialog.close();
                                sp.avai = res.count;
                                for (var n = 0; n < sp.move.length; n++) {
                                    var move = sp.move[n];
                                    if(sp.move[n].id == id) { break; }
                                }
                                sp.move.splice(n, 1);
                                avaiPrint();
                                movePrint();
                            });
                        }
                    }).o; // end dialog
                });
        } else {
            $("#zpMove").html('');
            $("#dopMenu A:last").show();
        }
        frameBodyHeightSet();
    }

    // переход к просмотру совместимой запчасти
    $("#compat").click(function (e) {
        var val = $(e.target).attr('val');
        if (val) {
            val = val.split('_');
            switch (val[0]) {
                case 'add': compatAdd(); break;
                case 'go':
                    var n = val[1];
                    var obj = sp.compatSpisok[n];
                    sp.num = obj.num;
                    obj.zakaz = sp.zakaz;
                    obj.avai = sp.avai;
                    obj.compatSpisok = sp.compatSpisok;
                    sp.compatSpisok.splice(n, 1, sp);
                    obj.move = sp.move;
                    obj.foto = sp.foto;
                    G.zp.view(obj);
                    break;
                case 'del': compatDel(val[1]); break;
            }
        }
    });

    // добавление совместимости
    function compatAdd() {
        var compat = {
            go:0,   // разрешение на создание совместимости
            add:0, // если запчасти нет в каталоге, автоматически вносится
            id:0     // если есть, сюда присваивается её id
        };
        html = "<DIV id=compat_add>" +
            "<H1>" + G.zp_name_ass[sp.name_id] + " для " + G.device_rod_ass[sp.device_id] + " " + G.vendor_ass[sp.vendor_id] + " " + G.model_ass[sp.model_id] + "</H1>" +
            "<DIV class=headName>Подходит к устройству:</DIV>" +
            "<DIV id=cdev></DIV>" +
            "<DIV id=cres></DIV>" +
            "</DIV>";
        var dialog = $("#zp_dialog").vkDialog({
            width:420,
            top:80,
            head:'Добавление совместимости с другими устройствами',
            content:html,
            butSubmit:'Добавить',
            submit:submit
        }).o;

        var CR = $("#cres");

        $("#cdev").device({
            width:220,
            device_id:sp.device_id,
            vendor_id:sp.vendor_id,
            add:1,
            func:devSelect
        });

        function devSelect(obj) {
            compat.go = 0;
            compat.add = 0;
            compat.id = 0;
            CR.html('');
            if(obj.device_id > 0 && obj.vendor_id > 0 && obj.model_id > 0) {
                if(obj.device_id == sp.device_id && obj.vendor_id == sp.vendor_id && obj.model_id == sp.model_id) {
                    CR.html("<EM class=red>Невозможно создать совместимость на это же устройство.</EM>");
                    compat.go = 2;
                } else {
                    compat.go = 3;
                    CR.imgUp();
                    obj.name_id = sp.name_id;
                    $.post("/remont/zp/view/AjaxCompatFind.php?" + G.values, obj, function (res) {
                        var zpName = G.zp_name_ass[obj.name_id] + " для " + G.device_rod_ass[obj.device_id] + " " + G.vendor_ass[obj.vendor_id] + " " + G.model_ass[obj.model_id];
                        var len = res.spisok.length;
                        var html = '';
                        if (len > 0) {
                            if (len == 1) {
                                html = "Запчасть <B>" + zpName + "</B><BR>" +
                                    "будет добавлена в совместимость. " +
                                    "Информация о движениях, наличиях и заказах будет сложена и станет общей для обоих запчастей.";
                                compat.id = res.spisok[0].id;
                                compat.go = 1;
                                CR.html(html);
                            } else {
                                var spisok = [];
                                for (var n = 0; n < len; n++) {
                                    spisok.push({
                                        uid:res.spisok[n].id,
                                        title:zpName + "<P>Цвет: " + G.color_ass[res.spisok[n].color_id]
                                    });
                                }
                                CR.html("<INPUT type=hidden id=radioCompat>");
                                $("#radioCompat").myRadio({
                                    spisok:spisok,
                                    bottom:25,
                                    func:function (id) {
                                        for (var n = 0; n < len; n++) {
                                            if (id == res.spisok[n].id) {
                                                compat.id = id;
                                                compat.go = 1;
                                                break;
                                            }
                                        }
                                        $("#cinfo").remove();
                                        if (res.spisok[n].compat_id == 0) {
                                            $("#radioCompat_radio").after("<DIV id=cinfo>Выбранная запчасть будет добавлена в совместимость. Информация о движениях, наличиях и заказах будет сложена и станет общей для обоих запчастей.</DIV>");
                                        } else if (res.spisok[n].compat_id == sp.compat_id) {
                                            $("#radioCompat_radio").after("<DIV id=cinfo class=red>Выбранная запчасть уже является совместимостью этой запчасти.</DIV>");
                                            compat.go = 4;
                                        } else {
                                            $("#radioCompat_radio").after("<DIV id=cinfo>Выбранная запчасть совместима с другими запчастями. При объединении информация о движениях, наличиях и заказах будет сложена и станет общей для всех.</DIV>");
                                        }
                                    }
                                });
                                $("#radioCompat_radio P").css({'text-align':'left',color:'#777'});
                            }
                        } else {
                            html = "Запчасти <B>" + zpName + "</B> нет в каталоге запчастей. " +
                                "При добавлении совместимости она будет автоматически внесена в каталог."
                            compat.go = 1;
                            compat.add = 1;
                            CR.html(html);
                        }
                    },'json');
                }
            }
        } // end devSelect

        function submit() {
            var txt;
            switch (compat.go) {
                case 0: txt = "Выбраны не все поля устройства."; break;
                case 2: txt = "Невозможно создать совместимость на это же устройство."; break;
                case 3: txt = "Ожидайте, идёт получение информации об устройстве.."; break;
                case 4: txt = "Выбранная запчасть уже является совместимостью этой запчасти."; break;
                case 1:
                    var obj = {
                        zid:             sp.id,
                        new_id:      compat.id,       // id уже существующей запчасти, с которой устанавливается совместимость
                        compat_id: sp.compat_id, // если исходная запчасть не совместима ни с кем, то стоит 0, иначе стоит свой id
                        name_id:    sp.name_id,
                        name_dop: sp.name_dop,
                        color_id:     sp.color_id,
                        device_id:  $("#cdev_device").val(),
                        vendor_id: $("#cdev_vendor").val(),
                        model_id:   $("#cdev_model").val(),
                        add:           compat.add
                    };
                    dialog.process();
                    $.post("/remont/zp/view/AjaxCompatAdd.php?" + G.values, obj, function (res) {
                        sp.compat_id = res.compat_id;
                        sp.compatSpisok.push({
                            num:            sp.compatSpisok.length,
                            id:                res.id,
                            name_id:     obj.name_id,
                            name_dop:  obj.name_dop,
                            color_id:      obj.color_id,
                            device_id:   obj.device_id,
                            vendor_id:   obj.vendor_id,
                            model_id:    obj.model_id,
                            compat_id:  sp.compat_id
                        });
                        dialog.close();
                        compatPrint();
                    }, 'json');
                    break;
            }
            if (compat.go != 1) { $(".vkButton:first").vkHint({msg:"<EM class=red>" + txt + "</EM>", top:-58, left:101, show:1, remove:1, indent:50}); }
        } // end compatAddSubmit()
    } // end compatAdd()

    // вывод списка совместимых запчастей
    function compatPrint() {
        var len = sp.compatSpisok.length;
        var HTML = "<DIV class=headBlue>Совместимость" +(len > 0 ? "<EM>(" + len + ")</EM>" : '') + "<A val=add_>добавить</A></DIV>" +
                   "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok>";
        if (len > 0) {
            for (var n = 0; n < len; n++) {
                var obj = sp.compatSpisok[n];
                HTML += "<TR><TD><A val=go_" + obj.num + ">" + G.zp_name_ass[obj.name_id] + " для " +
                                                                 G.device_rod_ass[obj.device_id] + " " +
                                                                 G.vendor_ass[obj.vendor_id] + " " +
                                                                 G.model_ass[obj.model_id] + "</A>" +
                         "<TD class=del><DIV class=img_del val=del_" + obj.num + "></DIV>";
            }
        } else { HTML += "<TR><TD class=empty>Совместимые запчасти отсутствуют"; }
        HTML += "</TABLE>";
        $("#compat").html(HTML);
        frameBodyHeightSet();
    }

    // Удаление совместимости
    function compatDel(n) {
        var id = sp.compatSpisok[n].id;
        var html = "<B>Подтвердите удаление совместимости.</B>";
        var dialog = $("#zp_dialog").vkDialog({
            width:270,
            head:'Удаление совместимости',
            butSubmit:'Удалить',
            content:html,
            submit:function () {
                dialog.process();
                //location.href = "/remont/zp/view/AjaxCompatDel.php?" + G.values + "&id=" + id
                $.getJSON("/remont/zp/view/AjaxCompatDel.php?" + G.values + "&id=" + id, function () {
                    dialog.close();
                    sp.compatSpisok.splice(n, 1);
                    compatPrint();
                    vkMsgOk("Совместимость удалена.");
                });
            }
        }).o;
    } // end of compatDel()

    // изменение заказа
    var menuA = $("#dopMenu A");
    if (sp.zakaz > 0) {  menuA.eq(4).find("DIV:first").css('color','#A33'); }
    menuA
        .eq(0).click(function () { G.zp.back(sp); }).end()
        .eq(2).click(function () { G.zp.add(sp, postEdit); }).end()
        .eq(3).click(avaiInsert).end()
        .eq(4).bind({
            mouseenter:function () {
                sp.zakaz_old = sp.zakaz;
                $(this).find('DIV').css('color','#A33').html("Заказано: <EM val=minus> — </EM><SPAN>" + sp.zakaz + "</SPAN><EM val=plus>+</EM>");
            },
            mouseleave:function () {
                var div = $(this).find('DIV');
                var leave = function () {
                    div.html("Заказа" + (sp.zakaz > 0 ? "но: " + sp.zakaz : "ть"));
                    if (sp.zakaz == 0) { div.css('color','#2B587A'); }
                }
                if(sp.zakaz != sp.zakaz_old) {
                    div.html("Заказано: <IMG src=/img/upload.gif>");
                    $.post("/remont/zp/AjaxZpZakazAdd.php?" + G.values, {zid:sp.id, count:sp.zakaz}, leave, 'json');
                } else { leave(); }
            },
            click:function (e) {
                switch($(e.target).attr('val')) {
                    case 'plus': zChange(1); break;
                    case 'minus': zChange(-1); break;
                }
            }
        }).end()
        .eq(5).click(function () {
            var dialog = $("#zp_dialog").vkDialog({
                width:300,
                head:'Удаление запчасти',
                content:"<CENTER>Подтвердите удаление запчасти.</CENTER>",
                butSubmit:'Удалить',
                submit:function () {
                    dialog.process();
                    $.getJSON("/remont/zp/view/AjaxZpDel.php?" + G.values + "&zid=" + sp.id, function (res) {
                        dialog.close();
                        G.zp.data.splice(sp.num, 1);
                        G.zp.back();
                    });
                }
            }).o; // end dialog
        });



    function zChange(c) {
        sp.zakaz = sp.zakaz * 1 + c;
        if(sp.zakaz < 0) { sp.zakaz = 0; }
        menuA.eq(4).find("SPAN:first").html(sp.zakaz + '');
    }

    $("#actA").linkMenu({
        head:'Выберите действие',
        spisok:[
            {uid:1,title:'+ внести приход'},
            {uid:2,title:'– установка'},
            {uid:3,title:'– продажа'},
            {uid:'defect',title:'– брак'},
            {uid:'return',title:'– возврат'},
            {uid:'write-off',title:'– списание'}
        ],
        func:function (uid) {
            if (uid == 1) {
                avaiInsert();
            } else if (sp.avai == 0) {
                $("#zp_dialog").vkDialog({ // сообщение об остутствии наличия запчасти
                    top:110,
                    width:220,
                    head:'Ошибка!',
                    content:"<CENTER>Запчасти нет в наличии.</CENTER>",
                    butSubmit:'',
                    butCancel:'Закрыть'
                });
            } else {
                switch (uid) {
                    case '2': setup(); break;
                    case '3': sale(); break;
                    default: other(uid);
                }
            }
        }
    });

    // редактирование запчасти
    function postEdit(obj) {
        sp.name_id = obj.name_id;
        sp.name_dop = obj.name_dop;
        $("#view H1:first").html(G.zp_name_ass[sp.name_id] + "<EM>" + sp.name_dop + "</EM>");
        sp.device_id = obj.device_id;
        sp.vendor_id = obj.vendor_id;
        sp.model_id = obj.model_id;
        $("#view H2:first").html("для " + G.device_rod_ass[sp.device_id] + " <A href='/index.php?" + G.values + "&my_page=remDeviceView&id=" + sp.model_id + "'>" + G.vendor_ass[sp.vendor_id] + " " + G.model_ass[sp.model_id] + "</A>");
        sp.color_id = obj.color_id;
        $("#view H3:first").html(sp.color_id > 0 ? "<EM>Цвет:</EM>" + G.color_ass[sp.color_id] : '');
        vkMsgOk("Редактирование данных произведено.");
    }

    // обновление наличия запчастей
    function avaiPrint() {
        $("#view .nal")
            .html(sp.avai > 0 ? "Наличие " + sp.avai + " шт." : "Нет в наличии.")
            .css('color', sp.avai > 0 ? '#0B0' : '#555');
    }

    // внесение наличия
    function avaiInsert() {
        G.zp.avaiInsert(sp, function (res) {
            avaiPrint();
            sp.move.unshift({
                id:res.id,
                w_id:G.vku.viewer_id,
                w_photo:G.vku.photo,
                w_name:G.vku.name,
                action:'',
                count:res.kolvo,
                summa:res.summa,
                dtime:res.dtime,
                zayav_id:0,
                client_id:0
            });
            movePrint();
        });
    }

    // установка запчасти
    function setup() {
        var html = "<TABLE cellpadding=0 cellspacing=5 class=zpDecTab>" +
            "<TR><TD class=tdAbout>Количество:<TD><INPUT type=text id=count maxlength=5 value=1><SPAN>(max: <B>" + sp.avai + "</B>)</SPAN>" +
            "<TR><TD class=tdAbout>Номер заявки:<TD><INPUT type=text id=zayavNomer maxlength=8><SPAN id=img></SPAN><DIV id=zFind></DIV>" +
            "<TR><TD class=tdAbout>Примечание:<TD><TEXTAREA id=prim></TEXTAREA>" +
            "</TABLE>";
        var dialog = $("#zp_dialog").vkDialog({
            width:380,
            head:'Установка запчасти',
            content:html,
            submit:submit
        }).o;

        $("#zayavNomer").focus().keyup(function () {
            sp.zayav_id = 0;
            $("#zFind").html('');
            $("#img").imgUp();
            var val = $(this).val();
            if (/[0-9]$/.test(val)) {
                $.getJSON("/remont/zp/view/AjaxZayavFind.php?" + G.values + "&nomer=" + val, function (res) {
                    if(res.id > 0) {
                        sp.zayav_id = res.id;
                        html = "<TABLE cellpadding=0 cellspacing=5><TR>" +
                            "<TD><A href='index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + res.id + "'><IMG src='" + res.img + "' height=40></A>" +
                            "<TD><A href='index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + res.id + "'>" + G.category_ass[res.category] + "<BR>" + G.device_ass[res.device_id] + "<BR>" + G.vendor_ass[res.vendor_id] + " " + G.model_ass[res.model_id] + "</A>" +
                            "</TABLE>";
                        $("#zFind").html(html);
                        $("#img").html('');
                    } else {
                        $("#img").html('Заявка не найдена.');
                    }
                });
            } else {
                $("#img").html('Некорректный ввод.');
            }
        });

        function submit() {
            if (!sp.zayav_id) {
                $("#zayavNomer")
                    .vkHint({msg:"<EM class=red>Указан неверный номер заявки.</EM>", top:-57, left:-61, show:1, remove:1})
                    .focus();
            } else {
                var count = $("#count").val();
                if (isNaN(count) || !count || count <= 0 || count > sp.avai * 1) {
                    $("#count")
                        .vkHint({msg:"<EM class=red>Некорректно указано количество,<BR>либо оно превышает допустимое значение.</EM>", top:-70, left:-35, indent:70, show:1, remove:1})
                        .focus();
                } else {
                    dialog.process();
                    $.post("/remont/zp/view/AjaxZpSet.php?" + G.values, {
                            zp_id:sp.id,
                            count:count,
                            zayav_id:sp.zayav_id,
                            type:'set',
                            prim:$("#prim").val()
                        },
                        function (res) {
                            vkMsgOk("Установка запчасти произведена.");
                            sp.avai -= count;
                            avaiPrint();
                            sp.move.unshift({
                                id:res.id,
                                w_id:G.vku.viewer_id,
                                w_photo:G.vku.photo,
                                w_name:G.vku.name,
                                action:'set',
                                count:count,
                                dtime:res.dtime,
                                zayav_id:sp.zayav_id,
                                nomer:$("#zayavNomer").val(),
                                client_id:0,
                                prim:$("#prim").val()
                            });
                            movePrint();
                            dialog.close();
                        },'json');
                }
            }
        }
    } // end setup()

    // продажа запчасти
    function sale() {
        var html = "<TABLE cellpadding=0 cellspacing=5 class=zpDecTab>" +
            "<TR><TD class=tdAbout>Количество:<TD><INPUT type=text id=count maxlength=5 value=1><SPAN>(max: <B>" + sp.avai + "</B>)</SPAN>" +
            "<TR><TD class=tdAbout>Цена за ед.:<TD><INPUT type=text id=cena maxlength=8> руб." +
            "<TR><TD class=tdAbout>Деньги поступили в кассу?:<TD><input type=hidden id=kassa value='-1'>" +
            "<TR><TD class=tdAbout>Клиент:<TD><INPUT TYPE=hidden id=client_id value=0>" +
            "<TR><TD class=tdAbout>Примечание:<TD><TEXTAREA id=prim></TEXTAREA>" +
            "</TABLE>";
        var dialog = $("#zp_dialog").vkDialog({
            width:440,
            head:'Продажа запчасти',
            content:html,
            submit:submit
        }).o;

        $("#zp_dialog .zpDecTab:first .tdAbout").css('width', '150px');

        $("#kassa").vkRadio({
            display:'inline-block',
            right:15,
            spisok:[{uid:1, title:'да'},{uid:0, title:'нет'}]
        });
        $("#kassa_radio").vkHint({msg:"Если это наличный платёж<BR>и деньги остаются в мастерской,<BR>укажите 'да'.", top:-83, left:-60});

        $("#client_id").clientSel({add:1});

        function submit() {
            var send = {
                zp_id:sp.id,
                count:$("#count").val(),
                cena:$("#cena").val(),
                client_id:$("#client_id").val(),
                kassa:$("#kassa").val(),
                type:'sale',
                prim:$("#prim").val()
            };
            var msg;
            if(!/^[\d]+$/.test(send.count) || send.count > sp.avai * 1) { $("#count").vkHint({msg:"<EM class=red>Некорректно указано количество,<BR>либо оно превышает допустимое значение.</EM>", top:-70, left:-16, show:1, remove:1, indent:50}).focus(); }
            else if(!/^[\d]+$/.test(send.cena) || send.cena == 0) {msg = "Некорректно указана цена."; $("#cena").focus(); }
            else if (send.kassa == -1) { msg = "Укажите, поступили деньги в кассу или нет."; }
            else {
                dialog.process();
                $.post("/remont/zp/view/AjaxZpSet.php?" + G.values, send, function (res) {
                    vkMsgOk("Продажа запчасти произведена.");
                    sp.avai -= send.count;
                    avaiPrint();
                    sp.move.unshift({
                        id:res.id,
                        w_id:G.vku.viewer_id,
                        w_photo:G.vku.photo,
                        w_name:G.vku.name,
                        action:'sale',
                        count:send.count,
                        summa:res.summa,
                        dtime:res.dtime,
                        zayav_id:0,
                        client_id:send.client_id,
                        fio:$("#client_id").next().find('INPUT:first').val(),
                        prim:send.prim
                    });
                    movePrint();
                    dialog.close();
                },'json');
            }
            if (msg) { $("#zp_dialog .bottom:first").vkHint({msg:"<EM class=red>" + msg + "</EM>", top:-48, left:123, show:1, remove:1, indent:50}); }
        } // end submit()
    } // end sale()

    // брак, списание, возврат
    function other(type) {
        var rus = {defect:'Забраковка', return:'Возврат', 'write-off':'Списание'};
        var end = {defect:'ена', return:'ён', 'write-off':'ено'};
        var HTML = "<TABLE cellpadding=0 cellspacing=5 class=zpDecTab>" +
            "<TR><TD class=tdAbout>Количество:<TD><INPUT type=text id=count maxlength=5 value=1><SPAN>(max: <B>" + sp.avai + "</B>)</SPAN>" +
            "<TR><TD class=tdAbout>Примечание:<TD><TEXTAREA id=prim></TEXTAREA></TABLE>";
        var dialog = $("#zp_dialog").vkDialog({
            width:380,
            head:rus[type] + ' запчасти',
            content:HTML,
            submit:submit
        }).o;

        function submit() {
            var count = $("#count").val();
            if(isNaN(count) || !count || count <= 0 || count > sp.avai * 1) {
                $("#count")
                    .vkHint({msg:"<EM class=red>Некорректно указано количество,<BR>либо оно превышает допустимое значение.</EM>", top:-70, left:-81, show:1, remove:1})
                    .focus();
            } else {
                dialog.process();
                $.post("/remont/zp/view/AjaxZpSet.php?" + G.values, {
                        zp_id:sp.id,
                        count:count,
                        type:type,
                        prim:$("#prim").val()
                    },
                    function (res) {
                        vkMsgOk(rus[type] + " запчасти произвед" + end[type] + ".");
                        sp.avai -= count;
                        avaiPrint();
                        sp.move.unshift({
                            id:res.id,
                            w_id:G.vku.viewer_id,
                            w_photo:G.vku.photo,
                            w_name:G.vku.name,
                            action:type,
                            count:count,
                            dtime:res.dtime,
                            zayav_id:0,
                            client_id:0,
                            prim:$("#prim").val()
                        });
                        movePrint();
                        dialog.close();
                    },'json');
            }
        }
    } // end other()
};


