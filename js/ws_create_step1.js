G.countries_spisok = [
    {uid:1,title:'Россия'},
    {uid:2,title:'Украина'},
    {uid:3,title:'Беларусь'},
    {uid:4,title:'Казахстан'},
    {uid:5,title:'Азербайджан'},
    {uid:6,title:'Армения'},
    {uid:7,title:'Грузия'},
    {uid:8,title:'Израиль'},
    {uid:11,title:'Кыргызстан'},
    {uid:12,title:'Латвия'},
    {uid:13,title:'Литва'},
    {uid:14,title:'Эстония'},
    {uid:15,title:'Молдова'},
    {uid:16,title:'Таджикистан'},
    {uid:17,title:'Туркмения'},
    {uid:18,title:'Узбекистан'}
];
G.cities_spisok = [
    {uid:1,title:'Москва'},
    {uid:2,title:'Санкт-Петербург'},
    {uid:35,title:'Великий Новгород'},
    {uid:10,title:'Волгоград'},
    {uid:49,title:'Екатеринбург'},
    {uid:60,title:'Казань'},
    {uid:61,title:'Калининград'},
    {uid:72,title:'Краснодар'},
    {uid:73,title:'Красноярск'},
    {uid:87,title:'Мурманск'},
    {uid:95,title:'Нижний Новгород'},
    {uid:99,title:'Новосибирск'},
    {uid:104,title:'Омск'},
    {uid:110,title:'Пермь'},
    {uid:119,title:'Ростов-на-Дону'},
    {uid:123,title:'Самара'},
    {uid:125,title:'Саратов'},
    {uid:151,title:'Уфа'},
    {uid:158,title:'Челябинск'}
];
for(var n = 0; n < 2; n++)
    G.cities_spisok[n].content = '<B>' + G.cities_spisok[n].title + '</B>';


// проверка наличия страны в списке
var COUNTRIES = $('#countries'),
    CITIES = $('#cities'),
    country,
    city,
    ok = 0;
for(var n = 0; n < G.countries_spisok.length; n++)
    if(G.countries_spisok[n].uid == G.vku.country_id) {
        ok = 1;
        break;
    }

if(ok == 0)
    COUNTRIES.val(1); //если нет, устанавливается Россия

country = COUNTRIES.vkSel({
    width:180,
    spisok:G.countries_spisok,
    func:function (id) {
        city.process();
        VK.api('places.getCities',{country:id}, function (data) {
            var d = data.response;
            for(var n = 0; n < d.length; d[n].uid = d[n].cid, n++);
            d[0].content = '<B>' + d[0].title + '</B>';
            city.spisok(d);
        });
    }
}).o;

city = CITIES.vkSel({
    width:180,
    title0:'Город не указан',
    spisok:G.cities_spisok,
    ro:0,
    funcKeyup:function (val) {
        VK.api('places.getCities',{country:country.val(), q:val}, function (data) {
            for(var n = 0; n < data.response.length; n++) {
                var sp = data.response[n];
                sp.uid = sp.cid;
                sp.content = sp.title + (sp.area ? '<DIV class=pole2>' + sp.area + '</DIV>' : '');
            }
            if(val.length == 0)
                data.response[0].content = '<B>' + data.response[0].title + '</B>';
            city.spisok(data.response);
        });
    }
}).o;

$('#org_name').focus();
$('.vkCancel').click(function() {
    location.href = URL + '&p=wscreate'
});

$('.vkButton').click(function () {
    var inp = $('#devs input'),
        devs = [],
        msg,
        t = $(this);
    for(var n = 0; n < inp.length; n++) {
        var u = inp.eq(n);
        if(u.val() == 1)
            devs.push(u.attr('id'));
    }
    var send = {
        op:'ws_create',
        org_name:$('#org_name').val(),
        country_id:country.val(),
        country_name:country.title(),
        city_id:city.val(),
        city_name:city.title(),
        devs:devs.length > 0 ? devs.join() : false
    };
    if(!send.org_name)
        msg = 'Название организации обязательно для заполнения.';
    else if(send.city_id == 0)
        msg = 'Не указан город, в котором находится Ваша мастерская.';
    else if(!send.devs)
        msg = 'Необходимо выбрать минимум одну категорию устройств.';
    else {
        t.addClass('busy');
        $.post(AJAX_MAIN, send, function(res) {
            t.removeClass('busy');
            if(res.success)
                location.href = URL;
        }, 'json')
    }
    if(msg)
        $('.vkButton').vkHint({
            msg:'<SPAN class="red">' + msg + '</SPAN>',
            top:-57,
            left:205,
            show:1,
            indent:50,
            remove:1
        });
});
