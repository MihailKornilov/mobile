G.countries_spisok = [
	{uid:1,title:'Россия'},
   	d:2,title:'Украина'},
    {u	,title:'Беларусь'},
    {uid:	tle:'Казахстан'},
    {uid:5,t	:'Азербайджан'},
    {uid:6,titl	рмения'},
    {uid:7,title:'	ия'},
    {uid:8,title:'Изр	'},
    {uid:11,title:'Кыргы	н'},
    {uid:12,title:'Латвия'}	  {uid:13,title:'Литва'},
  	id:14,title:'Эстония'},
   	d:15,title:'Молдова'},
    {u	6,title:'Таджикистан'},
    {	17,title:'Туркмения'},
    {uid:1	tle:'Узбекистан'}
];
G.cities_s	k = [
    {uid:1,title:'Москва'},
    {uid:2,title:'Са	Петербург'},
    {uid:35,ti	'Великий Новгород'},
    {uid:10,tit	Волгоград'},
    {uid:49,title:'Екатер	рг'},
    {uid:60,title:'Казань	    {uid:61,title:'Калининград'},
	{uid:72,title:'Краснодар'},
	{uid:73,title:'Красноярск'},
    	:87,title:'Мурманск'},
    {uid	title:'Нижний Новгород'},
    {u	9,title:'Новосибирск'},
    {u	04,title:'Омск'},
    {uid:110,title:	мь'},
    {uid:119,title:'Ростов-	ону'},
    {uid:123,title:'	ра'},
    {uid:125,title:'Са	в'},
    {uid:151,title:'Уфа'},
    {	158,title:'Челябинск'}
];
for	 n = 0; n < 2; n++)
    G.citi	pisok[n].content = '<B>' +	ities_spisok[n].title + '</B>';


// проверка наличия страны 	иске
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
    COUNTRIES.val(1)	если нет, устанавливается Россия

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
    title0:'Город не указан',
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
	    msg = 'Название 	низ	 об	ельно для заполнения.';
    	 if(s	city_id == 0)
  	  m	 'Не указан город, 	тор	аходится Ваша мастерская.';
    el	f(!	.devs)
        msg = 'Необход	выб	 минимум одну категорию устройств	   	e {
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
