var REGEXP_NUMERIC = /^\d+$/,
    REGEXP_CENA = /^[\d]+(.[\d]{1,2})?$/,
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
            case 'zp':
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
                model_id:$('#dev_model').val()
            },
            dev = $('#device_image');
        dev.html('');
        if(send.model_id > 0) {
            dev.addClass('busy');
            $.post(AJAX_MAIN, send, function(res) {
                if(res.success)
                    dev.html(res.img)
                       .find('img').on('load', function() {
                           $(this).show().parent().removeClass('busy');
                       });
            }, 'json');
        }
    },

    clientAdd = function(callback) {
        var html = '<table style="border-spacing:10px">' +
                '<tr><td class="label">Имя:<TD><INPUT TYPE="text" id="fio" style="width:220px;">' +
                '<tr><td class="label">Телефон:<TD><INPUT TYPE="text" id="telefon" style=width:220px;>' +
            '</TABLE>',
            dialog = vkDialog({
                width:340,
                head:'Добавление нoвого клиента',
                content:html,
                submit:submit
            });
        $('#fio').focus();
        function submit() {
            var send = {
                op:'client_add',
                fio:$('#fio').val(),
                telefon:$('#telefon').val()
            };
            if(!send.fio) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">Не указано имя клиента.</SPAN>',
                    top:-47,
                    left:81,
                    indent:40,
                    show:1,
                    remove:1,
                    correct:0
                });
                $('#fio').focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.close();
                    vkMsgOk('Новый клиент внесён.');
                    if(res.success)
                        if(typeof callback == 'function')
                            callback(res);
                        else
                            document.location.href = URL + '&p=client&d=info&id=' + res.uid;
                }, 'json');
            }
        }
    },
    clientFilter = function() {
        var v = {
            fast:$.trim($('#find input').val()),
            dolg:$('#dolg').val(),
            active:$('#active').val()
        };
        $('.filter')[v.fast ? 'hide' : 'show']();
        return v;
    },
    clientSpisokLoad = function() {
        var send = clientFilter(),
            result = $('.result');
        send.op = 'client_spisok_load';
        if(result.hasClass('busy'))
            return;
        result.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            result.removeClass('busy');
            if(res.success) {
                result.html(res.all);
                $('.left').html(res.spisok);
            }
        }, 'json');
    },
    clientZayavFilter = function() {
        return {
            client:G.clientInfo.id,
            status:$('#zayav_status .sel').attr('val'),
            device:$('#dev_device').val(),
            vendor:$('#dev_vendor').val(),
            model:$('#dev_model').val()
        };
    },
    clientZayavSpisokLoad = function() {
        var send = clientZayavFilter();
        send.op = 'client_zayav_load';
        $('#dopLinks').addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            $('#dopLinks').removeClass('busy');
            $('#zayav_result').html(res.all);
            $('#zayav_spisok').html(res.html);
        }, 'json');
    },

    zayavFilter = function () {
        var v = {
                find:$.trim($('#find input').val()),
                sort:$('#sort').val(),
                desc:$('#desc').val(),
                status:$('#status .sel').attr('val'),
                zpzakaz:$('#zpzakaz').val(),
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
            if(v.zpzakaz > 0) loc += '.zpzakaz=' + v.status;
            if(v.device > 0) loc += '.device=' + v.device;
            if(v.vendor > 0) loc += '.vendor=' + v.vendor;
            if(v.model > 0) loc += '.model=' + v.model;
            if(v.place != 0) loc += '.place=' + v.place;
            if(v.devstatus > 0) loc += '.devstatus=' + v.devstatus;
        }
        VK.callMethod('setLocation', hashLoc + loc);

        setCookie('zayav_find', escape(v.find));
        setCookie('zayav_sort', v.sort);
        setCookie('zayav_desc', v.desc);
        setCookie('zayav_status', v.status);
        setCookie('zayav_zpzakaz', v.zpzakaz);
        setCookie('zayav_device', v.device);
        setCookie('zayav_vendor', v.vendor);
        setCookie('zayav_model', v.model);
        setCookie('zayav_place', encodeURI(v.place));
        setCookie('zayav_devstatus', v.devstatus);

        return v;
    },
    zayavSpisokLoad = function() {
        var send = zayavFilter();
        $('.condLost')[(send.find ? 'add' : 'remove') + 'Class']('hide');
        send.op = 'zayav_spisok_load';

        $('#mainLinks').addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            $('#zayav .result').html(res.all);
            $('#zayav #spisok').html(res.html);
            $('#mainLinks').removeClass('busy');
        }, 'json');
    },
    zayavImgUpdate = function() {
        var send = {
            op:'zayav_img_update',
            zayav_id:G.zayavInfo.id
        };
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                $('#foto').html(res.html);
            }
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
                var del = res.acc == 0 && res.opl == 0;
                $('.delete')[(del ? 'remove' : 'add') + 'Class']('dn');
            }
        }, 'json');
    },

    zpFilter = function() {
        var v = {
                find:$.trim($('#find input').val()),
                menu:$('#menu .sel').attr('val'),
                name:$('#zp_name').val(),
                device:$('#dev_device').val(),
                vendor:$('#dev_vendor').val(),
                model:$('#dev_model').val(),
                bu:$('#bu').val()
            },
            loc = '';
        if(v.find) loc += '.find=' + escape(v.find);
        if(v.menu > 0) loc += '.menu=' + v.menu;
        if(v.name > 0) loc += '.name=' + v.name;
        if(v.device > 0) loc += '.device=' + v.device;
        if(v.vendor > 0) loc += '.vendor=' + v.vendor;
        if(v.model > 0) loc += '.model=' + v.model;
        if(v.bu > 0) loc += '.bu=' + v.bu;
        VK.callMethod('setLocation', hashLoc + loc);
        return v;
    },
    zpSpisokLoad = function() {
        var send = zpFilter();
        send.op = 'zp_spisok_load';
        $('#mainLinks').addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            $('#mainLinks').removeClass('busy');
            $('#zp .result').html(res.all);
            $('#zp .left').html(res.html);
        }, 'json');
    },
    zpImgUpdate = function() {
        var send = {
            op:'zp_img_update',
            zp_id:G.zpInfo.compat_id
        };
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                $('#foto').html(res.html);
            }
        }, 'json');
    },
    zpAvaiAdd = function(obj) {
        var html = '<table class="avaiAddTab">' +
                        '<tr><td class="left">' +
                            '<div class="name">' + obj.name + '</div>' +
                            '<div>' + obj.for + '</div>' +
                            '<div class="avai">Текущее наличие: <b>' + obj.count + '</b> шт.</div>' +
                            '<table class="inp">' +
                                '<tr><td class="label">Количество:<td><input type="text" id="count" maxlength="5">' +
                                '<tr><td class="label">Цена за ед.:<td><input type="text" id="cena" maxlength="10"><span>не обязательно</span>' +
                            '</table>' +
                            '<td valign="top">' + obj.img +
                    '</table>',
            dialog = vkDialog({
                head:'Внесение наличия запчасти',
                content:html,
                submit:submit
            });
        $('#count').focus();
        function submit() {
            var msg,
                send = {
                    op:'zp_avai_add',
                    zp_id:obj.zp_id,
                    count:$('#count').val(),
                    cena:$('#cena').val()
                };
            if(!send.cena)
                send.cena = 0;
            if (!REGEXP_NUMERIC.test(send.count) || send.count == 0) {
                msg = 'Некорректно указано количество.';
                $('#count').focus();
            } else if(send.cena != 0 && !REGEXP_CENA.test(send.cena)) {
                msg = 'Некорректно указана цена.';
                $('#cena').focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        obj.callback(res);
                        dialog.close();
                        vkMsgOk('Внесение наличия запчасти произведено.');
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
                    left:92
                });
        }
    },
    zpAvaiNo = function(c) {
        if(c == 0) {
            vkDialog({
                top:100,
                width:300,
                head:'Нет наличия',
                content:'<center>Запчасти нет в наличии.</center>',
                butSubmit:'',
                butCancel:'Закрыть'
            });
            return true;
        }
        return false;
    },
    zpAvaiUpdate = function() {
        var send = {
            op:'zp_avai_update',
            zp_id: G.zpInfo.id
        };
        $.post(AJAX_MAIN, send, function(res) {
            if(res.success) {
                G.zpInfo.count = res.count;
                $('.move').html(res.move);
                $('.avai')
                    [(res.count == 0 ? 'add' : 'remove') + 'Class']('no')
                    .html(res.count == 0 ? 'Нет в наличии.' : 'В наличии ' + res.count + ' шт.');
            }
        }, 'json');
    },

    reportHistoryLoad = function() {
        var send = {
            op:'report_history_load',
            worker:$('#report_history_worker').val(),
            action:$('#report_history_action').val()
        };
        $('#mainLinks').addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_history').html(res.html);
            $('#mainLinks').removeClass('busy');
        }, 'json');
    },
    reportRemindLoad = function() {
        var send = {
            op:'report_remind_load',
            status:$('#remind_status').val(),
            private:$('#remind_private').val()
        };
        $('#mainLinks').addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            $('#remind_spisok').html(res.html);
            $('#mainLinks').removeClass('busy');
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
        $('#mainLinks').addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            monthSum = res.summ.split(',');
            reportRashodMonthPrint();
            $('#report_rashod #spisok').html(res.html);
            $('#mainLinks').removeClass('busy');
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
            '<tr><td class="label">Наименование:<TD><INPUT type="text" id="rashod_category_name">' +
            '</TABLE>',
            dialog = vkDialog({
                width:320,
                head:'Новая категория для расходов',
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
                        vkMsgOk('Новая категория внесена.');
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
        $('#mainLinks').addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_kassa #spisok').html(res.html);
            $('#mainLinks').removeClass('busy');
        }, 'json');
    };

