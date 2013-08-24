var REGEXP_NUMERIC = /^\d+$/,
    URL = 'http://' + G.domain + '/index.php?' + G.values,
    AJAX_MAIN = 'http://' + G.domain + '/ajax/main.php?' + G.values,
    hashLoc,
    hashSet = function(hash) {
        if(!hash && !hash.p)
            return;
        hashLoc = hash.p;
        var s = true;
        switch(hash.p) {
            case 'zayav':
                if(hash.d == 'info')
                    hashLoc += '_' + hash.id;
                else if(hash.d == 'add')
                    hashLoc += '_add' + (REGEXP_NUMERIC.test(hash.id) ? '_' + hash.id : '');
                else if(!hash.d)
                    s = false;
                break;
            default:
                if(hash.d) {
                    hashLoc += '_' + hash.d;
                    if(hash.d1)
                        hashLoc += '_' + hash.d1;
                }
        }
        if(s)
            VK.callMethod('setLocation', hashLoc);
    },
    modelImageGet = function() {
        var send = {
                op:'model_img_get',
                model_id:$("#dev_model").val()
            },
            dev = $("#device_image");
        dev.html('');
        if(send.model_id > 0) {
            dev.addClass('busy');
            $.post(AJAX_MAIN, send, function(res) {
                if(res.success)
                    dev.html('<img src="' + res.img + '">')
                       .find('img').on('load', function() {
                           $(this).show().parent().removeClass('busy');
                       });
            }, 'json');
        }
    },
    zayavFilterValues,
    zayavFilterValuesGet = function () {
        var v = {
                find:$.trim($('#find').find('input').val()),
                sort:$('#sort').val(),
                desc:$('#desc').val(),
                status:$('#status .sel').attr('val'),
                device:$('#dev_device').val(),
                vendor:$('#dev_vendor').val(),
                model:$('#dev_model').val(),
                place:$('#device_place').val(),
                devstatus:$('#devstatus').val()
            },
            loc = '';
        if(v.sort != 1) loc += '.sort=' + v.sort;
        if(v.desc != 0) loc += '.desc=' + v.desc;
        if(v.find) loc += '.find=' + escape(v.find);
        else {
            if(v.status > 0) loc += '.status=' + v.status;
            if(v.device > 0) loc += '.device=' + v.device;
            if(v.vendor > 0) loc += '.vendor=' + v.vendor;
            if(v.model > 0) loc += '.model=' + v.model;
            if(v.place != 0) loc += '.place=' + v.place;
            if(v.devstatus > 0) loc += '.devstatus=' + v.devstatus;
        }
        VK.callMethod('setLocation', hashLoc + loc);
        zayavFilterValues = v;

        setCookie('zayav_find', escape(v.find));
        setCookie('zayav_sort', v.sort);
        setCookie('zayav_desc', v.desc);
        setCookie('zayav_status', v.status);
        setCookie('zayav_device', v.device);
        setCookie('zayav_vendor', v.vendor);
        setCookie('zayav_model', v.model);
        setCookie('zayav_place', encodeURI(v.place));
        setCookie('zayav_devstatus', v.devstatus);

        return zayavFilterValues;
    },
    zayavSpisokLoad = function() {
        var send = zayavFilterValuesGet();
        $('.condLost')[(send.find ? 'add' : 'remove') + 'Class']('hide');
        send.op = 'zayav_spisok_load';

        $('#mainLinks')
            .find('img').remove().end()
            .append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            $('#zayav .result').html(res.all);
            $('#zayav #spisok').html(res.html);
            $('#mainLinks img').remove();
        }, 'json');
    },
    zayavInfoMoneyUpdate = function() {
        var send = {
            op:'zayav_money_update',
            id:G.zayavInfo.id
        };
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                $('b.acc').html(res.acc);
                $('.acc_tr')[(res.acc == 0 ? 'add' : 'remove') + 'Class']('dn');
                $('b.op').html(res.opl);
                $('.op_tr')[(res.opl == 0 ? 'add' : 'remove') + 'Class']('dn');
                $('.dopl')
                    [(res.dopl == 0 ? 'add' : 'remove') + 'Class']('dn')
                    .html((res.dopl > 0 ? '+' : '') + res.dopl);
            }
        }, 'json');
    },
    reportHistoryLoad = function() {
        var send = {
            op:'report_history_load',
            worker:$('#report_history_worker').val(),
            action:$('#report_history_action').val()
        };
        $('#mainLinks')
            .find('img').remove().end()
            .append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_history').html(res.html);
            $('#mainLinks img').remove();
        }, 'json');
    },
    reportRemindLoad = function() {
        var send = {
            op:'report_remind_load',
            status:$('#remind_status').val(),
            private:$('#remind_private').val()
        };
        $('#mainLinks')
            .find('img').remove().end()
            .append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            $('#remind_spisok').html(res.html);
            $('#mainLinks img').remove();
        }, 'json');
    },
    reportPrihodLoad = function() {
        var send = {
            op:'report_prihod_load',
            day_begin:$('#report_prihod_day_begin').val(),
            day_end:$('#report_prihod_day_end').val(),
            del_show:$('#prihodShowDel').val() == 1 ? 1 : 0
        };
        $('.rightLinks a.sel').append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_prihod').html(res.html);
            $('.rightLinks a.sel img').remove();
        }, 'json');
    },
    reportRashodLoad = function() {
        var send = {
            op:'report_rashod_load',
            category:$('#rashod_category').val(),
            worker:$('#rashod_worker').val(),
            year:$('#rashod_year').val(),
            month:$('#rashod_monthSum').val()
        };
        if(send.month < 10) send.month = '0' + send.month;
        $('#mainLinks')
            .find('img').remove().end()
            .append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            monthSum = res.summ.split(',');
            reportRashodMonthPrint();
            $('#report_rashod #spisok').html(res.html);
            $('#mainLinks img').remove();
        }, 'json');
    },
    reportRashodMonthPrint = function() {
        var spisok = [];
        for(var n = 1; n <= 12; n++)
            spisok.push({uid:n, title:G.months_ass[n] + (monthSum[n - 1] > 0 ? '<div class="sum">' + monthSum[n - 1] + '</div>' : '')});
        $('#rashod_monthSum').vkRadio({
            top:5,
            light:1,
            spisok:spisok,
            func:reportRashodLoad
        });
    },
    rashodCategoryAdd = function(spisok, obj) {
        var html = '<TABLE>' +
            '<TR><TD class="tdAbout">Наименование:<TD><INPUT type="text" id="rashod_category_name">' +
            '</TABLE>',
            dialog = vkDialog({
                width:320,
                head:"Новая категория для расходов",
                content:html,
                submit:submit
            }),
            name = $('#rashod_category_name');
        name.focus();

        function submit() {
            var send = {
                op:'setup_rashod_category_add',
                name:name.val()
            };
            if(!send.name) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">Не указано наименование.</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-51,
                    left:73,
                    correct:0
                });
                name.focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("Новая категория внесена.");
                        obj.add({uid:res.id,title:send.name}).val(res.id);
                    }
                }, 'json');
            }
        }
    },
    reportKassaLoad = function() {
        var send = {
            op:'report_kassa_load',
            del_show:$('#kassaShowDel').val() == 1 ? 1 : 0
        };
        $('#mainLinks')
            .find('img').remove().end()
            .append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_kassa #spisok').html(res.html);
            $('#mainLinks img').remove();
        }, 'json');
    };

