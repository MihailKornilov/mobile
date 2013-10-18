G.countries_spisok = [
    {uid:1,title:'������'},
    {uid:2,title:'�������'},
    {uid:3,title:'��������'},
    {uid:4,title:'���������'},
    {uid:5,title:'�����������'},
    {uid:6,title:'�������'},
    {uid:7,title:'������'},
    {uid:8,title:'�������'},
    {uid:11,title:'����������'},
    {uid:12,title:'������'},
    {uid:13,title:'�����'},
    {uid:14,title:'�������'},
    {uid:15,title:'�������'},
    {uid:16,title:'�����������'},
    {uid:17,title:'���������'},
    {uid:18,title:'����������'}
];
G.cities_spisok = [
    {uid:1,title:'������'},
    {uid:2,title:'�����-���������'},
    {uid:35,title:'������� ��������'},
    {uid:10,title:'���������'},
    {uid:49,title:'������������'},
    {uid:60,title:'������'},
    {uid:61,title:'�����������'},
    {uid:72,title:'���������'},
    {uid:73,title:'����������'},
    {uid:87,title:'��������'},
    {uid:95,title:'������ ��������'},
    {uid:99,title:'�����������'},
    {uid:104,title:'����'},
    {uid:110,title:'�����'},
    {uid:119,title:'������-��-����'},
    {uid:123,title:'������'},
    {uid:125,title:'�������'},
    {uid:151,title:'���'},
    {uid:158,title:'���������'}
];
for(var n = 0; n < 2; n++)
    G.cities_spisok[n].content = '<B>' + G.cities_spisok[n].title + '</B>';


// �������� ������� ������ � ������
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
    COUNTRIES.val(1); //���� ���, ��������������� ������

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
    title0:'����� �� ������',
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
        msg = '�������� ����������� ����������� ��� ����������.';
    else if(send.city_id == 0)
        msg = '�� ������ �����, � ������� ��������� ���� ����������.';
    else if(!send.devs)
        msg = '���������� ������� ������� ���� ��������� ���������.';
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