$(document).ajaxError(function(event, request, settings) {
    if(!request.responseText)
        return;
    alert('Ошибка:\n\n' + request.responseText);
    //var txt = request.responseText;
    //throw new Error('<br />AJAX:<br /><br />' + txt + '<br />');
});

$(document)
    .on('click', '#script_style', function() {
        $.post(AJAX_MAIN, {'op':'script_style'}, function(res) {
            if(res.success)
                document.location.reload();
        }, 'json');
    })
    .on('click', '#cache_clear', function() {
        $.post(AJAX_MAIN, {'op':'cache_clear'}, function(res) {
            if(res.success)
                vkMsgOk('Кэш очищен.');
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
    })
    .on('keyup', '#zayavNomer', function() {
        var t = $(this);
        if(t.hasClass('busy'))
            return;
        t.next('.zayavNomerTab').remove().end()
         .after('<img src="/img/upload.gif">')
         .addClass('busy');
        var send = {
            op:'zayav_nomer_info',
            nomer:t.val()
        };
        $.post(AJAX_MAIN, send, function(res) {
            t.removeClass('busy')
             .next('img').remove();
            if(res.success)
                t.after(res.html);
        }, 'json');
    });

$.fn.clientSel = function(obj) {
    var t = $(this);
    obj = $.extend({
        width:240,
        add:null,
        client_id:t.val() || 0
    }, obj);

    if(obj.add)
        obj.add = function() {
            clientAdd(function(res) {
                sel.add(res).val(res.uid)
            });
        };

    var sel = t.vkSel({
            width:obj.width,
            title0:'Начните вводить данные клиента...',
            spisok:[],
            ro:0,
            nofind:'Клиентов не найдено',
            funcAdd:obj.add,
            funcKeyup:clientsGet
        }).o;
    sel.process();
    clientsGet();

    function clientsGet(val) {
        var send = {
            op:'client_sel',
            val:val ? val : '',
            client_id:obj.client_id
        };
        $.post(AJAX_MAIN, send, function(res) {
            if(res.success) {
                sel.spisok(res.spisok);
                if(obj.client_id > 0) {
                    sel.val(obj.client_id)
                    obj.client_id = 0;
                }
            }
        }, 'json');
    }
    return t;
};

$(document)
    .on('click', '#client #buttonCreate', clientAdd)
    .on('click', '#client #dolg_check', clientSpisokLoad)
    .on('click', '#client #active_check', clientSpisokLoad)
    .on('click', '#client .ajaxNext', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = clientFilter();
        send.op = 'client_next';
        send.page = next.attr('val');
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                next.remove();
                $('#client .left').append(res.spisok);
            } else
                next.removeClass('busy');
        }, 'json');
    });

$(document)
    .on('click', '#clientInfo .cedit', function() {
        var html = '<TABLE class="client_edit">' +
            '<tr><td class="label">Имя:<TD><INPUT TYPE="text" id="fio" value="' + $('.fio').html() + '">' +
            '<tr><td class="label">Телефон:<TD><INPUT TYPE="text" id="telefon" value="' + $('.telefon').html() + '">' +
            '<tr><td class="label">Объединить:<TD><INPUT TYPE="hidden" id="join">' +
            '<TR class=tr_join><TD class="label">с клиентом:<TD><INPUT TYPE="hidden" id="client2">' +
            '</TABLE>';
        var dialog = vkDialog({
            head:'Редактирование данных клиента',
            top:60,
            width:400,
            content:html,
            butSubmit:'Сохранить',
            submit:submit
        });
        $('#fio,#telefon').keyEnter(submit);
        $('#join').vkCheck();
        $('#join_check')
            .click(function() {
                $('.tr_join').toggle();
            })
            .vkHint({
                msg:'<B>Объединение клиентов.</B><br />' +
                    'Необходимо, если один клиент был внесён в базу дважды.<br /><br />' +
                    'Текущий клиент будет получателем.<br />Выберите второго клиента.<br />' +
                    'Все заявки, начисления и платежи станут общими после<br />объединения.<br /><br />' +
                    'Внимание, операция необратима!',
                width:330,
                top:-162,
                left:-79,
                indent:80
            });
        $('#client2').clientSel();
        function submit() {
            var msg,
                send = {
                    op:'client_edit',
                    client_id:G.clientInfo.id,
                    fio:$.trim($('#fio').val()),
                    telefon:$.trim($('#telefon').val()),
                    join:$('#join').val(),
                    client2:$('#client2').val()
                };
            if(send.join == 0)
                send.client2 = 0;
            if(!send.fio) {
                msg = 'Не указано имя клиента.';
                $("#fio").focus();
            } else if(send.join == 1 && send.client2 == 0)
                msg = 'Укажите второго клиента.';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        $('.fio').html(send.fio);
                        $('.telefon').html(send.telefon);
                        G.clientInfo.fio = send.fio;
                        if(send.client2 > 0)
                            document.location.reload();
                        dialog.close();
                        vkMsgOk('Данные клиента изменены.');
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>' + msg + '</SPAN>',
                    top:-47,
                    left:100,
                    indent:50,
                    show:1,
                    remove:1
                });
        }
    })
    .on('click', '#clientInfo .ajaxNext', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = clientZayavFilter();
        send.op = 'client_zayav_next';
        send.page = $(this).attr('val');
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success)
                next.after(res.html).remove();
            else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#clientInfo .remind_add', function() {
        var html = '<TABLE class="remind_add_tab">' +
            '<tr><td class="label">Клиент:<TD><b>' + G.clientInfo.fio + '</b>' +
            '<tr><td class="label top">Описание задания:<TD><TEXTAREA id="txt"></TEXTAREA>' +
            '<tr><td class="label">Крайний день выполнения:<TD><INPUT type="hidden" id="data">' +
            '<tr><td class="label">Личное:<TD><INPUT type="hidden" id="private">' +
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
            msg:'Задание сможете<br />видеть только Вы.',
            top:-71,
            left:-11,
            indent:'left',
            delayShow:1000
        });

        function submit() {
            var send = {
                op:'report_remind_add',
                from_client:1,
                client_id:G.clientInfo.id,
                zayav_id:0,
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
                    dialog.abort();
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Новое задание успешно добавлено.');
                        $('#remind_spisok').html(res.html);
                    }
                }, 'json');
            }
        }//submit()
    });

