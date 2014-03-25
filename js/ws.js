var AJAX_WS = SITE + '/ajax/ws.php?' + VALUES,
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

	colorSel = function(spisok) {
		$('#color_id')._select({
			width:120,
			title0:'���� �� ������',
			spisok:spisok,
			func:colorSelDop
		});
		$('#color_dop')._select({
			width:120,
			title0:'���� �� ������',
			spisok:COLOR_SPISOK,
			func:function(id) {
				colorSel(id ? COLORPRE_SPISOK : COLOR_SPISOK);
			}
		});
	},
	colorSelDop = function(id) {
		if(id) {
			if($('#color_id_select').length == 0)
				colorSel($('#color_dop').val() > 0 ? COLORPRE_SPISOK : COLOR_SPISOK);
			$('.color_dop').removeClass('dn');
		} else {
			colorSel(COLOR_SPISOK);
			$('#color_dop')._select(0);
			$('.color_dop').addClass('dn');
		}
	},

	clientAdd = function(callback) {
		var html = '<table style="border-spacing:10px">' +
				'<tr><td class="label">���:<TD><input type="text" id="fio" style="width:220px;">' +
				'<tr><td class="label">�������:<TD><input type="text" id="telefon" style=width:220px;>' +
			'</TABLE>',
			dialog = _dialog({
				width:340,
				head:'���������� �o���� �������',
				content:html,
				submit:submit
			});
		$('#fio').focus();
		function submit() {
			var send = {
				op:'client_add',
				fio:$('#fio').val(),
				telefon:$('#telefon').val()
			};
			if(!send.fio) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">�� ������� ��� �������.</SPAN>',
					top:-47,
					left:81,
					indent:40,
					show:1,
					remove:1,
					correct:0
				});
				$('#fio').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ������ �����.');
						if(typeof callback == 'function')
							callback(res);
						else
							document.location.href = URL + '&p=client&d=info&id=' + res.uid;
					} else dialog.abort();
				}, 'json');
			}
		}
	},
	clientFilter = function() {
		var v = {
			fast:cFind.inp(),
			dolg:$('#dolg').val(),
			active:$('#active').val(),
			comm:$('#comm').val()
		};
		$('.filter')[v.fast ? 'hide' : 'show']();
		return v;
	},
	clientSpisok = function() {
		var send = clientFilter(),
			result = $('.result');
		send.op = 'client_spisok';
		if(result.hasClass('busy'))
			return;
		result.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			result.removeClass('busy');
			if(res.success) {
				result.html(res.all);
				$('.left').html(res.spisok);
			}
		}, 'json');
	},
	clientZayavFilter = function() {
		return {
			client:CLIENT.id,
			status:$('#status').val(),
			diff:$('#diff').val(),
			device:$('#dev_device').val(),
			vendor:$('#dev_vendor').val(),
			model:$('#dev_model').val()
		};
	},
	clientZayavSpisok = function() {
		var send = clientZayavFilter();
		send.op = 'client_zayav_spisok';
		$('#dopLinks').addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			$('#dopLinks').removeClass('busy');
			$('#zayav_result').html(res.all);
			$('#zayav_spisok').html(res.html);
		}, 'json');
	},

	zayavFilter = function () {
		var v = {
				find:$.trim($('#find input').val()),
				sort:$('#sort').val(),
				desc:$('#desc').val(),
				status:$('#status').val(),
				diff:$('#diff').val(),
				zpzakaz:$('#zpzakaz').val(),
				device:$('#dev_device').val(),
				vendor:$('#dev_vendor').val(),
				model:$('#dev_model').val(),
				place:$('#device_place').val(),
				devstatus:$('#devstatus').val()
			},
			loc = '';
		if(v.sort != '1') loc += '.sort=' + v.sort;
		if(v.desc != '0') loc += '.desc=' + v.desc;
		if(v.find) loc += '.find=' + escape(v.find);
		else {
			if(v.status > 0) loc += '.status=' + v.status;
			if(v.diff > 0) loc += '.diff=' + v.diff;
			if(v.zpzakaz > 0) loc += '.zpzakaz=' + v.zpzakaz;
			if(v.device > 0) loc += '.device=' + v.device;
			if(v.vendor > 0) loc += '.vendor=' + v.vendor;
			if(v.model > 0) loc += '.model=' + v.model;
			if(v.place != 0) loc += '.place=' + v.place;
			if(v.devstatus > 0) loc += '.devstatus=' + v.devstatus;
		}
		VK.callMethod('setLocation', hashLoc + loc);

		setCookie('zayav_find', escape(v.find));
		setCookie('zayav_sort', v.sort);
		setCookie('zayav_desc', v.desc);
		setCookie('zayav_status', v.status);
		setCookie('zayav_diff', v.diff);
		setCookie('zayav_zpzakaz', v.zpzakaz);
		setCookie('zayav_device', v.device);
		setCookie('zayav_vendor', v.vendor);
		setCookie('zayav_model', v.model);
		setCookie('zayav_place', encodeURI(v.place));
		setCookie('zayav_devstatus', v.devstatus);

		return v;
	},
	zayavSpisokLoad = function() {
		var send = zayavFilter();
		$('.condLost')[(send.find ? 'add' : 'remove') + 'Class']('hide');
		send.op = 'zayav_spisok_load';

		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			$('#zayav .result').html(res.all);
			$('#zayav #spisok').html(res.html);
			$('#mainLinks').removeClass('busy');
		}, 'json');
	},
	zayavImgUpdate = function() {
		var send = {
			op:'zayav_img_update',
			zayav_id:ZAYAV.id
		};
		$.post(AJAX_WS, send, function (res) {
			if(res.success) {
				$('#foto').html(res.html);
			}
		}, 'json');
	},
	zayavInfoMoneyUpdate = function(res) {
		$('b.acc').html(res.acc);
		$('.acc_tr')[(res.acc == 0 ? 'add' : 'remove') + 'Class']('dn');
		$('b.op').html(res.opl);
		$('.op_tr')[(res.opl == 0 ? 'add' : 'remove') + 'Class']('dn');
		$('.dopl')
			[(res.diff == 0 ? 'add' : 'remove') + 'Class']('dn')
			.html((res.diff > 0 ? '+' : '') + res.diff);
		var del = res.acc == 0 && res.opl == 0;
		$('.delete')[(del ? 'remove' : 'add') + 'Class']('dn');
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

	zpFilter = function() {
		var v = {
				find:$.trim($('#find input').val()),
				menu:$('#menu').val(),
				name:$('#zp_name').val(),
				device:$('#dev_device').val(),
				vendor:$('#dev_vendor').val(),
				model:$('#dev_model').val(),
				bu:$('#bu').val()
			},
			loc = '';
		if(v.find) loc += '.find=' + escape(v.find);
		if(v.menu > 0) loc += '.menu=' + v.menu;
		if(v.name > 0) loc += '.name=' + v.name;
		if(v.device > 0) loc += '.device=' + v.device;
		if(v.vendor > 0) loc += '.vendor=' + v.vendor;
		if(v.model > 0) loc += '.model=' + v.model;
		if(v.bu > 0) loc += '.bu=' + v.bu;
		VK.callMethod('setLocation', hashLoc + loc);
		return v;
	},
	zpSpisokLoad = function() {
		var send = zpFilter();
		send.op = 'zp_spisok_load';
		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			$('#mainLinks').removeClass('busy');
			$('#zp .result').html(res.all);
			$('#zp .left').html(res.html);
		}, 'json');
	},
	zpImgUpdate = function() {
		var send = {
			op:'zp_img_update',
			zp_id:ZP.compat_id
		};
		$.post(AJAX_WS, send, function (res) {
			if(res.success) {
				$('#foto').html(res.html);
			}
		}, 'json');
	},
	zpAvaiAdd = function(obj) {
		var html = '<table class="avaiAddTab">' +
						'<tr><td class="left">' +
							'<div class="name">' + obj.name + '</div>' +
							'<div>' + obj.for + '</div>' +
							'<div class="avai">������� �������: <b>' + obj.count + '</b> ��.</div>' +
							'<table class="inp">' +
								'<tr><td class="label">����������:<td><input type="text" id="count" maxlength="5">' +
								'<tr><td class="label">���� �� ��.:<td><input type="text" id="cena" maxlength="10"><span>�� �����������</span>' +
							'</table>' +
							'<td valign="top">' + obj.img +
					'</table>',
			dialog = _dialog({
				head:'�������� ������� ��������',
				content:html,
				submit:submit
			});
		$('#count').focus();
		function submit() {
			var msg,
				send = {
					op:'zp_avai_add',
					zp_id:obj.zp_id,
					count:$('#count').val(),
					cena:$('#cena').val()
				};
			if(!send.cena)
				send.cena = 0;
			if (!REGEXP_NUMERIC.test(send.count) || send.count == 0) {
				msg = '����������� ������� ����������.';
				$('#count').focus();
			} else if(send.cena != 0 && !REGEXP_CENA.test(send.cena)) {
				msg = '����������� ������� ����.';
				$('#cena').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						obj.callback(res);
						dialog.close();
						_msg('�������� ������� �������� �����������.');
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-48,
					left:92
				});
		}
	},
	zpAvaiNo = function(c) {
		if(c == 0) {
			_dialog({
				top:100,
				width:300,
				head:'��� �������',
				content:'<center>�������� ��� � �������.</center>',
				butSubmit:'',
				butCancel:'�������'
			});
			return true;
		}
		return false;
	},
	zpAvaiUpdate = function() {
		var send = {
			op:'zp_avai_update',
			zp_id: ZP.id
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				ZP.count = res.count;
				$('.move').html(res.move);
				$('.avai')
					[(res.count == 0 ? 'add' : 'remove') + 'Class']('no')
					.html(res.count == 0 ? '��� � �������.' : '� ������� ' + res.count + ' ��.');
			}
		}, 'json');
	},

	historySpisok = function() {
		var send = {
			op:'history_spisok',
			worker:$('#worker').val(),
			action:$('#action').val()
		};
		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			$('.left').html(res.html);
			$('#mainLinks').removeClass('busy');
		}, 'json');
	},
	remindSpisok = function() {
		var send = {
			op:'remind_spisok',
			status:$('#status').val(),
			private:$('#private').val()
		};
		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			$('.left').html(res.html);
			$('#mainLinks').removeClass('busy');
		}, 'json');
	},
	incomeSpisok = function() {
		var send = {
			op:'income_spisok',
			day:$('.selected').val(),
			del:$('#del').val()
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				$('.inc-path').html(res.path);
				$('.spisok').html(res.html);
			}
		}, 'json');
	},
	expenseSpisok = function() {
		var send = {
			op:'expense_spisok',
			category:$('#category').val(),
			worker:$('#worker').val(),
			year:$('#year').val(),
			month:$('#monthSum').val()
		};
		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('#spisok').html(res.html);
				$('#monthList').html(res.mon);
			}
		}, 'json');
	},
	expenseCategoryAdd = function(spisok, obj) {
		var html = '<TABLE>' +
			'<tr><td class="label">������������:<TD><INPUT type="text" id="name" style="width:190px" />' +
			'</TABLE>',
			dialog = _dialog({
				width:320,
				head:'����� ��������� ��� ��������',
				content:html,
				submit:submit
			}),
			name = $('#name');
		name.focus();

		function submit() {
			var send = {
				op:'setup_expense_category_add',
				name:name.val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">�� ������� ������������.</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-51,
					left:73
				});
				name.focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ��������� �������.');
						obj.add({uid:res.id,title:send.name}).val(res.id);
					}
				}, 'json');
			}
		}
	},
	reportKassaLoad = function() {
		var send = {
			op:'report_kassa_load',
			del_show:$('#kassaShowDel').val() == 1 ? 1 : 0
		};
		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			$('#report_kassa #spisok').html(res.html);
			$('#mainLinks').removeClass('busy');
		}, 'json');
	};

