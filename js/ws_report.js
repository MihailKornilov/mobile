var incomeSpisok = function() {
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
	expenseFilter = function() {
		var arr = [],
			inp = $('#monthList input');
		for(var n = 1; n <= 12; n++)
			if(inp.eq(n - 1).val() == 1)
				arr.push(n);
		return {
			op:'expense_spisok',
			category:$('#category').val(),
			worker:$('#worker').val(),
			year:$('#year').val(),
			month:arr.join()
		};
	},
	expenseSpisok = function() {
		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, expenseFilter(), function(res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('#spisok').html(res.html);
				$('#monthList').html(res.mon);
			}
		}, 'json');
	},
	salarySpisok = function() {
		if($('.headName').hasClass('_busy'))
			return;
		var send = {
			op:'salary_spisok',
			worker_id:WORKER_ID,
			year:$('#year').val(),
			mon:$('#salmon').val()
		};
		$('.headName').addClass('_busy');
		$.post(AJAX_WS, send, function (res) {
			$('.headName').removeClass('_busy');
			if(res.success) {
				MON = send.mon * 1;
				YEAR = send.year;
				$('.headName em').html(MONTH_DEF[MON] + ' ' + YEAR);
				$('#spisok').html(res.html);
				$('#monthList').html(res.month);
			}
		}, 'json');
	};

