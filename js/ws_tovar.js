var AJAX_TOVAR = APP_HTML + '/ajax/ws_tovar.php?' + VALUES,

	tovarFilter = function(v) {
		v = v || {};
		v = {
			op:'tovar_spisok',
			device_id: v.device_id ? v.device_id : $('#dev_device').val(),
			vendor_id: v.vendor_id ? v.vendor_id : $('#dev_vendor').val(),
			model_id:$('#dev_model').val(),
			avai:$('#avai').val()
		};

		_cookie(VIEWER_ID + '_tovar_device_id', v.device_id);
		_cookie(VIEWER_ID + '_tovar_vendor_id', v.vendor_id);
		_cookie(VIEWER_ID + '_tovar_avai', v.avai);

		return v;
	},
	tovarSpisok = function() {
		$('#mainLinks').addClass('busy');
		$.post(AJAX_TOVAR, tovarFilter(), function(res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('.result').html(res.result);
				$('.left').html(res.html);
			}
		}, 'json');
	},

	tovarAvai = function(id) {
		var dialog = _dialog({
				top:30,
				width:560,
				head:'Подробности наличия товара',
				load:1,
				butSubmit:'',
				butCancel:'Закрыть'
			}),
			send = {
				op:'tovar_avai_show',
				tovar_id:id
			};

		$.post(AJAX_TOVAR, send, function(res) {
			if(res.success) {
				dialog.content.html(res.html);
			} else
				dialog.loadError();
		}, 'json');
	};

$(document)
	.on('click', '#tovar-add', function() {
		var t = $(this),
			html =
				'<table id="tovar-add-tab">' +
					'<tr><td class="label r">Категория:<td><input type="hidden" id="category_id" />' +
					'<tr><td class="label r topi">Устройство:<td><div id="dev-add"></div>' +
					'<tr><td class="label r">Наименование:<td><input type="text" id="name" />' +
				'</table>',
			dialog = _dialog({
				top:40,
				width:400,
				head:'Внесение нового товара в каталог',
				content:html,
				submit:submit
			});

		$('#category_id')._select({
			width:200,
			title0:'Без категории',
			spisok:TOVAR_CATEGORY_SPISOK
		});
		$('#dev-add').device({
			width:250,
			no_model:1,
			add:1
		});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'tovar_add',
				device_id:_num($('#dev-add_device').val()),
				vendor_id:_num($('#dev-add_vendor').val()),
				name:$.trim($('#name').val())
			};
			if(!send.device_id)
				dialog.err('Не выбрано устройство');
			else if(!send.vendor_id)
				dialog.err('Не выбран производитель');
			else if(!send.name)
				dialog.err('Не указано наименование');
			else {
				dialog.process();
				$.post(AJAX_TOVAR, send, function(res) {
					if(res.success) {
						_msg('Внесение нового товара произведено');
						dialog.close();
						tovarFilter({
							device_id:send.device_id,
							vendor_id:send.vendor_id
						});
						location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}

	})

	.on('click', '#tovar_next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = tovarFilter();
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_TOVAR, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#tovar #filter-clear', function() {
		$('#dev').device({
			width:155,
			type_no:1,
			device_multiselect:1,
			no_model:1,
			func:tovarSpisok
		});
		$('#avai')._check(0);
		tovarFilter();
		location.reload();
	})
	.on('click', '#tovar .avai_add', function() {
		var t = $(this),
			tovar_id = t.attr('val'),
			html =
			'<div id="tovar-avai-add">' +
				'<table class="tb">' +
					'<tr><td class="label">Наименование:<td>' + $('#u' + tovar_id).val() +
					'<tr><td class="label">Количество:<td><input type="text" id="count" maxlength="7" value="1" />' +
					'<tr><td class="label">Цвет:<td id="colors">' +
					'<tr><td class="label">Б/у:<td><input type="hidden" id="bu" />' +
					'<tr><td class="label">Цена закупки за ед.:<td><input type="text" id="cena" class="money" maxlength="10"> руб.' +
				'</table>' +
			'</div>',
			dialog = _dialog({
				width:440,
				head:'Внесение наличия товара',
				content:html,
				submit:submit
			});

		_valueColors();
		$('#count').focus();
		$('#count,#cena').keyEnter(submit);
		$('#bu')._check();
		function submit() {
			var send = {
				op:'tovar_avai_add',
				id: tovar_id,
				count:_num($('#count').val()),
				cena:$('#cena').val(),
				color_id:$('#color_id').val(),
				color_dop:$('#color_dop').val(),
				bu:$('#bu').val()
			};
			if (!send.count) {
				dialog.err('Некорректно указано количество');
				$('#count').focus();
			} else if(send.cena != 0 && !_cena(send.cena)) {
				dialog.err('Некорректно указана цена закупки');
				$('#cena').focus();
			} else {
				dialog.process();
				$.post(AJAX_TOVAR, send, function(res) {
					if(res.success) {
						dialog.close();
						tovarSpisok();
						tovarAvai(tovar_id);
					} else
						dialog.abort();
				}, 'json');
			}
		}

	})

	.on('click', '#tovar-catalog a.dev', function() {
		_cookie(VIEWER_ID + '_tovar_device_id', $(this).attr('val'));
		location.reload();
	})
	.on('click', '.tovar-unit .avai', function() {
		var t = $(this);
		tovarAvai(t.attr('val'));
	})

	.ready(function() {
		if($('#tovar-catalog').length) {
			$('#find')._search({
				width: 153,
				focus: 1,
				txt: 'Быстрый поиск...',
				enter: 1,
				func: tovarSpisok
			});
		}
		if($('#tovar').length) {
			$('#find')._search({
				width: 153,
				focus: 1,
				txt: 'Быстрый поиск...',
				enter: 1,
				func: tovarSpisok
			});
			$('#dev').device({
				width: 155,
				type_no: 1,
				device_id:T.device_id,
				vendor_id:T.vendor_id,
				device_multiselect:1,
				no_model:1,
				func: tovarSpisok
			});
			$('#avai')._check(tovarSpisok);
		}
	});
