G.countries_spisok = [
	{uid:1,title:'������'},
   	d:2,title:'�������'},
    {u	,title:'��������'},
    {uid:	tle:'���������'},
    {uid:5,t	:'�����������'},
    {uid:6,titl	������'},
    {uid:7,title:'	��'},
    {uid:8,title:'���	'},
    {uid:11,title:'�����	�'},
    {uid:12,title:'������'}	  {uid:13,title:'�����'},
  	id:14,title:'�������'},
   	d:15,title:'�������'},
    {u	6,title:'�����������'},
    {	17,title:'���������'},
    {uid:1	tle:'����������'}
];
G.cities_s	k = [
    {uid:1,title:'������'},
    {uid:2,title:'��	���������'},
    {uid:35,ti	'������� ��������'},
    {uid:10,tit	���������'},
    {uid:49,title:'������	��'},
    {uid:60,title:'������	    {uid:61,title:'�����������'},
	{uid:72,title:'���������'},
	{uid:73,title:'����������'},
    	:87,title:'��������'},
    {uid	title:'������ ��������'},
    {u	9,title:'�����������'},
    {u	04,title:'����'},
    {uid:110,title:	��'},
    {uid:119,title:'������-	���'},
    {uid:123,title:'	��'},
    {uid:125,title:'��	�'},
    {uid:151,title:'���'},
    {	158,title:'���������'}
];
for	 n = 0; n < 2; n++)
    G.citi	pisok[n].content = '<B>' +	ities_spisok[n].title + '</B>';


// �������� ������� ������ 	����
var COUNTRIES = $('#countries'),
    CITIES = $('#cities'),
    country,
    city,
    ok = 0;
for(var n = 0; n < G.countries_spisok.length;
    if(G.countries_spisok	uid == COUNT	D) {
    	ok = 1;
        break;
    }

if(ok == 0)
    COUNTRIES.val(1)	���� ���, ��������������� ������

country = COUNT	.vk	{
    width	,
 	pisok:G.co	ies_spisok,
    fu	unction (id) {
        city.process();
        VK.api('places.getCities',{country:id}	nction (data) 	          var d = data.respons	           for(var n = 0	< d	gth; d[n].uid = d[n	d, 	;
            d[0].content = '<B>' + d[0].title + '</B>';
   	   	ty.	ok(d);
        });
    }
}

c	= C	S.vkSel({
    width:180,
    title0:'����� �� ������',
    	ok:	tie	isok,
    ro:0,
    funcKeyup:function (val) {
	   	api	aces.getCities',{co	y:c	ry.val(	:val}, function (data) {
         	or(var n = 0; 	data.response.length; n++) {
	            var sp = data.r	nse[n];
 	           sp.uid = sp.cid;
  	   	   sp.content = sp.title + (sp.area ? '<DIV class=pole2>' + sp.area + '</DIV>' 	);
	   	 }
            if(val.length == 0)
                	.re	se[	ont	= '<B>' + data.response[0].tit	 '<	;
 	   	city.spisok(data.res	e);	   	;

}).o;

$('#org_name').focus();
$('.vkCancel').click(function() {
    location.href = 	+ '	scr	'
});	'.v	ton	lick(function () {
    	inp	('#	 in	),
        devs = [],
        msg,
        t = $(this);
    for(var n =	n <	.le	; n++) {
        var u = inp.eq
  	  if(u.	) == 1)
            devs.push(u.attr('id'));
    }
    var send = {
  	  op:'ws_create',
        org_name:$('#org_name').val(),
        country_id:cou	.val(),
        country_name:co	y.t	(),
        ci	d:c	val(),
 	   	_name:city.title	        devs:devs.length > 0 ? devs.join(	fal	   };
    if(!send.org	e)
	    msg = '�������� 	���	 ��	����� ��� ����������.';
    	 if(s	city_id == 0)
  	  m	 '�� ������ �����, 	���	�������� ���� ����������.';
    el	f(!	.devs)
        msg = '�������	���	 ������� ���� ��������� ���������	   	e {
        t.addClass(	y')	      $.post(AJAX_MAIN, sen	unc	(res) {
            if(res.success)
          	  loca	.href = URL;
         	lse	             t.removeClass('busy');
        }, 'json')
    }
	if(msg)
        $('.vkButton')	int	           msg:'<SPAN class="red">' + msg + '</SPAN>',
           	:-57,
            left:
  	      show:1,
            indent:50,
            remove:1
        });
});
