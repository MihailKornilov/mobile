var cityGet = function(val) {
	$('#cities')._select('process');
	VK.api('places.getCities',{country:$('#countries').val(), q:val}, function (data) {
		for(var n = 0; n < data.response.length; n++) {
			var sp = data.response[n];
			sp.uid = sp.cid;
			sp.content = sp.title + (sp.area ? '<span>' + sp.area + '</span>' : '');
		}
		if(val.length == 0)
			data.response[0].content = '<B>' + data.response[0].title + '</B>';
		$('#cities')._select(data.response);
	});
};

// проверка наличия страны в списке
if(!COUNTRY_ASS[COUNTRY_ID])
	$('#countries').val(1); //если нет, устанавливается Россия


$('#countries')._select({
	width:180,
	spisok:COUNTRY_SPISOK,
	func:function (id) {
		$('#cities')
			._select(0)
			._select('process');
		VK.api('places.getCities',{country:id}, function (data) {
			var d = data.response;
			for(n = 0; n < d.length; n++)
				d[n].uid = d[n].cid;
			d[0].content = '<b>' + d[0].title + '</b>';
			$('#cities')._select(d);
		});
	}
});
$('#cities')._select({
	width:180,
	title0:'Город не указан',
	spisok:CITY_SPISOK,
	write:1,
	funcKeyup:cityGet
});

if(COUNTRY_ASS[COUNTRY_ID])
	cityGet('');


$('#org_name').focus();
$('.vkCancel').click(function() {
	location.href = URL + '&p=wscreate'
});

$('.vkButton').click(function () {
	var t = $(this),
		inp = $('#devs input'),
		devs = [];
	for(var n = 0; n < inp.length; n++) {
		var u = inp.eq(n);
		if(u.val() == 1)
			devs.push(u.attr('id'));
	}
	var send = {
		op:'ws_create',
		org_name:$('#org_name').val(),
		country_id:$('#countries').val(),
		country_name:$('#countries')._select('title'),
		city_id:$('#cities').val(),
		city_name:$('#cities')._select('title'),
		devs:devs.length ? devs.join() : false
	};
	if(!send.org_name)
		err('Название организации обязательно для заполнения.');
	else if(send.city_id == 0)
		err('Не указан город, в котором находится Ваша мастерская.');
	else if(!send.devs)
		err('Необходимо выбрать минимум одну категорию устройств.');
	else {
		t.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				location.href = URL;
			else
				t.removeClass('busy');
		}, 'json')
	}
	function err(msg) {
		$('.vkButton').vkHint({
			msg:'<span class="red">' + msg + '</span>',
			top:-57,
			left:205,
			show:1,
			indent:50,
			remove:1
		});
	}
});
