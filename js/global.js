var AJAX_MAIN = 'http://' + G.domain + '/ajax/main.php?' + G.values,
    reportPrihodLoad = function (data) {
        var send = {
            op:'report_prihod_load',
            day_begin:$('#report_prihod_day_begin').val(),
            day_end:$('#report_prihod_day_end').val()
        };
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_prihod').html(res.html);
        }, 'json');
    };


$(document).ajaxError(function(event, request, settings) {
    if(!request.responseText)
        return;
    alert('Ошибка:\n\n' + request.responseText);
});


$(document).on('click', '#report_prihod_next', function () {
    if($(this).hasClass('busy'))
        return;
    var next = $(this),
        send = {
            op:'report_prihod_next',
            day_begin:$('#report_prihod_day_begin').val(),
            day_end:$('#report_prihod_day_end').val(),
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
});

$(document).ready(function() {
    $('#report_prihod_day_begin').vkCalendar({lost:1, place:'left', func:reportPrihodLoad});
    $("#report_prihod_day_end").vkCalendar({lost:1, place:'left', func:reportPrihodLoad});
});