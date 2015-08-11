var AJAX_WS = APP_HTML + '/ajax/ws.php?' + VALUES,
	AJAX_SA = APP_HTML + '/ajax/sa.php?' + VALUES,
	scannerWord = '',
	scannerTime = 0,
	scannerTimer,
	scannerDialog,
	scannerDialogShow = false,
	charSpisok = {
		48:'0',
		49:1,
		50:2,
		51:3,
		52:4,
		53:5,
		54:6,
		55:7,
		56:8,
		57:9,
		65:'A',
		66:'B',
		67:'C',
		68:'D',
		69:'E',
		70:'F',
		71:'G',
		72:'H',
		73:'I',
		74:'J',
		75:'K',
		76:'L',
		77:'M',
		78:'N',
		79:'O',
		80:'P',
		81:'Q',
		82:'R',
		83:'S',
		84:'T',
		85:'U',
		86:'V',
		87:'W',
		88:'X',
		89:'Y',
		90:'Z',
		189:'-'
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
			$.post(AJAX_WS, send, function(res) {
				if(res.success)
					 dev.html(res.img)
						.find('img').on('load', function() {
							$(this).show().parent().removeClass('busy');
						});
			}, 'json');
		}
	},

	_valueColors = function(color_id, color_dop) {
		var colors = $('#colors');
		if(!colors.length)
			return false;
		colors.html(
			'<input type="hidden" id="color_id" value="' + _num(color_id) + '" />' +
			'<tt>-</tt>' +
			'<input type="hidden" id="color_dop" value="' + _num(color_dop) + '" />'
		);
		$('#color_id')._select({
			width:120,
			title0:'Цвет не указан',
			spisok:COLOR_SPISOK,
			func:_valueColorSet
		});
		_valueColorSet(color_id);
	},
	_valueColorSet = function(v) {
		$('#colors').find('tt')[v ? 'show' : 'hide']();
		$('#color_dop')._select(!v ? 'remove' :  {
			width:120,
			title0:'Цвет не указан',
			spisok:COLOR_SPISOK,
			func:function(id) {
				$('#color_id')
					._select(id ? COLORPRE_SPISOK : COLOR_SPISOK)
					._select($('#color_id').val());
			}
		});
	},

	zayavFilter = function() {
		var v = {
				op:'zayav_spisok',
				find:$('#find')._search('val'),
				sort:$('#sort').val(),
				desc:$('#desc').val(),
				status:$('#status').val(),
				finish:$('#day_finish').val(),
				diff:$('#diff').val(),
				zpzakaz:$('#zpzakaz').val(),
				executer:$('#executer').val(),
				device:$('#dev_device').val(),
				vendor:$('#dev_vendor').val(),
				model:$('#dev_model').val(),
				diagnost:$('#diagnost').val(),
				place:$('#device_place').val()
			},
			loc = '';
		if(v.status != '1')
			v.finish = '0000-00-00';
		if(v.sort != '1') loc += '.sort=' + v.sort;
		if(v.desc != '0') loc += '.desc=' + v.desc;
		if(v.find) loc += '.find=' + escape(v.find);
		else {
			if(v.status > 0) loc += '.status=' + v.status;
			if(v.finish != '0000-00-00') loc += '.finish=' + v.finish;
			if(v.diff > 0) loc += '.diff=1';
			if(v.zpzakaz > 0) loc += '.zpzakaz=' + v.zpzakaz;
			if(v.executer > 0) loc += '.executer=' + v.executer;
			if(v.device > 0) loc += '.device=' + v.device;
			if(v.vendor > 0) loc += '.vendor=' + v.vendor;
			if(v.model > 0) loc += '.model=' + v.model;
			if(v.diagnost > 0) loc += '.diagnost=1';
			if(v.place != 0) loc += '.place=' + v.place;
		}
		VK.callMethod('setLocation', hashLoc + loc);

		_cookie(VIEWER_ID + '_zayav_find', escape(v.find));
		_cookie(VIEWER_ID + '_zayav_sort', v.sort);
		_cookie(VIEWER_ID + '_zayav_desc', v.desc);
		_cookie(VIEWER_ID + '_zayav_status', v.status);
		_cookie(VIEWER_ID + '_zayav_finish', v.finish);
		_cookie(VIEWER_ID + '_zayav_diff', v.diff);
		_cookie(VIEWER_ID + '_zayav_zpzakaz', v.zpzakaz);
		_cookie(VIEWER_ID + '_zayav_executer', v.executer);
		_cookie(VIEWER_ID + '_zayav_device', v.device);
		_cookie(VIEWER_ID + '_zayav_vendor', v.vendor);
		_cookie(VIEWER_ID + '_zayav_model', v.model);
		_cookie(VIEWER_ID + '_zayav_diagnost', v.diagnost);
		_cookie(VIEWER_ID + '_zayav_place', escape(v.place));

		return v;
	},
	zayavSpisok = function() {
		var send = zayavFilter();
		$('.condLost')[(send.find ? 'add' : 'remove') + 'Class']('hide');

		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			$('.result').html(res.all);
			$('#spisok').html(res.html);
			$('#mainLinks').removeClass('busy');
		}, 'json');
	},
	zayavMoneyUpdate = function() {//обновление информации о платежах
		var send = {
			op:'zayav_money_update',
			zayav_id:ZAYAV.id
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				$('b.acc').html(res.acc);
				$('.acc_tr')[(!res.acc ? 'add' : 'remove') + 'Class']('dn');
				$('b.op').html(res.opl);
				$('.op_tr')[(!res.opl ? 'add' : 'remove') + 'Class']('dn');
				$('.dopl')
					[(!res.diff ? 'add' : 'remove') + 'Class']('dn')
					.html((res.diff > 0 ? '+' : '') + res.diff);
				//обновление расходов по заявке
				$('.ze-spisok').remove();
				$('#ze_acc').html(res.acc_sum).after(res.html);
				ZAYAV.expense = res.array;
				ZAYAV.worker_zp = res.worker_zp;
			}
		}, 'json');
	},
	zayavDevSelect = function(dev) {
		modelImageGet(dev);
		if(dev.device_id == 0) {
			$('.equip_spisok').html('');
			$('.tr_equip').addClass('dn');
		} else if(dev.vendor_id == 0 && dev.model_id == 0) {
			var send = {
				op:'equip_check_get',
				device_id:dev.device_id
			};
			$.post(AJAX_WS, send, function(res) {
				if(res.spisok) {
					$('.equip_spisok').html(res.spisok);
					$('.tr_equip').removeClass('dn');
				} else {
					$('.equip_spisok').html('');
					$('.tr_equip').addClass('dn');
				}
			}, 'json');
		}
	},
	zayavPlace = function() {
		if(!window.PLACE_OTHER) {
			DEVPLACE_SPISOK.push({
				uid:0,
				title:'<div id="place-other-div">другое:<input type="text" id="place_other" class="dn" /></div>'
			});
			window.PLACE_OTHER = 1;
		}
		$('#place')._radio({
			spisok:DEVPLACE_SPISOK,
			light:1,
			func:function(val) {
				$('#place_other')[(val ? 'add' : 'remove') + 'Class']('dn');
				if(!val)
					$('#place_other').val('').focus();
			}
		});
	},
	kvitHtml = function(id) {
		var params =
			'scrollbars=yes,' +
			'resizable=yes,' +
			'status=no,' +
			'location=no,' +
			'toolbar=no,' +
			'menubar=no,' +
			'width=680,' +
			'height=500,' +
			'left=20,' +
			'top=20';
		window.open(APP_HTML + '/view/kvit_html.php?' + VALUES + '&id=' + id, 'kvit', params);
	},

	cartridgeNew = function(id, callback) {
		var t = $(this),
			html = '<table id="cartridge-new-tab">' +
				'<tr><td class="label r">Вид:<td><input type="hidden" id="type_id" value="1" />' +
				'<tr><td class="label r"><b>Модель картриджа:</b><td><input type="text" id="name" />' +
				'<tr><td class="label r">Заправка:<td><input type="text" id="cost_filling" class="money" maxlength="11" /> руб.' +
				'<tr><td class="label r">Восстановление:<td><input type="text" id="cost_restore" class="money" maxlength="11" /> руб.' +
				'<tr><td class="label r">Замена чипа:<td><input type="text" id="cost_chip" class="money" maxlength="11" /> руб.' +
				'</table>',
			dialog = _dialog({
				top:20,
				head:'Добавление нового картриджа',
				content:html,
				submit:submit
			});
		$('#type_id')._select({
			spisok:CARTRIDGE_TYPE
		});
		$('#name').focus();
		$('#name,#cost_filling,#cost_restore,#cost_chip').keyEnter(submit);
		function submit() {
			var send = {
				op:'cartridge_new',
				type_id:$('#type_id').val(),
				name:$('#name').val(),
				cost_filling:_num($('#cost_filling').val()),
				cost_restore:_num($('#cost_restore').val()),
				cost_chip:_num($('#cost_chip').val()),
				from:$('#setup-service-cartridge').length ? 'setup' : ''
			};
			if(!send.name) {
				dialog.err('Не указано наименование');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						if(send.from == 'setup')
							$('#spisok').html(res.spisok);
						else {
							CARTRIDGE_SPISOK = res.spisok;
							$('#' + id)._select(res.spisok);
							$('#' + id)._select(res.insert_id);
							callback(res.insert_id);
						}
						dialog.close();
						_msg('Внесено!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	},
	zayavCartridgeAdd = function() {
		if(!window.CLIENT)
			CLIENT = {
				id:0,
				fio:''
			};
		var html =
				'<table id="cartridge-add-tab">' +
					'<tr><td class="label">Клиент:' +
						'<td><input type="hidden" id="client_id" value="' + CLIENT.id + '" />' +
							'<b>' + CLIENT.fio + '</b>' +
					'<tr><td class="label"><b>Количество картриджей:</b><td><input type="text" id="count" /> шт.' +
					'<tr><td class="label topi">Расчёт:<td><input type="hidden" id="pay_type" />' +
					'<tr><td class="label topi">Список картриджей:<td id="crt">' +
					'<tr><td class="label top">Заметка:<td><textarea id="comm"></textarea>' +
				'</table>',
			dialog = _dialog({
				width:470,
				top:30,
				head:'Новая заявка на заправку картриджей',
				content:html,
				submit:submit
			});
		if(!CLIENT.id)
			$('#client_id').clientSel({add:1});
		$('#count').focus();
		$('#pay_type')._radio({
			light:1,
			spisok:PAY_TYPE
		});
		$('#crt').cartridge();
		$('#comm').autosize();
		function submit() {
			var send = {
				op:'zayav_cartridge_add',
				client_id:_num($('#client_id').val()),
				count:_num($('#count').val()),
				pay_type:_num($('#pay_type').val()),
				ids:$('#crt').cartridge('get'),
				comm:$('#comm').val()
			};
			if(!send.client_id) dialog.err('Не указан клиент');
			else if(!send.count) {
				dialog.err('Не указано количество картриджей');
				$('#count').focus();
			} else if(!send.pay_type) dialog.err('Укажите вид расчёта');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Заявка внесена');
						location.href = URL + '&p=zayav&d=info&id=' + res.id + '&from=cartridge';
					} else
						dialog.abort();
				}, 'json');
			}
		}
	},
	cartridgeFilter = function() {
		var v = {
			op:'zayav_cartridge_spisok',
			sort:$('#sort').val(),
			desc:$('#desc').val(),
			status:$('#status').val(),
			paytype:$('#paytype').val(),
			noschet:$('#noschet').val()
		};

		_cookie(VIEWER_ID + '_cart_sort', v.sort);
		_cookie(VIEWER_ID + '_cart_desc', v.desc);
		_cookie(VIEWER_ID + '_cart_status', v.status);
		_cookie(VIEWER_ID + '_cart_paytype', v.paytype);
		_cookie(VIEWER_ID + '_cart_noschet', v.noschet);

		return v;
	},
	cartridgeSpisok = function() {
		var send = cartridgeFilter();
		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, send, function(res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('.result').html(res.all);
				$('#spisok').html(res.html);
			}
		}, 'json');
	},
	zayavInfoCartridgeAdd = function() {
		var html =
				'<table id="cartridge-add-tab">' +
					'<tr><td class="label topi">Список картриджей:<td id="crt">' +
				'</table>',
			dialog = _dialog({
				width:470,
				top:30,
				head:'Добавление картриджей к заявке',
				content:html,
				submit:submit
			});
		$('#crt').cartridge();
		function submit() {
			var send = {
				op:'zayav_info_cartridge_add',
				zayav_id:ZAYAV.id,
				ids:$('#crt').cartridge('get')
			};
			if(!send.ids) dialog.err('Не выбрано ни одного картриджа');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						$('#cart-tab').html(res.html);
						_msg('Внесено.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	},


	zpNameSelect = function(id, name_id) {
		if(!window.ZPNAME)
			window.ZPNAME = {};
		ZPNAME.device_id = id;
		$('#name_id')._select({
			width:230,
			funcAdd:id ? zpNameAdd : null,
			title0:id ? 'Наименование запчасти не выбрано' : 'Сначала выберите устройство',
			spisok:ZPNAME_SPISOK[id]
		})._select(name_id || 0);
	},
	zpNameAdd = function() {//внесение новой запчасти
		var html =
				'<table id="zpname-add-tab">' +
					'<tr><td class="label">Устройство:<td><b>' + DEV_ASS[ZPNAME.device_id] + '</b>' +
					'<tr><td class="label">Запчасть:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'Добавление нового наименования запчасти',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'zpname_add',
				device_id:ZPNAME.device_id,
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.err('Не указана запчасть');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						if($('.sa-equip').length)
							$('#zp-spisok').html(res.zp);
						else {
							ZPNAME_SPISOK[ZPNAME.device_id].push({
								uid:res.id,
								title:send.name
							});
							$('#name_id')
								._select(ZPNAME_SPISOK[ZPNAME.device_id])
								._select(res.id);
						}
						dialog.close();
						_msg('Внесено!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	};

$.fn.device = function(o) {
	o = $.extend({
		width:150,
		func:function() {},
		func_device:function() {},
		type_no:0,
		device_id:0,
		vendor_id:0,
		model_id:0,
		device_multiselect:0,
		device_ids:null, // список id, которые нужно выводить в списке для устройств
		vendor_ids:null, // для производителей
		model_ids:null,  // для моделей
		add:0,
		device_funcAdd:null, // функции пусты, если нельзя добавлять новые элементы
		vendor_funcAdd:null,
		model_funcAdd:null,
		no_model:0 //не выводить список моделей (для товаров)
	},o);

	var t = $(this),
		id = t.attr('id'),
		html = '<input type="hidden" id="' + id + '_device" value="' + o.device_id + '">' +
			'<input type="hidden" id="' + id + '_vendor" value="' + o.vendor_id + '">' +
			'<input type="hidden" id="' + id + '_model" value="' + o.model_id + '">',
		device_no = ['Устройство не выбрано','Любое устройство'],
		vendor_no = ['Производитель не выбран','Любой производитель'],
		model_no = ['Модель не выбрана','Любая модель'],
		dialog;
	t.html(html);
	var devSel = $('#' + id + '_device'),
		venSel = $('#' + id + '_vendor'),
		modSel = $('#' + id + '_model');

	// создание нового списка устройств, которые нужно выводить в списке
	if(o.device_ids) {
		DEV_SPISOK = [];
		for(n = 0; n < o.device_ids.length; n++) {
			var uid = o.device_ids[n];
			DEV_SPISOK.push({uid:uid, title:DEV_ASS[uid]});
		}
	}

	// создание нового списка производителей, которые нужно выводить в списке
	if(o.vendor_ids) {
		var vendors = {};
		for(k in VENDOR_SPISOK) {
			for(n = 0; n < VENDOR_SPISOK[k].length; n++) {
				var sp = VENDOR_SPISOK[k][n];
				if(o.vendor_ids.indexOf(sp.uid) >= 0) {
					if(vendors[k] == undefined)
						vendors[k] = [];
					vendors[k].push(sp);
				}
			}
		}
		VENDOR_SPISOK = vendors;
	}

	// создание нового списка моделей, которые нужно выводить в списке
	if(o.model_ids) {
		var models = {};
		for(k in MODEL_SPISOK)
			for(n = 0; n < MODEL_SPISOK[k].length; n++) {
				var sp = MODEL_SPISOK[k][n];
				if(o.model_ids.indexOf(sp.uid) >= 0) {
					if(!models[k])
						models[k] = [];
					models[k].push(sp);
				}
			}
		MODEL_SPISOK = models;
	}

	// добавление новых устройств
	if(o.add > 0) {
		o.device_funcAdd = function() {
			var html = '<table class="device-add-tab">' +
				'<tr><td class="label">Название:<td><input type="text" id="daname">' +
				'</table>';
			dialog = _dialog({
				width:300,
				head:'Добавление нoвого устройства',
				content:html,
				submit:deviceAddSubmit
			});
			$('#daname')
				.focus()
				.keyEnter(deviceAddSubmit);
		};
		o.vendor_funcAdd = function () {
			var html ='<table class="device-add-tab">' +
				'<tr><td class="label">Название:<td><input type="text" id="vaname">' +
				'</table>';
			dialog = _dialog({
				width:300,
				head:'Добавление нoвого производителя',
				content:html,
				submit:vendorAddSubmit
			});
			$('#vaname')
				.focus()
				.keyEnter(vendorAddSubmit);
		};
		o.model_funcAdd = function(){
			var html = '<table class="device-add-tab">' +
				'<tr><td class="label">Название:<td><input type="text" id="maname">' +
				'</table>';
			dialog = _dialog({
				width:300,
				head:'Добавление нoвой модели',
				content:html,
				submit:modelAddSubmit,
				focus:'#model_name'
			});
			$('#maname')
				.focus()
				.keyEnter(modelAddSubmit);
		};
	}

	function deviceAddSubmit() {
		var send = {
			op:'base_device_add',
			name:$('#daname').focus().val()
		};
		if(!send.name)
			addHint('Не указано название устройства.');
		else if(name_test(DEV_SPISOK, send.name))
			addHint();
		else {
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				dialog.abort();
				if(res.success) {
					DEV_SPISOK.push({uid:res.id, title:send.name});
					DEV_ASS[res.id] = name;
					devSel
						._select(DEV_SPISOK)
						._select(res.id);
					getVendor(0);
					modSel.val(0)._select('remove'); //Удаляется селект модели и устанавливается в 0
					o.func(getIds());
					dialog.close();
				}
			} ,'json');
		}
	}
	function vendorAddSubmit() {
		var dv = devSel.val(),
			send = {
			op:'base_vendor_add',
			device_id:dv,
			name:$('#vaname').focus().val()
		};
		if(!send.name)
			addHint('Не указано название производителя.');
		else if(name_test(VENDOR_SPISOK[dv], send.name))
			addHint();
		else {
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				dialog.abort();
				if(res.success) {
					// если у устройства нет производителей, сначала создаётся пустой массив
					if(!VENDOR_SPISOK[dv])
						VENDOR_SPISOK[dv] = [];
					VENDOR_SPISOK[dv].push({uid:res.id, title:send.name});
					VENDOR_ASS[res.id] = send.name;
					venSel
						._select(VENDOR_SPISOK[dv])
						._select(res.id);
					getModel(0);
					dialog.close();
				}
			}, 'json');
		}
	}
	function modelAddSubmit() {
		var vv = venSel.val(),
			send = {
			op:'base_model_add',
			device_id:devSel.val(),
			vendor_id:vv,
			name:$('#maname').focus().val()
		};
		if(!send.name)
			addHint('Не указано название модели.');
		else if(name_test(MODEL_SPISOK[vv], send.name)) {
			addHint();
		} else {
			dialog.process();
			$.post(AJAX_WS, send, function (res) {
				dialog.abort();
				if(res.success) {
					// если у производителя нет моделей, сначала создаётся пустой массив
					if(!MODEL_SPISOK[vv])
						MODEL_SPISOK[vv] = [];
					MODEL_SPISOK[vv].push({uid:res.id, title:send.name});
					MODEL_ASS[res.id] = send.name;
					modSel
						._select(MODEL_SPISOK[vv])
						._select(res.id);
					dialog.close();
				}
			}, 'json');
		}
	}
	function addHint(msg) {
		msg = msg || 'Такое название уже есть в списке.';
		dialog.bottom.vkHint({
			msg:'<SPAN class="red">' + msg + '</SPAN>',
			top:-47,
			left:53,
			indent:50,
			show:1,
			remove:1
		});
	}

	// вывод списка устройств
	devSel._select({
		width:o.width,
		block:1,
		multiselect:o.device_multiselect,
		title0:device_no[o.type_no],
		spisok:DEV_SPISOK,
		func:function(device_id) {
			venSel.val(0);
			modSel.val(0)._select('remove'); //Удаляется селект модели и устанавливается в 0, если был ранее
			var id = devSel.val();
			if(id == '0' || id.split(',').length > 1)
				venSel._select('remove');
			else
				getVendor(0);
			o.func(getIds());
			o.func_device(device_id);
		},
		funcAdd:o.device_funcAdd,
		bottom:3
	});

	if(typeof o.device_id == 'number' && o.device_id || o.device_id != '0' && o.device_id.split(',').length == 1)
		getVendor();

	// вывод списка производителей
	function getVendor(vendor_id) {
		if(vendor_id != undefined)
			o.vendor_id = vendor_id; // изменяется значение производителя, если нужно
		venSel.val(o.vendor_id);
		venSel._select({
			width:o.width,
			block:1,
			title0:vendor_no[o.type_no],
			spisok:VENDOR_SPISOK[devSel.val()], // значение устройства получено из его объекта
			func:function(id) {
				modSel.val(0);
				if(!id)
					modSel._select('remove'); //Удаляется селект модели и устанавливается в 0
				else
					getModel(0);
				o.func(getIds());
			},
			funcAdd:o.vendor_funcAdd,
			bottom:3
		});
		venSel._select(o.vendor_id);
		if(o.vendor_id)
			getModel();
	}

	// вывод списка моделей
	function getModel(model_id) {
		if(o.no_model)
			return;
		if(model_id != undefined)
			o.model_id = model_id; //Изменяется значение модели, если нужно
		modSel.val(o.model_id);
		modSel._select({
			width:o.width,
			block:1,
			write:1,
			title0:model_no[o.type_no],
			spisok:MODEL_SPISOK[venSel.val()],
			limit:50,
			funcAdd:o.model_funcAdd,
			bottom:10,
			func:function() { o.func(getIds()); }
		});
		modSel._select(o.model_id);
	}

	// проверка на совпадение имени при внесении нового элемента
	function name_test(spisok, name) {
		if(spisok) {
		name = name.toLowerCase();
		for(var n = 0; n < spisok.length; n++)
			if(spisok[n].title.toLowerCase() == name)
				return true;
		}
		return false;
	}

	function getIds() {
		return {
			device_id:devSel.val(),
			vendor_id:venSel.val(),
			model_id:modSel.val()
		};
	}
};
$.fn.zayavExpense = function(o) {
	var t = $(this),
		id = t.attr('id'),
		num = 1,
		n;

	if(typeof o == 'string') {
		if(o == 'get') {
			var units = t.find('.ptab'),
				send = [];
			for(n = 0; n < units.length; n++) {
				var u = units.eq(n),
					attr = id + u.attr('val'),
					cat_id = $('#' + attr + 'cat').val(),
					sum = u.find('.zesum').val(),
					dop = '';
				if(cat_id == 0)
					continue;
				if(!_cena(sum) && sum != '0')
					return 'sum_error';
				if(ZE_TXT[cat_id])
					dop = u.find('.zetxt').val();
				else if(ZE_WORKER[cat_id]) {
					dop = $('#' + attr + 'worker').val();
				} else if(ZE_ZP[cat_id])
					dop = $('#' + attr + 'zp').val();

				send.push(cat_id + ':' +
						  dop + ':' +
						  sum);
			}
			return send.join();
		}
	}

	t.html('<div id="_ze-edit"></div>');
	var ze = t.find('#_ze-edit');

	if(typeof o == 'object')
		for(n = 0; n < o.length; n++)
			itemAdd(o[n])

	itemAdd();

	function itemAdd(v) {
		if(!v)
			v = [
				0, //0 - категория
				'',//1 - описание, id сотрудника или id запчасти
				'' //2 - сумма
			];
		var attr = id + num,
			attr_cat = attr + 'cat',
			attr_worker = attr + 'worker',
			attr_zp = attr + 'zp',
			html =
				'<table id="ptab'+ num + '" class="ptab" val="' + num + '"><tr>' +
					'<td><input type="hidden" id="' + attr_cat + '" value="' + v[0] + '" />' +
					'<td class="tddop">' +
						(v[0] && ZE_TXT[v[0]] ? '<input type="text" class="zetxt" placeholder="описание не указано" tabindex="' + (num * 10 - 1) + '" value="' + v[1] + '" />' : '') +
						(v[0] && ZE_WORKER[v[0]] ? '<input type="hidden" id="' + attr_worker + '" value="' + v[1] + '" />' : '') +
						(v[0] && ZE_ZP[v[0]] ? '<input type="hidden" id="' + attr_zp + '" value="' + v[1] + '" />' : '') +
					'<td class="tdsum' + (v[0] ? '' : ' dn') + '">' +
						'<input type="text" class="zesum" maxlength="6" tabindex="' + (num * 10) + '" value="' + v[2] + '" />руб.' +
				'</table>';
		ze.append(html);
		var ptab = $('#ptab' + num),
			tddop = ptab.find('.tddop'),
			zesum = ptab.find('.zesum');
		$('#' + attr_cat)._select({
			width:130,
			disabled:0,
			title0:'Категория',
			spisok:ZE_SPISOK,
			func:function(id) {
				ptab.find('.tdsum')[(id ? 'remove' : 'add') + 'Class']('dn');
				if(ZE_TXT[id]) {
					tddop.html('<input type="text" class="zetxt" placeholder="описание не указано" tabindex="' + (num * 10 - 11) + '" />');
					tddop.find('.zetxt').focus();
				} else if(ZE_WORKER[id]) {
					tddop.html('<input type="hidden" id="' + attr_worker + '" />');
					$('#' + attr_worker)._select({
						width:240,
						title0:'Сотрудник не указан',
						spisok:WORKER_SPISOK,
						func:function(v) {
							zesum.focus();
						}
					});
					zesum.focus();
				} else if(ZE_ZP[id]) {
					tddop.html('<input type="hidden" id="' + attr_zp + '" />');
					$('#' + attr_zp)._select({
						width:240,
						title0:'Запчасть не выбрана',
						spisok:ZAYAV.zp_avai,
						func:function(v) {
							zesum.focus();
						}
					});
					zesum.focus();
				} else {
					tddop.html('');
					zesum.focus();
				}
				zesum.val(id == 1 ? ZAYAV.worker_zp : '');
				if(id && !ptab.next().hasClass('ptab'))
					itemAdd();
			}
		});
		if(v[0] && ZE_WORKER[v[0]])
			$('#' + attr_worker)._select({
				width:240,
				disabled:0,
				title0:'Сотрудник',
				spisok:WORKER_SPISOK,
				func:function(v) {
					zesum.focus();
				}
			});
		if(v[0] && ZE_ZP[v[0]])
			$('#' + attr_zp)._select({
				width:240,
				title0:'Запчасть не выбрана',
				spisok:ZAYAV.zp_avai,
				func:function(v) {
					zesum.focus();
				}
			});
		num++;
	}
	return t;
};
$.fn.cartridge = function(o) {
	var t = $(this),
		id = t.attr('id'),
		num = 1,
		n;

	if(typeof o == 'string') {
		if(o == 'get') {
			var units = t.find('.icar'),
				send = [],
				v;
			for(n = 0; n < units.length; n++) {
				v = units.eq(n).val();
				if(v == 0)
					continue;
				send.push(v);
			}
			return send.join();
		}
	}
	if(typeof o == 'object')
		for(var i = 0; i < o.length; i++) {
			add(o[i]);
			num++;
		}

	add();
	function add(v) {
		t.append('<input type="hidden" class="icar" id="car' + num + '" ' + (v ? 'value="' + v + '" ' : '') + '/>');
		$('#car' + num)._select({
			width:170,
			bottom:4,
			title0:'картридж не выбран',
			write:1,
			spisok:CARTRIDGE_SPISOK,
			func:add_test,
			funcAdd:function(id) {
				cartridgeNew(id, add_test);
			}
		});
	}
	function add_test(v) {//проверка, все ли картриджи выбраны, затем добавлять новое поле
		if(!v)
			return;
		var units = t.find('.icar');
		for(n = 0; n < units.length; n++)
			if(units.eq(n).val() == 0)
				return;
		num++;
		add();
	}
};

$(document)
	.keydown(function(e) {
		if(scannerDialogShow)
			return;
//		if($('#scanner').length < 1)$('body').prepend('<div id="scanner"></div>');window.sc = $('#scanner');
		if(e.keyCode == 13) {
			var d = (new Date()).getTime(),
				time = d - scannerTime;
			if(scannerWord.length > 5 && time < 300) {
				scannerDialogShow = true;
				scannerTimer = setTimeout(timeStop, 500);
				scannerDialog = _dialog({
					head:'Сканер штрих-кода',
					width:250,
					content:'Получен код: <b>' + scannerWord + '</b>',
					butSubmit:'Поиск'
				});
				var send = {
					op:'scanner_word',
					word:scannerWord
				};
				scannerDialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						if(e.target.localName == 'input') {
							scannerDialog.close();
							return;
						}
						if(_cookie('p') == 'zayav' && _cookie('d') == 'add') {
							$('#' + (res.imei ? 'imei' : 'serial')).val(send.word);
							scannerDialog.close();
							return;
						}
						if(res.zayav_id)
							document.location.href = URL + '&p=zayav&d=info&id=' + res.zayav_id;
						else {
							var client_id = _cookie('p') == 'client' && _cookie('d') == 'info' ? _cookie('id') : 0;
							document.location.href =
								URL +
								'&p=zayav&d=add&' + (res.imei ? 'imei' : 'serial') + '=' + send.word +
								(client_id ? '&back=client&id=' + client_id : '');
						}
					} else
						scannerDialog.abort();
				}, 'json');
			}
//			sc.append('<br /> - Enter<br />len = ' + scannerWord.length + '<br />time = ' + time + '<br />');
		} else {
			if(scannerDialog) {
				scannerDialog.close();
				scannerDialog = undefined;
			}
			if(scannerTimer)
				clearTimeout(scannerTimer);
			scannerTimer = setTimeout(timeStop, 500);
			if(!scannerWord)
				scannerTime = (new Date()).getTime();
			scannerWord += charSpisok[e.keyCode] ||  '';
//			sc.append((charSpisok[e.keyCode] ||  '') + ' = ' + e.keyCode + ' - ' + ((new Date()).getTime() - scannerTime) + '<br />');
		}
		function timeStop() {
			scannerWord = '';
			scannerTime = 0;
			if(scannerTimer)
				clearTimeout(scannerTimer);
			scannerTimer = undefined;
			scannerDialogShow = false;
//			sc.append('<br /> - Clear<br />');
		}
	})


	.on('mouseenter', '.zayav_link', function(e) {
		var t = $(this),
			tooltip = t.find('.tooltip');
		if(!tooltip.hasClass('empty'))
			return;
		var send = {
			op:'zayav_tooltip',
			id:t.attr('val')
		};
		$.post(AJAX_WS, send, function(res) {
			if(e.clientY < 90)
				tooltip.css('top', '12px');
			tooltip
				.html(res.html)
				.removeClass('empty');
		}, 'json');
	})
	.on('keyup', '#zayavNomer', function() {
		var t = $(this);
		if(t.hasClass('_busy'))
			return;
		t.next('.zayavNomerTab').remove().end()
		 .addClass('_busy');
		var send = {
			op:'zayav_nomer_info',
			nomer:t.val()
		};
		$.post(AJAX_WS, send, function(res) {
			t.removeClass('_busy');
			if(res.success)
				t.after(res.html);
		}, 'json');
	})

	.on('click', '.day-finish-link', function(e) {//открытие календаря ремонтов
		e.stopPropagation();
		var t = $(this),
			save = t.hasClass('no-save') ? 0 : 1;
		if(t.hasClass('_busy'))
			return;
		var dialog = _dialog({
				top:40,
				width:480,
				head:'Календарь ремонтов',
				load:1,
				butSubmit:''
			}),
			send = {
				op:'zayav_day_finish',
				day:$('#day_finish').val(),
				zayav_spisok:$('#zayav').length
			};
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				dialog.content.html(res.html);
			else
				dialog.loadError();
		}, 'json');
		$(document)
			.off('click', '#zayav-finish-calendar td.d:not(.old),#fc-cancel,.fc-old-sel')
			.on('click', '#zayav-finish-calendar td.d:not(.old),#fc-cancel,.fc-old-sel', function() {
				if(t.hasClass('_busy'))
					return;
				dialog.close();
				t.addClass('_busy');
				send = {
					op:'zayav_day_finish_save',
					day:$(this).attr('val'),
					zayav_id:window.ZAYAV ? ZAYAV.id : 0,
					save:save
				};
				$.post(AJAX_WS, send, function(res) {
					t.removeClass('_busy');
					if(res.success) {
						t.prev('input').val(send.day);
						t.find('span').html(res.data);
						if($('#zayav').length)
							zayavSpisok();
					}
				}, 'json');
			});
	})
	.on('click', '#zayav-finish-calendar .ch', function() {//перемотка календаря ремонтов
		if($('#fc-head').hasClass('_busy'))
			return;
		$('#fc-head').addClass('_busy');
		var send = {
			op:'zayav_day_finish_next',
			mon:$(this).attr('val'),
			day:$('#day_finish').val(),
			zayav_spisok:$('#zayav').length
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				$('#zayav-finish-calendar').after(res.html).remove();
			else
				$('#fc-head').removeClass('_busy');
		}, 'json');
	})

	.on('click', '#zayav-add', function() {
		var t = $(this),
			back = t.attr('val');
		if(t.hasClass('cartridge')) {
			var html =
				'<div id="zayav-add-tab">' +
					'<div class="unit" id="go-device">' +
						'Приём в ремонт<br />оборудования' +
					'</div>' +
					'<div class="unit" id="cartridge-add">' +
						'Заправка, восстановление<br />картриджей' +
					'</div>' +
				'</div>',
				dialog = _dialog({
					top:30,
					width:300,
					head:'Новая заявка',
					content:html,
					butSubmit:''
				});
			$('#go-device').click(goDevice);
			$('#cartridge-add').click(function() {
				dialog.close();
				zayavCartridgeAdd();
			});
		} else
			goDevice();
		function goDevice() {
			location.href = URL + '&p=zayav&d=add&back=' + back;
		}
	})

	.on('click', '#zayav ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = zayavFilter();
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.zayav_unit', function() {
		_cookie('zback_scroll', VK_SCROLL);
		var t = $(this),
			from = t.hasClass('cart') ? '&from=cartridge' : '';
		location.href = URL + '&p=zayav&d=info&id=' + t.attr('val') + from;
	})
	.on('mouseenter', '.zayav_unit', function() {
		var t = $(this),
			msg = t.find('.note').html();
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
	.on('click', '#zayav #sort_radio div', zayavSpisok)
	.on('click', '#zayav #zpzakaz_radio div', zayavSpisok)
	.on('click', '#zayav .clear', function() {
		$('#find')._search('clear');
		$('#sort')._radio(1);
		$('#desc')._check(0);
		$('#status').rightLink(0);
		$('#day_finish').val('0000-00-00');
		$('.day-finish-link span').html('не указан');
		$('#diagnost')._check(0);
		$('#diff')._check(0);
		$('#zpzakaz')._radio(0);
		$('#executer')._select(0);
		$('#dev').device({
			width:155,
			type_no:1,
			device_ids:Z.device_ids,
			vendor_ids:Z.vendor_ids,
			model_ids:Z.model_ids,
			device_multiselect:1,
			func:zayavSpisok
		});
		$('#device_place')._select(0);
		zayavSpisok();
	})

	.on('click', '#zayav-cartridge ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = cartridgeFilter();
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#zayav-cartridge .clear', function() {
		$('#sort')._radio(1);
		$('#desc')._check(0);
		$('#status').rightLink(0);
		$('#paytype')._radio(0);
		$('#noschet')._check(0);
		cartridgeSpisok();
	})

	.on('click', '#zayav-info .zedit', function() {
		var html = '<table class="zayav-info-edit">' +
			'<tr><td class="label r">Клиент:		<td><input type="hidden" id="client_id" value="' + ZAYAV.client_id + '">' +
			'<tr><td class="label r top">Устройство:<td><table><td id="dev"><td id="device_image"></table>' +
			'<tr><td><td>' + ZAYAV.images +
			'<tr><td class="label r">IMEI:		  <td><input type="text" id="imei" maxlength="20" value="' + ZAYAV.imei + '">' +
			'<tr><td class="label r">Серийный номер:<td><input type="text" id="serial" maxlength="30" value="' + ZAYAV.serial + '">' +
			'<tr><td class="label r">Цвет:<td id="colors">' +
			'<tr class="tr_equip' + (ZAYAV.equip ? '' : ' dn') + '">' +
				'<td class="label r top">Комплектация:<td class="equip_spisok">' + ZAYAV.equip +
			'<tr><td class="label r">Диагностика:<td><input TYPE="hidden" id="diagnost" value="' + ZAYAV.diagnost + '" />' +
			'<tr><td class="label">Стоимость ремонта:<td><input type="text" class="money" id="pre_cost" maxlength="11" value="' + (ZAYAV.pre_cost ? ZAYAV.pre_cost : '') + '" /> руб.' +
		'</table>',
			dialog = _dialog({
				width:420,
				top:30,
				head:'Заявка №' + ZAYAV.nomer + ' - Редактирование',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#client_id').clientSel({width:267});
		$('#client_id_select').vkHint({
			msg:'Если изменяется клиент, то начисления и платежи заявки применяются на нового клиента.',
			width:200,
			top:-83,
			left:-2,
			delayShow:1500
		});
		$('#dev').device({
			width:190,
			device_id:ZAYAV.device,
			vendor_id:ZAYAV.vendor,
			model_id:ZAYAV.model,
			add:1,
			func:zayavDevSelect
		});
		modelImageGet();
		imageSortable();
		_valueColors(ZAYAV.color_id, ZAYAV.color_dop);
		$('#diagnost')._check();

		function submit() {
			var msg,
				send = {
					op:'zayav_edit',
					zayav_id:ZAYAV.id,
					client_id:$('#client_id').val(),
					device:$('#dev_device').val(),
					vendor:$('#dev_vendor').val(),
					model:$('#dev_model').val(),
					imei: $.trim($('#imei').val()),
					serial:$.trim($('#serial').val()),
					color_id:$('#color_id').val(),
					color_dop:$('#color_dop').val(),
					equip:'',
					diagnost:$('#diagnost').val(),
					pre_cost:$('#pre_cost').val()
				};
			if(!$('.tr_equip').hasClass('dn')) {
				var inp = $('.equip_spisok input'),
					arr = [];
				for(var n = 0; n < inp.length; n++) {
					var eq = inp.eq(n);
					if(eq.val() == 1)
						arr.push(eq.attr('id').split('_')[1]);
				}
				send.equip = arr.join();
			}
			if(send.deivce == 0) msg = 'Не выбрано устройство';
			else if(send.client_id == 0) msg = 'Не выбран клиент';
			else if(send.pre_cost && !REGEXP_NUMERIC.test(send.pre_cost)) {
				msg = 'Некорректно указана предварительная стоимость';
				$('#pre_cost').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Данные изменены!');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					top:-47,
					left:107,
					show:1,
					remove:1
				});
		}
	})
	.on('click', '#zayav-info .schet-add', function() {
		var t = $(this),
			spisok,// содержимое таблицы
			num,// очередной номер элемента таблицы
			dialog = _dialog({
				top:20,
				width:580,
				head:'Формирование счёта',
				load:1,
				butSubmit:'Сформировать',
				submit:submit
			});
		var send = {
			op:'zayav_cartridge_schet_load',
			zayav_id:ZAYAV.id
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				spisok = res.spisok;
				var html =
					'<div id="schet-add-tab">' +
						'<table class="_spisok" id="schet-tab"></table>' +
						'<div id="itog"></div>' +
						'<table id="sa-tab">' +
							'<tr><td class="label r">Дата:<td><input id="date_create" type="hidden" />' +
							'<tr><td class="label r topi">Приложения:<td><input id="dop" type="hidden" />' +
							'<tr><td><td><input id="acc" type="hidden" />' +
						'</table>' +
					'</div>';

				dialog.content.html(html);
				spisokPrint();
				$('#date_create')._calendar({lost:1});
				$('#dop')._radio({
					light:1,
					spisok:[
						{uid:1,title:'Накладная'},
						{uid:2,title:'Акт выполненных работ'}
					]
				});
				$('#acc')._check({
					name:'Произвести начисление по счёту'
				});
			} else
				dialog.loadError();
		}, 'json');
		function spisokPrint() {
			var html =
					'<tr><th>№' +
						'<th>Наименование товара' +
						'<th>Кол-во' +
						'<th>Цена' +
						'<th>Сумма' +
						'<th>',
				sum = 0;
			for(var n = 0; n < spisok.length; n++) {
				var sp = spisok[n],
				s = sp.cost * sp.count;
				html +=
					'<tr><td class="td-n">' + (n + 1) +
						'<td class="td-name">' + sp.name +
						'<td class="td-count">' + sp.count +
						'<td class="td-cost">' + sp.cost +
						'<td class="td-sum">' + s +
						'<td>' + (sp.del ? '<div val="' + n + '" class="img_del pole-del' + _tooltip('Удалить', -29) + '</div>' : '');
				sum += s;
			}
			num = n + 1;
			html += '<tr><td colspan="6" class="_next" id="pole-add">Добавить позицию для счёта';
			$('#schet-tab').html(html);
			$('#itog').html('Всего наименований ' + n + ', на сумму ' + sum + ' руб.');
			$('#pole-add').click(poleAdd);
			$('.pole-del').click(function() {
				spisok.splice(_num($(this).attr('val')), 1);
				spisokPrint();
			});
		}
		function poleAdd() {
			var t = $(this),
				html =
					'<tr id="tr-add">' +
						'<td class="td-n">' + num +
						'<td class="td-name"><input type="text" id="name" />' +
						'<td class="td-count"><input type="text" id="count" value="1" />' +
						'<td class="td-cost"><input type="text" id="cost" />' +
						'<td><div class="vkButton"><button>OK</button></div>' +
						'<td><div class="img_del' + _tooltip('Отменить', -32) + '</div>';
			t.parent().hide();
			$('#schet-tab').append(html);
			$('#name').focus();
			$('#tr-add .img_del').click(function() {
				$('#tr-add').remove();
				t.parent().show();
			});
			$('#tr-add .vkButton').click(poleSubmit);
		}
		function poleSubmit() {
			var name = $.trim($('#name').val()),
				count = _num($('#count').val()),
				cost = _num($('#cost').val());
			if(!name) {
				poleErr('Не указано наименование');
				$('#name').focus();
				return;
			}
			if(!count) {
				poleErr('Некорректная сумма');
				$('#count').focus();
				return;
			}
			if(!cost) {
				poleErr('Некорректное количество');
				$('#cost').focus();
				return;
			}
			spisok.push({
				name:name,
				count:count,
				cost:cost,
				del:1
			});
			spisokPrint();
		}
		function poleErr(msg) {
			$('#name').vkHint({
				msg:'<span class="red">' + msg + '</span>',
				remove:1,
				indent:40,
				show:1,
				top:-58,
				left:404
			});
		}
		function submit() {
			var send = {
				op:'zayav_cartridge_schet_add',
				zayav_id:ZAYAV.id,
				spisok:spisok,
				date_create:$('#date_create').val(),
				dop:_num($('#dop').val()),
				acc:_num($('#acc').val())
			};
			if(!send.spisok.length) dialog.err('Не добавлено ни одной позиции');
			else if(!send.dop) dialog.err('Выберите дополнительный документ');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function (res) {
					if(res.success) {
						$('#cart-tab').html(res.cart);
						$('#schet-spisok').html(res.schet);
						$('#money_spisok').html(res.acc);
						dialog.close();
						_msg('Новый счёт сформирован');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-info .acc_add', function() {
		var html = '<table class="zayav_accrual_add">' +
				'<tr><td class="label">Сумма: <td><input type="text" id="sum" class="money" maxlength="5" /> руб.' +
				'<tr><td class="label">Примечание:<em>(не обязательно)</em><td><input type="text" id="prim" maxlength="100" />' +
				'<tr><td class="label">Статус заявки: <td><input type="hidden" id="acc_status" value="2" />' +
				'<tr><td class="label">Добавить напоминание:<td><input type="hidden" id="acc_remind" />' +
			'</table>' +

			'<table class="zayav_accrual_add remind">' +
				'<tr><td class="label">Содержание:<td><input type="text" id="reminder_txt" value="Позвонить и сообщить о готовности.">' +
				'<tr><td class="label">Дата:<td><input type="hidden" id="reminder_day">' +
			'</table>';
		var dialog = _dialog({
			top:60,
			width:420,
			head:'Заявка №' + ZAYAV.nomer + ' - Начисление за выполненную работу',
			content:html,
			submit:submit
		});
		$('#sum').focus();
		$('#sum,#prim,#reminder_txt').keyEnter(submit);
		$('#acc_status')._dropdown({spisok:STATUS});
		$('#acc_remind')._check();
		$('#acc_remind_check').click(function(id) {
			$('.zayav_accrual_add.remind').toggle();
		});
		$('#reminder_day')._calendar();

		function submit() {
			var msg,
				send = {
					op:'zayav_accrual_add',
					zayav_id:ZAYAV.id,
					sum:$('#sum').val(),
					prim:$('#prim').val(),
					status:$('#acc_status').val(),
					remind:$('#acc_remind').val(),
					remind_txt:$('#reminder_txt').val(),
					remind_day:$('#reminder_day').val()
				};
			if(!REGEXP_NUMERIC.test(send.sum)) { msg = 'Некорректно указана сумма.'; $('#sum').focus(); }
			else if(send.remind == 1 && !send.remind_txt) { msg = 'Не указан текст напоминания'; $('#reminder_txt').focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('Начисление успешно произведено!');
						$('#money_spisok').html(res.html);
						zayavMoneyUpdate();
						if(res.status) {
							$('#status')
								.html(res.status.name)
								.css('background-color', '#' + res.status.color);
							$('#status_dtime').html(res.status.dtime);
						}
						if(res.remind)
							$('#remind-spisok').html(res.remind);
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
					show:1
				});
		}
	})
	.on('click', '#zayav-info .acc_del', function() {
		var send = {
			op:'zayav_accrual_del',
			id:$(this).attr('val')
		};
		var tr = $(this).parent().parent();
		tr.html('<td colspan="4" class="deleting">Удаление...');
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				tr.find('.deleting').html('Начисление удалено. <a class="acc_rest" val="' + send.id + '">Восстановить</a>');
				zayavMoneyUpdate();
			}
		}, 'json');
	})
	.on('click', '#zayav-info .acc_rest', function() {
		var send = {
				op:'zayav_accrual_rest',
				id:$(this).attr('val')
			},
			t = $(this),
			tr = t.parent().parent();
		t.remove();
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				tr.after(res.html).remove();
				zayavMoneyUpdate();
			}
		}, 'json');
	})
	.on('click', '#zayav-info .status_place', function() {
		var html =
			'<div id="zayav-status">' +
		(ZAYAV.status != 1 ?
				'<div class="st c1" val="1">' +
					'Ожидает выполнения' +
					'<div class="about">Возобновление работы по заявке.</div>' +
				'</div>'
		: '') +
		(ZAYAV.status != 2 ?
				'<div class="st c2" val="2">' +
					'Выполнено' +
					'<div class="about">' +
						'Заявка выполнена успешно.<br />' +
						'Не забудьте расписать расходы по заявке, проверьте начисления.<br />' +
						'Добавьте напоминание, если необходимо.' +
					'</div>' +
				'</div>'
		: '') +
		(ZAYAV.status != 3 ?
				'<div class="st c3" val="3">' +
					'Заявка отменена' +
					'<div class="about">Отмена заявки по какой-либо причине.</div>' +
				'</div>'
		: '') +
				'<input type="hidden" id="zs-status" />' +
				'<table id="zs-tab">' +
					'<tr><td class="label r topi">Местонахождение устройства:<td><input type="hidden" id="place" value="-1" />' +
					'<tr id="zs-srok" class="dn">' +
						'<td class="label r">Срок выполнения:' +
						'<td><input type="hidden" id="zs-day_finish" value="0000-00-00" />' +
							'<div class="day-finish-link no-save"><span>не указан</span></div>' +
				'</table>' +

			'</div>',

			dialog = _dialog({
				top:30,
				width:420,
				head:'Изменение статуса заявки',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		zayavPlace();
		$('.st').click(function() {
			var t = $(this),
				v = t.attr('val');
			t.parent().find('.st').hide();
			t.show();
			$('#zs-status').val(v);
			$('#zs-tab').show();
			if(v == 1)
				$('#zs-srok').removeClass('dn');
		});


		function submit() {
			var send = {
				op:'zayav_status_place',
				zayav_id:ZAYAV.id,
				status:$('#zs-status').val() * 1,
				place:$('#place').val() * 1,
				place_other:$('#place_other').val(),
				day_finish:$('#zs-day_finish').val()
			};
			if(send.dev_place > 0)
				send.place_other = '';
			if(!send.status)
				dialog.err('Выберите статус заявки');
			else if(send.place == -1 || send.place == 0 && send.place_other == '') {
				dialog.err('Не указано местонахождение устройства');
				$('#place_other').focus();
			} else if(send.status == 1 && send.day_finish == '0000-00-00')
				dialog.err('Не указан срок выполнения');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Изменения сохранены.');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-info .cartridge_status', function() {
		var html =
				'<div id="zayav-status">' +
					(ZAYAV.status != 1 ?
						'<div class="st c1" val="1">' +
							'Ожидает выполнения' +
							'<div class="about">Возобновление работы по заявке.</div>' +
						'</div>'
					: '') +
					(ZAYAV.status != 2 ?
						'<div class="st c2" val="2">' +
							'Выполнено' +
							'<div class="about">' +
								'Заявка выполнена успешно.<br />' +
								'Не забудьте расписать расходы по заявке, проверьте начисления.<br />' +
								'Добавьте напоминание, если необходимо.' +
							'</div>' +
						'</div>'
					: '') +
					(ZAYAV.status != 3 ?
						'<div class="st c3" val="3">' +
							'Заявка отменена' +
							'<div class="about">Отмена заявки по какой-либо причине.</div>' +
						'</div>'
					: '') +
						'<input type="hidden" id="zs-status" />' +
				'</div>',

			dialog = _dialog({
				top:30,
				width:420,
				head:'Изменение статуса заявки',
				content:html,
				butSubmit:'',
				submit:submit
			});
		$('.st').click(function() {
			var t = $(this),
				v = t.attr('val');
				$('#zs-status').val(v);
				submit();
		});


		function submit() {
			var send = {
				op: 'zayav_cartridge_status',
				zayav_id: ZAYAV.id,
				status: _num($('#zs-status').val())
			};
			dialog.process();
			$.post(AJAX_WS, send, function (res) {
				if(res.success) {
					dialog.close();
					_msg('Изменения сохранены.');
					document.location.reload();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '#zayav-info .dev_place', function() {
		var html =
				'<table class="device-add-tab">' +
					'<tr><td class="label r topi">Местонахождение устройства:<td><input type="hidden" id="place" value="-1" />' +
				'</table>',

			dialog = _dialog({
				head:'Изменение местонахождения устройства',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		zayavPlace(ZAYAV.place_other);

		function submit() {
			var send = {
				op:'zayav_device_place',
				zayav_id:ZAYAV.id,
				place:$('#place').val() * 1,
				place_other:$('#place_other').val()
			};
			if(send.dev_place > 0)
				send.place_other = '';
			if(send.place == -1 || send.place == 0 && send.place_other == '') {
				err('Не указано местонахождение устройства');
				$('#place_other').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Изменения сохранены.');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<span class="red">' + msg + '</SPAN>',
				top:-47,
				left:80,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '#zayav-info .zakaz', function() {
		var t = $(this),
			send = {
				op:'zayav_zp_zakaz',
				zayav_id:ZAYAV.id,
				zp_id:t.parent().parent().attr('val')
			};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				t.html('Заказано!').attr('class', 'zakaz_ok');
				_msg(res.msg);
			}
		}, 'json');
	})
	.on('click', '#zayav-info .zakaz_ok', function() {
		location.href = URL + '&p=zp&menu=3';
	})
	.on('click', '#zayav-info .zpAdd', function() {
		var html = '<div class="zayav_zp_add">' +
				'<center>Добавление запчасти к устройству<br />' +
					'<b>' +
						DEV_ASS[ZAYAV.device] + ' ' +
						VENDOR_ASS[ZAYAV.vendor] + ' ' +
						MODEL_ASS[ZAYAV.model] +
					'</b>.'+
				'</center>' +
				'<table style="border-spacing:6px">' +
					'<tr><td class="label r">Наименование запчасти:<td><input type="hidden" id="name_id" />' +
					'<tr><td class="label r">Версия:<td><input type="text" id="version" />' +
					'<tr><td class="label r">Цвет:<td><input type="hidden" id="color_id" />' +
				'</table>' +
			'</div>',
			dialog = _dialog({
				top:40,
				width:400,
				head:'Внесение новой запчасти',
				content:html,
				submit:submit
			});

		zpNameSelect(ZAYAV.device);
		$('#color_id')._select({
			width:130,
			title0:'Цвет не указан',
			spisok:COLOR_SPISOK
		});
		function submit() {
			var send = {
				op:'zayav_zp_add',
				zayav_id: ZAYAV.id,
				name_id:_num($('#name_id').val()),
				version:$('#version').val(),
				color_id:$('#color_id').val()
			};
			if(!send.name_id)
				dialog.err('Не выбрано наименование запчасти');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						_msg('Внесение запчасти произведено');
						dialog.close();
						$('#zpSpisok').html(res.html);
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-info .set', function() {
		var unit = $(this).parent().parent();
		var html = '<center class="zayav_zp_set">' +
			'Установка запчасти<br />' + unit.find('a:first').html() + '.<br />' +
			(unit.find('.color').length > 0 ? unit.find('.color').html() + '.<br />' : '') +
			'<br />Информация об установке также' +
			'<br />будет добавлена в заметки' +
			'<br />и в расходы по заявке.' +
		'</center>',
		dialog = _dialog({
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
				zayav_id:ZAYAV.id
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					zayavMoneyUpdate();
					dialog.close();
					_msg('Установка запчасти произведена.');
					unit.parent().html(res.zp_unit);
					$('.vkComment').after(res.comment).remove();
				} else
					dialog.abort();
			},'json');
		}
	})
	.on('click', '.zayav_kvit', function() {
		kvitHtml($(this).attr('val'));
	})

	.on('click', '#zayav-info .zc-edit', function() {//редактирование заявки с картриджами
		var html =
				'<table id="cartridge-add-tab">' +
					'<tr><td class="label">Клиент:' +
						'<td><input type="hidden" id="client_id" value="' + ZAYAV.client_id + '" />' +
					'<tr><td class="label"><b>Количество картриджей:</b><td><input type="text" id="count" value="' + ZAYAV.cartridge_count + '" /> шт.' +
					'<tr><td class="label topi">Расчёт:<td><input type="hidden" id="pay_type" value="' + ZAYAV.pay_type + '" />' +
				'</table>',
			dialog = _dialog({
				width:470,
				head:'Редактирование заявки',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#client_id').clientSel({add:1});
		$('#pay_type')._radio({
			light:1,
			spisok:PAY_TYPE
		});
		function submit() {
			var send = {
				op:'zayav_cartridge_edit',
				zayav_id:ZAYAV.id,
				client_id:_num($('#client_id').val()),
				count:_num($('#count').val()),
				pay_type:_num($('#pay_type').val())
			};
			if(!send.client_id) dialog.err('Не указан клиент');
			else if(!send.count) dialog.err('Не указано количество картриджей');
			else if(!send.pay_type) dialog.err('Укажите вид расчёта');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Заявка изменена');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-info #cart-add', zayavInfoCartridgeAdd)
	.on('click', '#zayav-info .cart-edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var id = t.attr('val'),
			cart_id = t.find('.cart_id').val(),
			filling = t.find('.filling').val(),
			restore = t.find('.restore').val(),
			chip = t.find('.chip').val(),
			cost = t.find('.cost').html(),
			prim = t.find('u').html(),
			html =
				'<table id="cart-edit-tab">' +
					'<tr><td class="label">Картридж:<td><input type="hidden" id="cart_id" value="' + cart_id + '" />' +
					'<tr><td class="label topi">Действие:' +
						'<td><input type="hidden" id="filling" value="' + filling + '" />' +
							'<input type="hidden" id="restore" value="' + restore + '" />' +
							'<input type="hidden" id="chip" value="' + chip + '" />' +
					'<tr><td class="label">Стоимость работ:<td><input type="text" class="money" id="cost" value="' + cost + '" /> руб.' +
					'<tr><td class="label">Примечание:<td><input type="text" id="prim" value="' + prim + '" />' +
				'</table>',
			dialog = _dialog({
				width:470,
				top:30,
				head:'Действия по картриджу',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#cart_id')._select({
			write:1,
			spisok:CARTRIDGE_SPISOK,
			func:costSet
		});
		$('#filling')._check({name:'Заправка',func:costSet});
		$('#restore')._check({name:'Восстановление',func:costSet});
		$('#chip')._check({name:'Замена чипа',func:costSet});
		function costSet() {
			var c = 0,
				cart_id = _num($('#cart_id').val());
			if($('#filling').val() == 1)
				c = CARTRIDGE_FILLING[cart_id];
			if($('#restore').val() == 1)
				c += CARTRIDGE_RESTORE[cart_id];
			if($('#chip').val() == 1)
				c += CARTRIDGE_CHIP[cart_id];
			$('#cost').val(c);
		}
		function submit() {
			var send = {
				op:'zayav_info_cartridge_edit',
				id:id,
				cart_id:_num($('#cart_id').val()),
				filling:$('#filling').val(),
				restore:$('#restore').val(),
				chip:$('#chip').val(),
				cost:$('#cost').val(),
				prim:$('#prim').val()
			};
			if(!send.cart_id) dialog.err('Не выбран картридж');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						$('#cart-tab').html(res.html);
						_msg('Изменено.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-info .cart-del', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var id = t.attr('val'),
			dialog = _dialog({
				head:'Удаление картриджа',
				content:'<center>Подтвердите удаление картриджа.</center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			var send = {
				op:'zayav_info_cartridge_del',
				id:id
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					$('#cart-tab').html(res.html);
					_msg('Удалено.');
				} else
					dialog.abort();
				}, 'json');
		}
	})

	.ready(function() {
		if($('#zayav').length) {
			$('#find')
				.vkHint({
					msg: 'Поиск производится по<br />совпадению в названии<br />модели, imei и серийном<br />номере.',
					ugol: 'right',
					top: -9,
					left: -178,
					delayShow: 800
				})
				._search({
					width: 153,
					focus: 1,
					txt: 'Быстрый поиск...',
					enter: 1,
					func: zayavSpisok
				})
				.inp(Z.find);
			$('#desc')._check(zayavSpisok);
			$('#status').rightLink(zayavSpisok);
			$('#diagnost')._check(zayavSpisok);
			$('#diff')._check(zayavSpisok);
			WORKER_SPISOK.push({uid: -1, title: 'Не назначен', content: '<b>Не назначен</b>'});
			$('#executer')._select({
				width: 155,
				title0: 'не указан',
				spisok: WORKER_SPISOK,
				func: zayavSpisok
			});
			$('#dev').device({
				width: 155,
				type_no: 1,
				device_id: Z.device_id,
				vendor_id: Z.vendor_id,
				model_id: Z.model_id,
				device_multiselect: 1,
				device_ids: Z.device_ids,
				vendor_ids: Z.vendor_ids,
				model_ids: Z.model_ids,
				func: zayavSpisok
			});
			// Нахождение устройства
			$('#device_place')._select({
				width: 155,
				title0: 'Любое местонахождение',
				spisok: DEVPLACE_SPISOK,
				func: zayavSpisok
			});
			//подсвечивание просмотренной заявки
			if(Z.cookie_id) {
				VK.callMethod('scrollWindow', _cookie('zback_scroll'));
				$('#u' + Z.cookie_id).css('opacity', 0.1).delay(400).animate({opacity: 1}, 700);
			}
			zayavFilter();
		}
		if($('#zayavAdd').length) {
			$('#client_id').clientSel({add: 1});
			$('#dev').device({
				width: 190,
				add: 1,
				device_ids: WS_DEVS,
				func: zayavDevSelect
			});
			zayavPlace();
			_valueColors(0);
			$('#comm').autosize();
			$('.vkCancel').click(function () {
				location.href = URL + '&p=' + $(this).attr('val');
			});
			$('.vkButton').click(function () {
				var t = $(this),
					send = {
						op: 'zayav_add',
						client_id: $('#client_id').val(),
						device: $('#dev_device').val(),
						vendor: $('#dev_vendor').val(),
						model: $('#dev_model').val(),
						equip: '',
						place: $('#place').val(),
						place_other: $('#place_other').val(),
						imei: $('#imei').val(),
						serial: $('#serial').val(),
						color: $('#color_id').val(),
						color_dop: $('#color_dop').val(),
						diagnost: $('#diagnost').val(),
						comm: $('#comm').val(),
						pre_cost: $('#pre_cost').val(),
						day_finish: $('#day_finish').val()
					};
				if(t.hasClass('busy'))
					return;
				if(!$('.tr_equip').hasClass('dn')) {
					var inp = $('.equip_spisok input'),
						arr = [];
					for (var n = 0; n < inp.length; n++) {
						var eq = inp.eq(n);
						if(eq.val() == 1)
							arr.push(eq.attr('id').split('_')[1]);
					}
					send.equip = arr.join();
				}
				var msg = '';
				if(send.client_id == 0) msg = 'Не выбран клиент';
				else if(send.device == 0) msg = 'Не выбрано устройство';
				else if(send.place == '-1' || send.place == 0 && !send.place_other) msg = 'Не указано местонахождение устройства';
				else if(send.pre_cost && (!REGEXP_NUMERIC.test(send.pre_cost) || send.pre_cost == 0)) {
					msg = 'Некорректно указана предварительная стоимость';
					$('#pre_cost').focus();
				} else if(send.day_finish == '0000-00-00') msg = 'Не указан срок выполнения ремонта';
				else {
					if(send.place > 0) send.place_other = '';
					t.addClass('busy');
					$.post(AJAX_WS, send, function (res) {
						if(res.success)
							location.href = URL + '&p=zayav&d=info&id=' + res.id;
						else
							t.removeClass('busy');
					}, 'json');
				}

				if(msg)
					$(this).vkHint({
						msg: '<SPAN class="red">' + msg + '</SPAN>',
						top: -48,
						left: 201,
						indent: 30,
						remove: 1,
						show: 1
					});
			});
		}
		if($('#zayav-info').length) {
			$('.hist').click(function () {
				$('#dopLinks .sel').removeClass('sel');
				$(this).addClass('sel');
				$('.itab').addClass('h');
			});
			$('.info').click(function () {
				$('#dopLinks .sel').removeClass('sel');
				$(this).addClass('sel');
				$('.itab').removeClass('h');
			});
			$('.delete')
				.click(function () {
					var dialog = _dialog({
						top: 110,
						width: 250,
						head: 'Удаление заявки',
						content: '<center>Подтвердите удаление заявки.</center>',
						butSubmit: 'Удалить',
						submit: function () {
							var send = {
								op: 'zayav_delete',
								zayav_id: ZAYAV.id
							};
							dialog.process();
							$.post(AJAX_WS, send, function (res) {
								if(res.success)
									location.href = URL + '&p=client&d=info&id=' + res.client_id;
								else
									dialog.abort();
							}, 'json');
						}
					});
				})
				.vkHint({
					msg: 'Заявку можно удалить при отсутствии платежей и начислений. Также удаляются все задачи к этой заявке.',
					width: 150,
					ugol: 'top',
					top: 39,
					left: 457,
					indent: 'right'
				});
			$('.img_print').click(function () {
				var html = '<table class="zayav-print">' +
						'<tr><td class="label">Дата приёма:<td>' + PRINT.dtime +
						'<tr><td class="label top">Устройство:<td>' + PRINT.device +
						'<tr><td class="label">Цвет:<td>' + (PRINT.color ? PRINT.color : '<i>не указан</i>') +
						'<tr><td class="label">IMEI:<td>' + (PRINT.imei ? PRINT.imei : '<i>не указан</i>') +
						'<tr><td class="label">Серийный номер:<td>' + (PRINT.serial ? PRINT.serial : '<i>не указан</i>') +
						'<tr><td class="label">Комплектация:<td>' + (PRINT.equip ? PRINT.equip : '<i>не указана</i>') +
						'<tr><td class="label">Заказчик:<td><b>' + PRINT.client + '</b>' +
						'<tr><td class="label">Телефон:<td>' + (PRINT.telefon ? PRINT.telefon : '<i>не указан</i>') +
						'<tr><td class="label top">Неисправность:<td><textarea id="defect">' + PRINT.defect + '</textarea>' +
						'<tr><td colspan="2"><a id="preview"><span>Предварительный просмотр квитанции</span></a>' +
						'</table>',
					dialog = _dialog({
						width: 380,
						top: 30,
						head: 'Заявка №' + ZAYAV.nomer + ' - Формирование квитанции',
						content: html,
						butSubmit: 'Сохранить квитанцию',
						submit: submit
					});
				$('#defect').focus().autosize();
				$('#preview').click(function () {
					var t = $(this),
						send = {
							op: 'zayav_kvit',
							zayav_id: ZAYAV.id,
							defect: $.trim($('#defect').val())
						};
					if(t.hasClass('_busy'))
						return;
					if(!send.defect) err(1);
					else {
						t.addClass('_busy');
						$.post(AJAX_WS, send, function (res) {
							t.removeClass('_busy');
							if(res.success)
								kvitHtml(res.id);
						}, 'json');
					}
				});
				function submit() {
					var send = {
						op: 'zayav_kvit',
						zayav_id: ZAYAV.id,
						defect: $.trim($('#defect').val()),
						active: 1
					};
					if(!send.defect) err();
					else {
						dialog.process();
						$.post(AJAX_WS, send, function (res) {
							if(res.success) {
								dialog.close();
								_msg('Квитанция сохранена');
								$('#kvit_spisok').html(res.html);
							} else
								dialog.abort();
						}, 'json');
					}
				}

				function err(prev) {
					dialog.bottom.vkHint({
						msg: '<SPAN class="red">Не указана неисправность</SPAN>',
						top: prev ? -112 : -47,
						left: prev ? 127 : 97,
						indent: 50,
						show: 1,
						remove: 1
					});
				}
			});
			$('#executer_id')._dropdown({
				title0: 'не указан',
				spisok: WORKER_SPISOK,
				func: function (v) {
					var td = $('#executer_td'),
						send = {
							op: 'zayav_executer_change',
							zayav_id: ZAYAV.id,
							executer_id: v
						};
					td.addClass('_busy');
					$.post(AJAX_WS, send, function (res) {
						td.removeClass('_busy');
						if(res.success)
							_msg('Исполнитель изменён');
					}, 'json');
				}
			});
			$('#executer_id_dropdown').vkHint({
				msg: 'Сотрудник, который назначен на выполнение данной заявки.',
				delayShow: 1000,
				width: 150,
				top: -79,
				left: -50
			});
			$('#ze-edit').click(function () {
				var html =
						'<table class="ze-edit-tab">' +
						'<tr><td class="label">Заявка: <td><b>№' + ZAYAV.nomer + '</b>' +
						'<tr><td class="label">Расходы:<td>' +
						'<tr><td colspan="2" id="zes">' +
						'</table>',
					dialog = _dialog({
						top: 30,
						width: 510,
						head: 'Изменение расходов заявки',
						content: html,
						butSubmit: 'Сохранить',
						submit: submit
					});
				$('#zes').zayavExpense(ZAYAV.expense);
				function submit() {
					var send = {
						op: 'zayav_expense_edit',
						zayav_id: ZAYAV.id,
						expense: $('#zes').zayavExpense('get')
					};
					if(send.expense == 'sum_error') err('Некорректно указана сумма');
					else {
						dialog.process();
						$.post(AJAX_WS, send, function (res) {
							if(res.success) {
								zayavMoneyUpdate();
								dialog.close();
								_msg('Сохранено.');
							} else
								dialog.abort();
						}, 'json');
					}
				}

				function err(msg) {
					dialog.bottom.vkHint({
						msg: '<SPAN class="red">' + msg + '</SPAN>',
						top: -47,
						left: 167,
						indent: 40,
						show: 1,
						remove: 1
					});
				}
			});
			$('#diagnost-ready').click(function () {
				var html =
						'<div class="_info">' +
						'После внесения результатов диагностики к заявке будет добавлен комментарий. ' +
						'При необходимости можно добавить напоминание.' +
						'</div>' +
						'<table class="_dialog-tab" id="zayav-diagnost-tab">' +
						'<tr><td class="label">Заявка:<td><b>№' + ZAYAV.nomer + '</b>' +
						'<tr><td class="label topi">Результаты:<td><textarea id="comm"></textarea>' +
						'<tr><td class="label">Добавить напоминание:<td><input type="hidden" id="diagnost-remind" />' +
						'<tr class="remind-tr"><td class="label">Содержание:<td><input type="text" id="remind-txt" value="Сообщить результаты диагностики">' +
						'<tr class="remind-tr"><td class="label">Дата:<td><input type="hidden" id="remind-day">' +
						'</table>',
					dialog = _dialog({
						width: 450,
						head: 'Внесение результатов диагностики',
						content: html,
						submit: submit
					});
				$('#comm').autosize().focus();
				$('#diagnost-remind')._check();
				$('#diagnost-remind_check').click(function (id) {
					$('.remind-tr').toggle();
				});
				$('#remind-day')._calendar();

				function submit() {
					var send = {
						op: 'zayav_diagnost',
						zayav_id: ZAYAV.id,
						comm: $('#comm').val(),
						remind: _num($('#diagnost-remind').val()),
						remind_txt: $('#remind-txt').val(),
						remind_day: $('#remind-day').val()
					};
					if(!send.comm) {
						dialog.err('Не указан текст результата');
						$('#comm').focus();
					}
					else if(send.remind && !send.remind_txt) {
						dialog.err('Не указано содержание напоминания');
						$('#remind-txt').focus();
					}
					else {
						dialog.process();
						$.post(AJAX_WS, send, function (res) {
							if(res.success) {
								dialog.close();
								_msg('Результаты диагностики внесены');
								document.location.reload();
							} else
								dialog.abort();
						}, 'json');
					}
				}
			});
		}

		if($('#zayav-cartridge').length) {
			$('#sort')._radio(cartridgeSpisok);
			$('#desc')._check(cartridgeSpisok);
			$('#status').rightLink(cartridgeSpisok);
			$('#paytype')._radio(cartridgeSpisok);
			$('#noschet')._check(cartridgeSpisok);
		}
	});