$(document).ajaxError(function(event, request, settings) {
    if(!request.responseText)
        return;
    alert('Ошибка:\n\n' + request.responseText);
});

$(document)
    .on('click', '#script_style', function() {
        $.post(AJAX_MAIN, {'op':'script_style'}, function(res) {
            if(res.success)
                location.reload();
        }, 'json');
    })
    .on('click', '#cache_clear', function() {
        $.post(AJAX_MAIN, {'op':'cache_clear'}, function(res) {
            if(res.success)
                vkMsgOk("Кэш очищен.");
        }, 'json');
    })
    .on('mouseenter', '.zayav_link', function(e) {
        var t = $(this),
            tooltip = t.find('.tooltip');
        if(!tooltip.hasClass('empty'))
            return;
        var send = {
            op:'tooltip_zayav_info_get',
            id:t.attr('val')
        };
        $.post(AJAX_MAIN, send, function(res) {
            tooltip
                .html(res.html)
                .removeClass('empty');
        }, 'json');
    });

$(document)
    .on('click', '#zayav_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this);
        zayavFilterValues.op = 'zayav_next';
        zayavFilterValues.page = $(this).attr('val');
        next.addClass('busy');
        $.post(AJAX_MAIN, zayavFilterValues, function (res) {
            if(res.success) {
                next.remove();
                $('#zayav #spisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#zayav .unit', function() {
        location.href = URL + '&p=zayav&d=info&id=' + $(this).attr('val');
    })
    .on('mouseenter', '#zayav .unit', function() {
        var t = $(this),
            msg = t.find('.msg').val();
        if(msg)
            t.vkHint({
                width:150,
                msg:msg,
                ugol:'left',
                top:6,
                left:484,
                show:1,
                indent:5,
                delayShow:500
            });
    })
    .on('click', '#zayav #desc_check', zayavSpisokLoad)
    .on('click', '#zayav #filter_break', function() {
        $('#find').topSearchClear();
        $('#sort').myRadioSet(1);
        $('#desc').val(0).next().attr('class', 'check0');
        $('#status').infoLinkSet(0);
        $("#dev").device({
            width:155,
            type_no:1,
            device_ids:G.device_ids,
            vendor_ids:G.vendor_ids,
            model_ids:G.model_ids,
            func:zayavSpisokLoad
        });
        G.vkSel_device_place.val(0);
        G.vkSel_device_status.val(0);
        zayavSpisokLoad();
    });

$(document)
    .on('click', '#zayavInfo .edit', function() {
        var html = '<TABLE cellspacing=8 class="zayavEdit">' +
            '<TR><TD class="label r">Клиент:        <TD><INPUT type="hidden" id="client_id" value=' + G.zayavInfo.client_id + '>' +
            '<TR><TD class="label r">Категория:     <TD><INPUT type="hidden" id="category" value=' + G.zayavInfo.category + '>' +
            '<TR><TD class="label r top">Устройство:    <TD><TABLE cellspacing="0"><TD id="dev"><TD id="device_image"></TABLE>' +
            '<TR><TD class="label r">IMEI:          <TD><INPUT type=text id="imei" maxlength="20" value="' + G.zayavInfo.imei + '">' +
            '<TR><TD class="label r">Серийный номер:<TD><INPUT type=text id="serial" maxlength="30" value="' + G.zayavInfo.serial + '">' +
            '<TR><TD class="label r">Цвет:          <TD><INPUT type="hidden" id=color_id value=' + G.zayavInfo.color_id + '>' +
        '</TABLE>',
            dialog = vkDialog({
                width:410,
                top:30,
                head:"Заявка №" + G.zayavInfo.nomer + " - Редактирование",
                content:html,
                butSubmit:'Сохранить',
                submit:submit
            });
        $('#client_id').clientSel();
        $('#vkSel_client_id').vkHint({
            msg:'Если изменяется клиент, то начисления и платежи заявки применяются на нового клиента.',
            width:200,
            top:-83,
            left:-2,
            delayShow:1500,
            correct:0
        });
        $('#category').vkSel({width:150, spisok:G.category_spisok});
        $("#dev").device({
            width:190,
            device_id:G.zayavInfo.device,
            vendor_id:G.zayavInfo.vendor,
            model_id:G.zayavInfo.model,
            device_ids:G.device_ids,
            add:1,
            func:modelImageGet
        });
        modelImageGet();
        $("#color_id").vkSel({width:170, title0:'Цвет не указан', spisok:G.color_spisok});

        function submit() {
            var msg,
                send = {
                    op:'zayav_edit',
                    zayav_id:G.zayavInfo.id,
                    client_id:$("#client_id").val(),
                    category:$("#category").val(),
                    device:$("#dev_device").val(),
                    vendor:$("#dev_vendor").val(),
                    model:$("#dev_model").val(),
                    imei: $.trim($("#imei").val()),
                    serial:$.trim($("#serial").val()),
                    color_id:$("#color_id").val()
                };
            if(send.deivce == 0) msg = 'Не выбрано устройство';
            else if(send.client_id == 0) msg = 'Не выбран клиент';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    dialog.close();
                    vkMsgOk("Данные изменены!");
                    document.location.reload();
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    top:-47,
                    left:107,
                    show:1,
                    remove:1,
                    correct:0
                });
        }
    })
    .on('click', '#zayavInfo .remind_add', function() {
        var html = '<TABLE class="remind_add_tab" cellspacing="6">' +
            '<TR><TD class="label">Заявка:<TD>№<b>' + G.zayavInfo.nomer + '</b>' +
            '<TR><TD class="label top">Описание задания:<TD><TEXTAREA id="txt"></TEXTAREA>' +
            '<TR><TD class="label">Крайний день выполнения:<TD><INPUT type="hidden" id="data">' +
            '<TR><TD class="label">Личное:<TD><INPUT type="hidden" id="private">' +
        '</TABLE>';
        var dialog = vkDialog({
                top:60,
                width:480,
                head:'Добавление нового задания',
                content:html,
                submit:submit
            }),
            txt = $('.remind_add_tab #txt'),
            day = $('.remind_add_tab #data'),
            priv = $('.remind_add_tab #private');
        txt.autosize().focus();
        day.vkCalendar();
        priv.vkCheck();
        $('.remind_add_tab #private_check').vkHint({
            msg:'Задание сможете<BR>видеть только Вы.',
            top:-71,
            left:-11,
            indent:'left',
            delayShow:1000
        });

        function submit() {
            var send = {
                    op:'report_remind_add',
                    from_zayav:1,
                    client_id:0,
                    zayav_id:G.zayavInfo.id,
                    txt:txt.val(),
                    day:day.val(),
                    private:priv.val()
                };
            if(!send.txt) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>Не указано содержание напоминания.</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-48,
                    left:150
                });
                txt.focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Новое задание успешно добавлено.');
                        $('#remind_spisok').html(res.html);
                    } else {
                        dialog.abort();
                    }
                }, 'json');
            }
        }//submit()
    })
    .on('click', '#zayavInfo .acc_add', function() {
        var html = '<TABLE cellspacing="8" class="zayav_accrual_add">' +
                '<tr><td class="label">Сумма: <TD><input type="text" id="sum" class="money" maxlength="5" /> руб.' +
                '<tr><td class="label">Примечание:<em>(не обязательно)</em><TD><input type="text" id="prim" maxlength="100" />' +
                '<tr><td class="label">Статус заявки: <TD><INPUT type="hidden" id="acc_status" value="2" />' +
                '<tr><td class="label">Состояние устройства:<TD><INPUT type="hidden" id="acc_dev_status" value="5" />' +
                '<tr><td class="label">Добавить напоминание:<TD><INPUT type="hidden" id="acc_remind" />' +
            '</TABLE>' +

            '<TABLE cellspacing="8" class="zayav_accrual_add remind">' +
                '<tr><td class="label">Содержание:<TD><input type="text" id="reminder_txt" value="Позвонить и сообщить о готовности.">' +
                '<tr><td class="label">Дата:<TD><INPUT type="hidden" id="reminder_day">' +
            '</TABLE>';
        var dialog = vkDialog({
            top:60,
            width:420,
            head:'Заявка №' + G.zayavInfo.nomer + ' - Начисление за выполненную работу',
            content:html,
            submit:submit
        });
        $('#sum').focus();
        $('#sum,#prim,#reminder_txt').keyEnter(submit);
        $('#acc_status').linkMenu({spisok:G.status_spisok});
        $('#acc_dev_status').linkMenu({spisok:G.device_status_spisok});
        $('#acc_remind').vkCheck();
        $('#acc_remind_check').click(function(id) {
            $('.zayav_accrual_add.remind').toggle();
        });
        $("#reminder_day").vkCalendar();

        function submit() {
            var msg,
                send = {
                    op:'zayav_accrual_add',
                    zayav_id:G.zayavInfo.id,
                    sum:$("#sum").val(),
                    prim:$("#prim").val(),
                    status:$("#acc_status").val(),
                    dev_status:$("#acc_dev_status").val(),
                    remind:$("#acc_remind").val(),
                    remind_txt:$("#reminder_txt").val(),
                    remind_day:$("#reminder_day").val()
                };
            if(!REGEXP_NUMERIC.test(send.sum)) { msg = 'Некорректно указана сумма.'; $('#summa').focus(); }
            else if(send.remind == 1 && !send.remind_txt) { msg = 'Не указан текст напоминания'; $('#reminder_txt').focus(); }
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("Начисление успешно произведено!");
                        $('.tabSpisok.mon').append(res.html);
                        zayavInfoMoneyUpdate();
                        if(res.status) {
                            $('#status')
                                .html(res.status.name)
                                .css('background-color', '#' + res.status.color);
                            $('#status_dtime').html(res.status.dtime);
                        }
                        if(res.remind)
                            $('#remind_spisok').html(res.remind);
                    }
                }, 'json');
            }

            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    top:-48,
                    left:123,
                    indent:40,
                    remove:1,
                    show:1,
                    correct:0
                });
        }
    })
    .on('click', '#zayavInfo .op_add', function() {
        var html = '<TABLE cellspacing="8" class="zayav_oplata_add">' +
            '<TR><TD class="label">Сумма:<TD><input type="text" id="sum" class="money" maxlength="5"> руб.' +
            '<TR><TD class="label">Деньги поступили в кассу?:<TD><input type="hidden" id="kassa" value="-1">' +
            '<TR><TD class="label">Местонахождение устройства:<TD><input type="hidden" id="dev_place" value="2">' +
            '<TR><TD class="label">Примечание:<em>(не обязательно)</em><TD><input type="text" id="prim">' +
        '</TABLE>';
        var dialog = vkDialog({
            top:60,
            width:440,
            head:'Заявка №' + G.zayavInfo.nomer + ' - Внесение платежа',
            content:html,
            submit:submit
        });
        $('#sum').focus();
        $('#sum,#prim').keyEnter(submit);
        $('#kassa').vkRadio({
            display:'inline-block',
            right:15,
            spisok:[
                {uid:1, title:'да'},
                {uid:0, title:'нет'}
            ]
        });
        $('#kassa_radio').vkHint({
            msg:"Если это наличный платёж<BR>и деньги остаются в мастерской,<BR>укажите 'да'.",
            top:-83,
            left:-60,
            delayShow:1000
        });
        $("#dev_place").linkMenu({spisok:G.device_place_spisok});
        function submit() {
            var msg,
                send = {
                    op:'zayav_oplata_add',
                    zayav_id:G.zayavInfo.id,
                    sum:$("#sum").val(),
                    kassa:$("#kassa").val(),
                    prim:$.trim($("#prim").val()),
                    dev_place:$("#dev_place").val()
                };
            if(!REGEXP_NUMERIC.test(send.sum)) { msg = "Некорректно указана сумма."; $("#sum").focus(); }
            else if(send.kassa == -1) msg = "Укажите, деньги поступили в кассу или нет.";
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    dialog.abort();
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Платёж успешно внесён!');
                        $('.tabSpisok.mon').append(res.html);
                        zayavInfoMoneyUpdate();
                    }
                }, 'json');
            }

            if(msg)
                dialog.bottom.vkHint({
                    msg:"<SPAN class=red>" + msg + "</SPAN>",
                    remove:1,
                    indent:40,
                    show:1,
                    top:-48,
                    left:135
                });
        }
    })
    .on('click', '#zayavInfo .acc_del', function() {
        var send = {
            op:'zayav_accrual_del',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent();
        tr.html('<td colspan="4" class="deleting">Удаление... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                tr.find('.deleting').html('Начисление удалено. <a class="acc_rest" val="' + send.id + '">Восстановить</a>');
                zayavInfoMoneyUpdate();
            }
        }, 'json');
    })
    .on('click', '#zayavInfo .op_del', function() {
        var send = {
            op:'zayav_oplata_del',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent();
        tr.html('<td colspan="4" class="deleting">Удаление... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                tr.find('.deleting').html('Платёж удалён. <a class="op_rest" val="' + send.id + '">Восстановить</a>');
                zayavInfoMoneyUpdate();
            }
        }, 'json');
    })
    .on('click', '#zayavInfo .acc_rest', function() {
        var send = {
                op:'zayav_accrual_rest',
                id:$(this).attr('val')
            },
            t = $(this),
            tr = t.parent().parent();
        t.after('<img src=/img/upload.gif>').remove();
        $.post(AJAX_MAIN, send, function(res) {
            if(res.success) {
                tr.after(res.html).remove();
                zayavInfoMoneyUpdate();
            }
        }, 'json');
    })
    .on('click', '#zayavInfo .op_rest', function() {
        var send = {
                op:'zayav_oplata_rest',
                id:$(this).attr('val')
            },
            t = $(this),
            tr = t.parent().parent();
        t.after('<img src=/img/upload.gif>').remove();
        $.post(AJAX_MAIN, send, function(res) {
            if(res.success) {
                tr.after(res.html).remove();
                zayavInfoMoneyUpdate();
            }
        }, 'json');
    })
    .on('click', '#zayavInfo .status_place', function() {
        var html = '<TABLE cellspacing="8">' +
            '<TR><TD class="label r top">Статус заявки:<TD><input type="hidden" id="z_status" value="' + G.zayavInfo.z_status + '">' +
            '<TR><TD class="label r top">Местонахождение устройства:<TD><input type="hidden" id="dev_place" value="' + G.zayavInfo.dev_place + '">' +
            '<TR><TD class="label r top">Состояние устройства:<TD><input type="hidden" id="dev_status" value="' + G.zayavInfo.dev_status + '">' +
            '</TABLE>',
            dialog = vkDialog({
                width:400,
                top:30,
                head:"Изменение статуса заявки и состояния устройства",
                content:html,
                butSubmit:'Сохранить',
                submit:submit
            });
        $("#z_status").vkRadio({
            spisok:G.status_spisok,
            top:5,
            light:1
        });

        var spisok = [];
        for(var n = 0; n < G.device_place_spisok.length; spisok.push(G.device_place_spisok[n]), n++);
        spisok.push({uid:0, title:'другое: <DIV id="place_other_div"><INPUT type="text" id="place_other" maxlength="20" value="' + G.zayavInfo.place_other + '"></DIV>'});
        $('#dev_place').vkRadio({
            spisok:spisok,
            top:5,
            light:1,
            func:function(val) {
                $('#place_other_div').css('display', val == 0 ? 'inline' : 'none');
                if(val == 0)
                    $('#place_other').val('').focus();
            }
        });
        if(G.zayavInfo.dev_place == 0)
            $("#place_other_div").css('display', 'inline');

        G.device_status_spisok.splice(0, 1);
        $("#dev_status").vkRadio({
            spisok:G.device_status_spisok,
            top:5,
            light:1
        });

        function submit() {
            var msg,
                send = {
                op:'zayav_status_place',
                zayav_id:G.zayavInfo.id,
                zayav_status:$("#z_status").val(),
                dev_status:$("#dev_status").val(),
                dev_place:$("#dev_place").val(),
                place_other:$("#place_other").val()
            };
            if(send.dev_place > 0)
                send.place_other = '';
            if(send.dev_place == 0 && send.place_other == '') {
                msg = 'Не указано местонахождение устройства';
                $("#place_other").focus();
            } else if(send.dev_status == 0)
                msg = 'Не указано состояние устройства';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("Изменения сохранены.");
                        $('#status')
                            .html(res.z_status.name)
                            .css('background-color', '#' + res.z_status.color);
                        $('#status_dtime').html(res.z_status.dtime)
                        $('.dev_status').html(res.dev_status);
                        $('.dev_place').html(res.dev_place);
                        G.zayavInfo.z_status = send.zayav_status;
                        G.zayavInfo.dev_status = send.dev_status;
                        G.zayavInfo.dev_place = send.dev_place;
                        G.zayavInfo.place_other = send.place_other;
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>' + msg + '</SPAN>',
                    top:-47,
                    left:103,
                    indent:50,
                    show:1,
                    remove:1,
                    correct:0
                });
        }
    })
    .on('click', '#zayavInfo .zakaz', function() {
        var t = $(this),
            send = {
                op:'zayav_zp_zakaz',
                zayav_id:G.zayavInfo.id,
                zp_id:t.parent().parent().attr('val')
            };
        $.post(AJAX_MAIN, send, function(res) {
            if(res.success) {
                t.html('Заказано!').attr('class', 'zakaz_ok');
                vkMsgOk(res.msg);
            }
        }, 'json');
    })
    .on('click', '#zayavInfo .zakaz_ok', function() {
        location.href = URL + '&my_page=remZp&id=[4]';//todo change link
    })
    .on('click', '#zayavInfo .zpAdd', function() {
        var html = '<div class="zayav_zp_add">' +
                '<CENTER>Добавление запчасти к устройству<br />' +
                    '<b>' +
                        G.device_ass[G.zayavInfo.device] + ' ' +
                        G.vendor_ass[G.zayavInfo.vendor] + ' ' +
                        G.model_ass[G.zayavInfo.model] +
                    '</b>.'+
                '</CENTER>' +
                '<TABLE cellspacing="5">' +
                    '<TR><TD class="label r">Наименование запчасти:<TD><INPUT TYPE="hidden" id="name_id">' +
                    '<TR><TD class="label r">Дополнительная информация:<TD><INPUT TYPE="text" id="name_dop" maxlength="30">' +
                    '<TR><TD class="label r">Цвет:<TD><INPUT TYPE="hidden" id="color_id">' +
                '</TABLE>' +
            '</div>',
            dialog = vkDialog({
                top:40,
                width:400,
                head:"Внесение новой запчасти",
                content:html,
                submit:submit
            });

        $('#name_id').vkSel({
            width:200,
            title0:'Наименование не выбрано',
            spisok:G.zp_name_spisok
        });
        $('#color_id').vkSel({
            width:130,
            title0:'Цвет не указан',
            spisok:G.color_spisok
        });
        function submit() {
            var send = {
                op:'zayav_zp_add',
                zayav_id: G.zayavInfo.id,
                name_id:$("#name_id").val(),
                name_dop:$("#name_dop").val(),
                color_id:$("#color_id").val()
            };
            if(send.name_id == 0)
                dialog.bottom.vkHint({msg:'<SPAN class="red">Не указано наименование запчасти.</SPAN>',
                    top:-47,
                    left:56,
                    show:1,
                    remove:1,
                    correct:0
                });
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        vkMsgOk("Внесение запчасти произведено.");
                        dialog.close();
                        $('#zpSpisok').append(res.html);
                    }
                }, 'json');
            }
        }
    })
    .on('click', '#zayavInfo .set', function() {
        var unit = $(this).parent().parent();
        var html = '<CENTER class="zayav_zp_set">' +
            'Установка запчасти<br />' + unit.find('a:first').html() + '.<br />' +
            (unit.find('.color').length > 0 ? unit.find('.color').html() + '.<br />' : '') +
            '<br />Информация об установке также<br />будет добавлена в заметки к заявке.' +
        '</CENTER>',
        dialog = vkDialog({
            top:150,
            width:400,
            head:"Установка запчасти",
            content:html,
            butSubmit:'Установить',
            submit:submit
        });
        function submit() {
            var send = {
                op:'zayav_zp_set',
                zp_id:unit.attr('val'),
                zayav_id:G.zayavInfo.id
            };
            dialog.process();
            $.post(AJAX_MAIN, send, function(res) {
                if(res.success) {
                    dialog.close();
                    vkMsgOk('Установка запчасти произведена.');
                    unit.after(res.zp_unit).remove();
                    $('.vkComment').after(res.comment).remove();
                }
            },'json');
        }
    });

