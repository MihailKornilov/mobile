$(document)
	.on('click', '.salary .bonus', function() {// внесение бонуса по платежам
		var n,
			year = [],
			week = [],
			html =
				'<table class="salary-tab">' +
					'<tr><td class="label">Процент:<td>' + PROCENT +
					'<tr><td class="label">Год:<td><input type="hidden" id="b-year" value="2015" />' +
					'<tr><td class="label">Неделя:<td><input type="hidden" id="week" />' +
					'<tr><td class="label">Сумма:<td><input type="text" id="sum" class="money" disabled /> руб.' +
				'</table>' +
				'<div id="bonus-spisok"></div>',
			dialog = _dialog({
				top:20,
				width:450,
				head:'Формирование бонуса по платежам',
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
			title0:'Неделя',
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
			$.post(AJAX_MAIN, send, function(res) {
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
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('Бонус начислен.');
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
			head:'Просмотр бонуса по платежам',
			load:1,
			butSubmit:'',
			butCancel:'Закрыть'
		});

		var send = {
			op:'salary_bonus_show',
			expense_id:$(this).attr('val')
		};
		$.post(AJAX_MAIN, send, function (res) {
			if(res.success)
				dialog.content.html(res.html);
			else
				dialog.loadError();
		}, 'json');
	});
