var AJAX_SA = SITE + '/ajax/sa.php?' + VALUES;

$(document)
    .on('click', '.sa-ws-info .ws_status_change', function() {
        var t = $(this),
            send = {
                op:'ws_status_change',
                ws_id:t.attr('val')
            };
        $.post(AJAX_SA, send, function(res) {
            if(res.success)
                document.location.reload();
            else
                t.vkHint({
                    msg:'<SPAN class=red>' + res.text + '</SPAN>',
                    top:-47,
                    left:27,
                    indent:50,
                    show:1,
                    remove:1
                });
        }, 'json');
    })
    .on('click', '.sa-ws-info .ws_enter', function() {
        setCookie('sa_viewer_id', $(this).attr('val'));
        document.location.reload();
    })
    .on('click', '.sa-ws-info .ws_del', function() {
        var t = $(this);
        var dialog = _dialog({
            top:110,
            width:250,
            head:'Удаление мастерской',
            content:'<center>Подтвердите удаление мастерской.</center>',
            butSubmit:'Удалить',
            submit:submit
        });
        function submit() {
            var send = {
                op:'ws_del',
                ws_id:t.attr('val')
            };
            $.post(AJAX_SA, send, function(res) {
                if(res.success)
                    document.location.reload();
            }, 'json');
        }
    })
    .on('click', '.sa-ws-info .ws_client_balans', function() {
        var t = $(this),
            send = {
                op:'ws_client_balans',
                ws_id:t.attr('val')
            };
        t.addClass('busy');
        $.post(AJAX_SA, send, function(res) {
            t.removeClass('busy');
            if(res.success)
                t.after(' Изменено: ' + res.count + '. Время: ' + res.time);
        }, 'json');
    })

    .on('click', '.sa-device .add', function() {
        var t = $(this),
            html = '<table class="sa-device-add">' +
                '<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" />' +
                '<tr><td class="label r">Родительный падеж (кого?):<td><input id="name_rod" type="text" maxlength="100" />' +
                '<tr><td class="label r">Множественное число:<td><input id="name_mn" type="text" maxlength="100" />' +
                '<tr><td class="label r top">Возможная комплектация:<td class="equip">' + devEquip +
                '</table>',
            dialog = _dialog({
                top:60,
                width:460,
                head:'Добавление нового наименования устройства',
                content:html,
                submit:submit
            });
        $('#name,#name_rod,#name_mn').keyEnter(submit);
        $('#name').focus();
        function submit() {
            var inp = $('.equip input'),
                arr = [];
            for(var n = 0; n < inp.length; n++) {
                var eq = inp.eq(n);
                if(eq.val() == 1)
                    arr.push(eq.attr('id').split('_')[1]);
            }
            var send = {
                op:'device_add',
                name:$('#name').val(),
                rod:$('#name_rod').val(),
                mn:$('#name_mn').val(),
                equip:arr.join()
            };
            if(!send.name || !send.rod || !send.mn) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>Необходимо заполнить все поля</SPAN>',
                    top:-47,
                    left:131,
                    indent:50,
                    show:1,
                    remove:1
                });
                if(!send.name)
                    $('#name').focus();
                else if(!send.rod)
                    $('#name_rod').focus();
                else if(!send.mn)
                    $('#name_mn').focus();
            } else {
                dialog.process();
                $.post(AJAX_SA, send, function(res) {
                    if(res.success) {
                        $('.spisok').html(res.html);
                        dialog.close();
                        _msg('Внесено!');
                        sortable();
                    } else
                        dialog.abort();
                }, 'json');
            }
        }
    })
    .on('click', '.sa-device .img_edit', function() {
        var t = $(this),
            id = t,
            dialog = _dialog({
                top:60,
                width:460,
                head:'Изменение данных устройства',
                content:'<center><img src="/img/upload.gif"></center>',
                submit:submit
            });
        while(id[0].tagName != 'DD')
            id = id.parent();
        id = id.attr('val');
        var send = {
            op:'device_get',
            id:id
        };
        $.post(AJAX_SA, send, function(res) {
            var html = '<table class="sa-device-add">' +
                '<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" value="' + res.name + '" />' +
                '<tr><td class="label r">Родительный падеж (кого?):<td><input id="name_rod" type="text" maxlength="100" value="' + res.name_rod + '" />' +
                '<tr><td class="label r">Множественное число:<td><input id="name_mn" type="text" maxlength="100" value="' + res.name_mn + '" />' +
                '<tr><td class="label r top">Возможная комплектация:<td class="equip">' + res.equip +
            '</table>';
            dialog.content.html(html);
            $('#name,#name_rod,#name_mn').keyEnter(submit);
            $('#name').focus();
        }, 'json');
        function submit() {
            var inp = $('.equip input'),
                arr = [];
            for(var n = 0; n < inp.length; n++) {
                var eq = inp.eq(n);
                if(eq.val() == 1)
                    arr.push(eq.attr('id').split('_')[1]);
            }
            var send = {
                op:'device_edit',
                id:id,
                name:$('#name').val(),
                rod:$('#name_rod').val(),
                mn:$('#name_mn').val(),
                equip:arr.join()
            };
            if(!send.name || !send.rod || !send.mn) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>Необходимо заполнить все поля</SPAN>',
                    top:-47,
                    left:131,
                    indent:50,
                    show:1,
                    remove:1
                });
                if(!send.name)
                    $('#name').focus();
                else if(!send.rod)
                    $('#name_rod').focus();
                else if(!send.mn)
                    $('#name_mn').focus();
            } else {
                dialog.process();
                $.post(AJAX_SA, send, function(res) {
                    if(res.success) {
                        $('.spisok').html(res.html);
                        dialog.close();
                        _msg('Изменено!');
                        sortable();
                    } else
                        dialog.abort();
                }, 'json');
            }
        }
    })
    .on('click', '.sa-device .img_del', function() {
        var t = $(this),
            dialog = _dialog({
                top:90,
                width:300,
                head:'Удаление устройства',
                content:'<center><b>Подтвердите удаление устройства.</b></center>',
                butSubmit:'Удалить',
                submit:submit
            });
        function submit() {
            while(t[0].tagName != 'DD')
                t = t.parent();
            var send = {
                op:'device_del',
                id:t.attr('val')
            };
            dialog.process();
            $.post(AJAX_SA, send, function(res) {
                if(res.success) {
                    $('.spisok').html(res.html);
                    dialog.close();
                    _msg('Удалено!');
                    sortable();
                } else
                    dialog.abort();
            }, 'json');
        }
    })

    .on('click', '.sa-equip .rightLink a', function() {
        $('.sa-equip .rightLink a.sel').removeClass('sel');
        $(this).addClass('sel');
        var send = {
            op:'equip_show',
            device_id:$('.sa-equip .rightLink .sel').attr('val')
        };
        $('.path').addClass('busy');
        $.post(AJAX_SA, send, function(res) {
            $('.path').removeClass('busy');
            $('#eq-spisok').html(res.html);
            sortable();
        }, 'json');
    })
    .on('click', '.sa-equip .add', function() {
        var t = $(this),
            html = '<table class="sa-equip-add">' +
                '<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" />' +
                '<tr><td class="label">Описание:<td><input id="title" type="text" maxlength="200" />' +
            '</table>',
            dialog = _dialog({
                top:90,
                width:350,
                head:'Добавление новой комплектации',
                content:html,
                submit:submit
            });
        $('#name,#title').keyEnter(submit);
        $('#name').focus();
        function submit() {
            var send = {
                op:'equip_add',
                name:$('#name').val(),
                title:$('#title').val(),
                device_id:$('.sa-equip .rightLink .sel').attr('val')
            };
            if(!send.name) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>Не указано наименование</SPAN>',
                    top:-47,
                    left:77,
                    indent:50,
                    show:1,
                    remove:1
                });
                $('#name').focus();
            } else {
                dialog.process();
                $.post(AJAX_SA, send, function(res) {
                    if(res.success) {
                        $('#eq-spisok').html(res.html);
                        dialog.close();
                        _msg('Внесено!');
                        sortable();
                    } else
                        dialog.abort();
                }, 'json');
            }
        }
    })
    .on('click', '.sa-equip .img_edit', function() {
        var t = $(this),
            id = t,
            dialog = _dialog({
                top:90,
                width:350,
                head:'Редактирование комплектации',
                content:'<center><img src="/img/upload.gif"></center>',
                butSubmit:'Сохранить',
                submit:submit
            });
        while(id[0].tagName != 'DD')
            id = id.parent();
        id = id.attr('val');
        var send = {
            op:'equip_get',
            id:id
        };
        $.post(AJAX_SA, send, function(res) {
            var html = '<table class="sa-equip-add">' +
                '<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" value="' + res.name + '" />' +
                '<tr><td class="label">Описание:<td><input id="title" type="text" maxlength="200" value="' + res.title + '" />' +
                '</table>';
            dialog.content.html(html);
            $('#name,#title').keyEnter(submit);
            $('#name').focus();
        }, 'json');

        function submit() {
            var send = {
                op:'equip_edit',
                id:id,
                name:$('#name').val(),
                title:$('#title').val(),
                device_id:$('.sa-equip .rightLink .sel').attr('val')
            };
            if(!send.name) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>Не указано наименование</SPAN>',
                    top:-47,
                    left:77,
                    indent:50,
                    show:1,
                    remove:1
                });
                $('#name').focus();
            } else {
                dialog.process();
                $.post(AJAX_SA, send, function(res) {
                    if(res.success) {
                        $('#eq-spisok').html(res.html);
                        dialog.close();
                        _msg('Сохранено!');
                        sortable();
                    } else
                        dialog.abort();
                }, 'json');
            }
        }
    })
    .on('click', '.sa-equip .img_del', function() {
        var t = $(this),
            dialog = _dialog({
                top:90,
                width:300,
                head:'Удаление комплектации',
                content:'<center><b>Подтвердите удаление комплектации.</b></center>',
                butSubmit:'Удалить',
                submit:submit
            });
        function submit() {
            while(t[0].tagName != 'DD')
                t = t.parent();
            var send = {
                op:'equip_del',
                id:t.attr('val'),
                device_id:$('.sa-equip .rightLink .sel').attr('val')
            };
            dialog.process();
            $.post(AJAX_SA, send, function(res) {
                if(res.success) {
                    $('#eq-spisok').html(res.html);
                    dialog.close();
                    _msg('Удалено!');
                    sortable();
                } else
                    dialog.abort();
            }, 'json');
        }
    })
    .on('click', '.sa-equip .check0,.sa-equip .check1', function() {
        var inp = $('.sa-equip ._sort input'),
            arr = [];
        for(var n = 0; n < inp.length; n++) {
            var eq = inp.eq(n);
            if(eq.val() == 1)
                arr.push(eq.attr('id').split('_')[1]);
        }
        var send = {
            op:'equip_set',
            device_id:$('.sa-equip .rightLink .sel').attr('val'),
            ids:arr.join()
        };
        $('.path').addClass('busy');
        $.post(AJAX_SA, send, function(res) {
            $('.path').removeClass('busy');
        }, 'json');
    });

$(document).ready(function() {

});