$(document)
    .on('click', '#report_history_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = {
                op:'report_history_next',
                worker:$('#report_history_worker').val(),
                action:$('#report_history_action').val(),
                page:$(this).attr('val')
            };
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                next.remove();
                $('#report_history').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    });

$(document)
    .on('click', '#report_remind .info a', function() {
        var info = $(this).parent(),
            show = info.hasClass('show');
        info[(show ? 'remove' : 'add') + 'Class']('show');
    })
    .on('mouseenter', '.remind_unit', function() {
        $(this).find('.action').show();
    })
    .on('mouseleave', '.remind_unit', function() {
        $(this).find('.action').hide();
    })
    .on('click', '.remind_unit .hist_a', function() {
        $(this).parent().parent().find('.hist').slideToggle();
    })
    .on('click', '.report_remind_add', function() {
        var html = '<TABLE class="remind_add_tab" cellspacing="6">' +
            '<TR><TD class="label">Назначение:<TD><INPUT type="hidden" id=destination>' +
            '<TR><TD class="label" id=target_name><TD id=target>' +
            '</TABLE>' +

            '<TABLE class="remind_add_tab" id="tab_content" cellspacing="6">' +
            '<TR><TD class="label top">Задание:<TD><TEXTAREA id=txt></TEXTAREA>' +
            '<TR><TD class="label">Крайний день выполнения:<TD><INPUT type="hidden" id=data>' +
            '<TR><TD class="label">Личное:<TD><INPUT type="hidden" id=private>' +
            '</TABLE>';
        var dialog = vkDialog({
            top:30,
            width:480,
            head:'Добавление нового задания',
            content:html,
            butSubmit:'Добавить',
            submit:submit
        });

        var dest = $('#destination').vkSel({
            width:150,
            title0:'Не указано',
            spisok:[
                {uid:1,title:'Клиент'},
                {uid:2,title:'Заявка'},
                {uid:3,title:'Произвольное задание'}
            ],
            func:destination
        }).o;

        $('#tab_content #txt').autosize();
        $('#tab_content #data').vkCalendar();
        $('#tab_content #private').vkCheck();
        $('#tab_content #private_check').vkHint({
            msg:'Задание сможете<BR>видеть только Вы.',
            top:-71,
            left:-11,
            indent:'left',
            delayShow:1000
        });

        function destination(id) {
            $('#target').html('');
            $('#target_name').html('');
            $('#tab_content #txt').val('');
            $('#tab_content').css('display', id > 0 ? 'block' : 'none');
            // если выбран клиент, вставляется селект с клиентами
            if (id == 1) {
                $('#target_name').html('Клиент:');
                $('#target').html('<DIV id=client_id></DIV>');
                $('#client_id').clientSel();
            }
            // если выбрана заявка
            if (id == 2) {
                $('#target_name').html('Номер заявки:');
                $('#target').html('<INPUT type=text id=zayav_nomer><INPUT type="hidden" id=zayav_id value=0><SPAN id=img></SPAN><DIV id=zayav_find></DIV>');
                $('#zayav_nomer').focus().on('keyup', function () {
                    $('#zayav_id').val(0);
                    $('#zayav_find').html('');
                    var val = $(this).val();
                    if (REGEXP_NUMERIC.test(val)) {
                        $('#img').imgUp();
                        $.getJSON('/remont/zp/view/AjaxZayavFind.php?' + G.values + '&nomer=' + val, function (res) {
                            if(res.id > 0) {
                                html = '<TABLE cellpadding=0 cellspacing=5><TR>' +
                                    '<TD><A href="index.php?' + G.values + '&my_page=remZayavkiInfo&id=' + res.id + '"><IMG src="' + res.img + '" height=40></A>' +
                                    '<TD><A href="index.php?' + G.values + '&my_page=remZayavkiInfo&id=' + res.id + '">' +
                                        G.category_ass[res.category] + '<BR>' +
                                        G.device_ass[res.device_id] + '<BR>' +
                                        G.vendor_ass[res.vendor_id] + ' ' +
                                        G.model_ass[res.model_id] + '</A>' +
                                    '</TABLE>';
                                $('#zayav_id').val(res.id);
                                $('#zayav_find').html(html);
                                $('#img').html('');
                            } else {
                                $('#img').html('Заявка не найдена.');
                            }
                        });
                    } else {
                        $('#img').html('некорректный ввод');
                    }
                });
            }
        }//destination()

        function submit() {
            var client_id = dest.val() == 1 ? $('#client_id').val() : 0,
                zayav_id = dest.val() == 2 ? $('#zayav_id').val() : 0,
                send = {
                    op:'report_remind_add',
                    client_id:client_id,
                    zayav_id:zayav_id,
                    txt:$('#tab_content #txt').val(),
                    day:$('#tab_content #data').val(),
                    private:$('#tab_content #private').val()
                },
                msg;
            if(dest.val() == 0) msg = 'Не выбрано назначение.';
            else if($('#client_id').length > 0 && send.client_id == 0) msg = 'Не выбран клиент.';
            else if($('#zayav_id').length > 0 && send.zayav_id == 0) msg = 'Не указан номер заявки.';
            else if(!send.txt) msg = 'Не указано содержание напоминания.';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Новое задание успешно добавлено.');
                        $('#remind_spisok').html(res.html);
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>' + msg + '</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-48,
                    left:150
                });
        }//submit()
        return false;
    })
    .on('click', '#report_remind_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = {
                op:'report_remind_next',
                page:$(this).attr('val')
            };
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                next.remove();
                $('#remind_spisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '.remind_unit .edit', function() {
        var dialog = vkDialog({
                top:30,
                width:400,
                head:"Новое действие для напоминания",
                content:'<center><img src="/img/upload.gif"></center>',
                butSubmit:"Применить",
                submit:submit
            }),
            curDay,
            id = $(this).attr('val'),
            send = {
                op:'report_remind_get',
                id:id
            };
        $.post(AJAX_MAIN, send, function(res) {
            curDay = res.day;
            var html = '<TABLE cellspacing="6" class="remind_action_tab">' +
                '<TR><TD class="label">' + (res.client ? "Клиент:" : '') + (res.zayav ? "Заявка:" : '') +
                    '<TD>' + (res.client ? res.client : '') + (res.zayav ? res.zayav : '') +
                '<TR><TD class="label">Задание:<TD><B>' + res.txt + '</B>' +
                '<TR><TD class="label">Внёс:<TD>' + res.viewer + ', ' + res.dtime +
                '<TR><TD class="label top">Действие:<TD><INPUT type="hidden" id=action value="0">' +
                '</TABLE>' +

                '<TABLE cellspacing="6" class=remind_action_tab id=new_action>' +
                '<TR><TD class="label" id=new_about><TD id=new_title>' +
                '<TR><TD class="label top" id=new_comm><TD><TEXTAREA id=comment></TEXTAREA>' +
                '</TABLE>';
            dialog.content.html(html);

            $("#action").vkRadio({
                top:5,
                spisok:[
                    {uid:1, title:'Перенести на другую дату'},
                    {uid:2, title:'Выполнено'},
                    {uid:3, title:'Отменить'}
                ],
                func:function (id) {
                    $("#new_action").show();
                    $("#comment").val('');
                    $("#new_about").html('');
                    $("#new_title").html('');
                    if (id == 1) {
                        $("#new_about").html("Дата:");
                        $("#new_title").html('<INPUT type="hidden" id=data>');
                        $("#new_comm").html("Причина:");
                        $("#new_action #data").vkCalendar();
                    }
                    if(id == 2) $("#new_comm").html("Комментарий:");
                    if(id == 3) $("#new_comm").html("Причина:");
                }
            });

            $("#comment").autosize();
        }, 'json');

        function submit () {
            var send = {
                op:'report_remind_edit',
                id:id,
                action:parseInt($("#action").val()),
                day:curDay,
                status:1,
                history:$("#comment").val(),
                from_zayav:G.zayavInfo ? G.zayavInfo.id : 0
            };
            switch(send.action) {
                case 1: send.day = $("#data").val(); break;
                case 2: send.status = 2; break; // выполнено
                case 3: send.status = 0;        // отменено
            }

            var msg;
            if(!send.action) msg = "Укажите новое действие.";
            else if((send.action == 1 || send.action == 3) && !send.history) msg = "Не указана причина.";
            else if(send.action == 1 && send.day == curDay) msg = "Выберите новую дату.";
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("Задание отредактировано.");
                        $('#remind_spisok').html(res.html);
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-48,
                    left:115
                });
        }
    })
    .on('click', '#remind_private_check', reportRemindLoad);

