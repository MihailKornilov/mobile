var AJAX_SA = 'http://' + G.domain + '/ajax/sa.php?' + G.values;

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
        var dialog = vkDialog({
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

    .on('click', '.sa-equip .rightLinks a', function() {
        $('.sa-equip .rightLinks a.sel').removeClass('sel');
        $(this).addClass('sel');
        var send = {
            op:'ws_equip_show',
            device_id:$('.sa-equip .rightLinks .sel').attr('val')
        };
        $.post(AJAX_SA, send, function(res) {
            $('#eq-spisok').html(res.html);
        }, 'json');
    })
    .on('click', '.sa-equip .add', function() {
        var t = $(this),
            html = '<table class="ws-equip-add">' +
                '<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" />' +
                '<tr><td class="label">Описание:<td><input id="title" type="text" maxlength="200" />' +
            '</table>',
            dialog = vkDialog({
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
                op:'ws_equip_add',
                name:$('#name').val(),
                title:$('#title').val(),
                device_id:$('.sa-equip .rightLinks .sel').attr('val')
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
                        vkMsgOk('Внесено!');
                        sortable();
                    } else
                        dialog.abort();
                }, 'json');
            }
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
            op:'ws_equip_set',
            device_id:$('.sa-equip .rightLinks .sel').attr('val'),
            ids:arr.join()
        };
        $.post(AJAX_SA, send, function(res) {

        }, 'json');
    });

$(document).ready(function() {

});