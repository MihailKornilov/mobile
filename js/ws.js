var scannerWord = '',
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
			$.post(AJAX_MAIN, send, function(res) {
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
			title0:'���� �� ������',
			spisok:COLOR_SPISOK,
			func:_valueColorSet
		});
		_valueColorSet(color_id);
	},
	_valueColorSet = function(v) {
		$('#colors').find('tt')[v ? 'show' : 'hide']();
		$('#color_dop')._select(!v ? 'remove' :  {
			width:120,
			title0:'���� �� ������',
			spisok:COLOR_SPISOK,
			func:function(id) {
				$('#color_id')
					._select(id ? COLORPRE_SPISOK : COLOR_SPISOK)
					._select($('#color_id').val());
			}
		});
	},

	zayavEdit = function() {
		var html =
			'<table class="zayav-info-edit">' +
				'<tr><td class="label r">������:		<td><input type="hidden" id="client_id" value="' + ZAYAV.client_id + '">' +
				'<tr><td class="label r top">����������:<td><table><td id="dev"><td id="device_image"></table>' +
				'<tr><td><td>' + ZAYAV.images +
				'<tr><td class="label r">IMEI:		  <td><input type="text" id="imei" maxlength="20" value="' + ZAYAV.imei + '">' +
				'<tr><td class="label r">�������� �����:<td><input type="text" id="serial" maxlength="30" value="' + ZAYAV.serial + '">' +
				'<tr><td class="label r">����:<td id="colors">' +
				'<tr class="tr_equip' + (ZAYAV.equip ? '' : ' dn') + '">' +
					'<td class="label r top">������������:<td class="equip_spisok">' + ZAYAV.equip +
				'<tr><td class="label r">�����������:<td><input TYPE="hidden" id="diagnost" value="' + ZAYAV.diagnost + '" />' +
				'<tr><td class="label">��������� �������:<td><input type="text" class="money" id="pre_cost" maxlength="11" value="' + (ZAYAV.pre_cost ? ZAYAV.pre_cost : '') + '" /> ���.' +
			'</table>',
			dialog = _dialog({
				width:420,
				top:30,
				head:'������ �' + ZAYAV.nomer + ' - ��������������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#client_id').clientSel({width:267});
		$('#client_id_select').vkHint({
			msg:'���� ���������� ������, �� ���������� � ������� ������ ����������� �� ������ �������.',
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
			if(send.deivce == 0) msg = '�� ������� ����������';
			else if(send.client_id == 0) msg = '�� ������ ������';
			else if(send.pre_cost && !REGEXP_NUMERIC.test(send.pre_cost)) {
				msg = '����������� ������� ��������������� ���������';
				$('#pre_cost').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ ��������!');
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
	zayavSpisok = function(v, id) {
		_filterSpisok(ZAYAV, v, id);
		$('.condLost')[(ZAYAV.find ? 'add' : 'remove') + 'Class']('hide');
		$.post(AJAX_MAIN, ZAYAV, function(res) {
			if(res.success) {
				$('.result').html(res.all);
				$('#spisok').html(res.spisok);
			}
		}, 'json');
	},
	zayavMoneyUpdate = function() {//���������� ���������� � ��������
		var send = {
			op:'zayav_money_update',
			zayav_id:ZAYAV.id
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				$('b.acc').html(res.acc);
				$('.acc_tr')[(!res.acc ? 'add' : 'remove') + 'Class']('dn');
				$('b.op').html(res.opl);
				$('.op_tr')[(!res.opl ? 'add' : 'remove') + 'Class']('dn');
				$('.dopl')
					[(!res.diff ? 'add' : 'remove') + 'Class']('dn')
					.html((res.diff > 0 ? '+' : '') + res.diff);
				//���������� �������� �� ������
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
			$.post(AJAX_MAIN, send, function(res) {
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
	zayavPlace = function(func) {
		if(!window.PLACE_OTHER) {
			DEVPLACE_SPISOK.push({
				uid:0,
				title:'<div id="place-other-div">������:<input type="text" id="place_other" class="dn" /></div>'
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
				if(typeof func == 'function')
					func();
			}
		});
	},
	zayavKvit = function () {
		var html = '<table class="zayav-print">' +
				'<tr><td class="label">���� �����:<td>' + PRINT.dtime +
				'<tr><td class="label top">����������:<td>' + PRINT.device +
				'<tr><td class="label">����:<td>' + (PRINT.color ? PRINT.color : '<i>�� ������</i>') +
				'<tr><td class="label">IMEI:<td>' + (PRINT.imei ? PRINT.imei : '<i>�� ������</i>') +
				'<tr><td class="label">�������� �����:<td>' + (PRINT.serial ? PRINT.serial : '<i>�� ������</i>') +
				'<tr><td class="label">������������:<td>' + (PRINT.equip ? PRINT.equip : '<i>�� �������</i>') +
				'<tr><td class="label">��������:<td><b>' + PRINT.client + '</b>' +
				'<tr><td class="label">�������:<td>' + (PRINT.telefon ? PRINT.telefon : '<i>�� ������</i>') +
				'<tr><td class="label top">�������������:<td><textarea id="defect">' + PRINT.defect + '</textarea>' +
				'<tr><td colspan="2"><a id="preview"><span>��������������� �������� ���������</span></a>' +
				'</table>',
			dialog = _dialog({
				width: 380,
				top: 30,
				head: '������ �' + ZAYAV.nomer + ' - ������������ ���������',
				content: html,
				butSubmit: '��������� ���������',
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
			if(!send.defect)
				dialog.err('�� ������� �������������');
			else {
				t.addClass('_busy');
				$.post(AJAX_MAIN, send, function (res) {
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
			if(!send.defect)
				dialog.err('�� ������� �������������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('��������� ���������');
						$('#kvit_spisok').html(res.html);
					} else
						dialog.abort();
				}, 'json');
			}
		}
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
				'<tr><td class="label r">���:<td><input type="hidden" id="type_id" value="1" />' +
				'<tr><td class="label r"><b>������ ���������:</b><td><input type="text" id="name" />' +
				'<tr><td class="label r">��������:<td><input type="text" id="cost_filling" class="money" maxlength="11" /> ���.' +
				'<tr><td class="label r">��������������:<td><input type="text" id="cost_restore" class="money" maxlength="11" /> ���.' +
				'<tr><td class="label r">������ ����:<td><input type="text" id="cost_chip" class="money" maxlength="11" /> ���.' +
				'</table>',
			dialog = _dialog({
				top:20,
				head:'���������� ������ ���������',
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
				dialog.err('�� ������� ������������');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
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
						_msg('�������!');
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
					'<tr><td class="label">������:' +
						'<td><input type="hidden" id="client_id" value="' + CLIENT.id + '" />' +
							'<b>' + CLIENT.fio + '</b>' +
					'<tr><td class="label"><b>���������� ����������:</b><td><input type="text" id="count" /> ��.' +
					'<tr><td class="label topi">������:<td><input type="hidden" id="pay_type" />' +
					'<tr><td class="label topi">������ ����������:<td id="crt">' +
					'<tr><td class="label top">�������:<td><textarea id="comm"></textarea>' +
				'</table>',
			dialog = _dialog({
				width:470,
				top:30,
				head:'����� ������ �� �������� ����������',
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
			if(!send.client_id) dialog.err('�� ������ ������');
			else if(!send.count) {
				dialog.err('�� ������� ���������� ����������');
				$('#count').focus();
			} else if(!send.pay_type) dialog.err('������� ��� �������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ �������');
						location.href = URL + '&p=zayav&d=info&id=' + res.id + '&from=cartridge';
					} else
						dialog.abort();
				}, 'json');
			}
		}
	},
	cartridgeSpisok = function(v, id) {
		_filterSpisok(CARTRIDGE, v, id);
		$.post(AJAX_MAIN, CARTRIDGE, function(res) {
			if(res.success) {
				$('.result').html(res.all);
				$('#spisok').html(res.spisok);
			}
		}, 'json');
	},
	cartridgeSchetSet = function(schet_id) {
		var send = {
			op:'zayav_cartridge_schet_set',
			schet_id:schet_id,
			ids:_checkAll()
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				location.reload();
		}, 'json');
	},
	zayavInfoCartridgeAdd = function() {
		var html =
				'<table id="cartridge-add-tab">' +
					'<tr><td class="label topi">������ ����������:<td id="crt">' +
				'</table>',
			dialog = _dialog({
				width:470,
				top:30,
				head:'���������� ���������� � ������',
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
			if(!send.ids) dialog.err('�� ������� �� ������ ���������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						$('#cart-tab').html(res.html);
						_msg('�������.');
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
			title0:id ? '������������ �������� �� �������' : '������� �������� ����������',
			spisok:ZPNAME_SPISOK[id]
		})._select(name_id || 0);
	},
	zpNameAdd = function() {//�������� ����� ��������
		var html =
				'<table id="zpname-add-tab">' +
					'<tr><td class="label">����������:<td><b>' + DEV_ASS[ZPNAME.device_id] + '</b>' +
					'<tr><td class="label">��������:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'���������� ������ ������������ ��������',
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
				dialog.err('�� ������� ��������');
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
						_msg('�������!');
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
		device_ids:null, // ������ id, ������� ����� �������� � ������ ��� ���������
		vendor_ids:null, // ��� ��������������
		model_ids:null,  // ��� �������
		add:0,
		device_funcAdd:null, // ������� �����, ���� ������ ��������� ����� ��������
		vendor_funcAdd:null,
		model_funcAdd:null,
		no_model:0 //�� �������� ������ ������� (��� �������)
	},o);

	var t = $(this),
		id = t.attr('id'),
		html = '<input type="hidden" id="' + id + '_device" value="' + o.device_id + '">' +
			'<input type="hidden" id="' + id + '_vendor" value="' + o.vendor_id + '">' +
			'<input type="hidden" id="' + id + '_model" value="' + o.model_id + '">',
		device_no = ['���������� �� �������','����� ����������'],
		vendor_no = ['������������� �� ������','����� �������������'],
		model_no = ['������ �� �������','����� ������'],
		dialog;
	t.html(html);
	var devSel = $('#' + id + '_device'),
		venSel = $('#' + id + '_vendor'),
		modSel = $('#' + id + '_model');

	// �������� ������ ������ ���������, ������� ����� �������� � ������
	if(o.device_ids) {
		DEV_SPISOK = [];
		for(n = 0; n < o.device_ids.length; n++) {
			var uid = o.device_ids[n];
			DEV_SPISOK.push({uid:uid, title:DEV_ASS[uid]});
		}
	}

	// �������� ������ ������ ��������������, ������� ����� �������� � ������
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

	// �������� ������ ������ �������, ������� ����� �������� � ������
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

	// ���������� ����� ���������
	if(o.add > 0) {
		o.device_funcAdd = function() {
			var html = '<table class="device-add-tab">' +
				'<tr><td class="label">��������:<td><input type="text" id="daname">' +
				'</table>';
			dialog = _dialog({
				width:300,
				head:'���������� �o���� ����������',
				content:html,
				submit:deviceAddSubmit
			});
			$('#daname')
				.focus()
				.keyEnter(deviceAddSubmit);
		};
		o.vendor_funcAdd = function () {
			var html ='<table class="device-add-tab">' +
				'<tr><td class="label">��������:<td><input type="text" id="vaname">' +
				'</table>';
			dialog = _dialog({
				width:300,
				head:'���������� �o���� �������������',
				content:html,
				submit:vendorAddSubmit
			});
			$('#vaname')
				.focus()
				.keyEnter(vendorAddSubmit);
		};
		o.model_funcAdd = function(){
			var html = '<table class="device-add-tab">' +
				'<tr><td class="label">��������:<td><input type="text" id="maname">' +
				'</table>';
			dialog = _dialog({
				width:300,
				head:'���������� �o��� ������',
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
			addHint('�� ������� �������� ����������.');
		else if(name_test(DEV_SPISOK, send.name))
			addHint();
		else {
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				dialog.abort();
				if(res.success) {
					DEV_SPISOK.push({uid:res.id, title:send.name});
					DEV_ASS[res.id] = name;
					devSel
						._select(DEV_SPISOK)
						._select(res.id);
					getVendor(0);
					modSel.val(0)._select('remove'); //��������� ������ ������ � ��������������� � 0
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
			addHint('�� ������� �������� �������������.');
		else if(name_test(VENDOR_SPISOK[dv], send.name))
			addHint();
		else {
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				dialog.abort();
				if(res.success) {
					// ���� � ���������� ��� ��������������, ������� �������� ������ ������
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
			addHint('�� ������� �������� ������.');
		else if(name_test(MODEL_SPISOK[vv], send.name)) {
			addHint();
		} else {
			dialog.process();
			$.post(AJAX_MAIN, send, function (res) {
				dialog.abort();
				if(res.success) {
					// ���� � ������������� ��� �������, ������� �������� ������ ������
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
		msg = msg || '����� �������� ��� ���� � ������.';
		dialog.bottom.vkHint({
			msg:'<SPAN class="red">' + msg + '</SPAN>',
			top:-47,
			left:53,
			indent:50,
			show:1,
			remove:1
		});
	}

	// ����� ������ ���������
	devSel._select({
		width:o.width,
		block:1,
		multiselect:o.device_multiselect,
		title0:device_no[o.type_no],
		spisok:DEV_SPISOK,
		func:function(device_id) {
			venSel.val(0);
			modSel.val(0)._select('remove'); //��������� ������ ������ � ��������������� � 0, ���� ��� �����
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

	// ����� ������ ��������������
	function getVendor(vendor_id) {
		if(vendor_id != undefined)
			o.vendor_id = vendor_id; // ���������� �������� �������������, ���� �����
		venSel.val(o.vendor_id);
		venSel._select({
			width:o.width,
			block:1,
			title0:vendor_no[o.type_no],
			spisok:VENDOR_SPISOK[devSel.val()], // �������� ���������� �������� �� ��� �������
			func:function(id) {
				modSel.val(0);
				if(!id)
					modSel._select('remove'); //��������� ������ ������ � ��������������� � 0
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

	// ����� ������ �������
	function getModel(model_id) {
		if(o.no_model)
			return;
		if(model_id != undefined)
			o.model_id = model_id; //���������� �������� ������, ���� �����
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

	// �������� �� ���������� ����� ��� �������� ������ ��������
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
			title0:'�������� �� ������',
			write:1,
			spisok:CARTRIDGE_SPISOK,
			func:add_test,
			funcAdd:function(id) {
				cartridgeNew(id, add_test);
			}
		});
	}
	function add_test(v) {//��������, ��� �� ��������� �������, ����� ��������� ����� ����
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
					head:'������ �����-����',
					width:250,
					content:'������� ���: <b>' + scannerWord + '</b>',
					butSubmit:'�����'
				});
				var send = {
					op:'scanner_word',
					word:scannerWord
				};
				scannerDialog.process();
				$.post(AJAX_MAIN, send, function(res) {
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

	.on('mouseenter', '.zayav_link', function(e) {//�������� ��������� ��� ������, ���� ������ ���� ������
		if(e.clientY < 90)
			$(this).find('.tooltip').css('top', '12px');
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
		$.post(AJAX_MAIN, send, function(res) {
			t.removeClass('_busy');
			if(res.success)
				t.after(res.html);
		}, 'json');
	})

	.on('click', '.day-finish-link', function(e) {//�������� ��������� ��������
		e.stopPropagation();
		var t = $(this),
			save = t.hasClass('no-save') ? 0 : 1;
		if(t.hasClass('_busy'))
			return;
		var dialog = _dialog({
				top:40,
				width:480,
				head:'��������� ��������',
				load:1,
				butSubmit:''
			}),
			send = {
				op:'zayav_day_finish',
				day:$('#day_finish').val(),
				zayav_spisok:$('#zayav').length
			};
		$.post(AJAX_MAIN, send, function(res) {
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
				$.post(AJAX_MAIN, send, function(res) {
					t.removeClass('_busy');
					if(res.success) {
						t.prev('input').val(send.day);
						t.find('span').html(res.data);
						if($('#zayav').length)
							zayavSpisok(send.day, 'finish');
					}
				}, 'json');
			});
	})
	.on('click', '#zayav-finish-calendar .ch', function() {//��������� ��������� ��������
		if($('#fc-head').hasClass('_busy'))
			return;
		$('#fc-head').addClass('_busy');
		var send = {
			op:'zayav_day_finish_next',
			mon:$(this).attr('val'),
			day:$('#day_finish').val(),
			zayav_spisok:$('#zayav').length
		};
		$.post(AJAX_MAIN, send, function(res) {
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
						'���� � ������<br />������������' +
					'</div>' +
					'<div class="unit" id="cartridge-add">' +
						'��������, ��������������<br />����������' +
					'</div>' +
				'</div>',
				dialog = _dialog({
					top:30,
					width:300,
					head:'����� ������',
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
	.on('click', '#zayav .clear', function() {
		$('#find')._search('clear');
		$('#sort')._radio(1);
		$('#desc')._check(0);
		$('#status').rightLink(0);
		$('#day_finish').val('0000-00-00');
		$('.day-finish-link span').html('�� ������');
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
		$('#place')._select(0);

		ZAYAV.find = '';
		ZAYAV.sort = 1;
		ZAYAV.desc = 0;
		ZAYAV.status = 0;
		ZAYAV.finish = '0000-00-00';
		ZAYAV.diagnost = 0;
		ZAYAV.diff = 0;
		ZAYAV.zpzakaz = 0;
		ZAYAV.executer = 0;
		ZAYAV.device = 0;
		ZAYAV.vendor = 0;
		ZAYAV.model = 0;
		ZAYAV.place = 0;
		zayavSpisok();
	})

	.on('click', '#zayav-cartridge .clear', function() {
		$('#sort')._radio(1);
		$('#desc')._check(0);
		$('#status').rightLink(0);
		$('#paytype')._radio(0);
		$('#noschet')._check(0);
		CARTRIDGE.sort = 1;
		CARTRIDGE.desc = 0;
		CARTRIDGE.status = 0;
		CARTRIDGE.paytype = 0;
		CARTRIDGE.noschet = 0;
		cartridgeSpisok();
	})

	.on('click', '#zayav-info .zedit', zayavEdit)
	.on('click', '#zayav-status-button .status_place', function() {
		var html =
			'<div id="zayav-status">' +
		(ZAYAV.status != 1 ?
				'<div class="st c1" val="1">' +
					'������� ����������' +
					'<div class="about">������������� ������ �� ������.</div>' +
				'</div>'
		: '') +
		(ZAYAV.status != 2 ?
				'<div class="st c2" val="2">' +
					'���������' +
					'<div class="about">' +
						'������ ��������� �������.<br />' +
						'�� �������� ��������� ������� �� ������, ��������� ����������.<br />' +
						'�������� �����������, ���� ����������.' +
					'</div>' +
				'</div>'
		: '') +
		(ZAYAV.status != 3 ?
				'<div class="st c3" val="3">' +
					'������ ��������' +
					'<div class="about">������ ������ �� �����-���� �������.</div>' +
				'</div>'
		: '') +
				'<input type="hidden" id="zs-status" />' +
				'<table id="zs-tab">' +
					'<tr><td class="label r topi">��������������� ����������:<td><input type="hidden" id="place" value="-1" />' +
					'<tr id="zs-srok" class="dn">' +
						'<td class="label r">���� ����������:' +
						'<td><input type="hidden" id="zs-day_finish" value="0000-00-00" />' +
							'<div class="day-finish-link no-save"><span>�� ������</span></div>' +
				'</table>' +

			'</div>',

			dialog = _dialog({
				top:30,
				width:420,
				head:'��������� ������� ������',
				content:html,
				butSubmit:'���������',
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
				dialog.err('�������� ������ ������');
			else if(send.place == -1 || send.place == 0 && send.place_other == '') {
				dialog.err('�� ������� ��������������� ����������');
				$('#place_other').focus();
			} else if(send.status == 1 && send.day_finish == '0000-00-00')
				dialog.err('�� ������ ���� ����������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('��������� ���������.');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-status-button .cartridge_status', function() {
		var html =
				'<div id="zayav-status">' +
					(ZAYAV.status != 1 ?
						'<div class="st c1" val="1">' +
							'������� ����������' +
							'<div class="about">������������� ������ �� ������.</div>' +
						'</div>'
					: '') +
					(ZAYAV.status != 2 ?
						'<div class="st c2" val="2">' +
							'���������' +
							'<div class="about">' +
								'������ ��������� �������.<br />' +
								'�� �������� ��������� ������� �� ������, ��������� ����������.<br />' +
								'�������� �����������, ���� ����������.' +
							'</div>' +
						'</div>'
					: '') +
					(ZAYAV.status != 3 ?
						'<div class="st c3" val="3">' +
							'������ ��������' +
							'<div class="about">������ ������ �� �����-���� �������.</div>' +
						'</div>'
					: '') +
						'<input type="hidden" id="zs-status" />' +
				'</div>',

			dialog = _dialog({
				top:30,
				width:420,
				head:'��������� ������� ������',
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
			$.post(AJAX_MAIN, send, function (res) {
				if(res.success) {
					dialog.close();
					_msg('��������� ���������');
					document.location.reload();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '#zayav-info .dev_place', function() {
		var html =
				'<table class="device-add-tab">' +
					'<tr><td class="label r topi">��������������� ����������:<td><input type="hidden" id="place" value="-1" />' +
				'</table>',

			dialog = _dialog({
				head:'��������� ��������������� ����������',
				content:html,
				butSubmit:'���������',
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
				err('�� ������� ��������������� ����������');
				$('#place_other').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('��������� ���������.');
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
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				t.html('��������!').attr('class', 'zakaz_ok');
				_msg(res.msg);
			}
		}, 'json');
	})
	.on('click', '#zayav-info .zakaz_ok', function() {
		location.href = URL + '&p=zp&menu=3';
	})
	.on('click', '#zayav-info .zpAdd', function() {
		var html = '<div class="zayav_zp_add">' +
				'<center>���������� �������� � ����������<br />' +
					'<b>' +
						DEV_ASS[ZAYAV.device] + ' ' +
						VENDOR_ASS[ZAYAV.vendor] + ' ' +
						MODEL_ASS[ZAYAV.model] +
					'</b>.'+
				'</center>' +
				'<table style="border-spacing:6px">' +
					'<tr><td class="label r">������������ ��������:<td><input type="hidden" id="name_id" />' +
					'<tr><td class="label r">������:<td><input type="text" id="version" />' +
					'<tr><td class="label r">����:<td><input type="hidden" id="color_id" />' +
				'</table>' +
			'</div>',
			dialog = _dialog({
				top:40,
				width:400,
				head:'�������� ����� ��������',
				content:html,
				submit:submit
			});

		zpNameSelect(ZAYAV.device);
		$('#color_id')._select({
			width:130,
			title0:'���� �� ������',
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
				dialog.err('�� ������� ������������ ��������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						_msg('�������� �������� �����������');
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
			'��������� ��������<br />' + unit.find('a:first').html() + '.<br />' +
			(unit.find('.color').length > 0 ? unit.find('.color').html() + '.<br />' : '') +
			'<br />���������� �� ��������� �����' +
			'<br />����� ��������� � �������' +
			'<br />� � ������� �� ������.' +
		'</center>',
		dialog = _dialog({
			top:150,
			width:400,
			head:'��������� ��������',
			content:html,
			butSubmit:'����������',
			submit:submit
		});
		function submit() {
			var send = {
				op:'zayav_zp_set',
				zp_id:unit.attr('val'),
				zayav_id:ZAYAV.id
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('��������� �������� �����������.');
					location.reload();
				} else
					dialog.abort();
			},'json');
		}
	})
	.on('click', '.zayav_kvit', function() {
		kvitHtml($(this).attr('val'));
	})

	.on('click', '#zayav-info .zc-edit', function() {//�������������� ������ � �����������
		var html =
				'<table id="cartridge-add-tab">' +
					'<tr><td class="label">������:' +
						'<td><input type="hidden" id="client_id" value="' + ZAYAV.client_id + '" />' +
					'<tr><td class="label"><b>���������� ����������:</b><td><input type="text" id="count" value="' + ZAYAV.cartridge_count + '" /> ��.' +
					'<tr><td class="label topi">������:<td><input type="hidden" id="pay_type" value="' + ZAYAV.pay_type + '" />' +
				'</table>',
			dialog = _dialog({
				width:470,
				head:'�������������� ������',
				content:html,
				butSubmit:'���������',
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
			if(!send.client_id) dialog.err('�� ������ ������');
			else if(!send.count) dialog.err('�� ������� ���������� ����������');
			else if(!send.pay_type) dialog.err('������� ��� �������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ ��������');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-cartridge-info #cart-add', zayavInfoCartridgeAdd)
	.on('click', '#zayav-cartridge-info .cart-edit', function() {
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
					'<tr><td class="label">��������:<td><input type="hidden" id="cart_id" value="' + cart_id + '" />' +
					'<tr><td class="label topi">��������:' +
						'<td><input type="hidden" id="filling" value="' + filling + '" />' +
							'<input type="hidden" id="restore" value="' + restore + '" />' +
							'<input type="hidden" id="chip" value="' + chip + '" />' +
					'<tr><td class="label">��������� �����:<td><input type="text" class="money" id="cost" value="' + cost + '" /> ���.' +
					'<tr><td class="label">����������:<td><input type="text" id="prim" value="' + prim + '" />' +
				'</table>',
			dialog = _dialog({
				width:470,
				top:30,
				head:'�������� �� ���������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#cart_id')._select({
			write:1,
			spisok:CARTRIDGE_SPISOK,
			func:costSet
		});
		$('#filling')._check({name:'��������',func:costSet});
		$('#restore')._check({name:'��������������',func:costSet});
		$('#chip')._check({name:'������ ����',func:costSet});
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
			if(!send.cart_id) dialog.err('�� ������ ��������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						$('#cart-tab').html(res.html);
						_msg('��������.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-cartridge-info .cart-del', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var id = t.attr('val'),
			dialog = _dialog({
				head:'�������� ���������',
				content:'<center>����������� �������� ���������.</center>',
				butSubmit:'�������',
				submit:submit
			});
		function submit() {
			var send = {
				op:'zayav_info_cartridge_del',
				id:id
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					$('#cart-tab').html(res.html);
					_msg('�������.');
				} else
					dialog.abort();
				}, 'json');
		}
	})

	.ready(function() {
		if($('#zayav').length) {
			$('#find')
				.vkHint({
					msg: '����� ������������ ��<br />���������� � ��������<br />������, imei � ��������<br />������.',
					ugol: 'right',
					top: -9,
					left: -178,
					delayShow: 800
				})
				._search({
					width: 153,
					focus: 1,
					txt: '������� �����...',
					enter: 1,
					func: zayavSpisok
				})
				.inp(ZAYAV.find);
			$('#sort')._radio(zayavSpisok);
			$('#desc')._check(zayavSpisok);
			$('#status').rightLink(zayavSpisok);
			$('#diagnost')._check(zayavSpisok);
			$('#diff')._check(zayavSpisok);
			$('#zpzakaz')._radio(zayavSpisok);
			WORKER_SPISOK.push({uid: -1, title: '�� ��������', content: '<b>�� ��������</b>'});
			$('#executer')._select({
				width: 155,
				title0: '�� ������',
				spisok: WORKER_SPISOK,
				func: zayavSpisok
			});
			$('#dev').device({
				width: 155,
				type_no: 1,
				device_id: ZAYAV.device,
				vendor_id: ZAYAV.vendor,
				model_id: ZAYAV.model,
				device_multiselect: 1,
				device_ids: Z.device_ids,
				vendor_ids: Z.vendor_ids,
				model_ids: Z.model_ids,
				func: function(v) {
					ZAYAV.device = v.device_id;
					ZAYAV.vendor = v.vendor_id;
					ZAYAV.model = v.model_id;
					zayavSpisok();
				}
			});
			// ���������� ����������
			$('#place')._select({
				width: 155,
				title0: '����� ���������������',
				spisok: DEVPLACE_SPISOK,
				func: zayavSpisok
			});
			//������������� ������������� ������
			if(Z.cookie_id) {
				VK.callMethod('scrollWindow', _cookie('zback_scroll'));
				$('#u' + Z.cookie_id).css('opacity', 0.1).delay(400).animate({opacity: 1}, 700);
			}
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
				if(send.client_id == 0) msg = '�� ������ ������';
				else if(send.device == 0) msg = '�� ������� ����������';
				else if(send.place == '-1' || send.place == 0 && !send.place_other) msg = '�� ������� ��������������� ����������';
				else if(send.pre_cost && (!REGEXP_NUMERIC.test(send.pre_cost) || send.pre_cost == 0)) {
					msg = '����������� ������� ��������������� ���������';
					$('#pre_cost').focus();
				} else if(send.day_finish == '0000-00-00') msg = '�� ������ ���� ���������� �������';
				else {
					if(send.place > 0) send.place_other = '';
					t.addClass('busy');
					$.post(AJAX_MAIN, send, function (res) {
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
			$('#zayav-action')._dropdown({
				head:'��������',
				nosel:1,
				spisok:[
					{uid:1, title:'������������� ������ ������'},
					{uid:2, title:'<b>����������� ���������</b>'},
					{uid:3, title:'������������ ���� �� ������'},
//					{uid:4, title:'�������� ������ ������'},
					{uid:5, title:'���������'},
					{uid:6, title:'<b>������� �����</b>'},
					{uid:7, title:'�������'},
					{uid:8, title:'�������� ������� �� ������'},
					{uid:9, title:'����� �����������'}
//					{uid:10, title:'�������� ���� ���������� ������'},
//					{uid:11, title:'�������� ��������������� ����������'},
//					{uid:12, title:'�������� �������� � ����������'},
//					{uid:13, title:'<tt class="red">������� ������</tt>'}
				],
				func:function(v) {
					switch(v) {
						case 1: zayavEdit(); break;
						case 2:
							if(WS_ID == 3)
								location.href = APP_HTML + '/view/kvit_comtex.php?' + VALUES + '&id=' + ZAYAV.id;
							else
								zayavKvit();
							break;
						case 3:
							_schetEdit({
								edit:1,
								client_id:ZAYAV.client_id,
								client:ZAYAV.client_link,
								zayav_id:ZAYAV.id
							});
							break;
						case 5: _accrualAdd(); break;
						case 6: _incomeAdd(); break;
						case 7: _refundAdd(); break;
						case 8: _zayavExpenseEdit(); break;
						case 9: _remindAdd(); break;
					}
				}
			});
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
			$('#executer_id')._dropdown({
				title0: '�� ������',
				spisok: WORKER_SPISOK,
				func: function (v) {
					var td = $('#executer_td'),
						send = {
							op: 'zayav_executer_change',
							zayav_id: ZAYAV.id,
							executer_id: v
						};
					td.addClass('_busy');
					$.post(AJAX_MAIN, send, function (res) {
						td.removeClass('_busy');
						if(res.success)
							_msg('����������� �������');
					}, 'json');
				}
			});
			$('#executer_id_dropdown').vkHint({
				msg: '���������, ������� �������� �� ���������� ������ ������.',
				delayShow: 1000,
				width: 150,
				top: -79,
				left: -50
			});
			$('#diagnost-ready').click(function () {
				var html =
						'<div class="_info">' +
							'����� �������� ����������� ����������� � ������ ����� �������� �����������. ' +
							'��� ������������� ����� �������� �����������.' +
						'</div>' +
						'<table class="_dialog-tab" id="zayav-diagnost-tab">' +
						'<tr><td class="label">������:<td><b>�' + ZAYAV.nomer + '</b>' +
						'<tr><td class="label topi">����������:<td><textarea id="comm"></textarea>' +
						'<tr><td class="label">�������� �����������:<td><input type="hidden" id="diagnost-remind" />' +
						'<tr class="remind-tr"><td class="label">����������:<td><input type="text" id="remind-txt" value="�������� ���������� �����������">' +
						'<tr class="remind-tr"><td class="label">����:<td><input type="hidden" id="remind-day">' +
						'</table>',
					dialog = _dialog({
						width: 450,
						head: '�������� ����������� �����������',
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
						dialog.err('�� ������ ����� ����������');
						$('#comm').focus();
					}
					else if(send.remind && !send.remind_txt) {
						dialog.err('�� ������� ���������� �����������');
						$('#remind-txt').focus();
					}
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function (res) {
							if(res.success) {
								dialog.close();
								_msg('���������� ����������� �������');
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
		if($('#zayav-cartridge-info').length) {
			$('#zayav-action')._dropdown({
				head:'��������',
				nosel:1,
				spisok:[
//					{uid:1, title:'������������� ������ ������'},
					{uid:2, title:'<b>����������� ���������</b>'},
					{uid:3, title:'������������ ���� �� ������'},
//					{uid:4, title:'�������� ������ ������'},
					{uid:5, title:'���������'},
					{uid:6, title:'<b>������� �����</b>'},
					{uid:7, title:'�������'},
					{uid:8, title:'�������� ������� �� ������'},
					{uid:9, title:'����� �����������'}
//					{uid:13, title:'<tt class="red">������� ������</tt>'}
				],
				func:function(v) {
					switch(v) {
//						case 1: zayavEdit(); break;
						case 2:
							if(WS_ID == 3)
								location.href = APP_HTML + '/view/kvit_cartridge.php?' + VALUES + '&id=' + ZAYAV.id;
							break;
						case 3:
							if(!_checkAll()) {
								_schetEdit({
									edit:1,
									client_id:ZAYAV.client_id,
									client:ZAYAV.client_link,
									zayav_id:ZAYAV.id
								});
								break;
							}
							var	dialog = _dialog({
									head:'��������� ���������� � ����������',
									load:1,
									butSubmit:''
								}),
								send = {
									op:'zayav_cartridge_ids',
									ids:_checkAll()
								};
							$.post(AJAX_MAIN, send, function(res) {
								if(res.success) {
									dialog.close();
									_schetEdit({
										edit:1,
										client_id:ZAYAV.client_id,
										client:ZAYAV.client_link,
										zayav_id:ZAYAV.id,
										arr:res.arr,
										func:cartridgeSchetSet
									});
								} else
									dialog.loadError();
							}, 'json');
							break;
						case 5: _accrualAdd(); break;
						case 6: _incomeAdd(); break;
						case 7: _refundAdd(); break;
						case 8: _zayavExpenseEdit(); break;
						case 9: _remindAdd(); break;
					}
				}
			});
		}
	});