$(document)
    .on('click', '#report_prihod_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = {
                op:'report_prihod_next',
                day_begin:$('#report_prihod_day_begin').val(),
                day_end:$('#report_prihod_day_end').val(),
                del_show:$('#prihodShowDel').val() == 1 ? 1 : 0,
                page:$(this).attr('val')
            };
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                next.remove();
                $('#report_prihod .tabSpisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#report_prihod .summa_add', function() {
        var html = '<TABLE cellpadding="0" cellspacing="8" id="report_prihod_add">' +
            '<TR><TD class=tdAbout>Содержание:<TD><INPUT type="text" id="about" maxlength="100">' +
            '<TR><TD class=tdAbout>Сумма:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> руб.' +
            '<TR><TD class=tdAbout>Деньги поступили в кассу?:<TD><INPUT type="hidden" id="kassa" value="-1">' +
            '</TABLE>';
        var dialog = vkDialog({
                width:380,
                head:"Внесение поступления средств",
                content:html,
                submit:submit
            }),
            kassa = $('#report_prihod_add #kassa'),
            sum = $("#report_prihod_add #sum"),
            about = $("#report_prihod_add #about");

        kassa.vkRadio({
            display:'inline-block',
            right:15,
            spisok:[{uid:1, title:'да'},{uid:0, title:'нет'}]
        });
        about.focus();

        function submit() {
            var send = {
                op:'report_prihod_add',
                about:about.val(),
                sum:sum.val(),
                kassa:kassa.val()
            };
            var msg;
            if(!send.about) { msg = "Не указано содержание."; about.focus(); }
            else if(!REGEXP_NUMERIC.test(send.sum)) { msg = "Некорректно указана сумма."; sum.focus(); }
            else if(send.kassa == -1) msg = "Укажите, деньги поступили в кассу?";
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("Новое поступление внесено.");
                        reportPrihodLoad();
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-53,
                    left:103,
                    correct:0
                });
        }
    })
    .on('click', '#report_prihod .img_del', function() {
        var send = {
            op:'report_prihod_del',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent(),
            trSave = tr.html();
        tr.html('<td colspan="4" class="deleting">Удаление... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                vkMsgOk("Удаление произведено.");
                if($('#prihodShowDel').val() == 1)
                    tr.addClass('deleted')
                      .html(trSave)
                      .find('.img_del').attr('class', 'img_rest').attr('title', 'Восстановить платёж');
                else
                    tr.remove();
            }
        }, 'json');
    })
    .on('click', '#report_prihod .img_rest', function() {
        var send = {
            op:'report_prihod_rest',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent(),
            trSave = tr.html();
        tr.html('<td colspan="4" class="deleting">Восстановление... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                vkMsgOk("Восстановление произведено.");
                tr.removeClass('deleted')
                  .html(trSave)
                  .find('.img_rest').attr('class', 'img_del').attr('title', 'Удалить платёж');
            }
        }, 'json');
    })
    .on('click', '#prihodShowDel_check', reportPrihodLoad);