$(document)
    .on('click', '#zayav .ajaxNext', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = zayavFilter();
        send.op = 'zayav_next';
        send.page = $(this).attr('val');
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success)
                next.after(res.html).remove();
            else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '.zayav_unit', function() {
        document.location.href = URL + '&p=zayav&d=info&id=' + $(this).attr('val');
    })
    .on('mouseenter', '.zayav_unit', function() {
        var t = $(this),
            msg = t.find('.msg').val();
        if(msg)
            t.vkHint({
                width:150,
                msg:msg,
                ugol:'left',
                top:10,
                left:t.width() + 43,
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
        $('#zpzakaz').myRadioSet(0);
        $('#dev').device({
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
    .on('click', '#zayavInfo .zedit', function() {
        var html = '<TABLE style="border-spacing:8px">' +
            '<tr><td class="label r">Клиент:        <TD><INPUT type="hidden" id="client_id" value=' + G.zayavInfo.client_id + '>' +
            '<tr><td class="label r top">Устройство:<TD><TABLE><TD id="dev"><TD id="device_image"></TABLE>' +
            '<tr><td class="label r">IMEI:          <TD><INPUT type=text id="imei" maxlength="20" value="' + G.zayavInfo.imei + '">' +
            '<tr><td class="label r">Серийный номер:<TD><INPUT type=text id="serial" maxlength="30" value="' + G.zayavInfo.serial + '">' +
            '<tr><td class="label r">Цвет:          <TD><INPUT type="hidden" id=color_id value=' + G.zayavInfo.color_id + '>' +
        '</TABLE>',
            dialog = vkDialog({
                width:410,
                top:30,
                head:'Заявка №' + G.zayavInfo.nomer + ' - Редактирование',
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
        $('#dev').device({
            width:190,
            device_id:G.zayavInfo.device,
            vendor_id:G.zayavInfo.vendor,
            model_id:G.zayavInfo.model,
            device_ids:G.device_ids,
            add:1,
            func:modelImageGet
        });
        modelImageGet();
        $('#color_id').vkSel({width:170, title0:'Цвет не указан', spisok:G.color_spisok});

        function submit() {
            var msg,
                send = {
                    op:'zayav_edit',
                    zayav_id:G.zayavInfo.id,
                    client_id:$('#client_id').val(),
                    device:$('#dev_device').val(),
                    vendor:$('#dev_vendor').val(),
                    model:$('#dev_model').val(),
                    imei: $.trim($('#imei').val()),
                    serial:$.trim($('#serial').val()),
                    color_id:$('#color_id').val()
                };
            if(send.deivce == 0) msg = 'Не выбрано устройство';
            else if(send.client_id == 0) msg = 'Не выбран клиент';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    dialog.close();
                    vkMsgOk('Данные изменены!');
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
    .on('click', '#zayavInfo .delete', function() {
        var dialog = vkDialog({
            top:110,
            width:250,
            head:'Удаление заявки',
            content:'<CENTER>Подтвердите удаление заявки.</CENTER>',
            butSubmit:'Удалить',
            submit:function() {
                var send = {
                    op:'zayav_delete',
                    zayav_id:G.zayavInfo.id
                };
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success)
                        location.href = URL + '&p=client&d=info&id=' + res.client_id;
                }, 'json');
            }
        });
    })
    .on('click', '#zayavInfo .remind_add', function() {
        var html = '<TABLE class="remind_add_tab">' +
            '<tr><td class="label">Заявка:<TD>№<b>' + G.zayavInfo.nomer + '</b>' +
            '<tr><td class="label top">Описание задания:<TD><TEXTAREA id="txt"></TEXTAREA>' +
            '<tr><td class="label">Крайний день выполнения:<TD><INPUT type="hidden" id="data">' +
            '<tr><td class="label">Личное:<TD><INPUT type="hidden" id="private">' +
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
            msg:'Задание сможете<br />видеть только Вы.',
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
        var html = '<TABLE class="zayav_accrual_add">' +
                '<tr><td class="label">Сумма: <TD><input type="text" id="sum" class="money" maxlength="5" /> руб.' +
                '<tr><td class="label">Примечание:<em>(не обязательно)</em><TD><input type="text" id="prim" maxlength="100" />' +
                '<tr><td class="label">Статус заявки: <TD><INPUT type="hidden" id="acc_status" value="2" />' +
                '<tr><td class="label">Состояние устройства:<TD><INPUT type="hidden" id="acc_dev_status" value="5" />' +
                '<tr><td class="label">Добавить напоминание:<TD><INPUT type="hidden" id="acc_remind" />' +
            '</TABLE>' +

            '<TABLE class="zayav_accrual_add remind">' +
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
        $('#reminder_day').vkCalendar();

        function submit() {
            var msg,
                send = {
                    op:'zayav_accrual_add',
                    zayav_id:G.zayavInfo.id,
                    sum:$('#sum').val(),
                    prim:$('#prim').val(),
                    status:$('#acc_status').val(),
                    dev_status:$('#acc_dev_status').val(),
                    remind:$('#acc_remind').val(),
                    remind_txt:$('#reminder_txt').val(),
                    remind_day:$('#reminder_day').val()
                };
            if(!REGEXP_NUMERIC.test(send.sum)) { msg = 'Некорректно указана сумма.'; $('#sum').focus(); }
            else if(send.remind == 1 && !send.remind_txt) { msg = 'Не указан текст напоминания'; $('#reminder_txt').focus(); }
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Начисление успешно произведено!');
                        $('._spisok._money').append(res.html);
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
        var html = '<TABLE class="zayav_oplata_add">' +
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
            msg:'Если это наличный платёж<br />и деньги остаются в мастерской,<br />укажите "да".',
            top:-83,
            left:-60,
            delayShow:1000
        });
        $('#dev_place').linkMenu({spisok:G.device_place_spisok});
        function submit() {
            var msg,
                send = {
                    op:'zayav_oplata_add',
                    zayav_id:G.zayavInfo.id,
                    sum:$('#sum').val(),
                    kassa:$('#kassa').val(),
                    prim:$.trim($('#prim').val()),
                    dev_place:$('#dev_place').val()
                };
            if(!REGEXP_NUMERIC.test(send.sum)) { msg = 'Некорректно указана сумма.'; $('#sum').focus(); }
            else if(send.kassa == -1) msg = 'Укажите, деньги поступили в кассу или нет.';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    dialog.abort();
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Платёж успешно внесён!');
                        $('._spisok._money').append(res.html);
                        zayavInfoMoneyUpdate();
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
        $.post(AJAX_MAIN, send, function(res) {
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
        var html = '<TABLE style="border-spacing:8px">' +
            '<TR><TD class="label r top">Статус заявки:<TD><input type="hidden" id="z_status" value="' + G.zayavInfo.z_status + '">' +
            '<TR><TD class="label r top">Местонахождение устройства:<TD><input type="hidden" id="dev_place" value="' + G.zayavInfo.dev_place + '">' +
            '<TR><TD class="label r top">Состояние устройства:<TD><input type="hidden" id="dev_status" value="' + G.zayavInfo.dev_status + '">' +
            '</TABLE>',
            dialog = vkDialog({
                width:400,
                top:30,
                head:'Изменение статуса заявки и состояния устройства',
                content:html,
                butSubmit:'Сохранить',
                submit:submit
            });
        $('#z_status').vkRadio({
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
            $('#place_other_div').css('display', 'inline');

        G.device_status_spisok.splice(0, 1);
        $('#dev_status').vkRadio({
            spisok:G.device_status_spisok,
            top:5,
            light:1
        });

        function submit() {
            var msg,
                send = {
                op:'zayav_status_place',
                zayav_id:G.zayavInfo.id,
                zayav_status:$('#z_status').val(),
                dev_status:$('#dev_status').val(),
                dev_place:$('#dev_place').val(),
                place_other:$('#place_other').val()
            };
            if(send.dev_place > 0)
                send.place_other = '';
            if(send.dev_place == 0 && send.place_other == '') {
                msg = 'Не указано местонахождение устройства';
                $('#place_other').focus();
            } else if(send.dev_status == 0)
                msg = 'Не указано состояние устройства';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Изменения сохранены.');
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
        location.href = URL + '&p=zp&menu=3';
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
                '<TABLE style="border-spacing:6px">' +
                    '<TR><TD class="label r">Наименование запчасти:<TD><input type="hidden" id="name_id">' +
                    '<TR><TD class="label r">Версия:<TD><input type="text" id="version" maxlength="30">' +
                    '<TR><TD class="label r">Цвет:<TD><input type="hidden" id="color_id">' +
                    '<TR><TD class="label r">Б/у:<TD><input type="hidden" id="bu">' +
                '</TABLE>' +
            '</div>',
            dialog = vkDialog({
                top:40,
                width:380,
                head:'Внесение новой запчасти',
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
        $('#bu').vkCheck();
        function submit() {
            var send = {
                op:'zayav_zp_add',
                zayav_id: G.zayavInfo.id,
                name_id:$('#name_id').val(),
                version:$('#version').val(),
                color_id:$('#color_id').val(),
                bu:$('#bu').val()
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
                        vkMsgOk('Внесение запчасти произведено.');
                        dialog.close();
                        $('#zpSpisok')
                            .append(res.html)
                            .find('._empty').remove();
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
            head:'Установка запчасти',
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
    .on('click', '#zp #bu_check', zpSpisokLoad)
    .on('click', '#zp .ajaxNext', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = zpFilter();
        send.op = 'zp_next';
        send.page = $(this).attr('val');
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success)
                next.after(res.spisok).remove();
            else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#zp .avai_add', function() {
        var unit = $(this);
        while(!unit.hasClass('unit'))
            unit = unit.parent();
        var obj = {
            zp_id:unit.attr('val'),
            name:unit.find('.name').html(),
            for:unit.find('.for').html(),
            count:$(this).hasClass('avai') ? $(this).find('b').html() : 0,
            img:unit.find('.img').html(),
            callback:function(res) {
                unit.find('.avai_add')
                    .removeClass('hid')
                    .addClass('avai')
                    .html('В наличии: <b>' + res.count + '</b>');
            }
        }
        zpAvaiAdd(obj);
        if($('.avaiAddTab img').attr('val'))
            $('.avaiAddTab img').addClass('fotoView');
    })
    .on('mouseenter', '.zpzakaz:not(.busy)', function() {
        window.zakaz_count = $(this).find('tt:first').next('b').html();
    })
    .on('mouseleave', '.zpzakaz:not(.busy)', function() {
        var t = $(this),
            count = t.find('tt:first').next('b').html(),
            unit = t;
        if(count != window.zakaz_count) {
            t.removeClass('hid')
             .addClass('busy')
             .find('.cnt').html('ано: <b></b>');
            while(!unit.hasClass('unit'))
                unit = unit.parent();
            var send = {
                op:'zp_zakaz_edit',
                zp_id:unit.attr('val'),
                count:count
            };
            $.post(AJAX_MAIN, send, function(res) {
                t.removeClass('busy');
                if(res.success) {
                    t.find('.cnt').html(count > 0 ? 'ано: <b>' + count + '</b>' : 'ать');
                    if(count == 0)
                        t.addClass('hid');
                }
            }, 'json');
        }
    })
    .on('click', '.zpzakaz tt', function() {
        var t = $(this),
            znak = t.html(),
            c = t[znak == '+' ? 'prev' : 'next'](),
            count = c.html();
        if(znak == '+')
            count++;
        else {
            count--;
            if(count < 0)
                count = 0;
        }
        c.html(count);
    })
    .on('click', '#zp .add', function() {
        var html = '<table class="zp_add_dialog">' +
            '<tr><td class="label">Наименование запчасти:<td><input type="hidden" id="name_id">' +
            '<tr><td class="label top">Устройство:<td id="add_dev">' +
            '<tr><td class="label">Версия:<td><input type="text" id="version">' +
            '<tr><td class="label">Б/у:<td><input type="hidden" id="add_bu">' +
            '<tr><td class="label">Цвет:<td><input type="hidden" id="color_id">' +
            '</table>',
            dialog = vkDialog({
                top:70,
                width:380,
                head:'Внесение новой запчасти в каталог',
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
        $('#add_bu').vkCheck();
        $('#add_dev').device({
            width:200
        });

        function submit() {
            var msg,
                send = {
                    op:'zp_add',
                    name_id:$('#name_id').val(),
                    device_id:$('#add_dev_device').val(),
                    vendor_id:$('#add_dev_vendor').val(),
                    model_id:$('#add_dev_model').val(),
                    version:$('#version').val(),
                    bu:$('#add_bu').val(),
                    color_id:$('#color_id').val()
                };
            if(send.name_id == 0) msg = 'Не указано наименование запчасти.';
            else if(send.device_id == 0) msg = 'Не выбрано устройство';
            else if(send.vendor_id == 0) msg = 'Не выбран производитель';
            else if(send.model_id == 0) msg = 'Не выбрана модель';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        dialog.close();
                        window.zp_name.val(send.name_id);
                        $("#dev").device({
                            width:153,
                            type_no:1,
                            device_ids:G.ws.devs,
                            device_id:send.device_id,
                            vendor_id:send.vendor_id,
                            model_id:send.model_id,
                            func:zpSpisokLoad
                        });
                        zpSpisokLoad();
                    }
                },'json');
            }

            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    left:110,
                    top:-47,
                    indent:50,
                    show:1,
                    remove:1
                });
        }
    });

$(document)
    .on('click', '#zpInfo .avai_add', function() {
        var obj = G.zpInfo;
        obj.zp_id = obj.id;
        obj.callback = zpAvaiUpdate;
        zpAvaiAdd(obj);
        $('.avaiAddTab img')
            .removeAttr('height')
            .width(80);
    })
    .on('click', '#zpInfo .edit', function() {
        var html = '<table class="zp_add_dialog">' +
                '<tr><td class="label">Наименование запчасти:<td><input type="hidden" id="name_id" value="' + G.zpInfo.name_id + '">' +
                '<tr><td class="label top">Устройство:<td id="add_dev">' +
                '<tr><td class="label">Версия:<td><input type="text" id="version" value="' + G.zpInfo.version + '">' +
                '<tr><td class="label">Б/у:<td><input type="hidden" id="add_bu" value="' + G.zpInfo.bu + '">' +
                '<tr><td class="label">Цвет:<td><input type="hidden" id="color_id" value="' + G.zpInfo.color_id + '">' +
            '</table>',
            dialog = vkDialog({
                top:30,
                width:380,
                head:'Редактирование запчасти',
                content:html,
                butSubmit:'Сохранить',
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
        $('#add_bu').vkCheck();
        $('#add_dev').device({
            width:200,
            device_id:G.zpInfo.device,
            vendor_id:G.zpInfo.vendor,
            model_id:G.zpInfo.model
        });

        function submit() {
            var msg,
                send = {
                    op:'zp_edit',
                    zp_id:G.zpInfo.id,
                    name_id:$('#name_id').val(),
                    device_id:$('#add_dev_device').val(),
                    vendor_id:$('#add_dev_vendor').val(),
                    model_id:$('#add_dev_model').val(),
                    version:$('#version').val(),
                    bu:$('#add_bu').val(),
                    color_id:$('#color_id').val()
                };
            if(send.name_id == 0) msg = 'Не указано наименование запчасти.';
            else if(send.device_id == 0) msg = 'Не выбрано устройство';
            else if(send.vendor_id == 0) msg = 'Не выбран производитель';
            else if(send.model_id == 0) msg = 'Не выбрана модель';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Редактирование данных произведено.');
                        window.location.reload();
                    }
                },'json');
            }

            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    left:110,
                    top:-47,
                    indent:50,
                    show:1,
                    remove:1
                });
        }
    })
    .on('click', '#zpInfo .set', function() {
        if(zpAvaiNo(G.zpInfo.count))
            return;
        var html = '<table class="zp_dec_dialog">' +
                '<tr><td class="label r">Количество:<td><input type="text" id="count" value="1"><span>(max: <b>' + G.zpInfo.count + '</b>)</span>' +
                '<tr><td class="label r top">Номер заявки:<td><input type="text" id="zayavNomer">' +
                '<tr><td class="label r top">Примечание:<td><textarea id="prim"></textarea>' +
            '</table>',
            dialog = vkDialog({
                width:340,
                head:'Установка запчасти',
                content:html,
                submit:submit
            });
        $('#count').focus().select();

        function submit() {
            var msg,
                send = {
                    op:'zayav_zp_set',
                    zp_id:G.zpInfo.id,
                    count:$('#count').val(),
                    zayav_id:$('#zayavNomerId').length > 0 ? $('#zayavNomerId').val() : 0,
                    prim:$('#prim').val()
                };
            if(!REGEXP_NUMERIC.test(send.count) || send.count > G.zpInfo.count || send.count == 0) {
                msg = 'Некорректно указано количество.';
                $('#count').focus();
            } else if(send.zayav_id == 0) {
                msg = 'Не указан номер заявки.';
                $('#zayavNomer').focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        zpAvaiUpdate();
                        dialog.close();
                        vkMsgOk('Установка запчасти произведена.');
                    }
                },'json');
            }

            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    left:74,
                    top:-47,
                    indent:50,
                    show:1,
                    remove:1
                });
        }
    })
    .on('click', '#zpInfo .sale', function() {
        if(zpAvaiNo(G.zpInfo.count))
            return;
        var html = '<table class="zp_dec_dialog">' +
                '<tr><td class="label r">Количество:<td><input type="text" id="count" value="1"><span>(max: <b>' + G.zpInfo.count + '</b>)</span>' +
                '<tr><td class="label r">Цена за ед.:<td><input type="text" id="cena" maxlength="8"> руб.' +
                '<tr><td class="label r">Деньги поступили в кассу?:<td><input type="hidden" id="kassa" value="-1">' +
                '<tr><td class="label r">Клиент:<td><input type="hidden" id="client_id">' +
                '<tr><td class="label r top">Примечание:<td><textarea id="prim"></textarea>' +
                '</table>',
            dialog = vkDialog({
                top:40,
                width:440,
                head:'Продажа запчасти',
                content:html,
                submit:submit
            });

        $('#count').focus().select();
        $('#client_id').clientSel({add:1});
        $('#kassa').vkRadio({
            display:'inline-block',
            right:15,
            spisok:[{uid:1, title:'да'},{uid:0, title:'нет'}]
        });

        function submit() {
            var msg,
                send = {
                    op:'zp_sale',
                    zp_id:G.zpInfo.id,
                    count:$('#count').val(),
                    cena:$('#cena').val(),
                    kassa:$('#kassa').val(),
                    client_id:$('#client_id').val(),
                    prim:$('#prim').val()
                };
            if(!REGEXP_NUMERIC.test(send.count) || send.count > G.zpInfo.count || send.count == 0) {
                msg = 'Некорректно указано количество.';
                $('#count').focus();
            } else if(!REGEXP_CENA.test(send.cena)) {
                msg = 'Некорректно указана цена.';
                $('#cena').focus();
            } else if(send.kassa == '-1') msg = 'Укажите, поступили деньги в кассу или нет.';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        zpAvaiUpdate();
                        dialog.close();
                        vkMsgOk('Продажа запчасти произведена.');
                    }
                },'json');
            }

            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    left:123,
                    top:-47,
                    indent:50,
                    show:1,
                    remove:1
                });
        }
    })
    .on('click', '#zpInfo .defect,#zpInfo .return,#zpInfo .writeoff', function() {
        if(zpAvaiNo(G.zpInfo.count))
            return;
        var rus = {defect:'Забраковка', return:'Возврат', 'writeoff':'Списание'},
            end = {defect:'ена', return:'ён', 'writeoff':'ено'},
            type = $(this).attr('class'),
            html = '<table class="zp_dec_dialog">' +
                '<tr><td class="label r">Количество:<td><input type="text" id="count" value="1"><span>(max: <b>' + G.zpInfo.count + '</b>)</span>' +
                '<tr><td class="label r top">Примечание:<td><textarea id="prim"></textarea>' +
                '</table>',
            dialog = vkDialog({
                top:60,
                width:340,
                head:rus[type] + ' запчасти',
                content:html,
                submit:submit
            });

        $('#count').focus().select();

        function submit() {
            var send = {
                op:'zp_other',
                zp_id:G.zpInfo.id,
                type:type,
                count:$('#count').val(),
                prim:$('#prim').val()
            };
            if(!REGEXP_NUMERIC.test(send.count) || send.count > G.zpInfo.count || send.count == 0) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">Некорректно указано количество.</SPAN>',
                    left:73,
                    top:-47,
                    indent:50,
                    show:1,
                    remove:1
                });
                $('#count').focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        zpAvaiUpdate();
                        dialog.close();
                        vkMsgOk(rus[type] + ' запчасти произвед' + end[type] + '.');
                    }
                },'json');
            }
        }
    })
    .on('click', '#zpInfo .move .img_del', function() {
        var id = $(this).attr('val');
        var dialog = vkDialog({
            top:110,
            width:250,
            head:'Удаление заявки',
            content:'<center>Подтвердите удаление записи.</center>',
            butSubmit:'Удалить',
            submit:function() {
                var send = {
                    op:'zp_move_del',
                    id:id
                };
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        zpAvaiUpdate();
                        dialog.close();
                        vkMsgOk('Запись удалена.');
                    }
                }, 'json');
            }
        });
    })
    .on('click', '#zpInfo .move .ajaxNext', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = {
                op:'zp_move_next',
                zp_id:G.zpInfo.id,
                page:$(this).attr('val')
            };
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success)
                next.after(res.spisok).remove();
            else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#zpInfo .compat_add', function() {
        var sp = G.zpInfo,
            html = '<div class="compatAddTab">' +
                '<div class="name">' +
                    (sp.bu == 1 ? '<span class="bu">Б/y</span>' : '') +
                    sp.name + '<br />' +
                    sp.for +
                '</div>' +
                '<table class="prop">' +
                    (sp.version ? '<tr><td class="label">Версия:<td>' + sp.version : '') +
                    (sp.color_id > 0 ? '<tr><td class="label">Цвет:<td>' + sp.color_name : '') +
                '</table>' +
                '<div class="headName">Подходит к устройству:</div>' +
                '<div id="dev"></div>' +
                '<div id="cres"></div>' +
            '</div>',
            dialog = vkDialog({
                top:90,
                width:400,
                head:'Добавление совместимости с другими устройствами',
                content:html,
                butSubmit:'Добавить',
                submit:submit
            }),
            cres = $('#cres'),
            dev = {},
            go = 0;
        $('#dev').device({
            width:220,
            device_id:sp.device,
            vendor_id:sp.vendor,
            add:1,
            func:devSelect
        });

        function devSelect(obj) {
            dialog.abort();
            go = 0;
            dev = obj;
            cres.html('');
            if(obj.device_id > 0 && obj.vendor_id > 0 && obj.model_id > 0) {
                if(obj.device_id == sp.device && obj.vendor_id == sp.vendor && obj.model_id == sp.model) {
                    cres.html('<em class="red">Невозможно создать совместимость на это же устройство.</em>');
                    return;
                }
                var send = {
                    op:'zp_compat_find',
                    zp_id:sp.id,
                    bu:sp.bu,
                    name_id:sp.name_id,
                    device_id:obj.device_id,
                    vendor_id:obj.vendor_id,
                    model_id:obj.model_id,
                    color_id:sp.color_id
                };
                cres.html('<img src="/img/upload.gif">');
                $.post(AJAX_MAIN, send, function(res) {
                    cres.html('');
                    if(res.success)
                        finded(res);
                }, 'json');
            }
        }

        function finded(res) {
            if(res.id) {
                if(res.compat_id == sp.compat_id) {
                    cres.html('<em class="red">Выбранная запчасть уже является совместимостью этой запчасти.</em>');
                    return;
                }
                cres.html('Запчасть <B>' + res.name + '</B><br />' +
                          'будет добавлена в совместимость.<br /><br />' +
                          'Информация о движениях, наличиях<br />' +
                          'и заказах будет сложена и станет<br />' +
                          'общей для обоих запчастей.');
            } else
                cres.html('Запчасти <b>' + res.name + '</b><br />' +
                          'нет в каталоге запчастей.<br /><br />' +
                          'При добавлении совместимости она<br />' +
                          'будет автоматически внесена в каталог.');
            go = 1;
        }

        function submit() {
            if(go == 0) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">Выберите устройство для добавления совместимости.</SPAN>',
                    left:103,
                    top:-47,
                    indent:50,
                    show:1,
                    remove:1
                });
                return;
            }
            var send = {
                op:'zp_compat_add',
                zp_id:sp.id,
                device_id:dev.device_id,
                vendor_id:dev.vendor_id,
                model_id:dev.model_id
            };
            dialog.process();
            $.post(AJAX_MAIN, send, function(res) {
                dialog.abort();
                if(res.success) {
                    dialog.close();
                    vkMsgOk('Совместимость создана.');
                    window.location.reload();
                }
            }, 'json');
        }
    })
    .on('click', '#zpInfo .compatSpisok .img_del', function() {
        var id = $(this).attr('val');
        var dialog = vkDialog({
            top:110,
            width:250,
            head:'Удаление совместимости',
            content:'<center>Подтвердите удаление совместимости.</center>',
            butSubmit:'Удалить',
            submit:function() {
                var send = {
                    op:'zp_compat_del',
                    id:id,
                    zp_id:G.zpInfo.id
                };
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    dialog.abort();
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Совместимость удалена.');
                        $('.compatCount').html(res.count);
                        $('.compatSpisok').html(res.spisok);
                    }
                }, 'json');
            }
        });
        return false;
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
    .on('click', '.remind_unit .hist_a', function() {
        $(this).parent().parent().find('.hist').slideToggle();
    })
    .on('click', '.report_remind_add', function() {
        var html = '<TABLE class="remind_add_tab">' +
            '<tr><td class="label">Назначение:<TD><INPUT type="hidden" id="destination" />' +
            '<tr><td class="label top" id="target_name"><TD id="target">' +
            '</TABLE>' +

            '<TABLE class="remind_add_tab" id="tab_content">' +
            '<tr><td class="label top">Задание:<TD><TEXTAREA id=txt></TEXTAREA>' +
            '<tr><td class="label">Крайний день выполнения:<TD><INPUT type="hidden" id="data" />' +
            '<tr><td class="label">Личное:<TD><INPUT type="hidden" id="private" />' +
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
            msg:'Задание сможете<br />видеть только Вы.',
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
            if(id == 1) {
                $('#target_name').html('Клиент:');
                $('#target').html('<DIV id="client_id"></DIV>');
                $('#client_id').clientSel();
            }
            if(id == 2) {
                $('#target_name').html('Номер заявки:');
                $('#target').html('<INPUT type="text" id="zayavNomer" />');
                $('#zayavNomer').focus();
            }
        }

        function submit() {
            var client_id = dest.val() == 1 ? $('#client_id').val() : 0,
                zayav_id = $('#zayavNomerId').length > 0 ? $('#zayavNomerId').val() : 0,
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
            else if(dest.val() == 1 && send.client_id == 0) msg = 'Не выбран клиент.';
            else if(dest.val() == 2 && send.zayav_id == 0) {
                msg = 'Не указан номер заявки.';
                $('#zayavNomer').focus();
            } else if(!send.txt) msg = 'Не указано содержание напоминания.';
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
        }
        return false;
    })
    .on('click', '#report_remind .ajaxNext', function() {
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
                head:'Новое действие для напоминания',
                content:'<center><img src="/img/upload.gif"></center>',
                butSubmit:'Применить',
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
            var html = '<TABLE class="remind_action_tab">' +
                '<tr><td class="label">' + (res.client ? 'Клиент:' : '') + (res.zayav ? 'Заявка:' : '') +
                    '<TD>' + (res.client ? res.client : '') + (res.zayav ? res.zayav : '') +
                '<tr><td class="label">Задание:<TD><B>' + res.txt + '</B>' +
                '<tr><td class="label">Внёс:<TD>' + res.viewer + ', ' + res.dtime +
                '<tr><td class="label top">Действие:<TD><INPUT type="hidden" id=action value="0">' +
                '</TABLE>' +

                '<TABLE class="remind_action_tab" id="new_action">' +
                '<tr><td class="label" id="new_about"><TD id="new_title">' +
                '<tr><td class="label top" id="new_comm"><TD><TEXTAREA id="comment"></TEXTAREA>' +
                '</TABLE>';
            dialog.content.html(html);

            $('#action').vkRadio({
                top:5,
                spisok:[
                    {uid:1, title:'Перенести на другую дату'},
                    {uid:2, title:'Выполнено'},
                    {uid:3, title:'Отменить'}
                ],
                func:function (id) {
                    $('#new_action').show();
                    $('#comment').val('');
                    $('#new_about').html('');
                    $('#new_title').html('');
                    if (id == 1) {
                        $('#new_about').html('Дата:');
                        $('#new_title').html('<INPUT type="hidden" id="data">');
                        $('#new_comm').html('Причина:');
                        $('#new_action #data').vkCalendar();
                    }
                    if(id == 2) $('#new_comm').html('Комментарий:');
                    if(id == 3) $('#new_comm').html('Причина:');
                }
            });

            $('#comment').autosize();
        }, 'json');

        function submit () {
            var send = {
                op:'report_remind_edit',
                id:id,
                action:parseInt($('#action').val()),
                day:curDay,
                status:1,
                history:$('#comment').val(),
                from_zayav:G.zayavInfo ? G.zayavInfo.id : 0,
                from_client:G.clientInfo ? G.clientInfo.id : 0
            };
            switch(send.action) {
                case 1: send.day = $('#data').val(); break;
                case 2: send.status = 2; break; // выполнено
                case 3: send.status = 0;        // отменено
            }

            var msg;
            if(!send.action) msg = 'Укажите новое действие.';
            else if((send.action == 1 || send.action == 3) && !send.history) msg = 'Не указана причина.';
            else if(send.action == 1 && send.day == curDay) msg = 'Выберите новую дату.';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Задание отредактировано.');
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
                $('#report_prihod ._spisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#report_prihod .summa_add', function() {
        var html = '<TABLE id="report_prihod_add">' +
            '<tr><td class="label">Содержание:<TD><INPUT type="text" id="about" maxlength="100">' +
            '<tr><td class="label">Сумма:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> руб.' +
            '<tr><td class="label">Деньги поступили в кассу?:<TD><INPUT type="hidden" id="kassa" value="-1">' +
            '</TABLE>';
        var dialog = vkDialog({
                width:380,
                head:'Внесение поступления средств',
                content:html,
                submit:submit
            }),
            kassa = $('#report_prihod_add #kassa'),
            sum = $('#report_prihod_add #sum'),
            about = $('#report_prihod_add #about');

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
            if(!send.about) { msg = 'Не указано содержание.'; about.focus(); }
            else if(!REGEXP_NUMERIC.test(send.sum)) { msg = 'Некорректно указана сумма.'; sum.focus(); }
            else if(send.kassa == -1) msg = 'Укажите, деньги поступили в кассу?';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Новое поступление внесено.');
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
                vkMsgOk('Удаление произведено.');
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
                vkMsgOk('Восстановление произведено.');
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
                $('#report_rashod ._spisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#report_rashod #add', function() {
        var html = '<TABLE id="report_rashod_add">' +
                '<tr><td class="label">Категория:<TD><INPUT type="hidden" id="category" value="0">' +
                '<tr><td class="label">Описание:<TD><INPUT type="text" id="about" maxlength="100">' +
                '<tr><td class="label">Сотрудник:<TD><INPUT type="hidden" id="worker" value="0">' +
                '<tr><td class="label">Сумма:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> руб.' +
                '<tr><td class="label">Деньги взяты из кассы?:<TD><INPUT type="hidden" id="kassa" value="-1">' +
            '</TABLE>',
            dialog = vkDialog({
                top:60,
                width:380,
                head:'Внесение расхода',
                content:html,
                submit:submit
            }),
            category = $('#report_rashod_add #category'),
            worker = $('#report_rashod_add #worker'),
            kassa = $('#report_rashod_add #kassa'),
            sum = $('#report_rashod_add #sum'),
            about = $('#report_rashod_add #about');

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
            if(!send.about && send.category == 0) { msg = 'Выберите категорию или укажите описание.'; about.focus(); }
            else if(!REGEXP_NUMERIC.test(send.sum)) { msg = 'Некорректно указана сумма.'; sum.focus(); }
            else if(send.kassa == -1) msg = 'Укажите, деньги взяты из кассы или нет.';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Новый расход внесён.');
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
                vkMsgOk('Удаление произведено.');
                tr.remove();
            }
        }, 'json');
    })
    .on('click', '#report_rashod .img_edit', function() {
        var dialog = vkDialog({
                top:60,
                width:380,
                head:'Редактирование расхода',
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
            var html = '<TABLE id="report_rashod_add">' +
                '<tr><td class="label">Категория:<TD><INPUT type="hidden" id="category" value="' + res.category + '">' +
                '<tr><td class="label">Описание:<TD><INPUT type="text" id="about" maxlength="150" value="' + res.about + '">' +
                '<tr><td class="label">Сотрудник:<TD><INPUT type="hidden" id="worker" value="' + res.worker_id + '">' +
                '<tr><td class="label">Сумма:<TD><INPUT type="text" id="sum" class="money" maxlength="8" value="' + res.sum + '"> руб.' +
                '<tr><td class="label">Деньги взяты из кассы?:<TD><INPUT type="hidden" id="kassa" value="' + res.kassa + '">' +
                '</TABLE>';
            dialog.content.html(html);
            category = $('#report_rashod_add #category');
            worker = $('#report_rashod_add #worker');
            kassa = $('#report_rashod_add #kassa');
            sum = $('#report_rashod_add #sum');
            about = $('#report_rashod_add #about');

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
            if(!send.about && send.category == 0) { msg = 'Выберите категорию или укажите описание.'; about.focus(); }
            else if(!REGEXP_NUMERIC.test(send.sum)) { msg = 'Некорректно указана сумма.'; sum.focus(); }
            else if(send.kassa == -1) msg = 'Укажите, деньги взяты из кассы или нет.';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Расход изменён.');
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
                $('#report_kassa ._spisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#report_kassa .actions a:first', function() {
        var kassa = $('#kassa_summa'),
            kassa_sum = parseInt(kassa.html()),
            html = '<TABLE style="border-spacing:8px">' +
                '<tr><td class="label r">Сумма:<TD><INPUT type="text" class="money" id="kassa_down_sum" maxlength="8" />' +
                '<tr><td class="label r">Комментарий:<TD><INPUT type="text" id="kassa_down_txt" />' +
                '</TABLE>',
            dialog = vkDialog({
                head:'Внесение денег в кассу',
                content:html,
                submit:submit
            }),
            sum = $('#kassa_down_sum'),
            txt = $('#kassa_down_txt');

        sum.focus();

        function submit() {
            var send = {
                op:'report_kassa_action',
                txt:txt.val(),
                sum:sum.val(),
                down:0
            };
            var msg;
            if(!REGEXP_NUMERIC.test(send.sum)) { msg = 'Некорректно указана сумма.'; sum.focus(); }
            else if(!send.txt) { msg = 'Не указан комментарий.'; txt.focus(); }
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Новая запись внесёна.');
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
            html = '<TABLE style="border-spacing:8px">' +
                '<tr><td class="label r">Сумма:' +
                    '<TD><INPUT type="text" class="money" id="kassa_down_sum" maxlength="8" /> max: ' + kassa_sum +
                '<tr><td class="label r">Комментарий:<TD><INPUT type="text" id="kassa_down_txt" />' +
                '</TABLE>',
            dialog = vkDialog({
                head:'Взятие денег из кассы',
                content:html,
                submit:submit
            }),
            sum = $('#kassa_down_sum'),
            txt = $('#kassa_down_txt');

        sum.focus();

        function submit() {
            var send = {
                op:'report_kassa_action',
                txt:txt.val(),
                sum:sum.val(),
                down:1
            };
            var msg;
            if(!REGEXP_NUMERIC.test(send.sum)) { msg = 'Некорректно указана сумма.'; sum.focus(); }
            else if(send.sum > kassa_sum) { msg = 'Введённая сумма превышает сумму в кассе.'; sum.focus(); }
            else if(!send.txt) { msg = 'Не указан комментарий.'; txt.focus(); }
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('Новая запись внесёна.');
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
                vkMsgOk('Удаление произведено.');
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
                vkMsgOk('Восстановление произведено.');
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

    if($('#client').length > 0) {
        $('#find').topSearch({
            width:585,
            focus:1,
            txt:'Начните вводить данные клиента',
            func:clientSpisokLoad
        });
        $('#buttonCreate').vkHint({
            msg:'<B>Внесение нового клиента в базу.</B><br /><br />' +
                'После внесения Вы попадаете на страницу с информацией о клиенте для дальнейших действий.<br /><br />' +
                'Клиентов также можно добавлять при <A href="' + URL + '&p=zayav&d=add&back=client">создании новой заявки</A>.',
            ugol:'right',
            width:215,
            top:-38,
            left:-250,
            indent:40,
            delayShow:1000,
            correct:0
        });
        $('#dolg_check').vkHint({
            msg:'<b>Список должников.</b><br /><br />' +
                'Выводятся клиенты, у которых баланс менее 0. Также в результате отображается общая сумма долга.',
            ugol:'right',
            width:150,
            top:-6,
            left:-185,
            indent:20,
            delayShow:1000,
            correct:0
        });
    }
    if($('#clientInfo').length > 0) {
        $('#dopLinks .link').click(function() {
            $('#dopLinks .link').removeClass('sel');
            $(this).addClass('sel');
            var val = $(this).attr('val');
            $('.res').css('display', val == 'zayav' ? 'block' : 'none');
            $('#zayav_filter').css('display', val == 'zayav' ? 'block' : 'none');
            $('#zayav_spisok').css('display', val == 'zayav' ? 'block' : 'none');
            $('#money_spisok').css('display', val == 'money' ? 'block' : 'none');
            $('#remind_spisok').css('display', val == 'remind' ? 'block' : 'none');
            $('#comments').css('display', val == 'comm' ? 'block' : 'none');
        });
        G.status_spisok.unshift({uid:0, title:'Любой статус'});
        $('#zayav_status').infoLink({
            spisok:G.status_spisok,
            func:clientZayavSpisokLoad
        });
        $('#dev').device({
            width:145,
            type_no:1,
            device_ids:G.device_ids,
            vendor_ids:G.vendor_ids,
            model_ids:G.model_ids,
            func:clientZayavSpisokLoad
        });
    }

    if($('#zayav').length > 0) {
        $('#find')
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
        $('#sort').myRadio({
            spisok:[
                {uid:1,title:'По дате добавления'},
                {uid:2,title:'По обновлению статуса'}
            ],
            bottom:5,
            func:zayavSpisokLoad
        });
        G.status_spisok.unshift({uid:0, title:'Любой статус'});
        $('#status').infoLink({
            spisok:G.status_spisok,
            func:zayavSpisokLoad
        }).infoLinkSet(G.zayav_status);
        $('#zpzakaz').vkRadio({
            spisok:[
                {uid:0, title:'Все заявки'},
                {uid:1, title:'Да'},
                {uid:2, title:'Нет'}
            ],
            top:6,
            light:1,
            func:zayavSpisokLoad
        });
        $('#dev').device({
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
        for(n = 0; n < G.place_other.length; n++) {
            var sp = G.place_other[n];
            G.device_place_spisok.push({uid:encodeURI(sp), title:sp});
        }
        G.device_place_spisok.push({uid:-1, title:'не известно', content:'<B>не известно</B>'});
        G.vkSel_device_place = $('#device_place').vkSel({
            width:155,
            title0:'Любое местонахождение',
            spisok:G.device_place_spisok,
            func:zayavSpisokLoad
        }).o;
        // Состояние устройства
        G.device_status_spisok.splice(0, 1);
        G.device_status_spisok.push({uid:-1, title:'не известно', content:'<B>не известно</B>'});
        G.vkSel_device_status = $('#devstatus').vkSel({
            width:155,
            title0:'Любое состояние',
            spisok:G.device_status_spisok,
            func:zayavSpisokLoad
        }).o;
        zayavFilter();
    }
    if($('#zayavAdd').length > 0) {
        $('#client_id').clientSel({add:1});
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
            $('#comm').val(arr.join(', '));
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
        $('.vkButton').click(function () {
            var send = {
                op:'zayav_add',
                client:$('#client_id').val(),
                device:$('#dev_device').val(),
                vendor:$('#dev_vendor').val(),
                model:$('#dev_model').val(),
                place:$('#place').val(),
                place_other:$('#place_other').val(),
                imei:$('#imei').val(),
                serial:$('#serial').val(),
                color:$('#color_id').val(),
                comm:$('#comm').val(),
                reminder:$('#reminder').val()
            };
            send.reminder_txt = send.reminder == 1 ? $('#reminder_txt').val() : '';
            send.reminder_day = send.reminder == 1 ? $('#reminder_day').val() : '';

            var msg = '';
            if(send.client == 0) msg = 'Не выбран клиент';
            else if(send.device == 0) msg = 'Не выбрано устройство';
            else if(send.place == '' || send.place == 0 && !send.place_other) msg = 'Не указано местонахождение устройства';
            else if(send.reminder == 1 && !send.reminder_txt) msg = 'Не указан текст напоминания';
            else {
                if(send.place > 0) send.place_other = '';
                $(this).addClass('busy');
                $.post(AJAX_MAIN, send, function(res) {
                    location.href = URL + '&p=zayav&d=info&id=' + res.id;
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
        $('.delete').vkHint({
            msg:'Заявку можно удалить при отсутствии платежей и начислений. Также удаляются все задачи к этой заявке.',
            width:150,
            ugol:'top',
            top:40,
            left:456,
            indent:90,
            correct:0
        });
        $('.fotoUpload').fotoUpload({
            owner:'zayav' + G.zayavInfo.id,
            func:zayavImgUpdate
        });
    }

    if($('#zp').length > 0) {
        $('#find')
            .topSearch({
                width:134,
                focus:1,
                txt:'Быстрый поиск...',
                enter:1,
                func:zpSpisokLoad
            })
            .topSearchSet(G.zp_find);
        $("#menu")
            .infoLink({
            spisok:[
                {uid:0,title:'Общий каталог'},
                {uid:1,title:'Наличие'},
                {uid:2,title:'Нет в наличии'},
                {uid:3,title:'Заказ'}],
            func:zpSpisokLoad
        })
            .infoLinkSet(G.zp_menu);
        window.zp_name = $("#zp_name").vkSel({
            width:153,
            title0:'Любое наименование',
            spisok:G.zp_name_spisok,
            func:zpSpisokLoad
        }).o;
        $("#dev").device({
            width:153,
            type_no:1,
            device_ids:G.ws.devs,
            device_id:G.zp_device,
            vendor_id:G.zp_vendor,
            model_id:G.zp_model,
            func:zpSpisokLoad
        });
        zpFilter();
    }
    if($('#zpInfo').length > 0) {
        $('.fotoUpload').fotoUpload({
            owner:'zp' + G.zpInfo.compat_id,
            func:zpImgUpdate
        });
    }
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

    frameBodyHeightSet();
});