$.fn.clientSel = function(o) {
	var t = $(this);
	o = $.extend({
		width:260,
		add:null,
		client_id:t.val() || 0,
		func:function() {}
	}, o);

	if(o.add)
		o.add = function() {
			clientAdd(function(res) {
				var arr = [];
				arr.push(res);
				t._select(arr);
				t._select(res.uid);
			});
		};

	t._select({
		width:o.width,
		title0:'������� ������� ������ �������...',
		spisok:[],
		write:1,
		nofind:'�������� �� �������',
		func:o.func,
		funcAdd:o.add,
		funcKeyup:clientsGet
	});
	clientsGet();

	function clientsGet(val) {
		var send = {
			op:'client_sel',
			val:val || '',
			client_id:o.client_id
		};
		t._select('process');
		$.post(AJAX_WS, send, function(res) {
			t._select('cancel');
			if(res.success) {
				t._select(res.spisok);
				if(o.client_id) {
					t._select(o.client_id);
					o.client_id = 0;
				}
			}
		}, 'json');
	}
	return t;
};

$.fn.device = function(o) {
	o = $.extend({
		width:150,
		func:function() {},
		type_no:0,
		device_id:0,
		vendor_id:0,
		model_id:0,
		device_ids:null, // ������ id, ������� ����� �������� � ������ ��� ���������
		vendor_ids:null, // ��� ��������������
		model_ids:null,  // ��� �������
		add:0,
		device_funcAdd:null, // ������� �����, ���� ������ ��������� ����� ��������
		vendor_funcAdd:null,
		model_funcAdd:null
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
	if (o.add > 0) {
		o.device_funcAdd = function() {
			var html = '<table class="device-add-tab">' +
				'<tr><td class="label">��������:<TD><input type="text" id="daname">' +
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
			var html ='<TABLE class="device-add-tab">' +
				'<TR><TD class="label">��������:<TD><input type="text" id="vaname">' +
				'</TABLE>';
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
			var html = '<TABLE class="device-add-tab">' +
				'<TR><TD class="label">��������:<TD><input type="text" id="maname">' +
				'</TABLE>';
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
			$.post(AJAX_WS, send, function(res) {
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
			$.post(AJAX_WS, send, function(res) {
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
					getModel();
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
			$.post(AJAX_WS, send, function (res) {
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
		title0:device_no[o.type_no],
		spisok:DEV_SPISOK,
		func:function(id) {
			venSel.val(0);
			modSel.val(0)._select('remove'); //��������� ������ ������ � ��������������� � 0, ���� ��� �����
			if(!id)
				venSel._select('remove');
			else
				getVendor(0);
			o.func(getIds());
		},
		funcAdd:o.device_funcAdd,
		bottom:3
	});
	if(o.device_id)
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
		name = name.toLowerCase();
		for(var n = 0; n < spisok.length; n++)
			if(spisok[n].title.toLowerCase() == name)
				return true;
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
					top:60,
					width:250,
					content:'������� ���: <b>' + scannerWord + '</b>',
					butSubmit:'�����'
				});
				var send = {
					op:'scanner_word',
					word:scannerWord
				};
				scannerDialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						if(res.zayav_id)
							document.location.href = URL + '&p=zayav&d=info&id=' + res.zayav_id;
						else
							document.location.href = URL + '&p=zayav&d=add&' + (res.imei ? 'imei' : 'serial') + '=' + send.word;
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
			op:'tooltip_zayav_info_get',
			id:t.attr('val')
		};
		$.post(AJAX_WS, send, function(res) {
			tooltip
				.html(res.html)
				.removeClass('empty');
		}, 'json');
	})
	.on('keyup', '#zayavNomer', function() {
		var t = $(this);
		if(t.hasClass('busy'))
			return;
		t.next('.zayavNomerTab').remove().end()
		 .after('<img src="/img/upload.gif">')
		 .addClass('busy');
		var send = {
			op:'zayav_nomer_info',
			nomer:t.val()
		};
		$.post(AJAX_WS, send, function(res) {
			t.removeClass('busy')
			 .next('img').remove();
			if(res.success)
				t.after(res.html);
		}, 'json');
	})

	.on('click', '#client ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = clientFilter();
		send.op = 'client_next';
		send.page = next.attr('val');
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success) {
				next.remove();
				$('#client .left').append(res.spisok);
			} else
				next.removeClass('busy');
		}, 'json');
	})

	.on('click', '#clientInfo .cedit', function() {
		var html = '<TABLE class="client_edit">' +
			'<tr><td class="label">���:<TD><input type="text" id="fio" value="' + $('.fio').html() + '">' +
			'<tr><td class="label">�������:<TD><input type="text" id="telefon" value="' + $('.telefon').html() + '">' +
			'<tr><td class="label">����������:<TD><input type="hidden" id="join">' +
			'<TR class=tr_join><TD class="label">� ��������:<TD><input type="hidden" id="client2">' +
			'</TABLE>';
		var dialog = _dialog({
			head:'�������������� ������ �������',
			top:60,
			width:400,
			content:html,
			butSubmit:'���������',
			submit:submit
		});
		$('#fio,#telefon').keyEnter(submit);
		$('#join')._check();
		$('#join_check')
			.click(function() {
				$('.tr_join').toggle();
			})
			.vkHint({
				msg:'<B>����������� ��������.</B><br />' +
					'����������, ���� ���� ������ ��� ����� � ���� ������.<br /><br />' +
					'������� ������ ����� �����������.<br />�������� ������� �������.<br />' +
					'��� ������, ���������� � ������� ������ ������ �����<br />�����������.<br /><br />' +
					'��������, �������� ����������!',
				width:330,
				delayShow:1500,
				top:-162,
				left:-79,
				indent:80
			});
		$('#client2').clientSel({width:240});
		function submit() {
			var msg,
				send = {
					op:'client_edit',
					client_id:CLIENT.id,
					fio:$.trim($('#fio').val()),
					telefon:$.trim($('#telefon').val()),
					join:$('#join').val(),
					client2:$('#client2').val()
				};
			if(send.join == 0)
				send.client2 = 0;
			if(!send.fio) {
				msg = '�� ������� ��� �������.';
				$("#fio").focus();
			} else if(send.join == 1 && send.client2 == 0)
				msg = '������� ������� �������.';
			else if(send.join == 1 && send.client2 == CLIENT.id)
				msg = '�������� ������� �������.';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						$('.fio').html(send.fio);
						$('.telefon').html(send.telefon);
						CLIENT.fio = send.fio;
						if(send.client2 > 0)
							document.location.reload();
						dialog.close();
						_msg('������ ������� ��������.');
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>' + msg + '</SPAN>',
					top:-47,
					left:100,
					indent:50,
					show:1,
					remove:1
				});
		}
	})
	.on('click', '#clientInfo #zayav_spisok ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = clientZayavFilter();
		send.op = 'client_zayav_next';
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#clientInfo .remind_add', function() {
		var html = '<TABLE class="remind_add_tab">' +
			'<tr><td class="label">������:<TD><b>' + CLIENT.fio + '</b>' +
			'<tr><td class="label top">�������� �������:<TD><TEXTAREA id="txt"></TEXTAREA>' +
			'<tr><td class="label">������� ���� ����������:<TD><INPUT type="hidden" id="data">' +
			'<tr><td class="label">������:<TD><INPUT type="hidden" id="private">' +
			'</TABLE>';
		var dialog = _dialog({
				top:60,
				width:480,
				head:'���������� ������ �������',
				content:html,
				submit:submit
			}),
			txt = $('.remind_add_tab #txt'),
			day = $('.remind_add_tab #data'),
			priv = $('.remind_add_tab #private');
		txt.autosize().focus();
		day._calendar();
		priv._check();
		$('.remind_add_tab #private_check').vkHint({
			msg:'������� �������<br />������ ������ ��.',
			top:-71,
			left:-11,
			indent:'left',
			delayShow:1000
		});

		function submit() {
			var send = {
				op:'report_remind_add',
				from_client:1,
				client_id:CLIENT.id,
				zayav_id:0,
				txt:txt.val(),
				day:day.val(),
				private:priv.val()
			};
			if(!send.txt) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>�� ������� ���������� �����������.</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-48,
					left:150
				});
				txt.focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('����� ������� ������� ���������.');
						$('#remind_spisok').html(res.html);
					}
				}, 'json');
			}
		}//submit()
	})
	.on('click', '#clientInfo #histories ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = {
				op:'client_history_next',
				page:$(this).attr('val'),
				client_id:CLIENT.id
			};
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})


	.on('click', '#zayav ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = zayavFilter();
		send.op = 'zayav_next';
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
		document.location.href = URL + '&p=zayav&d=info&id=' + $(this).attr('val');
	})
	.on('mouseenter', '.zayav_unit', function() {
		var t = $(this),
			msg = t.find('.msg').val();
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
	.on('click', '#zayav #sort_radio div', zayavSpisokLoad)
	.on('click', '#zayav #zpzakaz_radio div', zayavSpisokLoad)
	.on('click', '#zayav #filter_break', function() {
		zFind.clear();
		$('#sort')._radio(1);
		$('#desc')._check(0);
		$('#status').rightLink(0);
		$('#diff')._check(0);
		$('#zpzakaz')._radio(0);
		$('#dev').device({
			width:155,
			type_no:1,
			device_ids:DEVICE_IDS,
			vendor_ids:VENDOR_IDS,
			model_ids:MODEL_IDS,
			func:zayavSpisokLoad
		});
		$('#device_place')._select(0);
		$('#devstatus')._select(0);
		zayavSpisokLoad();
	})

	.on('click', '#zayavInfo .zedit', function() {
		var html = '<TABLE class="zayav-info-edit">' +
			'<tr><td class="label r">������:		<TD><INPUT type="hidden" id="client_id" value="' + ZAYAV.client_id + '">' +
			'<tr><td class="label r top">����������:<TD><TABLE><TD id="dev"><TD id="device_image"></TABLE>' +
			'<tr><td class="label r">IMEI:		  <TD><INPUT type="text" id="imei" maxlength="20" value="' + ZAYAV.imei + '">' +
			'<tr><td class="label r">�������� �����:<TD><INPUT type="text" id="serial" maxlength="30" value="' + ZAYAV.serial + '">' +
			'<tr><td class="label r">����:' +
				'<td><INPUT type="hidden" id="color_id" value="' + ZAYAV.color_id + '" />' +
					'<span class="color_dop dn"><tt>-</tt><INPUT TYPE="hidden" id="color_dop" value="' + ZAYAV.color_dop + '" /></span>' +
			'<tr class="tr_equip' + (ZAYAV.equip ? '' : ' dn') + '">' +
				'<td class="label r top">������������:<TD class="equip_spisok">' + ZAYAV.equip +
		'</TABLE>',
			dialog = _dialog({
				width:410,
				top:30,
				head:'������ �' + ZAYAV.nomer + ' - ��������������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#client_id').clientSel();
		$('#client_id_select').vkHint({
			msg:'���� ���������� ������, �� ���������� � ������� ������ ����������� �� ������ �������.',
			width:200,
			top:-83,
			left:-2,
			delayShow:1500,
			correct:0
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
		colorSelDop(ZAYAV.color_id);

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
					equip:''
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
			else {
				dialog.process();
				$.post(AJAX_WS, send, function (res) {
					dialog.close();
					_msg('������ ��������!');
					document.location.reload();
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					top:-47,
					left:107,
					show:1,
					remove:1,
					correct:0
				});
		}
	})
	.on('click', '#zayavInfo .delete', function() {
		var dialog = _dialog({
			top:110,
			width:250,
			head:'�������� ������',
			content:'<CENTER>����������� �������� ������.</CENTER>',
			butSubmit:'�������',
			submit:function() {
				var send = {
					op:'zayav_delete',
					zayav_id:ZAYAV.id
				};
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success)
						location.href = URL + '&p=client&d=info&id=' + res.client_id;
				}, 'json');
			}
		});
	})
	.on('click', '#zayavInfo .remind_add', function() {
		var html = '<TABLE class="remind_add_tab">' +
			'<tr><td class="label">������:<TD>�<b>' + ZAYAV.nomer + '</b>' +
			'<tr><td class="label top">�������� �������:<TD><TEXTAREA id="txt"></TEXTAREA>' +
			'<tr><td class="label">������� ���� ����������:<TD><INPUT type="hidden" id="data">' +
			'<tr><td class="label">������:<TD><INPUT type="hidden" id="private">' +
		'</TABLE>';
		var dialog = _dialog({
				top:60,
				width:480,
				head:'���������� ������ �������',
				content:html,
				submit:submit
			}),
			txt = $('.remind_add_tab #txt'),
			day = $('.remind_add_tab #data'),
			priv = $('.remind_add_tab #private');
		txt.autosize().focus();
		day._calendar();
		priv._check();
		$('.remind_add_tab #private_check').vkHint({
			msg:'������� �������<br />������ ������ ��.',
			top:-71,
			left:-11,
			indent:'left',
			delayShow:1000
		});

		function submit() {
			var send = {
					op:'report_remind_add',
					from_zayav:1,
					client_id:0,
					zayav_id:ZAYAV.id,
					txt:txt.val(),
					day:day.val(),
					private:priv.val()
				};
			if(!send.txt) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>�� ������� ���������� �����������.</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-48,
					left:150
				});
				txt.focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ������� ������� ���������.');
						$('#remind_spisok').html(res.html);
					} else {
						dialog.abort();
					}
				}, 'json');
			}
		}//submit()
	})
	.on('click', '#zayavInfo .acc_add', function() {
		var html = '<TABLE class="zayav_accrual_add">' +
				'<tr><td class="label">�����: <TD><input type="text" id="sum" class="money" maxlength="5" /> ���.' +
				'<tr><td class="label">����������:<em>(�� �����������)</em><TD><input type="text" id="prim" maxlength="100" />' +
				'<tr><td class="label">������ ������: <TD><INPUT type="hidden" id="acc_status" value="2" />' +
				'<tr><td class="label">��������� ����������:<TD><INPUT type="hidden" id="acc_dev_status" value="5" />' +
				'<tr><td class="label">�������� �����������:<TD><INPUT type="hidden" id="acc_remind" />' +
			'</TABLE>' +

			'<TABLE class="zayav_accrual_add remind">' +
				'<tr><td class="label">����������:<TD><input type="text" id="reminder_txt" value="��������� � �������� � ����������.">' +
				'<tr><td class="label">����:<TD><INPUT type="hidden" id="reminder_day">' +
			'</TABLE>';
		var dialog = _dialog({
			top:60,
			width:420,
			head:'������ �' + ZAYAV.nomer + ' - ���������� �� ����������� ������',
			content:html,
			submit:submit
		});
		$('#sum').focus();
		$('#sum,#prim,#reminder_txt').keyEnter(submit);
		$('#acc_status')._dropdown({spisok:STATUS});
		$('#acc_dev_status')._dropdown({spisok:DEVSTATUS_SPISOK});
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
					dev_status:$('#acc_dev_status').val(),
					remind:$('#acc_remind').val(),
					remind_txt:$('#reminder_txt').val(),
					remind_day:$('#reminder_day').val()
				};
			if(!REGEXP_NUMERIC.test(send.sum)) { msg = '����������� ������� �����.'; $('#sum').focus(); }
			else if(send.remind == 1 && !send.remind_txt) { msg = '�� ������ ����� �����������'; $('#reminder_txt').focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('���������� ������� �����������!');
						$('._spisok._money').append(res.html);
						zayavInfoMoneyUpdate(res);
						if(res.status) {
							$('#status')
								.html(res.status.name)
								.css('background-color', '#' + res.status.color);
							$('#status_dtime').html(res.status.dtime);
						}
						if(res.remind)
							$('#remind_spisok').html(res.remind);
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
					show:1,
					correct:0
				});
		}
	})
	.on('click', '#zayavInfo .acc_del', function() {
		var send = {
			op:'zayav_accrual_del',
			id:$(this).attr('val')
		};
		var tr = $(this).parent().parent();
		tr.html('<td colspan="4" class="deleting">��������... <img src=/img/upload.gif></td>');
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				tr.find('.deleting').html('���������� �������. <a class="acc_rest" val="' + send.id + '">������������</a>');
				zayavInfoMoneyUpdate(res);
			}
		}, 'json');
	})
	.on('click', '#zayavInfo .acc_rest', function() {
		var send = {
				op:'zayav_accrual_rest',
				id:$(this).attr('val')
			},
			t = $(this),
			tr = t.parent().parent();
		t.after('<img src=/img/upload.gif>').remove();
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				tr.after(res.html).remove();
				zayavInfoMoneyUpdate(res);
			}
		}, 'json');
	})
	.on('click', '#zayavInfo .op_add', function() {
		var html = '<TABLE class="zayav_oplata_add">' +
			'<TR><TD class="label">�����:<TD><input type="text" id="sum" class="money" maxlength="5"> ���.' +
			'<TR><TD class="label">������ ��������� � �����?:<TD><input type="hidden" id="kassa" value="-1">' +
			'<TR><TD class="label">��������������� ����������:<TD><input type="hidden" id="dev_place" value="2">' +
			'<TR><TD class="label">����������:<em>(�� �����������)</em><TD><input type="text" id="prim">' +
		'</TABLE>';
		var dialog = _dialog({
			top:60,
			width:440,
			head:'������ �' + ZAYAV.nomer + ' - �������� �������',
			content:html,
			submit:submit
		});
		$('#sum').focus();
		$('#sum,#prim').keyEnter(submit);
		$('#kassa')._radio({
			spisok:[
				{uid:1, title:'��'},
				{uid:0, title:'���'}
			]
		});
		$('#kassa_radio').vkHint({
			msg:'���� ��� �������� �����<br />� ������ �������� � ����������,<br />������� "��".',
			top:-83,
			left:-60,
			delayShow:1000
		});
		$('#dev_place')._dropdown({spisok:DEVPLACE_SPISOK});
		function submit() {
			var msg,
				send = {
					op:'zayav_oplata_add',
					zayav_id:ZAYAV.id,
					sum:$('#sum').val(),
					kassa:$('#kassa').val(),
					prim:$.trim($('#prim').val()),
					dev_place:$('#dev_place').val()
				};
			if(!REGEXP_NUMERIC.test(send.sum)) { msg = '����������� ������� �����.'; $('#sum').focus(); }
			else if(send.kassa == -1) msg = '�������, ������ ��������� � ����� ��� ���.';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function (res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('����� ������� �����!');
						$('._spisok._money').append(res.html);
						zayavInfoMoneyUpdate(res);
					}
				}, 'json');
			}

			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-48,
					left:135
				});
		}
	})
	.on('click', '#zayavInfo .status_place', function() {
		var html = '<TABLE style="border-spacing:8px">' +
			'<TR><TD class="label r topi">������ ������:<TD><input type="hidden" id="z_status" value="' + ZAYAV.z_status + '">' +
			'<TR><TD class="label r topi">��������������� ����������:<TD><input type="hidden" id="place" value="' + ZAYAV.dev_place + '">' +
			'<TR><TD class="label r topi">��������� ����������:<TD><input type="hidden" id="dev_status" value="' + ZAYAV.dev_status + '">' +
			'</TABLE>',
			dialog = _dialog({
				width:400,
				top:30,
				head:'��������� ������� ������ � ��������� ����������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#z_status')._radio({
			spisok:STATUS,
			light:1
		});

		var spisok = [];
		for(var n = 0; n < DEVPLACE_SPISOK.length; n++)
			spisok.push(DEVPLACE_SPISOK[n]);
		spisok.push({
			uid:0,
			title:'������: <INPUT type="text" ' +
								 'id="place_other" ' + (!ZAYAV.place_other ? 'class="dn" ' : '') +
								 'maxlength="20" ' +
								 'value="' + ZAYAV.place_other + '">'
		});
		$('#place')._radio({
			spisok:spisok,
			light:1,
			func:function(val) {
				$('#place_other')[(val == 0 ? 'remove' : 'add') + 'Class']('dn');
				if(val == 0)
					$('#place_other').val('').focus();
			}
		});
		$('#dev_status')._radio({
			spisok:DEVSTATUS_SPISOK,
			light:1
		});

		function submit() {
			var msg,
				send = {
				op:'zayav_status_place',
				zayav_id:ZAYAV.id,
				zayav_status:$('#z_status').val(),
				dev_status:$('#dev_status').val(),
				dev_place:$('#place').val(),
				place_other:$('#place_other').val()
			};
			if(send.dev_place > 0)
				send.place_other = '';
			if(send.dev_place == 0 && send.place_other == '') {
				msg = '�� ������� ��������������� ����������';
				$('#place_other').focus();
			} else if(send.dev_status == 0)
				msg = '�� ������� ��������� ����������';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('��������� ���������.');
						$('#status')
							.html(res.z_status.name)
							.css('background-color', '#' + res.z_status.color);
						$('#status_dtime').html(res.z_status.dtime)
						$('.dev_status').html(res.dev_status);
						$('.dev_place').html(res.dev_place);
						ZAYAV.z_status = send.zayav_status;
						ZAYAV.dev_status = send.dev_status;
						ZAYAV.dev_place = send.dev_place;
						ZAYAV.place_other = send.place_other;
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>' + msg + '</SPAN>',
					top:-47,
					left:103,
					indent:50,
					show:1,
					remove:1,
					correct:0
				});
		}
	})
	.on('click', '#zayavInfo .zakaz', function() {
		var t = $(this),
			send = {
				op:'zayav_zp_zakaz',
				zayav_id:ZAYAV.id,
				zp_id:t.parent().parent().attr('val')
			};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				t.html('��������!').attr('class', 'zakaz_ok');
				_msg(res.msg);
			}
		}, 'json');
	})
	.on('click', '#zayavInfo .zakaz_ok', function() {
		location.href = URL + '&p=zp&menu=3';
	})
	.on('click', '#zayavInfo .zpAdd', function() {
		var html = '<div class="zayav_zp_add">' +
				'<CENTER>���������� �������� � ����������<br />' +
					'<b>' +
						DEV_ASS[ZAYAV.device] + ' ' +
						VENDOR_ASS[ZAYAV.vendor] + ' ' +
						MODEL_ASS[ZAYAV.model] +
					'</b>.'+
				'</CENTER>' +
				'<TABLE style="border-spacing:6px">' +
					'<TR><TD class="label r">������������ ��������:<TD><input type="hidden" id="name_id">' +
					'<TR><TD class="label r">������:<TD><input type="text" id="version" maxlength="30">' +
					'<TR><TD class="label r">����:<TD><input type="hidden" id="color_id">' +
					'<TR><TD class="label r">�/�:<TD><input type="hidden" id="bu">' +
				'</TABLE>' +
			'</div>',
			dialog = _dialog({
				top:40,
				width:380,
				head:'�������� ����� ��������',
				content:html,
				submit:submit
			});

		$('#name_id')._select({
			width:200,
			title0:'������������ �� �������',
			spisok:ZPNAME_SPISOK
		});
		$('#color_id')._select({
			width:130,
			title0:'���� �� ������',
			spisok:COLOR_SPISOK
		});
		$('#bu')._check();
		function submit() {
			var send = {
				op:'zayav_zp_add',
				zayav_id: ZAYAV.id,
				name_id:$('#name_id').val(),
				version:$('#version').val(),
				color_id:$('#color_id').val(),
				bu:$('#bu').val()
			};
			if(send.name_id == 0)
				dialog.bottom.vkHint({msg:'<SPAN class="red">�� ������� ������������ ��������.</SPAN>',
					top:-47,
					left:56,
					show:1,
					remove:1,
					correct:0
				});
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						_msg('�������� �������� �����������.');
						dialog.close();
						$('#zpSpisok')
							.append(res.html)
							.find('._empty').remove();
					}
				}, 'json');
			}
		}
	})
	.on('click', '#zayavInfo .set', function() {
		var unit = $(this).parent().parent();
		var html = '<CENTER class="zayav_zp_set">' +
			'��������� ��������<br />' + unit.find('a:first').html() + '.<br />' +
			(unit.find('.color').length > 0 ? unit.find('.color').html() + '.<br />' : '') +
			'<br />���������� �� ��������� �����<br />����� ��������� � ������� � ������.' +
		'</CENTER>',
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
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('��������� �������� �����������.');
					unit.after(res.zp_unit).remove();
					$('.vkComment').after(res.comment).remove();
				}
			},'json');
		}
	})

	.on('click', '#zp #bu_check', zpSpisokLoad)
	.on('click', '#zp ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = zpFilter();
		send.op = 'zp_next';
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.spisok).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#zp .avai_add', function() {
		var unit = $(this);
		while(!unit.hasClass('unit'))
			unit = unit.parent();
		var obj = {
			zp_id:unit.attr('val'),
			name:unit.find('.name').html(),
			for:unit.find('.for').html(),
			count:$(this).hasClass('avai') ? $(this).find('b').html() : 0,
			img:unit.find('.img').html(),
			callback:function(res) {
				unit.find('.avai_add')
					.removeClass('hid')
					.addClass('avai')
					.html('� �������: <b>' + res.count + '</b>');
			}
		}
		zpAvaiAdd(obj);
		if($('.avaiAddTab img').attr('val'))
			$('.avaiAddTab img').addClass('fotoView');
	})
	.on('mouseenter', '.zpzakaz:not(.busy)', function() {
		window.zakaz_count = $(this).find('tt:first').next('b').html();
	})
	.on('mouseleave', '.zpzakaz:not(.busy)', function() {
		var t = $(this),
			count = t.find('tt:first').next('b').html(),
			unit = t;
		if(count != window.zakaz_count) {
			t.removeClass('hid')
			 .addClass('busy')
			 .find('.cnt').html('���: <b></b>');
			while(!unit.hasClass('unit'))
				unit = unit.parent();
			var send = {
				op:'zp_zakaz_edit',
				zp_id:unit.attr('val'),
				count:count
			};
			$.post(AJAX_WS, send, function(res) {
				t.removeClass('busy');
				if(res.success) {
					t.find('.cnt').html(count > 0 ? '���: <b>' + count + '</b>' : '���');
					if(count == 0)
						t.addClass('hid');
				}
			}, 'json');
		}
	})
	.on('click', '.zpzakaz tt', function() {
		var t = $(this),
			znak = t.html(),
			c = t[znak == '+' ? 'prev' : 'next'](),
			count = c.html();
		if(znak == '+')
			count++;
		else {
			count--;
			if(count < 0)
				count = 0;
		}
		c.html(count);
	})
	.on('click', '#zp .add', function() {
		var html = '<table class="zp_add_dialog">' +
			'<tr><td class="label">������������ ��������:<td><input type="hidden" id="name_id">' +
			'<tr><td class="label top">����������:<td id="add_dev">' +
			'<tr><td class="label">������:<td><input type="text" id="version">' +
			'<tr><td class="label">�/�:<td><input type="hidden" id="add_bu">' +
			'<tr><td class="label">����:<td><input type="hidden" id="color_id">' +
			'</table>',
			dialog = _dialog({
				top:70,
				width:380,
				head:'�������� ����� �������� � �������',
				content:html,
				submit:submit
			});
		$('#name_id')._select({
			width:200,
			title0:'������������ �� �������',
			spisok:ZPNAME_SPISOK
		});
		$('#color_id')._select({
			width:130,
			title0:'���� �� ������',
			spisok:COLOR_SPISOK
		});
		$('#add_bu')._check();
		$('#add_dev').device({width:200});

		function submit() {
			var msg,
				send = {
					op:'zp_add',
					name_id:$('#name_id').val(),
					device_id:$('#add_dev_device').val(),
					vendor_id:$('#add_dev_vendor').val(),
					model_id:$('#add_dev_model').val(),
					version:$('#version').val(),
					bu:$('#add_bu').val(),
					color_id:$('#color_id').val()
				};
			if(send.name_id == 0) msg = '�� ������� ������������ ��������.';
			else if(send.device_id == 0) msg = '�� ������� ����������';
			else if(send.vendor_id == 0) msg = '�� ������ �������������';
			else if(send.model_id == 0) msg = '�� ������� ������';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						$("#zp_name")._select(send.name_id);
						$("#dev").device({
							width:153,
							type_no:1,
							device_ids:WS_DEVS,
							device_id:send.device_id,
							vendor_id:send.vendor_id,
							model_id:send.model_id,
							func:zpSpisokLoad
						});
						zpSpisokLoad();
					}
				},'json');
			}

			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					left:110,
					top:-47,
					indent:50,
					show:1,
					remove:1
				});
		}
	})

	.on('click', '#zpInfo .avai_add', function() {
		var obj = ZP;
		obj.zp_id = obj.id;
		obj.callback = zpAvaiUpdate;
		zpAvaiAdd(obj);
		$('.avaiAddTab img')
			.removeAttr('height')
			.width(80);
	})
	.on('click', '#zpInfo .edit', function() {
		var html = '<table class="zp_add_dialog">' +
				'<tr><td class="label">������������ ��������:<td><input type="hidden" id="name_id" value="' + ZP.name_id + '">' +
				'<tr><td class="label top">����������:<td id="add_dev">' +
				'<tr><td class="label">������:<td><input type="text" id="version" value="' + ZP.version + '">' +
				'<tr><td class="label">�/�:<td><input type="hidden" id="add_bu" value="' + ZP.bu + '">' +
				'<tr><td class="label">����:<td><input type="hidden" id="color_id" value="' + ZP.color_id + '">' +
			'</table>',
			dialog = _dialog({
				top:30,
				width:380,
				head:'�������������� ��������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#name_id')._select({
			width:200,
			title0:'������������ �� �������',
			spisok:ZPNAME_SPISOK
		});
		$('#color_id')._select({
			width:130,
			title0:'���� �� ������',
			spisok:COLOR_SPISOK
		});
		$('#add_bu')._check();
		$('#add_dev').device({
			width:200,
			device_id:ZP.device,
			vendor_id:ZP.vendor,
			model_id:ZP.model
		});

		function submit() {
			var msg,
				send = {
					op:'zp_edit',
					zp_id:ZP.id,
					name_id:$('#name_id').val(),
					device_id:$('#add_dev_device').val(),
					vendor_id:$('#add_dev_vendor').val(),
					model_id:$('#add_dev_model').val(),
					version:$('#version').val(),
					bu:$('#add_bu').val(),
					color_id:$('#color_id').val()
				};
			if(send.name_id == 0) msg = '�� ������� ������������ ��������.';
			else if(send.device_id == 0) msg = '�� ������� ����������';
			else if(send.vendor_id == 0) msg = '�� ������ �������������';
			else if(send.model_id == 0) msg = '�� ������� ������';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('�������������� ������ �����������.');
						window.location.reload();
					}
				},'json');
			}

			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					left:110,
					top:-47,
					indent:50,
					show:1,
					remove:1
				});
		}
	})
	.on('click', '#zpInfo .set', function() {
		if(zpAvaiNo(ZP.count))
			return;
		var html = '<table class="zp_dec_dialog">' +
				'<tr><td class="label r">����������:<td><input type="text" id="count" value="1"><span>(max: <b>' + ZP.count + '</b>)</span>' +
				'<tr><td class="label r top">����� ������:<td><input type="text" id="zayavNomer">' +
				'<tr><td class="label r top">����������:<td><textarea id="prim"></textarea>' +
			'</table>',
			dialog = _dialog({
				width:340,
				head:'��������� ��������',
				content:html,
				submit:submit
			});
		$('#count').focus().select();

		function submit() {
			var msg,
				send = {
					op:'zayav_zp_set',
					zp_id:ZP.id,
					count:$('#count').val(),
					zayav_id:$('#zayavNomerId').length > 0 ? $('#zayavNomerId').val() : 0,
					prim:$('#prim').val()
				};
			if(!REGEXP_NUMERIC.test(send.count) || send.count > ZP.count || send.count == 0) {
				msg = '����������� ������� ����������.';
				$('#count').focus();
			} else if(send.zayav_id == 0) {
				msg = '�� ������ ����� ������.';
				$('#zayavNomer').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						zpAvaiUpdate();
						dialog.close();
						_msg('��������� �������� �����������.');
					}
				},'json');
			}

			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					left:74,
					top:-47,
					indent:50,
					show:1,
					remove:1
				});
		}
	})
	.on('click', '#zpInfo .sale', function() {
		if(zpAvaiNo(ZP.count))
			return;
		var html = '<table class="zp_dec_dialog">' +
				'<tr><td class="label r">����������:<td><input type="text" id="count" value="1"><span>(max: <b>' + ZP.count + '</b>)</span>' +
				'<tr><td class="label r">���� �� ��.:<td><input type="text" id="cena" maxlength="8"> ���.' +
				'<tr><td class="label r">������ ��������� � �����?:<td><input type="hidden" id="kassa" value="-1">' +
				'<tr><td class="label r">������:<td><input type="hidden" id="client_id">' +
				'<tr><td class="label r top">����������:<td><textarea id="prim"></textarea>' +
				'</table>',
			dialog = _dialog({
				top:40,
				width:440,
				head:'������� ��������',
				content:html,
				submit:submit
			});

		$('#count').focus().select();
		$('#client_id').clientSel({add:1,width:240});
		$('#kassa')._radio({
			spisok:[
				{uid:1, title:'��'},
				{uid:0, title:'���'}
			]
		});

		function submit() {
			var msg,
				send = {
					op:'zp_sale',
					zp_id:ZP.id,
					count:$('#count').val(),
					cena:$('#cena').val(),
					kassa:$('#kassa').val(),
					client_id:$('#client_id').val(),
					prim:$('#prim').val()
				};
			if(!REGEXP_NUMERIC.test(send.count) || send.count > ZP.count || send.count == 0) {
				msg = '����������� ������� ����������.';
				$('#count').focus();
			} else if(!REGEXP_CENA.test(send.cena)) {
				msg = '����������� ������� ����.';
				$('#cena').focus();
			} else if(send.kassa == '-1') msg = '�������, ��������� ������ � ����� ��� ���.';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						zpAvaiUpdate();
						dialog.close();
						_msg('������� �������� �����������.');
					}
				},'json');
			}

			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					left:123,
					top:-47,
					indent:50,
					show:1,
					remove:1
				});
		}
	})
	.on('click', '#zpInfo .defect,#zpInfo .return,#zpInfo .writeoff', function() {
		if(zpAvaiNo(ZP.count))
			return;
		var rus = {defect:'����������', return:'�������', 'writeoff':'��������'},
			end = {defect:'���', return:'��', 'writeoff':'���'},
			type = $(this).attr('class'),
			html = '<table class="zp_dec_dialog">' +
				'<tr><td class="label r">����������:<td><input type="text" id="count" value="1"><span>(max: <b>' + ZP.count + '</b>)</span>' +
				'<tr><td class="label r top">����������:<td><textarea id="prim"></textarea>' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:340,
				head:rus[type] + ' ��������',
				content:html,
				submit:submit
			});

		$('#count').focus().select();

		function submit() {
			var send = {
				op:'zp_other',
				zp_id:ZP.id,
				type:type,
				count:$('#count').val(),
				prim:$('#prim').val()
			};
			if(!REGEXP_NUMERIC.test(send.count) || send.count > ZP.count || send.count == 0) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">����������� ������� ����������.</SPAN>',
					left:73,
					top:-47,
					indent:50,
					show:1,
					remove:1
				});
				$('#count').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						zpAvaiUpdate();
						dialog.close();
						_msg(rus[type] + ' �������� ��������' + end[type] + '.');
					}
				},'json');
			}
		}
	})
	.on('click', '#zpInfo .move .img_del', function() {
		var id = $(this).attr('val');
		var dialog = _dialog({
			top:110,
			width:250,
			head:'�������� ������',
			content:'<center>����������� �������� ������.</center>',
			butSubmit:'�������',
			submit:function() {
				var send = {
					op:'zp_move_del',
					id:id
				};
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						zpAvaiUpdate();
						dialog.close();
						_msg('������ �������.');
					}
				}, 'json');
			}
		});
	})
	.on('click', '#zpInfo .move ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = {
				op:'zp_move_next',
				zp_id:ZP.id,
				page:$(this).attr('val')
			};
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.spisok).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#zpInfo .compat_add', function() {
		var sp = ZP,
			html = '<div class="compatAddTab">' +
				'<div class="name">' +
					(sp.bu == 1 ? '<span class="bu">�/y</span>' : '') +
					sp.name + '<br />' +
					sp.for +
				'</div>' +
				'<table class="prop">' +
					(sp.version ? '<tr><td class="label">������:<td>' + sp.version : '') +
					(sp.color_id > 0 ? '<tr><td class="label">����:<td>' + sp.color_name : '') +
				'</table>' +
				'<div class="headName">�������� � ����������:</div>' +
				'<div id="dev"></div>' +
				'<div id="cres"></div>' +
			'</div>',
			dialog = _dialog({
				top:90,
				width:400,
				head:'���������� ������������� � ������� ������������',
				content:html,
				butSubmit:'��������',
				submit:submit
			}),
			cres = $('#cres'),
			dev = {},
			go = 0;
		$('#dev').device({
			width:220,
			device_id:sp.device,
			vendor_id:sp.vendor,
			add:1,
			func:devSelect
		});

		function devSelect(obj) {
			dialog.abort();
			go = 0;
			dev = obj;
			cres.html('');
			if(obj.device_id > 0 && obj.vendor_id > 0 && obj.model_id > 0) {
				if(obj.device_id == sp.device && obj.vendor_id == sp.vendor && obj.model_id == sp.model) {
					cres.html('<em class="red">���������� ������� ������������� �� ��� �� ����������.</em>');
					return;
				}
				var send = {
					op:'zp_compat_find',
					zp_id:sp.id,
					bu:sp.bu,
					name_id:sp.name_id,
					device_id:obj.device_id,
					vendor_id:obj.vendor_id,
					model_id:obj.model_id,
					color_id:sp.color_id
				};
				cres.html('<img src="/img/upload.gif">');
				$.post(AJAX_WS, send, function(res) {
					cres.html('');
					if(res.success)
						finded(res);
				}, 'json');
			}
		}

		function finded(res) {
			if(res.id) {
				if(res.compat_id == sp.compat_id) {
					cres.html('<em class="red">��������� �������� ��� �������� �������������� ���� ��������.</em>');
					return;
				}
				cres.html('�������� <B>' + res.name + '</B><br />' +
						  '����� ��������� � �������������.<br /><br />' +
						  '���������� � ���������, ��������<br />' +
						  '� ������� ����� ������� � ������<br />' +
						  '����� ��� ����� ���������.');
			} else
				cres.html('�������� <b>' + res.name + '</b><br />' +
						  '��� � �������� ���������.<br /><br />' +
						  '��� ���������� ������������� ���<br />' +
						  '����� ������������� ������� � �������.');
			go = 1;
		}

		function submit() {
			if(go == 0) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">�������� ���������� ��� ���������� �������������.</SPAN>',
					left:103,
					top:-47,
					indent:50,
					show:1,
					remove:1
				});
				return;
			}
			var send = {
				op:'zp_compat_add',
				zp_id:sp.id,
				device_id:dev.device_id,
				vendor_id:dev.vendor_id,
				model_id:dev.model_id
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				dialog.abort();
				if(res.success) {
					dialog.close();
					_msg('������������� �������.');
					window.location.reload();
				}
			}, 'json');
		}
	})
	.on('click', '#zpInfo .compatSpisok .img_del', function() {
		var id = $(this).attr('val');
		var dialog = _dialog({
			top:110,
			width:250,
			head:'�������� �������������',
			content:'<center>����������� �������� �������������.</center>',
			butSubmit:'�������',
			submit:function() {
				var send = {
					op:'zp_compat_del',
					id:id,
					zp_id:ZP.id
				};
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('������������� �������.');
						$('.compatCount').html(res.count);
						$('.compatSpisok').html(res.spisok);
					}
				}, 'json');
			}
		});
		return false;
	})

	.on('click', '#history_next', function() {
		var next = $(this),
			send = {
				op:'history_next',
				page:next.attr('val'),
				worker:$('#worker').val(),
				action:$('#action').val()
			};
		if(next.hasClass('busy'))
			return;
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})

	.on('click', '.remind_unit .hist_a', function() {
		$(this).parent().parent().find('.hist').slideToggle();
	})
	.on('click', '.report_remind_add', function() {
		var html = '<TABLE class="remind_add_tab">' +
			'<tr><td class="label">����������:<TD><INPUT type="hidden" id="destination" />' +
			'<tr><td class="label topi" id="target_name"><TD id="target">' +
			'</TABLE>' +

			'<TABLE class="remind_add_tab" id="tab_content">' +
			'<tr><td class="label top">�������:<TD><TEXTAREA id=txt></TEXTAREA>' +
			'<tr><td class="label">������� ���� ����������:<TD><INPUT type="hidden" id="data" />' +
			'<tr><td class="label">������:<TD><INPUT type="hidden" id="priv" />' +
			'</TABLE>';
		var dialog = _dialog({
			top:30,
			width:480,
			head:'���������� ������ �������',
			content:html,
			butSubmit:'��������',
			submit:submit
		});

		$('#destination')._select({
			width:150,
			title0:'�� �������',
			spisok:[
				{uid:1,title:'������'},
				{uid:2,title:'������'},
				{uid:3,title:'������������ �������'}
			],
			func:destination
		});

		$('#txt').autosize();
		$('#data')._calendar();
		$('#priv')._check();
		$('#priv_check').vkHint({
			msg:'������� �������<br />������ ������ ��.',
			top:-71,
			left:-11,
			indent:'left',
			delayShow:1000
		});

		function destination(id) {
			$('#target').html('');
			$('#target_name').html('');
			$('#txt').val('');
			$('#tab_content').css('display', id > 0 ? 'block' : 'none');
			if(id == 1) {
				$('#target_name').html('������:');
				$('#target').html('<div id="client_id"></div>');
				$('#client_id').clientSel();
			}
			if(id == 2) {
				$('#target_name').html('����� ������:');
				$('#target').html('<input type="text" id="zayavNomer" />');
				$('#zayavNomer').focus();
			}
		}

		function submit() {
			var client_id = $('#destination').val() == 1 ? $('#client_id').val() : 0,
				zayav_id = $('#zayavNomerId').length > 0 ? $('#zayavNomerId').val() : 0,
				send = {
					op:'report_remind_add',
					client_id:client_id,
					zayav_id:zayav_id,
					txt:$('#txt').val(),
					day:$('#data').val(),
					private:$('#priv').val()
				},
				msg;
			if($('#destination').val() == 0) msg = '�� ������� ����������.';
			else if($('#destination').val() == 1 && send.client_id == 0) msg = '�� ������ ������.';
			else if($('#destination').val() == 2 && send.zayav_id == 0) {
				msg = '�� ������ ����� ������.';
				$('#zayavNomer').focus();
			} else if(!send.txt) msg = '�� ������� ���������� �����������.';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ������� ������� ���������.');
						$('.left').html(res.html);
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>' + msg + '</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-48,
					left:150
				});
		}
		return false;
	})
	.on('click', '#remind_next', function() {
		var next = $(this),
			send = {
				op:'remind_next',
				page:$(this).attr('val'),
				status:$('#status').val(),
				private:$('#private').val()
			};
		if(next.hasClass('busy'))
			return;
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.remind_unit .edit', function() {
		var dialog = _dialog({
				top:30,
				width:400,
				head:'����� �������� ��� �����������',
				load:1,
				butSubmit:'���������',
				submit:submit
			}),
			curDay,
			id = $(this).attr('val'),
			send = {
				op:'report_remind_get',
				id:id
			};
		$.post(AJAX_WS, send, function(res) {
			curDay = res.day;
			var html = '<TABLE class="remind_action_tab">' +
				'<tr><td class="label">' + (res.client ? '������:' : '') + (res.zayav ? '������:' : '') +
					'<TD>' + (res.client ? res.client : '') + (res.zayav ? res.zayav : '') +
				'<tr><td class="label">�������:<TD><B>' + res.txt + '</B>' +
				'<tr><td class="label">����:<TD>' + res.viewer + ', ' + res.dtime +
				'<tr><td class="label top">��������:<TD><INPUT type="hidden" id=action value="0">' +
				'</TABLE>' +

				'<TABLE class="remind_action_tab" id="new_action">' +
				'<tr><td class="label" id="new_about"><TD id="new_title">' +
				'<tr><td class="label top" id="new_comm"><TD><TEXTAREA id="comment"></TEXTAREA>' +
				'</TABLE>';
			dialog.content.html(html);

			$('#action')._radio({
				spisok:[
					{uid:1, title:'��������� �� ������ ����'},
					{uid:2, title:'���������'},
					{uid:3, title:'��������'}
				],
				func:function(id) {
					$('#new_action').show();
					$('#comment').val('');
					$('#new_about').html('');
					$('#new_title').html('');
					if (id == 1) {
						$('#new_about').html('����:');
						$('#new_title').html('<INPUT type="hidden" id="data">');
						$('#new_comm').html('�������:');
						$('#new_action #data')._calendar();
					}
					if(id == 2) $('#new_comm').html('�����������:');
					if(id == 3) $('#new_comm').html('�������:');
				}
			});

			$('#comment').autosize();
		}, 'json');

		function submit () {
			var send = {
				op:'report_remind_edit',
				id:id,
				action:parseInt($('#action').val()),
				day:curDay,
				status:1,
				history:$('#comment').val(),
				from_zayav:typeof ZAYAV == 'undefined' ? 0 : ZAYAV.id,
				from_client:window.CLIENT ? CLIENT.id : 0
			};
			switch(send.action) {
				case 1: send.day = $('#data').val(); break;
				case 2: send.status = 2; break; // ���������
				case 3: send.status = 0;		// ��������
			}

			var msg;
			if(!send.action) msg = '������� ����� ��������.';
			else if((send.action == 1 || send.action == 3) && !send.history) msg = '�� ������� �������.';
			else if(send.action == 1 && send.day == curDay) msg = '�������� ����� ����.';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������� ���������������.');
						$('#remind_spisok').html(res.html);
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-48,
					left:115
				});
		}
	})

	.on('click', '#income_next', function() {
		var next = $(this),
			send = {
				op:'income_next',
				page:next.attr('val'),
				day:$('.selected').val(),
				del:$('#del').val()
			};
		if(next.hasClass('busy'))
			return;
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.income .add', function() {
		var html = '<TABLE id="income-add-tab">' +
			'<tr><td class="label">����������:<TD><INPUT type="text" id="about" maxlength="100">' +
			'<tr><td class="label">�����:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> ���.' +
			'<tr><td class="label">������ ��������� � �����?:<TD><INPUT type="hidden" id="kassa" value="-1">' +
			'</TABLE>';
		var dialog = _dialog({
				width:380,
				head:'�������� ����������� �������',
				content:html,
				submit:submit
			}),
			kassa = $('#kassa'),
			sum = $('#sum'),
			about = $('#about');

		kassa._radio({
			spisok:[
				{uid:1, title:'��'},
				{uid:0, title:'���'}
			]
		});
		about.focus();

		function submit() {
			var send = {
				op:'income_add',
				about:about.val(),
				sum:sum.val(),
				kassa:kassa.val()
			};
			var msg;
			if(!send.about) { msg = '�� ������� ����������.'; about.focus(); }
			else if(!REGEXP_NUMERIC.test(send.sum)) { msg = '����������� ������� �����.'; sum.focus(); }
			else if(send.kassa == -1) msg = '�������, ������ ��������� � �����?';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('����� ����������� �������.');
						incomeSpisok();
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-53,
					left:103,
					correct:0
				});
		}
	})
	.on('click', '.income-del', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		if(t.hasClass('deleting'))
			return;
		t.addClass('deleting');
		var send = {
			op:'income_del',
			id:t.attr('val')
		};
		$.post(AJAX_WS, send, function(res) {
			t.removeClass('deleting');
			if(res.success)
				t.addClass('deleted');
		}, 'json');
	})
	.on('click', '.income-rest', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var send = {
			op:'income_rest',
			id:t.attr('val')
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				t.removeClass('deleted');
		}, 'json');
	})

	.on('click', '.expense ._next', function() {
		var next = $(this),
			send = {
				op:'expense_next',
				page:next.attr('val'),
				category:$('#category').val(),
				worker:$('#worker').val(),
				year:$('#year').val(),
				month:$('#monthSum').val()
			};
		if(next.hasClass('busy'))
			return;
		next.addClass('busy');
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.expense .add', function() {
		var html = '<TABLE id="expense-add-tab">' +
				'<tr><td class="label">���������:<TD><INPUT type="hidden" id="cat" value="0">' +
				'<tr><td class="label">��������:<TD><INPUT type="text" id="about" maxlength="100">' +
				'<tr><td class="label">���������:<TD><INPUT type="hidden" id="work" value="0">' +
				'<tr><td class="label">�����:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> ���.' +
				'<tr><td class="label">������ ����� �� �����?:<TD><INPUT type="hidden" id="kassa" value="-1">' +
			'</TABLE>',
			dialog = _dialog({
				width:380,
				head:'�������� �������',
				content:html,
				submit:submit
			}),
			category = $('#cat'),
			worker = $('#work'),
			kassa = $('#kassa'),
			sum = $('#sum'),
			about = $('#about');

		category._select({
			width:180,
			title0:'�� �������',
			spisok:RASHOD_CATEGORY,
			funcAdd:expenseCategoryAdd
		});

		worker._select({
			title0:'�� ������',
			spisok:RASHOD_VIEWER
		});

		kassa._radio({
			spisok:[
				{uid:1, title:'��'},
				{uid:0, title:'���'}
			]
		});
		about.focus();

		function submit() {
			var send = {
				op:'expense_add',
				category:category.val(),
				about:about.val(),
				worker:worker.val(),
				sum:sum.val(),
				kassa:kassa.val()
			};
			var msg;
			if(!send.about && send.category == 0) { msg = '�������� ��������� ��� ������� ��������.'; about.focus(); }
			else if(!REGEXP_NUMERIC.test(send.sum)) { msg = '����������� ������� �����.'; sum.focus(); }
			else if(send.kassa == -1) msg = '�������, ������ ����� �� ����� ��� ���.';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('����� ������ �����.');
						expenseSpisok();
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-53,
					left:103,
					correct:0
				});
		}
	})
	.on('click', '.expense .img_del', function() {
		var send = {
			op:'expense_del',
			id:$(this).attr('val')
		};
		var tr = $(this).parent().parent();
		tr.html('<td colspan="4" class="deleting">��������... <img src=/img/upload.gif></td>');
		$.post(AJAX_WS, send, function (res) {
			if(res.success) {
				_msg('�������� �����������.');
				tr.remove();
			}
		}, 'json');
	})
	.on('click', '.expense .img_edit', function() {
		var dialog = _dialog({
				top:60,
				width:380,
				head:'�������������� �������',
				load:1,
				butSubmit:'���������',
				submit:submit
			}),
			id = $(this).attr('val'),
			category,
			worker,
			kassa,
			sum,
			about,
			send = {
				op:'expense_get',
				id:id
			};
		$.post(AJAX_WS, send, function(res) {
			var html = '<TABLE id="expense-add-tab">' +
				'<tr><td class="label">���������:<TD><INPUT type="hidden" id="cat" value="' + res.category + '">' +
				'<tr><td class="label">��������:<TD><INPUT type="text" id="about" maxlength="150" value="' + res.about + '">' +
				'<tr><td class="label">���������:<TD><INPUT type="hidden" id="work" value="' + res.worker_id + '">' +
				'<tr><td class="label">�����:<TD><INPUT type="text" id="sum" class="money" maxlength="8" value="' + res.sum + '"> ���.' +
				'<tr><td class="label">������ ����� �� �����?:<TD><INPUT type="hidden" id="kassa" value="' + res.kassa + '">' +
				'</TABLE>';
			dialog.content.html(html);
			category = $('#cat');
			worker = $('#work');
			kassa = $('#kassa');
			sum = $('#sum');
			about = $('#about');

			category._select({
				width:180,
				title0:'�� �������',
				spisok:RASHOD_CATEGORY,
				funcAdd:expenseCategoryAdd
			});

			worker._select({
				title0:'�� ������',
				spisok:RASHOD_VIEWER
			});

			kassa._radio({
				spisok:[
					{uid:1, title:'��'},
					{uid:0, title:'���'}
				]
			});
			about.focus();
		}, 'json');

		function submit() {
			var send = {
				id:id,
				op:'expense_edit',
				category:category.val(),
				about:about.val(),
				worker:worker.val(),
				sum:sum.val(),
				kassa:kassa.val()
			};
			var msg;
			if(!send.about && send.category == 0) { msg = '�������� ��������� ��� ������� ��������.'; about.focus(); }
			else if(!REGEXP_NUMERIC.test(send.sum)) { msg = '����������� ������� �����.'; sum.focus(); }
			else if(send.kassa == -1) msg = '�������, ������ ����� �� ����� ��� ���.';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('������ �������.');
						expenseSpisok();
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-53,
					left:103,
					correct:0
				});
		}
	})

	.on('click', '#report_kassa #set_go', function() {
		if($(this).hasClass('busy'))
			return;
		var t = $(this),
			sum = $('#set_summa'),
			send = {
				op:'report_kassa_set',
				sum:sum.val()
			};
		if(!REGEXP_NUMERIC.test(send.sum)) {
			sum.vkHint({
				msg:'<SPAN class=red>����������� ������� �����.</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-70,
				left:16,
				correct:0
			});
			sum.focus();
		} else {
			t.addClass('busy');
			$.post(AJAX_WS, send, function (res) {
				if(res.success) {
					location.reload();
				} else
					t.removeClass('busy');
			}, 'json');
		}
	})
	.on('click', '#report_kassa_next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = {
				op:'report_kassa_next',
				page:$(this).attr('val')
			};
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success) {
				next.remove();
				$('#report_kassa ._spisok').append(res.html);
			} else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#report_kassa .actions a:first', function() {
		var kassa = $('#kassa_summa'),
			kassa_sum = parseInt(kassa.html()),
			html = '<TABLE style="border-spacing:8px">' +
				'<tr><td class="label r">�����:<TD><INPUT type="text" class="money" id="kassa_down_sum" maxlength="8" />' +
				'<tr><td class="label r">�����������:<TD><INPUT type="text" id="kassa_down_txt" />' +
				'</TABLE>',
			dialog = _dialog({
				head:'�������� ����� � �����',
				content:html,
				submit:submit
			}),
			sum = $('#kassa_down_sum'),
			txt = $('#kassa_down_txt');

		sum.focus();

		function submit() {
			var send = {
				op:'report_kassa_action',
				txt:txt.val(),
				sum:sum.val(),
				down:0
			};
			var msg;
			if(!REGEXP_NUMERIC.test(send.sum)) { msg = '����������� ������� �����.'; sum.focus(); }
			else if(!send.txt) { msg = '�� ������ �����������.'; txt.focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('����� ������ ������.');
						kassa.html(kassa_sum += parseInt(send.sum));
						reportKassaLoad();
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-47,
					left:92,
					correct:0
				});
		}
	})
	.on('click', '#report_kassa .actions a:last', function() {
		var kassa = $('#kassa_summa'),
			kassa_sum = kassa.html(),
			html = '<TABLE style="border-spacing:8px">' +
				'<tr><td class="label r">�����:' +
					'<TD><INPUT type="text" class="money" id="kassa_down_sum" maxlength="8" /> max: ' + kassa_sum +
				'<tr><td class="label r">�����������:<TD><INPUT type="text" id="kassa_down_txt" />' +
				'</TABLE>',
			dialog = _dialog({
				head:'������ ����� �� �����',
				content:html,
				submit:submit
			}),
			sum = $('#kassa_down_sum'),
			txt = $('#kassa_down_txt');

		sum.focus();

		function submit() {
			var send = {
				op:'report_kassa_action',
				txt:txt.val(),
				sum:sum.val(),
				down:1
			};
			var msg;
			if(!REGEXP_NUMERIC.test(send.sum)) { msg = '����������� ������� �����.'; sum.focus(); }
			else if(send.sum > kassa_sum) { msg = '�������� ����� ��������� ����� � �����.'; sum.focus(); }
			else if(!send.txt) { msg = '�� ������ �����������.'; txt.focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('����� ������ ������.');
						kassa.html(kassa_sum -= send.sum);
						reportKassaLoad();
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-47,
					left:92,
					correct:0
				});
		}
	})
	.on('click', '#report_kassa .img_del', function() {
		var send = {
			op:'report_kassa_del',
			id:$(this).attr('val')
		};
		var tr = $(this).parent().parent(),
			trSave = tr.html();
		tr.html('<td colspan="4" class="deleting">��������... <img src=/img/upload.gif></td>');
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				_msg('�������� �����������.');
				if($('#kassaShowDel').val() == 1)
					tr.addClass('deleted')
						.html(trSave)
						.find('.img_del').attr('class', 'img_rest').attr('title', '������������');
				else
					tr.remove();
				$('#kassa_summa').html(res.sum);
			}
		}, 'json');
	})
	.on('click', '#report_kassa .img_rest', function() {
		var send = {
			op:'report_kassa_rest',
			id:$(this).attr('val')
		};
		var tr = $(this).parent().parent(),
			trSave = tr.html();
		tr.html('<td colspan="4" class="deleting">��������������... <img src=/img/upload.gif></td>');
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				_msg('�������������� �����������.');
				tr.removeClass('deleted')
					.html(trSave)
					.find('.img_rest').attr('class', 'img_del').attr('title', '������� �����');
				$('#kassa_summa').html(res.sum);
			}
		}, 'json');
	})
	.on('click', '#kassaShowDel_check', reportKassaLoad)

	.ready(function() {
		if($('#client').length) {
			window.cFind = $('#find')._search({
				width:602,
				focus:1,
				enter:1,
				txt:'������� ������� ������ �������',
				func:clientSpisok
			});
			$('#buttonCreate').vkHint({
				msg:'<B>�������� ������ ������� � ����.</B><br /><br />' +
					'����� �������� �� ��������� �� �������� � ����������� � ������� ��� ���������� ��������.<br /><br />' +
					'�������� ����� ����� ��������� ��� <A href="' + URL + '&p=zayav&d=add&back=client">�������� ����� ������</A>.',
				ugol:'right',
				width:215,
				top:-38,
				left:-250,
				indent:40,
				delayShow:1000,
				correct:0
			}).click(clientAdd);
			$('#dolg')._check(clientSpisok)
			$('#active')._check(clientSpisok)
			$('#comm')._check(clientSpisok)
			$('#dolg_check').vkHint({
				msg:'<b>������ ���������.</b><br /><br />' +
					'��������� �������, � ������� ������ ����� 0. ����� � ���������� ������������ ����� ����� �����.',
				ugol:'right',
				width:150,
				top:-6,
				left:-185,
				indent:20,
				delayShow:1000,
				correct:0
			});
		}
		if($('#clientInfo').length) {
			$('#dopLinks .link').click(function() {
				$('#dopLinks .link').removeClass('sel');
				$(this).addClass('sel');
				var val = $(this).attr('val');
				$('.res').css('display', val == 'zayav' ? 'block' : 'none');
				$('#zayav_filter').css('display', val == 'zayav' ? 'block' : 'none');
				$('#zayav_spisok').css('display', val == 'zayav' ? 'block' : 'none');
				$('#money_spisok').css('display', val == 'money' ? 'block' : 'none');
				$('#remind_spisok').css('display', val == 'remind' ? 'block' : 'none');
				$('#comments').css('display', val == 'comm' ? 'block' : 'none');
				$('#histories').css('display', val == 'hist' ? 'block' : 'none');
			});
			$('#status').rightLink(clientZayavSpisok);
			$('#diff')._check(clientZayavSpisok);
			$('#dev').device({
				width:145,
				type_no:1,
				device_ids:DEVICE_IDS,
				vendor_ids:VENDOR_IDS,
				model_ids:MODEL_IDS,
				func:clientZayavSpisok
			});
		}

		if($('#zayav').length) {
			window.zFind = $('#find')
				.vkHint({
					msg:'����� ������������ ��<br />���������� � ��������<br />������, imei � ��������<br />������.',
					ugol:'right',
					top:-9,
					left:-178,
					delayShow:800,
					correct:0
				})
				._search({
					width:153,
					focus:1,
					txt:'������� �����...',
					enter:1,
					func:zayavSpisokLoad
				});
			zFind.inp(G.zayav_find);
			$('#desc')._check(zayavSpisokLoad);
			$('#status').rightLink(zayavSpisokLoad);
			$('#diff')._check(zayavSpisokLoad);
			$('#dev').device({
				width:155,
				type_no:1,
				device_id:G.zayav_device,
				vendor_id:G.zayav_vendor,
				model_id:G.zayav_model,
				device_ids:DEVICE_IDS,
				vendor_ids:VENDOR_IDS,
				model_ids:MODEL_IDS,
				func:zayavSpisokLoad
			});
			// ���������� ����������
			for(n = 0; n < G.place_other.length; n++) {
				var sp = G.place_other[n];
				DEVPLACE_SPISOK.push({uid:encodeURI(sp), title:sp});
			}
			DEVPLACE_SPISOK.push({uid:-1, title:'�� ��������', content:'<B>�� ��������</B>'});
			$('#device_place')._select({
				width:155,
				title0:'����� ���������������',
				spisok:DEVPLACE_SPISOK,
				func:zayavSpisokLoad
			});
			// ��������� ����������
			DEVSTATUS_SPISOK.splice(0, 1);
			DEVSTATUS_SPISOK.push({uid:-1, title:'�� ��������', content:'<B>�� ��������</B>'});
			$('#devstatus')._select({
				width:155,
				title0:'����� ���������',
				spisok:DEVSTATUS_SPISOK,
				func:zayavSpisokLoad
			});
			zayavFilter();
		}
		if($('#zayavAdd').length) {
			$('#client_id').clientSel({add:1});
			$('#dev').device({
				width:190,
				add:1,
				device_ids:WS_DEVS,
				func:zayavDevSelect
			});
			DEVPLACE_SPISOK.push({uid:0, title:'������: <input type="text" id="place_other" class="dn" maxlength="20" />'});
			$('#place')._radio({
				spisok:DEVPLACE_SPISOK,
				func:function(val) {
					$('#place_other')[(val != 0 ? 'add' : 'remove') + 'Class']('dn');
					if(val == 0)
						$('#place_other').val('').focus();
				}
			});
			colorSelDop(0);
			$(document).on('click', '#fault', function() {
				var i = $(this).find('INPUT'),
					arr = [];
				for(var n = 0; n < i.length; n++)
					if(i.eq(n).val() == 1) {
						var uid = i.eq(n).attr('id').split('_')[1];
						arr.push(FAULT_ASS[uid]);
					}
				$('#comm').val(arr.join(', '));
			});
			$('#comm').autosize();
			$('#reminder_check').click(function(id) {
				$('#reminder_tab').toggle();
				$('#reminder_txt').focus();
			});
			$('#reminder_day')._calendar();
			$('.vkCancel').click(function() {
				location.href = URL + '&p=' + $(this).attr('val');
			});
			$('.vkButton').click(function () {
				if($(this).hasClass('busy'))
					return;
				var send = {
					op:'zayav_add',
					client:$('#client_id').val(),
					device:$('#dev_device').val(),
					vendor:$('#dev_vendor').val(),
					model:$('#dev_model').val(),
					equip:'',
					place:$('#place').val(),
					place_other:$('#place_other').val(),
					imei:$('#imei').val(),
					serial:$('#serial').val(),
					color:$('#color_id').val(),
					color_dop:$('#color_dop').val(),
					comm:$('#comm').val(),
					reminder:$('#reminder').val()
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
				send.reminder_txt = send.reminder == 1 ? $('#reminder_txt').val() : '';
				send.reminder_day = send.reminder == 1 ? $('#reminder_day').val() : '';

				var msg = '';
				if(send.client == 0) msg = '�� ������ ������';
				else if(send.device == 0) msg = '�� ������� ����������';
				else if(send.place == '-1' || send.place == 0 && !send.place_other) msg = '�� ������� ��������������� ����������';
				else if(send.reminder == 1 && !send.reminder_txt) msg = '�� ������ ����� �����������';
				else {
					if(send.place > 0) send.place_other = '';
					$(this).addClass('busy');
					$.post(AJAX_WS, send, function(res) {
						location.href = URL + '&p=zayav&d=info&id=' + res.id;
					}, 'json');
				}

				if(msg)
					$(this).vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						top:-48,
						left:201,
						indent:30,
						remove:1,
						show:1,
						correct:0
					});
			});
		}
		if($('#zayavInfo').length) {
			$('.delete').vkHint({
				msg:'������ ����� ������� ��� ���������� �������� � ����������. ����� ��������� ��� ������ � ���� ������.',
				width:150,
				ugol:'top',
				top:40,
				left:456,
				indent:90,
				correct:0
			});
			$('.fotoUpload').fotoUpload({
				owner:'zayav' + ZAYAV.id,
				func:zayavImgUpdate
			});
			$('.img_print').click(function() {
				var html = '<table class="zayav-print">' +
						'<tr><td class="label">���� �����:<td>' + PRINT.dtime +
						'<tr><td class="label top">����������:<td>' + PRINT.device +
		 (PRINT.color ? '<tr><td class="label">����:<td>' + PRINT.color : '') +
		  (PRINT.imei ? '<tr><td class="label">IMEI:<td>' + PRINT.imei : '') +
		(PRINT.serial ? '<tr><td class="label">�������� �����:<td>' + PRINT.serial : '') +
		 (PRINT.equip ? '<tr><td class="label">������������:<td>' + PRINT.equip : '') +
						'<tr><td class="label">��������:<td><b>' + PRINT.client + '</b>' +
	   (PRINT.telefon ? '<tr><td class="label">�������:<td>' + PRINT.telefon : '') +
						'<tr><td class="label top">�������������:<td><textarea id="defect">' + PRINT.defect + '</textarea>' +
						'</table>',
					dialog = _dialog({
						width:380,
						top:30,
						head:'������ �' + ZAYAV.nomer + ' - ������ ���������',
						content:html,
						butSubmit:'',
						submit:submit
					});
				$('#defect').focus().autosize();
				function submit() {}
			});
		}

		if($('#zp').length) {
			$('#find')
				._search({
					width:153,
					focus:1,
					txt:'������� �����...',
					enter:1,
					func:zpSpisokLoad
				})
				.inp(G.zp_find);
			$('#menu_rightLink a').click(function() {
				$('#menu').rightLink($(this).attr('val'));
				zpSpisokLoad();
			});
			$("#zp_name")._select({
				width:153,
				title0:'����� ������������',
				spisok:ZPNAME_SPISOK,
				func:zpSpisokLoad
			}).o;
			$("#dev").device({
				width:153,
				type_no:1,
				device_ids:WS_DEVS,
				device_id:G.zp_device,
				vendor_id:G.zp_vendor,
				model_id:G.zp_model,
				func:zpSpisokLoad
			});
			zpFilter();
		}
		if($('#zpInfo').length) {
			$('.fotoUpload').fotoUpload({
				owner:'zp' + ZP.compat_id,
				func:zpImgUpdate
			});
		}

		if($('#report.history').length) {
			$('#worker')._select({
				width:140,
				title0:'�� ������',
				spisok:WORKERS,
				func:historySpisok
			});
			$('#action')._select({
				width:140,
				title0:'�� �������',
				spisok:[
					{uid:1, title:'�������'},
					{uid:2, title:'������'},
					{uid:3, title:'��������'},
					{uid:4, title:'�������'}
				],
				func:historySpisok
			});
		}
		if($('#report.remind').length) {
			$('#status')._radio(remindSpisok);
			$('#private')._check(remindSpisok);
		}
		if($('#report.income').length) {
			window._calendarFilter = incomeSpisok;
			$('#del')._check(incomeSpisok);
		}
		if($('#report.expense').length) {
			$('#category')._select({
				width:140,
				title0:'����� ���������',
				spisok:RASHOD_CATEGORY,
				func:expenseSpisok
			});
			$('#worker')._select({
				width:140,
				title0:'��� ����������',
				spisok:WORKERS,
				func:expenseSpisok
			});
			$('#year').years({func:expenseSpisok});
			$('#monthSum')._radio(expenseSpisok)
		}
	});