$(document)
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
	.on('click', '.income-add', function() {
		var html = '<table id="income-add-tab">' +
			'<input type="hidden" id="zayav_id" value="' + (window.ZAYAV ? ZAYAV.id : 0) + '" />' +
			(window.ZAYAV ? '<tr><td class="label">������:<td><b>�' + ZAYAV.nomer + '</b>' : '') +
			'<tr><td class="label">����:<td><input type="hidden" id="invoice_id" value="' + (INVOICE_SPISOK.length ? INVOICE_SPISOK[0].uid : 0) + '" />' +
			'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="11" /> ���.' +
			'<tr><td class="label">��������:<td><input type="text" id="prim" maxlength="100" />' +
		(window.ZAYAV && !ZAYAV.cartridge ?
			'<tr><td class="label topi">���������������<br />����������:<td><input type="hidden" id="place" value="-1" />'
		: '') +
			'</table>';
		var dialog = _dialog({
				width:380,
				head:'�������� �������',
				content:html,
				submit:submit
			});
		$('#invoice_id')._select({
			width:218,
			title0:'�� ������',
			spisok:INVOICE_SPISOK,
			func:function() {
				$('#sum').focus();
			}
		});
		$('#sum').focus();
		$('#sum,#prim').keyEnter(submit);
		if(window.ZAYAV)
			zayavPlace();

		function submit() {
			var send = {
				op:'income_add',
				zayav_id:$('#zayav_id').val(),
				cartridge:window.ZAYAV && ZAYAV.cartridge ? 1 : 0,
				invoice_id:$('#invoice_id').val(),
				sum:$('#sum').val(),
				prim:$('#prim').val(),
				place:window.ZAYAV && !ZAYAV.cartridge ? $('#place').val() : 0,
				place_other:window.ZAYAV && !ZAYAV.cartridge ? $('#place_other').val() : ''
			};
			if(send.invoice_id == 0) dialog.err('�� ������ ����');
			else if(!REGEXP_CENA.test(send.sum)) { dialog.err('����������� ������� �����'); $('#sum').focus(); }
			else if(!window.ZAYAV && !send.prim) { dialog.err('�� ������� ��������'); $('#prim').focus(); }
			else if(window.ZAYAV && !send.cartridge && send.place == -1) dialog.err('�� ������� ��������������� ����������');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('����� ����� �����.');
						if(window.ZAYAV) {
							$('#money_spisok').html(res.html);
							if(res.comment)
								$('.vkComment').after(res.comment).remove();
							zayavMoneyUpdate();
						} else
							incomeSpisok();
					}
				}, 'json');
			}
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
			if(res.success) {
				t.addClass('deleted');
				if(window.ZAYAV)
					zayavMoneyUpdate();
			}
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
			if(res.success) {
				t.removeClass('deleted');
				if(window.ZAYAV)
					zayavMoneyUpdate();
			}
		}, 'json');
	})

	.on('click', '.expense #monthList div', expenseSpisok)
	.on('click', '.expense ._next', function() {
		var next = $(this),
			send = expenseFilter();
		send.page = next.attr('val');
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
	.on('click', '.expense .img_del', function() {
		var send = {
			op:'expense_del',
			id:$(this).attr('val')
		};
		var tr = $(this).parent().parent();
		tr.html('<td colspan="4" class="deleting">��������...</td>');
		$.post(AJAX_WS, send, function (res) {
			if(res.success) {
				_msg('�������� �����������.');
				tr.remove();
			}
		}, 'json');
	})

	.on('click', '.schet-unit .to-pay', function() {
		var t = $(this),
			p = t.parent(),
			nomer = p.find('.pay-nomer').html(),
			html =
				'<table class="_dialog-tab">' +
					'<tr><td class="label">� �����:<td><b>' + nomer + '</b>' +
					'<tr><td class="label">�����:<td><input type="text" class="money" disabled id="sum" value="' + p.find('.pay-sum').html() + '" /> ���.' +
					'<tr><td class="label">���� ������:<td><input type="hidden" id="pay-day" />' +
					'<tr><td class="label">��������� ����:<td>' + INVOICE_ASS[4] +
				'</table>';
		var dialog = _dialog({
				width:320,
				head:'������ �����',
				content:html,
				butSubmit:'��������',
				submit:submit
			});

		$('#pay-day')._calendar({lost:1});
		function submit() {
			var send = {
				op:'schet_pay',
				schet_id:t.attr('val'),
				day:$('#pay-day').val()
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					$('#spisok').html(res.html);
					dialog.close();
					_msg('���� ' + nomer + ' �������');
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.schet-unit .img_edit', function() {
		var t = $(this),
			schet_id = t.attr('val'),
			spisok,// ���������� �������
			num,// ��������� ����� �������� �������
			dialog = _dialog({
				top:20,
				width:580,
				head:'�������������� �����',
				load:1,
				butSubmit:'���������',
				submit:submit
			});
		var send = {
			op:'schet_edit_load',
			schet_id:schet_id
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				spisok = res.spisok;
				var html =
					'<div id="schet-add-tab">' +
						'<table class="_spisok" id="schet-tab"></table>' +
						'<div id="itog"></div>' +
						'<table id="sa-tab">' +
							'<tr><td class="label r">����:<td><input id="date_create" type="hidden" value="' + res.date_create + '" />' +
							'<tr><td class="label r topi">����������:<td><input id="dop" type="hidden" value="' + res.dop + '" />' +
						'</table>' +
					'</div>';

				dialog.content.html(html);
				spisokPrint();
				$('#date_create')._calendar({lost:1});
				$('#dop')._radio({
					light:1,
					spisok:[
						{uid:1,title:'���������'},
						{uid:2,title:'��� ����������� �����'}
					]
				});
			} else
				dialog.loadError();
		}, 'json');
		function spisokPrint() {
			var html =
					'<tr><th>�' +
					'<th>������������ ������' +
					'<th>���-��' +
					'<th>����' +
					'<th>�����' +
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
					'<td>' + (sp.del ? '<div val="' + n + '" class="img_del pole-del' + _tooltip('�������', -29) + '</div>' : '');
				sum += s;
			}
			num = n + 1;
			html += '<tr><td colspan="6" class="_next" id="pole-add">�������� ������� ��� �����';
			$('#schet-tab').html(html);
			$('#itog').html('����� ������������ ' + n + ', �� ����� ' + sum + ' ���.');
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
					'<td><div class="img_del' + _tooltip('��������', -32) + '</div>';
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
				poleErr('�� ������� ������������');
				$('#name').focus();
				return;
			}
			if(!count) {
				poleErr('������������ �����');
				$('#count').focus();
				return;
			}
			if(!cost) {
				poleErr('������������ ����������');
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
				op:'schet_edit',
				schet_id:schet_id,
				spisok:spisok,
				date_create:$('#date_create').val(),
				dop:_num($('#dop').val())
			};
			if(!send.spisok.length) dialog.err('�� ��������� �� ����� �������');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						if(window.ZAYAV) {
							$('#schet-spisok').html(res.schet_zayav);
							$('#money_spisok').html(res.acc);
						} else
							$('#spisok').html(res.schet_all);
						dialog.close();
						_msg('������ ����� ��������');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})

	.on('click', '.invoice_set', function() {
		var t = $(this),
			html =
				'<table class="_dialog-tab">' +
					'<tr><td class="label">�����:<td><input type="text" class="money" id="sum" maxlength="11" /> ���.' +
				'</table>';
		var dialog = _dialog({
				width:320,
				head:'��������� ������� ����� �����',
				content:html,
				butSubmit:'����������',
				submit:submit
			});

		$('#sum').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'invoice_set',
				invoice_id:t.attr('val'),
				sum:$('#sum').val()
			};
			if(!REGEXP_CENA.test(send.sum)) {
				err('����������� ������� �����');
				$('#sum').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						$('#invoice-spisok').html(res.html);
						dialog.close();
						_msg('��������� ����� �����������');
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-48,
				left:72
			});
		}
	})
	.on('click', '.invoice_reset', function() {
		var t = $(this),
			html = '����� �� ����� ����� ��������.';
		var dialog = _dialog({
				width:320,
				head:'����� ����� �����',
				content:html,
				butSubmit:'���������',
				submit:submit
			});

		function submit() {
			var send = {
				op:'invoice_reset',
				invoice_id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					$('#invoice-spisok').html(res.html);
					dialog.close();
					_msg('����� ��������');
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '#report.invoice .img_note', function() {
		var dialog = _dialog({
			top:20,
			width:570,
			head:'������� �������� �� ������',
			load:1,
			butSubmit:'',
			butCancel:'�������'
		});
		var send = {
			op:'invoice_history',
			invoice_id:$(this).attr('val')
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				dialog.content.html(res.html);
			else
				dialog.loadError();
		}, 'json');
	})
	.on('click', '.invoice-history ._next', function() {
		var next = $(this),
			send = {
				op:'invoice_history',
				page:next.attr('val'),
				invoice_id:$('#invoice_history_id').val()
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

	.on('click', '.salary .rate-set', function() {
		var html =
				'<div class="_info">' +
					'����� ��������� ������ ���������� ��������� ����� ����� ������������� ����������� ' +
					'�� ��� ������ � ����������� ���� ��������� ��������������. ' +
				'</div>' +
				'<table class="salary-tab set">' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="11" value="' + (RATE.sum ? RATE.sum : '') + '" /> ���.' +
					'<tr><td class="label">������:<td><input type="hidden" id="period" value="' + RATE.period + '" />' +
					'<tr class="tr-day' + (RATE.period == 3 ? ' dn' : '') + '">' +
						'<td class="label">���� ����������:' +
						'<td>' +
							'<div class="div-day' + (RATE.period != 1 ? ' dn' : '') + '"><input type="text" id="day" maxlength="2" value="' + RATE.day + '" /></div>' +
							'<div class="div-week' + (RATE.period != 2 ? ' dn' : '') + '"><input type="hidden" id="day_week" value="' + RATE.day + '" /></div>' +
				'</table>',
			dialog = _dialog({
				top:30,
				width:320,
				head:'��������� ������ �/� ��� ����������',
				content:html,
				butSubmit:'����������',
				submit:submit
			});

		$('#sum').focus();
		$('#sum,#day').keyEnter(submit);
		$('#period')._select({
			width:70,
			spisok:SALARY_PERIOD,
			func:function(id) {
				$('#day_week')._select(1);
				$('.tr-day')[(id == 3 ? 'add' : 'remove') + 'Class']('dn');
				$('.div-day')[(id != 1 ? 'add' : 'remove') + 'Class']('dn');
				$('.div-week')[(id != 2 ? 'add' : 'remove') + 'Class']('dn');
			}
		});
		$('#day_week')._select({
			spisok:[
				{uid:1,title:'�����������'},
				{uid:2,title:'�������'},
				{uid:3,title:'�����'},
				{uid:4,title:'�������'},
				{uid:5,title:'�������'},
				{uid:6,title:'�������'},
				{uid:7,title:'�����������'}
			]
		});

		function submit() {
			var send = {
				op:'salary_rate_set',
				worker_id:WORKER_ID,
				sum:_cena($('#sum').val()),
				period:$('#period').val() * 1,
				day:$('#day').val() * 1
			};
			if(send.period == 2)
				send.day = $('#day_week').val() * 1;
			if(!send.sum) { err('����������� ������� �����.'); $('#sum').focus(); }
			else if(send.period == 1 && (!REGEXP_NUMERIC.test(send.day) || !send.day || send.day > 28)) { err('����������� ������ ����.'); $('#day').focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						RATE.sum = send.sum;
						RATE.period = send.period;
						RATE.day = send.day;
						dialog.close();
						_msg('��������� ������ �����������.');
						salarySpisok();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:74
			});
		}
	})
	.on('click', '.salary .bonus', function() {// �������� ������ �� ��������
		var n,
			year = [],
			week = [],
			html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�������:<td>' + PROCENT +
					'<tr><td class="label">���:<td><input type="hidden" id="b-year" value="2015" />' +
					'<tr><td class="label">������:<td><input type="hidden" id="week" />' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" disabled /> ���.' +
				'</table>' +
				'<div id="bonus-spisok"></div>',
			dialog = _dialog({
				top:20,
				width:450,
				head:'������������ ������ �� ��������',
				content:html,
				submit:submit
			});

		for(n = 2014; n <= 2015; n++)
			year.push({uid:n,title:n});
		$('#b-year')._select({
			width:100,
			spisok:year,
			func:bonus_spisok
		});

		for(n = 1; n <= 53; n++)
			week.push({uid:n,title:n});
		$('#week')._select({
			title0:'������',
			width:100,
			spisok:week,
			func:bonus_spisok
		});

		function bonus_spisok() {
			var send = {
				op:'salary_bonus_spisok',
				worker_id:WORKER_ID,
				year:$('#b-year').val(),
				week:$('#week').val() * 1
			};
			if(!send.week) {
				$('#bonus-spisok').html('');
				$('#sum').val('');
				return;
			}
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					$('#bonus-spisok').html(res.spisok);
					bonus_sum();
					$('.i-expense').keyup(function() {
						var t = $(this),
							expense = t.val(),
							money = t.parent().parent().find('b').html(),
							bns = t.parent().parent().find('.bns');
						if(!REGEXP_NUMERIC.test(expense))
							return;
						bns.html(Math.round((money - expense) * PROCENT / 100));
						bonus_sum();
					});
				}
			}, 'json');
		}
		function bonus_sum() {
			var bns = $('.bns'),
				sum = 0,
				send = [];
			for(n = 0; n < bns.length; n++) {
				var eq = bns.eq(n);
				sum += eq.html() * 1;
				send.push(eq.attr('val') + ':' + eq.parent().find('.i-expense').val() + ':' + eq.html());
			}
			$('#sum').val(sum);
			return send.join();
		}
		function submit() {
			var send = {
				op:'salary_bonus',
				worker_id:WORKER_ID,
				year:$('#b-year').val(),
				week:$('#week').val(),
				bonus:bonus_sum()
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('����� ��������.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary .bonus-show', function() {
		var dialog = _dialog({
			top:20,
			width:500,
			head:'�������� ������ �� ��������',
			load:1,
			butSubmit:'',
			butCancel:'�������'
		});

		var send = {
			op:'salary_bonus_show',
			expense_id:$(this).attr('val')
		};
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				dialog.content.html(res.html);
			else
				dialog.loadError();
		}, 'json');
	})
	.on('click', '.salary .up', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="8"> ���.' +
					'<tr><td class="label">��������:<td><input type="text" id="about" maxlength="50">' +
					'<tr><td class="label">�����:' +
						'<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
							'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
					'</table>',
			dialog = _dialog({
				head:'�������� ���������� ��� ����������',
				content:html,
				submit:submit
			});

		$('#sum').focus();
		$('#sum,#about').keyEnter(submit);
		$('#tabmon')._select({
			width:80,
			spisok:MON_SPISOK
		});
		$('#tabyear')._select({
			width:60,
			spisok:YEAR_SPISOK
		});
		function submit() {
			var send = {
				op:'salary_up',
				worker_id:WORKER_ID,
				sum:_cena($('#sum').val()),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!send.sum) {
				err('����������� ������� �����.');
				$('#sum').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('���������� �����������.');
						salarySpisok();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:93
			});
		}
	})
	.on('click', '.salary .deduct', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="8"> ���.' +
					'<tr><td class="label">��������:<td><input type="text" id="about" maxlength="100">' +
					'<tr><td class="label">�����:' +
						'<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
							'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
					'</table>',
			dialog = _dialog({
				head:'�������� ������ �� ��������',
				content:html,
				submit:submit
			});

		$('#sum').focus();
		$('#sum,#about').keyEnter(submit);
		$('#tabmon')._select({
			width:80,
			spisok:MON_SPISOK
		});
		$('#tabyear')._select({
			width:60,
			spisok:YEAR_SPISOK
		});
		function submit() {
			var send = {
				op:'salary_deduct',
				worker:WORKER_ID,
				sum:$('#sum').val(),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!REGEXP_NUMERIC.test(send.sum)) { err('����������� ������� �����.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ���������.');
						salarySpisok();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:93
			});
		}
	})
	.on('click', '.salary .ze_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'��������',
				content:'<center>����������� �������� ������.</center>',
				butSubmit:'�������',
				submit:submit
			});
		while(t[0].tagName != 'TR')
			t = t.parent();
		function submit() {
			var send = {
				op:'salary_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('�������.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary .zp_add', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�� �����:' +
						'<td><input type="hidden" id="invoice_id">' +
							'<a href="' + URL + '&p=setup&d=invoice" class="img_edit' + _tooltip('��������� ������', -56) + '</a>' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="8"> ���.' +
					'<tr><td class="label">��������:<td><input type="text" id="about" maxlength="100">' +
					'<tr><td class="label">�����:' +
						'<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
							'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
				'</table>',
			dialog = _dialog({
				head:'������ �������� ����������',
				content:html,
				submit:submit
			});

		$('#sum').focus();
		$('#invoice_id')._select({
			title0:'�� ������',
			spisok:INVOICE_SPISOK,
			func:function() {
				$('#sum').focus();
			}
		});
		$('#sum,#about').keyEnter(submit);
		$('#tabmon')._select({
			width:80,
			spisok:MON_SPISOK
		});
		$('#tabyear')._select({
			width:60,
			spisok:YEAR_SPISOK
		});

		function submit() {
			var send = {
				op:'salary_zp_add',
				worker_id:WORKER_ID,
				invoice_id:$('#invoice_id').val() * 1,
				sum:_cena($('#sum').val()),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!send.invoice_id) err('������� � ������ ����� ������������ ������.');
			else if(!send.sum) { err('����������� ������� �����.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ �������� �����������.');
						salarySpisok();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:93
			});
		}
	})
	.on('click', '.salary .zp_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'�������� �/�',
				content:'<center>����������� �������� ������.</center>',
				butSubmit:'�������',
				submit:submit
			});
		function submit() {
			var send = {
				op:'expense_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('�������.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary .deduct_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'��������',
				content:'<center>����������� �������� ������.</center>',
				butSubmit:'�������',
				submit:submit
			});
		while(t[0].tagName != 'TR')
			t = t.parent();
		function submit() {
			var send = {
				op:'salary_deduct_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('�������.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary .start-set', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="8"> ���.' +
				'</table>',
			dialog = _dialog({
				head:'��������� ������� �� �������� ����������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});

		$('#sum').focus().keyEnter(submit);

		function submit() {
			var send = {
				op:'salary_start_set',
				worker_id:WORKER_ID,
				sum:_cena($('#sum').val())
			};
			dialog.process();
			$.post(AJAX_WS, send, function (res) {
				if(res.success) {
					dialog.close();
					_msg('��������� �����������.');
					$('#spisok').html(res.html);
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('mouseenter', '.salary .show', function() {
		$(this).removeClass('show');
	})
	.on('click', '.go-report-salary', function() {
		var v = $(this).attr('val').split(':');
		location.href = URL + '&p=report&d=salary&id=' + v[0] + '&year=' + v[1] + '&mon=' + v[2] + '&acc_id=' + v[3];
	})

	.ready(function() {
		if($('#report.history').length) {
			$('#viewer_id_add')._select({
				width:140,
				title0:'�� ������',
				spisok:WORKERS,
				func:_history
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
				func:_history
			});
		}
		if($('#report.income').length) {
			window._calendarFilter = incomeSpisok;
			$('#del')._check(incomeSpisok);
		}
		if($('#report.expense').length) {
			$('.add').click(function() {
				var html =
					'<table id="expense-add-tab">' +
						'<tr><td class="label">���������:<td><input type="hidden" id="expense_id" />' +
							'<a href="' + URL + '&p=setup&d=expense" class="img_edit' + _tooltip('��������� ��������� ��������', -95) + '</a>' +
						'<tr class="tr-work dn"><td class="label">���������:<td><input type="hidden" id="worker_id" />' +
						'<tr class="tr-work dn"><td class="label">�����:' +
							'<td><input type="hidden" id="tabmon" value="' + ((new Date).getMonth() + 1) + '" /> ' +
								'<input type="hidden" id="tabyear" value="' + (new Date).getFullYear() + '" />' +
						'<tr><td class="label">��������:<td><input type="text" id="prim" maxlength="100">' +
						'<tr><td class="label">�� �����:<td><input type="hidden" id="invoice_id" value="' + (INVOICE_SPISOK.length ? INVOICE_SPISOK[0].uid : 0) + '" />' +
						'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="11" /> ���.' +
					'</table>',
					dialog = _dialog({
						width:380,
						head:'�������� �������',
						content:html,
						submit:submit
					});

				$('#expense_id')._select({
					width:200,
					title0:'�� �������',
					spisok:EXPENSE_SPISOK,
					func:function(id) {
						$('#worker_id')._select(0);
						$('.tr-work')[(EXPENSE_WORKER[id] ? 'remove' : 'add') + 'Class']('dn');
					}
				});
				$('#worker_id')._select({
					width:200,
					title0:'�� ������',
					spisok:WORKERS
				});
				$('#prim').focus();
				$('#invoice_id')._select({
					width:200,
					title0:'�� ������',
					spisok:INVOICE_SPISOK,
					func:function() {
						$('#sum').focus();
					}
				});
				$('#tabmon')._select({
					width:80,
					spisok:MON_SPISOK
				});
				$('#tabyear')._select({
					width:60,
					spisok:YEAR_SPISOK
				});

				function submit() {
					var send = {
						op:'expense_add',
						expense_id:$('#expense_id').val(),
						worker_id:$('#worker_id').val(),
						prim:$('#prim').val(),
						invoice_id:$('#invoice_id').val(),
						sum:_cena($('#sum').val()),
						mon:$('#tabmon').val(),
						year:$('#tabyear').val()
					};
					if(!send.prim && send.expense_id == 0) { err('�������� ��������� ��� ������� ��������.'); $('#prim').focus(); }
					else if(send.invoice_id == 0) err('������� � ������ ����� ������������ ������.');
					else if(!send.sum) { err('����������� ������� �����.'); $('#sum').focus(); }
					else {
						dialog.process();
						$.post(AJAX_WS, send, function (res) {
							if(res.success) {
								dialog.close();
								_msg('����� ������ �����.');
								expenseSpisok();
							} else
								dialog.abort();
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						remove:1,
						indent:40,
						show:1,
						top:-47,
						left:103
					});
				}
			});
			$('#category')._select({
				width:140,
				title0:'����� ���������',
				spisok:EXPENSE_SPISOK,
				func:expenseSpisok
			});
			$('#worker')._select({
				width:140,
				title0:'��� ����������',
				spisok:WORKERS,
				func:expenseSpisok
			});
			$('#year').years({
				func:expenseSpisok,
				center:function() {
					var inp = $('#monthList input'),
						all = 0;
					for(var n = 1; n <= 12; n++)
						if(inp.eq(n - 1).val() == 0) {
							all = 1;
							break;
						}
					for(n = 1; n <= 12; n++)
						$('#c' + n)._check(all);
					expenseSpisok();
				}
			});		}
		if($('#report.invoice').length) {
			$('.transfer').click(function() {
				var t = $(this),
					from = INVOICE_SPISOK[0] ? INVOICE_SPISOK[0].uid : 0,
					to = INVOICE_SPISOK[1] ? INVOICE_SPISOK[1].uid : 0,
					html = '<table class="_dialog-tab">' +
							'<tr><td class="label">�� �����:<td><input type="hidden" id="from" value="' + from + '" />' +
							'<tr><td class="label">�� ����:<td><input type="hidden" id="to" value="' + to + '" />' +
							'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" /> ���. ' +
							'<tr><td class="label">�����������:<td><input type="text" id="about" />' +
						'</table>',
					dialog = _dialog({
						width:350,
						head:'������� ����� �������',
						content:html,
						butSubmit:'���������',
						submit:submit
					});
				$('#from')._select({
					width:218,
					title0:'�� ������',
					spisok:INVOICE_SPISOK
				});
				$('#to')._select({
					width:218,
					title0:'�� ������',
					spisok:INVOICE_SPISOK
				});
				$('#sum,#about').keyEnter(submit);
				function submit() {
					var send = {
						op:'invoice_transfer',
						from:$('#from').val() * 1,
						to:$('#to').val() * 1,
						sum:$('#sum').val(),
						about:$('#about').val()
					};
					if(!send.from) err('�������� ����-�����������');
					else if(!send.to) err('�������� ����-����������');
					else if(send.from == send.to) err('�������� ������ ����');
					else if(!REGEXP_CENA.test(send.sum) || send.sum == 0) { err('����������� ������� �����'); $('#sum').focus(); }
					else {
						dialog.process();
						$.post(AJAX_WS, send, function(res) {
							if(res.success) {
								$('#invoice-spisok').html(res.i);
								$('.transfer-spisok').html(res.t);
								dialog.close();
								_msg('������� ���������.');
							} else
								dialog.abort();
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<span class="red">' + msg + '</span>',
						top:-47,
						left:92,
						indent:50,
						show:1,
						remove:1
					});
				}
			});
		}
		if($('#report.salary').length) {
			if($('#monthList').length) {
				$('#year').years({func:salarySpisok});
				$('#salmon')._radio({func:salarySpisok});
			}
		}
	});