$(document)
    .on('click', '#report_rashod_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = {
                op:'report_rashod_next',
                page:$(this).attr('val')
            };
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                next.remove();
                $('#report_rashod .tabSpisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#report_rashod #add', function() {
        var html = '<TABLE cellpadding="0" cellspacing="8" id="report_rashod_add">' +
                '<TR><TD class=tdAbout>Категория:<TD><INPUT type="hidden" id="category" value="0">' +
                '<TR><TD class=tdAbout>Описание:<TD><INPUT type="text" id="about" maxlength="100">' +
                '<TR><TD class=tdAbout>Сотрудник:<TD><INPUT type="hidden" id="worker" value="0">' +
                '<TR><TD class=tdAbout>Сумма:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> руб.' +
                '<TR><TD class=tdAbout>Деньги взяты из кассы?:<TD><INPUT type="hidden" id="kassa" value="-1">' +
            '</TABLE>',
            dialog = vkDialog({
                top:60,
                width:380,
                head:"Внесение расхода",
                content:html,
                submit:submit
            }),
            category = $("#report_rashod_add #category"),
            worker = $("#report_rashod_add #worker"),
            kassa = $('#report_rashod_add #kassa'),
            sum = $("#report_rashod_add #sum"),
            about = $("#report_rashod_add #about");

        category.vkSel({
            width:180,
            title0:'Не указана',
            spisok:rashodCaregory,
            funcAdd:rashodCategoryAdd
        });

        worker.vkSel({
            title0:'Не выбран',
            spisok:rashodViewers
        });

        kassa.vkRadio({
            display:'inline-block',
            right:15,
            spisok:[{uid:1, title:'да'},{uid:0, title:'нет'}]
        });
        about.focus();

        function submit() {
            var send = {
                op:'report_rashod_add',
                category:category.val(),
                about:about.val(),
                worker:worker.val(),
                sum:sum.val(),
                kassa:kassa.val()
            };
            var msg;
            if(!send.about && send.category == 0) { msg = "Выберите категорию или укажите описание."; about.focus(); }
            else if(!REGEXP_NUMERIC.test(send.sum)) { msg = "Некорректно указана сумма."; sum.focus(); }
            else if(send.kassa == -1) msg = "Укажите, деньги взяты из кассы или нет.";
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("Новый расход внесён.");
                        reportRashodLoad();
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-53,
                    left:103,
                    correct:0
                });
        }
    })
    .on('click', '#report_rashod .img_del', function() {
        var send = {
            op:'report_rashod_del',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent();
        tr.html('<td colspan="4" class="deleting">Удаление... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                vkMsgOk("Удаление произведено.");
                tr.remove();
            }
        }, 'json');
    })
    .on('click', '#report_rashod .img_edit', function() {
        var dialog = vkDialog({
                top:60,
                width:380,
                head:"Редактирование расхода",
                content:'<center><img src="/img/upload.gif"></center>',
                butSubmit:'Сохранить',
                submit:submit
            }),
            id = $(this).attr('val'),
            category,
            worker,
            kassa,
            sum,
            about,
            send = {
                op:'report_rashod_get',
                id:id
            };
        $.post(AJAX_MAIN, send, function(res) {
            var html = '<TABLE cellpadding="0" cellspacing="8" id="report_rashod_add">' +
                '<TR><TD class=tdAbout>Категория:<TD><INPUT type="hidden" id="category" value="' + res.category + '">' +
                '<TR><TD class=tdAbout>Описание:<TD><INPUT type="text" id="about" maxlength="150" value="' + res.about + '">' +
                '<TR><TD class=tdAbout>Сотрудник:<TD><INPUT type="hidden" id="worker" value="' + res.worker_id + '">' +
                '<TR><TD class=tdAbout>Сумма:<TD><INPUT type="text" id="sum" class="money" maxlength="8" value="' + res.sum + '"> руб.' +
                '<TR><TD class=tdAbout>Деньги взяты из кассы?:<TD><INPUT type="hidden" id="kassa" value="' + res.kassa + '">' +
                '</TABLE>';
            dialog.content.html(html);
            category = $("#report_rashod_add #category");
            worker = $("#report_rashod_add #worker");
            kassa = $('#report_rashod_add #kassa');
            sum = $("#report_rashod_add #sum");
            about = $("#report_rashod_add #about");

            category.vkSel({
                width:180,
                title0:'Не указана',
                spisok:rashodCaregory,
                funcAdd:rashodCategoryAdd
            });

            worker.vkSel({
                title0:'Не выбран',
                spisok:rashodViewers
            });

            kassa.vkRadio({
                display:'inline-block',
                right:15,
                spisok:[{uid:1, title:'да'},{uid:0, title:'нет'}]
            });
            about.focus();
        }, 'json');

        function submit() {
            var send = {
                id:id,
                op:'report_rashod_edit',
                category:category.val(),
                about:about.val(),
                worker:worker.val(),
                sum:sum.val(),
                kassa:kassa.val()
            };
            var msg;
            if(!send.about && send.category == 0) { msg = "Выберите категорию или укажите описание."; about.focus(); }
            else if(!REGEXP_NUMERIC.test(send.sum)) { msg = "Некорректно указана сумма."; sum.focus(); }
            else if(send.kassa == -1) msg = "Укажите, деньги взяты из кассы или нет.";
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("Расход изменён.");
                        reportRashodLoad();
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-53,
                    left:103,
                    correct:0
                });
        }
    });

