// Деньги
function moneySpisok() {
    var dopMenu = "<DIV id=dopMenu>" +
        "<A class=linkSel><I></I><B></B><DIV val=money_0>Поступления</DIV><B></B><I></I></A>" +
        "<A class=link><I></I><B></B><DIV val=money_1>Расходы</DIV><B></B><I></I></A>" +
        "<A class=link><I></I><B></B><DIV val=money_2>Касса</DIV><B></B><I></I></A>" +
        "<DIV style=clear:both;></DIV></DIV>";

    html = "<DIV id=money>" + dopMenu + "<DIV id=money_content></DIV></DIV>";
    $("#content").html(html);

    $("#content #dopMenu").on('click', function (e) {
        var val = $(e.target).attr('val');
        if (val) {
            val = val.split('_')[1];
            $(this).find('.linkSel').attr('class', 'link');
            $(this).find('A').eq(val).attr('class', 'linkSel');
            switch (val) {
                case '0': goPrihod(); break;
                case '1': goRashod(); break;
                case '2': goKassa(); break;
            }
        }
    });

    goPrihod();

    // переход в Поступления
    function goPrihod() {
        var html = "<DIV class=findHead>Период</DIV>" +
            "<EM class=period_em>от:</EM><INPUT type=hidden id=day_begin>" +
            "<EM class=period_em>до:</EM><INPUT type=hidden id=day_end>";
        $("#podmenu").html(html);

        $("#day_begin").vkCalendar({lost:1, place:'left', func:function (data) { G.spisok.print({day_begin:data}); }});
        $("#day_end").vkCalendar({lost:1, place:'left', func:function (data) { G.spisok.print({day_end:data}); }});

        html = "<DIV id=prihod>" +
            "<DIV id=summa></DIV><A id=prihod_add>Внести произвольную сумму</A>" +
            "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok><TR><TH class=sum>Сумма<TH class=about>Описание<TH class=data>Дата</TABLE>" +
            "<DIV id=prihod_spisok></DIV>" +
            "</DIV>";
        $("#money_content").html(html);
        $("#prihod_add").vkHint({msg:"Это может быть, например,<BR>продажа устройства,<BR>которое не внесено в базу.", ugol:'top', top:13, left:38});
        $("#prihod_add").on('click', prihodAdd);


        G.spisok.unit = function (sp) {
            var txt = sp.txt;
            if (sp.zayav_id > 0) { txt = "Заявка <A href='/index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + sp.zayav_id + "'><EM>№</EM>" + sp.zayav_nomer + "</A>"; }
            if (sp.zp_id > 0) {
                txt = "Продажа запчасти " +
                    "<A href='/index.php?" + G.values + "&my_page=remZp&id=" + sp.zp_id + "'>" +
                    "<B>" + G.zp_name_ass[sp.zp_name] + "</B>" +
                    " для " + G.device_rod_ass[sp.zp_device] +
                    " " + G.vendor_ass[sp.zp_vendor] +
                    " " + G.model_ass[sp.zp_model] +
                    "</A>";
            }
            var html = "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok><TR>" +
                "<TD class=sum align=center><B>" + sp.sum + "</B>" +
                "<TD class=about>" + txt +
                "<TD class=data>" + sp.dtime_add + "<BR><A href='http://vk.com/id" + sp.viewer_id + "'>" + G.vkusers[sp.viewer_id] + "</A>" +
                "</TABLE>";
            return html;
        };

        G.spisok.create({
            url:"/remont/report/money/AjaxMoneyGet.php",
            limit:10,
            view:$("#prihod_spisok"),
            imgup:$("#summa"),
            nofind:"За выбранный период платежей нет.",
            //    a:1,
            values:{
                day_begin:$("#day_begin").val(),
                day_end:$("#day_end").val()
            },
            callback:function (res) {
                $("#summa").html("Сумма: <B>" + G.spisok.data.sum + "</B> руб.");
            }
        });
    } // end goPrihod




    // Внесение прихода
    function prihodAdd() {
        var html = "<TABLE cellpadding=0 cellspacing=0 id=prihod_add_tab>" +
            "<TR><TD class=tdAbout>Содержание:<TD><INPUT type=text id=prihod_txt maxlength=100>" +
            "<TR><TD class=tdAbout>Сумма:<TD><INPUT type=text id=prihod_sum maxlength=8> руб." +
            "<TR><TD class=tdAbout>Деньги поступили в кассу?:<TD><INPUT type=hidden id=prihod_kassa value='-1'>" +
            "</TABLE>";
        var dialog = $("#report_dialog").vkDialog({
            width:380,
            head:"Внесение поступления средств",
            content:html,
            submit:submit
        }).o;

        $("#prihod_kassa").vkRadio({
            display:'inline-block',
            right:15,
            spisok:[{uid:1, title:'да'},{uid:0, title:'нет'}]
        });
        $("#prihod_txt").focus();

        function submit() {
            var send = {
                txt:$("#prihod_txt").val(),
                sum:$("#prihod_sum").val(),
                kassa:$("#prihod_kassa").val()
            };

            var msg;
            if (!send.txt) { msg = "Не указано содержание."; $("#prihod_txt").focus(); }
            else if (!/^\d+$/.test(send.sum)) { msg = "Некорректно указана сумма."; $("#prihod_sum").focus(); }
            else if (send.kassa == -1) { msg = "Укажите, деньги взяты их кассы или нет."; }
            else {
                dialog.process();
                $.post("/remont/report/money/AjaxPrihodRashodAdd.php?" + G.values, send, function (res) {
                    dialog.close();
                    vkMsgOk("Новое поступление внесено.");
                    if (send.kassa == 1) { G.kassa_sum += send.sum; }
                    G.spisok.print();
                }, 'json');
            }
            if (msg) { $("#report_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:105}); }
        }
    } // end prihodAdd










    function goRashod() {
        var d = new Date();
        var mon = d.getMonth() + 1;
        var html = "<DIV class=findHead>Период</DIV>" +
            "<INPUT type=hidden id=rashod_mon value='" + d.getFullYear() + "-" + (mon < 10 ? '0' : '') + mon + "'>";
        $("#podmenu").html(html);

        $("#rashod_mon").vkSel({
            width:140,
            spisok: G.rashod_mon,
            func:rashodSpisok
        });

        html = "<DIV id=rashod>" +
            "<DIV class=headName>Список расходов мастерской<A>Внести новый расход</A></DIV>" +
            "<DIV id=spisok><IMG src=/img/upload.gif></DIV>" +
            "</DIV>";
        $("#money_content").html(html);
        $("#rashod A:first").on('click', rashodAdd);
        rashodSpisok();
    } // end goRashod




    // вывод списка расходов
    function rashodSpisok() {
        var val = "&mon=" + $("#rashod_mon").val();
        $.getJSON("/remont/report/money/AjaxRashodGet.php?" + G.values + val, function (res) {
            var html = "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok><TR><TH>Сумма<TH>Описание<TH>Дата";
            var summa = 0;
            for (var n = 0; n < res.spisok.length; n++) {
                sp = res.spisok[n];
                summa += sp.sum;
                html += "<TR>" +
                    "<TD class=sum>" + sp.sum +
                    "<TD class=txt>" + sp.txt +
                    "<TD class=dtime>" + sp.dtime;
            }
            html += "</TABLE>";
            html = "Сумма: <b>" + summa + "</b> руб." + html;
            $("#rashod #spisok").html(html);
        });
    } // end rashodSpisok





    // добавление расхода
    function rashodAdd() {
        var html = "<TABLE cellpadding=0 cellspacing=0 id=rashod_add_tab>" +
            "<TR><TD class=tdAbout>Содержание:<TD><INPUT type=text id=rashod_txt maxlength=100>" +
            "<TR><TD class=tdAbout>Сумма:<TD><INPUT type=text id=rashod_sum maxlength=8> руб." +
            "<TR><TD class=tdAbout>Деньги взяты из кассы?<TD><INPUT type=hidden id=rashod_kassa value='-1'>" +
            "</TABLE>";
        var dialog = $("#report_dialog").vkDialog({
            head:"Внесение расхода",
            content:html,
            submit:submit
        }).o;

        $("#rashod_kassa").vkRadio({
            display:'inline-block',
            right:15,
            spisok:[{uid:1, title:'да'},{uid:0, title:'нет'}]
        });
        $("#rashod_txt").focus();

        function submit() {
            var send = {
                txt:$("#rashod_txt").val(),
                sum:$("#rashod_sum").val(),
                kassa:$("#rashod_kassa").val()
            };

            var msg;
            if (!send.txt) { msg = "Не указано содержание."; $("#rashod_txt").focus(); }
            else if (!/^\d+$/.test(send.sum)) { msg = "Некорректно указана сумма."; $("#rashod_sum").focus(); }
            else if (send.kassa == -1) { msg = "Укажите, деньги взяты их кассы или нет."; }
            else {
                dialog.process();
                send.sum *= -1;
                $.post("/remont/report/money/AjaxPrihodRashodAdd.php?" + G.values, send, function (res) {
                    dialog.close();
                    vkMsgOk("Новый расход внесён.");
                    if (send.kassa == 1) { G.kassa_sum += send.sum; }
                    rashodSpisok();
                }, 'json');
            }
            if (msg) { $("#report_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:95}); }
        }
    } // end rashodAdd






    // Переход в кассу
    function goKassa() {
        var html;
        $("#podmenu").html('');

        if (G.kassa_sum == -1) { kassaSet(); }
        else {
            html = "<DIV id=kassa>" +
                "<DIV id=in>В кассе: <B id=summa>" + G.kassa_sum + "</B> руб. " +
                "<A>Взять из кассы</A> :: "+
                "<A>Положить в кассу</A></DIV>" +
                "<DIV id=about_tab>Действия с кассой за текущий месяц:</DIV>" +
                "<DIV id=spisok><IMG src=/img/upload.gif></DIV>" +
                "</DIV>";
            $("#money_content").html(html);

            $("#kassa A:first").click(kassaGet);
            $("#kassa A:eq(1)").click(kassaPut);

            kassaSpisok();
        }

        // вывод списка кассы
        function kassaSpisok() {
            $.getJSON("/remont/report/money/AjaxKassaGet.php?" + G.values, function (res) {
                var html = "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok><TR><TH>Сумма<TH>Описание<TH>Дата";
                for (var n = 0; n < res.spisok.length; n++) {
                    sp = res.spisok[n];
                    switch (sp.type) {
                        case '1': sp.txt = "оплата по заявке <A href='/index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + sp.zayav_id + "'>№" + sp.zayav_nomer + "</A>"; break;
                        case '2':
                            sp.txt = "Продажа запчасти " +
                                "<A href='/index.php?" + G.values + "&my_page=remZp&id=" + sp.zp_id + "'>" +
                                "<B>" + G.zp_name_ass[sp.zp_name] + "</B>" +
                                " для " + G.device_rod_ass[sp.zp_device] +
                                " " + G.vendor_ass[sp.zp_vendor] +
                                " " + G.model_ass[sp.zp_model] +
                                "</A>";
                    }
                    html += "<TR>" +
                        "<TD class=sum>" + sp.sum +
                        "<TD class=txt>" + sp.txt +
                        "<TD class=dtime>" + sp.dtime;
                }
                html += "</TABLE>";
                $("#kassa #spisok").html(html);
                frameBodyHeightSet();
            });
        }

        // взять деньги из кассы
        function kassaGet() {
            var html = "<TABLE cellpadding=0 cellspacing=8 class=kassa_tab>" +
                "<TR><TD class=tdAbout>Сумма:<TD><INPUT type=text id=sum maxlength=8> (max: " + G.kassa_sum + ")" +
                "<TR><TD class=tdAbout>Комментарий:<TD><INPUT type=text id=txt>" +
                "</TABLE>";
            var dialog = $("#report_dialog").vkDialog({
                head:"Взятие денег из кассы",
                content:html,
                butSubmit:"Применить",
                submit:submit
            }).o;

            $("#sum").focus();

            function submit() {
                var send = {
                    sum:$("#sum").val(),
                    txt:$("#txt").val()
                };
                var msg;
                if (!/^[0-9]+$/.test(send.sum)) { msg = "Некорректно введена сумма."; }
                else if (send.sum > G.kassa_sum) { msg = "Введённая сумма превышает сумму в кассе."; }
                else if (!send.txt) { msg = "Не указан комментарий."; }
                else {
                    send.sum *= -1;
                    dialog.process();
                    $.post("/remont/report/money/AjaxKassaAdd.php?" + G.values, send, function (res) {
                        dialog.close();
                        vkMsgOk("Операция выполнена.");
                        G.kassa_sum += send.sum;
                        $("#kassa #summa").html(G.kassa_sum);
                        kassaSpisok();
                    }, 'json');
                }
                if (msg) { $("#report_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:96}); }
            }
        } // end kassaGet

        // положить деньги в кассу
        function kassaPut() {
            var html = "<TABLE cellpadding=0 cellspacing=8 class=kassa_tab>" +
                "<TR><TD class=tdAbout>Сумма:<TD><INPUT type=text id=sum maxlength=8> руб." +
                "<TR><TD class=tdAbout>Комментарий:<TD><INPUT type=text id=txt>" +
                "</TABLE>";
            var dialog = $("#report_dialog").vkDialog({
                head:"Внесение денег в кассу",
                content:html,
                submit:submit
            }).o;

            $("#sum").focus();

            function submit() {
                var send = {
                    sum:$("#sum").val(),
                    txt:$("#txt").val()
                };
                var msg;
                if (!/^[0-9]+$/.test(send.sum)) { msg = "Некорректно введена сумма."; }
                else if (!send.txt) { msg = "Не указан комментарий."; }
                else {
                    dialog.process();
                    $.post("/remont/report/money/AjaxKassaAdd.php?" + G.values, send, function (res) {
                        dialog.close();
                        vkMsgOk("Операция выполнена.");
                        G.kassa_sum += send.sum * 1;
                        $("#kassa #summa").html(G.kassa_sum);
                        kassaSpisok();
                    }, 'json');
                }
                if (msg) { $("#report_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:96}); }
            }
        } // end kassaPut
    } // end goKassa





    // установка начального значения в кассе
    function kassaSet() {
        var html = "<DIV id=kassa_set>" +
            "<DIV class=info>Установите значение, равное текущей сумме денег, находящейся сейчас в мастерской. " +
            "От этого значения будет вестись дальнейший учёт средств, поступающих, либо забирающихся из кассы.<BR>" +
            "<B>Внимание!</B> Данную операцию можно произвести только один раз.</DIV>" +
            "<TABLE cellpadding=0 cellspacing=8 id=kassa_set_tab><TR>" +
            "<TD>Сумма: <INPUT type=text id=kassa_set_sum maxlength=8> руб." +
            "<TD id=kassa_set_button><DIV class=vkButton><BUTTON>Установить</BUTTON></DIV>" +
            "</TABLE>" +
            "</DIV>";

        $("#money_content").html(html);
        $("#kassa_set_sum").focus();
        $("#kassa_set BUTTON:first").on('click', function () {
            var send = { summa:$("#kassa_set_sum").val() };
            var msg;
            if (!/^[\d]+$/.test(send.summa)) { msg = "Некорректно введена сумма."; $("#kassa_set_sum").focus(); }
            else {
                $(this).butProcess();
                $.post("/remont/report/money/AjaxKassaSet.php?" + G.values, send, function (res) {
                    vkMsgOk("Значение установлено.");
                    G.kassa_sum = send.summa;
                    goKassa();
                }, 'json');
            }
            if (msg) { $("#kassa_set").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:31, left:134, correct:0}); }
        });
    } // end kassaSet
} // end moneySpisok