$(document)
    .on('click', '#report_kassa #set_go', function() {
        if($(this).hasClass('busy'))
            return;
        var t = $(this),
            sum = $('#set_summa'),
            send = {
                op:'report_kassa_set',
                sum:sum.val()
            };
        if(!REGEXP_NUMERIC.test(send.sum)) {
            sum.vkHint({
                msg:'<SPAN class=red>Некорректно введена сумма.</SPAN>',
                remove:1,
                indent:40,
                show:1,
                top:-70,
                left:16,
                correct:0
            });
            sum.focus();
        } else {
            t.addClass('busy');
            $.post(AJAX_MAIN, send, function (res) {
                if(res.success) {
                    location.reload();
                } else
                    t.removeClass('busy');
            }, 'json');
        }
    })
    .on('click', '#report_kassa_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = {
                op:'report_kassa_next',
                page:$(this).attr('val')
            };
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                next.remove();
                $('#report_kassa .tabSpisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#report_kassa .actions a:first', function() {
        var kassa = $('#kassa_summa'),
            kassa_sum = parseInt(kassa.html()),
            html = '<TABLE cellspacing="8">' +
                '<TR><TD class="label r">Сумма:<TD><INPUT type="text" class="money" id="kassa_down_sum" maxlength="8" />' +
                '<TR><TD class="label r">Комментарий:<TD><INPUT type="text" id="kassa_down_txt" />' +
                '</TABLE>',
            dialog = vkDialog({
                head:"Внесение денег в кассу",
                content:html,
                submit:submit
            }),
            sum = $("#kassa_down_sum"),
            txt = $("#kassa_down_txt");

        sum.focus();

        function submit() {
            var send = {
                op:'report_kassa_action',
                txt:txt.val(),
                sum:sum.val(),
                down:0
            };
            var msg;
            if(!REGEXP_NUMERIC.test(send.sum)) { msg = "Некорректно указана сумма."; sum.focus(); }
            else if(!send.txt) { msg = "Не указан комментарий."; txt.focus(); }
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("Новая запись внесёна.");
                        kassa.html(kassa_sum += parseInt(send.sum));
                        reportKassaLoad();
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-47,
                    left:92,
                    correct:0
                });
        }
    })
    .on('click', '#report_kassa .actions a:last', function() {
        var kassa = $('#kassa_summa'),
            kassa_sum = kassa.html(),
            html = '<TABLE cellspacing="8">' +
                '<TR><TD class="label r">Сумма:' +
                    '<TD><INPUT type="text" class="money" id="kassa_down_sum" maxlength="8" /> max: ' + kassa_sum +
                '<TR><TD class="label r">Комментарий:<TD><INPUT type="text" id="kassa_down_txt" />' +
                '</TABLE>',
            dialog = vkDialog({
                head:"Взятие денег из кассы",
                content:html,
                submit:submit
            }),
            sum = $("#kassa_down_sum"),
            txt = $("#kassa_down_txt");

        sum.focus();

        function submit() {
            var send = {
                op:'report_kassa_action',
                txt:txt.val(),
                sum:sum.val(),
                down:1
            };
            var msg;
            if(!REGEXP_NUMERIC.test(send.sum)) { msg = "Некорректно указана сумма."; sum.focus(); }
            else if(send.sum > kassa_sum) { msg = "Введённая сумма превышает сумму в кассе."; sum.focus(); }
            else if(!send.txt) { msg = "Не указан комментарий."; txt.focus(); }
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("Новая запись внесёна.");
                        kassa.html(kassa_sum -= send.sum);
                        reportKassaLoad();
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-47,
                    left:92,
                    correct:0
                });
        }
    })
    .on('click', '#report_kassa .img_del', function() {
        var send = {
            op:'report_kassa_del',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent(),
            trSave = tr.html();
        tr.html('<td colspan="4" class="deleting">Удаление... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function(res) {
            if(res.success) {
                vkMsgOk("Удаление произведено.");
                if($('#kassaShowDel').val() == 1)
                    tr.addClass('deleted')
                        .html(trSave)
                        .find('.img_del').attr('class', 'img_rest').attr('title', 'Восстановить');
                else
                    tr.remove();
                $('#kassa_summa').html(res.sum);
            }
        }, 'json');
    })
    .on('click', '#report_kassa .img_rest', function() {
        var send = {
            op:'report_kassa_rest',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent(),
            trSave = tr.html();
        tr.html('<td colspan="4" class="deleting">Восстановление... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function(res) {
            if(res.success) {
                vkMsgOk("Восстановление произведено.");
                tr.removeClass('deleted')
                    .html(trSave)
                    .find('.img_rest').attr('class', 'img_del').attr('title', 'Удалить платёж');
                $('#kassa_summa').html(res.sum);
            }
        }, 'json');
    })
    .on('click', '#kassaShowDel_check', reportKassaLoad);

$(document).ready(function() {
    frameHidden.onresize = frameBodyHeightSet;

    if($('#report_history').length > 0) {
        $('#report_history_worker').vkSel({
            width:140,
            title0:'Не указан',
            spisok:workers,
            func:reportHistoryLoad
        });
        $('#report_history_action').vkSel({
            width:140,
            title0:'Не выбрано',
            spisok:[
                {uid:1, title:'Клиенты'},
                {uid:2, title:'Заявки'},
                {uid:3, title:'Запчасти'},
                {uid:4, title:'Платежи'}
            ],
            func:reportHistoryLoad
        });
    }
    if($('#report_remind').length > 0) {
        $('#remind_status').vkRadio({
            top:6,
            light:1,
            spisok:[
                {uid:1, title:'Активные'},
                {uid:2, title:'Выполнены'},
                {uid:0, title:'Отменены'}
            ],
            func:reportRemindLoad
        });
    }
    if($('#report_prihod').length > 0) {
        $('#report_prihod_day_begin').vkCalendar({lost:1, place:'left', func:reportPrihodLoad});
        $('#report_prihod_day_end').vkCalendar({lost:1, place:'left', func:reportPrihodLoad});
    }
    if($('#report_rashod').length > 0) {
        $('#rashod_category').vkSel({
            width:140,
            title0:'Любая категория',
            spisok:rashodCaregory,
            func:reportRashodLoad
        });
        $('#rashod_worker').vkSel({
            width:140,
            title0:'Все сотрудники',
            spisok:rashodViewers,
            func:reportRashodLoad
        });
        $('#rashod_year').years({func:reportRashodLoad});
        reportRashodMonthPrint();
    }

    if($('#zayav').length > 0) {
        $("#find")
            .topSearch({
                width:134,
                focus:1,
                txt:'Быстрый поиск...',
                enter:1,
                func:zayavSpisokLoad
            })
            .topSearchSet(G.zayav_find)
            .vkHint({
                msg:'Поиск производится по<br />совпадению в названии<br />модели, imei и серийном<br />номере.',
                ugol:'right',
                top:-9,
                left:-178,
                delayShow:800,
                correct:0
            });
        $("#sort").myRadio({
            spisok:[
                {uid:1,title:'По дате добавления'},
                {uid:2,title:'По обновлению статуса'}
            ],
            bottom:5,
            func:zayavSpisokLoad
        });
        G.status_spisok.unshift({uid:0, title:'Любой статус'});
        $("#status").infoLink({
            spisok:G.status_spisok,
            func:zayavSpisokLoad
        }).infoLinkSet(G.zayav_status);
        $("#dev").device({
            width:155,
            type_no:1,
            device_id:G.zayav_device,
            vendor_id:G.zayav_vendor,
            model_id:G.zayav_model,
            device_ids:G.device_ids,
            vendor_ids:G.vendor_ids,
            model_ids:G.model_ids,
            func:zayavSpisokLoad
        });
        // Нахождение устройства
        for(var n = 0; n < G.place_other.length; n++) {
            var sp = G.place_other[n];
            G.device_place_spisok.push({uid:encodeURI(sp), title:sp});
        }
        G.device_place_spisok.push({uid:-1, title:'не известно', content:'<B>не известно</B>'});
        G.vkSel_device_place = $("#device_place").vkSel({
            width:155,
            title0:'Любое местонахождение',
            spisok:G.device_place_spisok,
            func:zayavSpisokLoad
        }).o;
        // Состояние устройства
        G.device_status_spisok.splice(0, 1);
        G.device_status_spisok.push({uid:-1, title:'не известно', content:'<B>не известно</B>'});
        G.vkSel_device_status = $("#devstatus").vkSel({
            width:155,
            title0:'Любое состояние',
            spisok:G.device_status_spisok,
            func:zayavSpisokLoad
        }).o;
        zayavFilterValuesGet();
    }
    if($('#zayavAdd').length > 0) {
        $("#client_id").clientSel({add:1});
        $("#category").vkSel({width:150, spisok:G.category_spisok});
        // создание нового списка устройств, которые выбраны для этой мастерской
        G.device_spisok = [];
        for(var n = 0; n < G.ws.devs.length; n++) {
            var uid = G.ws.devs[n];
            G.device_spisok.push({uid:uid, title:G.device_ass[uid]});
        }
        $('#dev').device({
            width:190,
            add:1,
            func:modelImageGet
        });
        G.device_place_spisok.push({uid:0, title:'другое: <DIV id="place_other_div"><INPUT type="text" id="place_other" maxlength="20"></DIV>'});
        $('#place').vkRadio({
            spisok:G.device_place_spisok,
            top:4,
            bottom:6,
            func:function(val) {
                $('#place_other_div').css('display', val == 0 ? 'inline-block' : 'none');
                if(val == 0) $('#place_other').val('').focus();
            }
        });
        $('#color_id').vkSel({width:170, title0:'Цвет не указан', spisok:G.color_spisok});
        $(document).on('click', '#fault', function() {
            var i = $(this).find('INPUT');
            var arr = [];
            for(var n = 0; n < i.length; n++) {
                if(i.eq(n).val() == 1) {
                    var uid = i.eq(n).attr('id').split('_')[1];
                    arr.push(G.fault_ass[uid]);
                }
            }
            $("#comm").val(arr.join(', '));
        });
        $('#comm').autosize();
        $('#reminder_check').click(function(id) {
            $('#reminder_tab').toggle();
            $('#reminder_txt').focus();
        });
        $('#reminder_day').vkCalendar();
        $('.vkCancel').click(function() {
            location.href = URL + '&p=' + $(this).attr('val');
        });
        $(".vkButton").click(function () {
            var send = {
                op:'zayav_add',
                client:$("#client_id").val(),
                category:$("#category").val(),
                device:$("#dev_device").val(),
                vendor:$("#dev_vendor").val(),
                model:$("#dev_model").val(),
                place:$("#place").val(),
                place_other:$("#place_other").val(),
                imei:$("#imei").val(),
                serial:$("#serial").val(),
                color:$("#color_id").val(),
                comm:$("#comm").val(),
                reminder:$("#reminder").val()
            };
            send.reminder_txt = send.reminder == 1 ? $("#reminder_txt").val() : '';
            send.reminder_day = send.reminder == 1 ? $("#reminder_day").val() : '';

            var msg = '';
            if(send.client == 0) msg = 'Не выбран клиент';
            else if(send.device == 0) msg = 'Не выбрано устройство';
            else if(send.place == '' || send.place == 0 && !send.place_other) msg = 'Не указано местонахождение устройства';
            else if(send.reminder == 1 && !send.reminder_txt) msg = 'Не указан текст напоминания';
            else {
                if(send.place > 0) send.place_other = '';
                $(this).addClass('busy');
                $.post(AJAX_MAIN, send, function (res) {
                    location.href = URL + '&my_page=remZayavkiInfo&id=' + res.id; //todo
                }, 'json');
            }

            if(msg)
                $(this).vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    top:-48,
                    left:201,
                    indent:30,
                    remove:1,
                    show:1,
                    correct:0
                });
        });
    }
    if($('#zayavInfo').length > 0) {
        $("#foto_upload").fotoUpload({
            owner:'zayav' + G.zayavInfo.id,
            max_x:200,
            max_y:320,
            func:function(obj) { G.zayav.foto.push(obj); G.zayav.update(); }
        });
    }

    frameBodyHeightSet();
});