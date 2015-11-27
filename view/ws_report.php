<?php

// ---===! report !===--- Секция отчётов
/*
function history_types($v, $filter) {
	switch($v['type']) {
		case 1: return
					($filter['zayav_id'] ?
						'Заявка создана' :
						'Создана новая заявка '.$v['zayav_link'].
						($filter['client_id'] ? '' : ' для клиента '.$v['client_link'])
					).
					'.';
		case 2: return $filter['zayav_id'] ? 'Заявка удалена.' : 'Удалена заявка '.$v['zayav_link'].'.';
		case 3: return ($filter['client_id'] ? 'Клиент внесён' : 'Внесён новый клиент '.$v['client_link']).'.';
		case 4:
			$statusPrev = $v['value1'] ? _zayavStatus($v['value1']) : '';
			$status = _zayavStatus($v['value']);
			return 'Изменён статус заявки'.
					($filter['zayav_id'] ? '' : ' '.(!isset($v['zayav_link']) ? 'id=<b>'.$v['id'].'</b>' : $v['zayav_link'])).
					($v['value1'] ? ':<br />' : ' на ').
					($v['value1'] ? '<span style="background-color:#'.$statusPrev['color'].'" class="zstatus">'.$statusPrev['name'].'</span> » ' : '').
					'<span style="background-color:#'.$status['color'].'" class="zstatus">'.$status['name'].'</span>';
		case 5: return 'Произведено начисление на сумму <b>'.$v['value'].'</b> руб.'.
						($filter['zayav_id'] ? '' : ' для заявки '.$v['zayav_link'].'.');
		case 6: return
			'Внесён платёж '.
			($v['value2'] ? '<span class="oplata">'._invoice($v['value2']).'</span> ' : '').
			'на сумму <b>'.$v['value'].'</b> руб. '.
			($v['value1'] ? '<span class="prim">('.$v['value1'].')</span> ' : '').
			($v['zayav_id'] && !$filter['zayav_id'] ? 'по заявке '.$v['zayav_link'].'. ' : '').
			($v['zp_id'] ? '<br />Продана запчасть '.$v['zp_link'].'. ' : '');
		case 7: return 'Отредактированы данные заявки'.
						($filter['zayav_id'] ? '' : ' '.$v['zayav_link']).
						($v['value'] ? ':<div class="changes">'.$v['value'].'</div>' : '').
						'.';
		case 8:
			return 'Удалено начисление на сумму <b>'.$v['value'].'</b> руб. '.
				($v['value1'] ? '('.$v['value1'].')' : '').
				($filter['zayav_id'] ? '' : ' у заявки '.$v['zayav_link']).
				'.';
		case 9:
			return 'Удалён платёж '.
				($v['value2'] ? '<span class="oplata">'._invoice($v['value2']).'</span> ' : '').
				'на сумму <b>'.$v['value'].'</b> руб. '.
				($v['value1'] ? '<span class="prim">('.$v['value1'].')</span> ' : '').
				($v['zayav_id'] && !$filter['zayav_id'] ? ' у заявки '.$v['zayav_link'] : '').
				($v['zp_id'] ? ' (Продажа запчасти '.$v['zp_link'].')' : '').
				'.';
		case 10: return 'Отдерактированы данные клиента'.
						($filter['client_id'] ? '' : ' '.$v['client_link']).
						($v['value'] ? ':<div class="changes">'.$v['value'].'</div>' : '.');
		case 11: return 'Произведено объединение клиентов <i>'.$v['value'].'</i> и '.$v['client_link'].'.';
		case 13: return 'Произведена установка запчасти '.$v['zp_link'].
						($filter['zayav_id'] ? '' : ' по заявке '.$v['zayav_link']).
						'.';
		case 15: return 'Произведено списание запчасти '.$v['zp_link'].'';
		case 16: return 'Произведён возврат запчасти '.$v['zp_link'].'';
		case 17: return 'Забракована запчасть '.$v['zp_link'].'';
		case 18: return 'Внесено наличие запчасти '.$v['zp_link'].' в количестве '.$v['value'].' шт.';
		case 19:
			return 'Восстановлен платёж '.
				($v['value2'] ? '<span class="oplata">'._invoice($v['value2']).'</span> ' : '').
				'на сумму <b>'.$v['value'].'</b> руб. '.
				($v['value1'] ? '<span class="prim">('.$v['value1'].')</span> ' : '').
				($v['zayav_id'] && !$filter['zayav_id'] ? ' у заявки '.$v['zayav_link'] : '').
				($v['zp_id'] ? ' (Продажа запчасти '.$v['zp_link'].')' : '').
				'.';
		case 20:
			return 'Создано новое задание'.
				($v['zayav_id'] && !$filter['zayav_id'] ? ' для заявки '.$v['zayav_link'] : '').
				($v['client_id']  && !$filter['client_id'] ? ' для клиента '.$v['client_link'] : '').
				'.';
		case 21: return 'Внесён расход на сумму <b>'.$v['value'].'</b> руб.';
		case 22: return 'Удалён расход на сумму <b>'.$v['value'].'</b> руб.';
//		case 23: return 'Изменены данные расхода на сумму <b>'.$v['value'].'</b> руб.';
		case 27: return 'Восстановлено начисление на сумму <b>'.$v['value'].'</b> руб. '.
						($v['value1'] ? '('.$v['value1'].')' : '').
						($filter['zayav_id'] ? '' : ' у заявки '.$v['zayav_link']).
						'.';
		case 28: return 'Установка текущей суммы для счёта <span class="oplata">'._invoice($v['value1']).'</span>: <b>'.$v['value'].'</b> руб.';
		case 29: return 'Изменение местонахождения устройства'.
						($filter['zayav_id'] ? '' : ' по заявке '.$v['zayav_link']).
						':<div class="changes">'.$v['value'].'</div>';
		case 30: return 'Изменение расходов'.($filter['zayav_id'] ? '' : ' по заявке '.$v['zayav_link']).':<div class="changes z">'.$v['value'].'</div>';

		case 35: return 'Изменена ставка у сотрудника <u>'._viewer($v['value'], 'name').'</u>:<div class="changes">'.$v['value1'].'</div>.';
		case 36: return
			'Внесение начисления з/п на сумму <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'для сотрудника <u>'._viewer($v['value2'], 'name').'</u>.';
		case 37: return
			'Выдача з/п на сумму <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'для сотрудника <u>'._viewer($v['value2'], 'name').'</u>.';


		case 39:
			return 'Перевод '.
					($v['value1'] > 100 ?
						'от сотрудника <u>'._viewer($v['value1'], 'name').'</u> ' :
						'со счёта <span class="oplata">'._invoice($v['value1']).'</span> '
					).
					($v['value2'] > 100 ?
						'сотруднику <u>'._viewer($v['value2'], 'name').'</u> ' :
						'на счёт <span class="oplata">'._invoice($v['value2']).'</span> '
					).
					'в сумме <b>'.$v['value'].'</b> руб. '.
					($v['value3'] ? '<span class="prim">('.$v['value3'].')</span>' : '');
		case 45: return 'Установка баланса з/п в сумме <b>'.$v['value1'].'</b> руб. '.
						'для сотрудника <u>'._viewer($v['value'], 'name').'</u>. ';
		case 44: return
			'Внесение вычета из з/п на сумму <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'у сотрудника <u>'._viewer($v['value2'], 'name').'</u>.';

		case 46: return 'Автоматическое начисление з/п сотруднику <u>'._viewer($v['value1'], 'name').'</u> '.
						'в размере <b>'.$v['value'].'</b> руб. <em>('.$v['value2'].')</em>.';

		case 50: return 'Удаление начисления з/п в сумме <b>'.$v['value'].'</b> руб. у сотрудника <u>'._viewer($v['value1'], 'name').'</u>.';
		case 51: return 'Удаление вычета в сумме <b>'.$v['value'].'</b> руб. '.
						($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
						'у сотрудника <u>'._viewer($v['value2'], 'name').'</u>.';

		case 52: return 'Изменение срока выполнения'.($filter['zayav_id'] ? '' : ' заявки '.$v['zayav_link']).':<div class="changes">'.$v['value'].'</div>';

		case 53: return 'Сброс суммы на счёте <span class="oplata">'._invoice($v['value']).'</span>.';

		case 54: return
			($filter['zayav_id'] ?
				'Заявка на заправку картриджей создана' :
				'Создана новая заявка '.$v['zayav_link'].' на заправку картриджей'.
				($filter['client_id'] ? '' : ' для клиента '.$v['client_link'])
			).
			'.';
		case 55: return ($filter['zayav_id'] ? 'Д' : 'К заявке '.$v['zayav_link'].' д').'обавлены картриджи: '.$v['value'].'.';
		case 56: return 'Удалён картридж <u>'.$v['value'].'</u> '.($filter['zayav_id'] ? '' : 'у заявки '.$v['zayav_link']).'.';
		case 57: return
			'Операции с картриджем <u>'.$v['value'].'</u>'.
			($filter['zayav_id'] ? '' : ' по заявке '.$v['zayav_link']).
			':<div class="changes">'.$v['value1'].'</div>';

		case 58: return
			'Изменение исполнителя'.
			($filter['zayav_id'] ? '' : ' по заявке '.$v['zayav_link']).
			':<div class="changes">'.$v['value'].'</div>';

		case 59: return
			'Сформирован счёт № <b>'.$v['value'].'</b> от <u>'.FullData($v['value2']).' г.</u> на сумму '.$v['value1'].' руб.'.
			($filter['zayav_id'] ? '' : ' по заявке '.$v['zayav_link']).'.';
		case 60: return
			'Оплачен счёт № <b>'.$v['value'].'</b> от <u>'.FullData($v['value2']).' г.</u> на сумму '.$v['value1'].' руб.'.
			($filter['zayav_id'] ? '' : ' по заявке '.$v['zayav_link']).'.';
		case 61: return
			'Отредактирован счёт № <b>'.$v['value'].'</b>'.
			($filter['zayav_id'] ? '' : ' по заявке '.$v['zayav_link']).'.';

		case 62: return 'Внесены результаты диагностики '.($filter['zayav_id'] ? '' : 'по заявке '.$v['zayav_link']).'.';

		case 63: return
			'Счёт № <b>'.$v['value'].'</b> передан клиенту. День: '.FullData($v['value1'], 1).'.'.
			($filter['zayav_id'] ? '' : ' Заявка '.$v['zayav_link'].'.');

		case 1001: return 'В настройках: добавление нового сотрудника <u>'._viewer($v['value'], 'name').'</u>.';
		case 1002: return 'В настройках: удаление сотрудника <u>'._viewer($v['value'], 'name').'</u>.';

		case 1004: return 'В настройках: организация удалена.';

		case 1005: return 'В настройках: внесение новой категории расходов организации <u>'.$v['value'].'</u>.';
		case 1006: return 'В настройках: изменение данных категории расходов организации <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1007: return 'В настройках: удаление категории расходов организации <u>'.$v['value'].'</u>.';

		case 1008: return 'В настройках: внесение нового счёта <u>'.$v['value'].'</u>.';
		case 1009: return 'В настройках: изменение данных счёта <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1010: return 'В настройках: удаление счёта <u>'.$v['value'].'</u>.';

		case 1011: return 'В настройках: внесение нового вида платежа <u>'.$v['value'].'</u>.';
		case 1012: return 'В настройках: изменение вида платежа <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1013: return 'В настройках: удаление вида платежа <u>'.$v['value'].'</u>.';

		case 1014: return '<a href="'.URL.'&p=setup&d=zayavexpense">В настройках:</a> внесение новой категории расходов заявки <u>'.$v['value'].'</u>.';
		case 1015: return '<a href="'.URL.'&p=setup&d=zayavexpense">В настройках:</a> изменение данных категории расходов заявки <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1016: return '<a href="'.URL.'&p=setup&d=zayavexpense">В настройках:</a> удаление данных категории расходов заявки <u>'.$v['value'].'</u>.';

		case 1017: return '<a href="'.URL.'&p=setup&d=service&d1=cartridge">В настройках:</a> внесение нового картриджа <u>'.$v['value'].'</u>.';
		case 1018: return '<a href="'.URL.'&p=setup&d=service&d1=cartridge">В настройках:</a> изменение данных картриджа <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1019: return '<a href="'.URL.'&p=setup&d=service&d1=cartridge">В настройках:</a> объединены картриджи <u>'.$v['value'].'</u> и <u>'.$v['value1'].'</u>.';

		case 1020: return '<a href="'.URL.'&p=setup&d=rekvisit">В настройках:</a> изменены реквизиты организации:<div class="changes">'.$v['value'].'</div>';

		case 1021: return '<a href="'.URL.'&p=setup&d=info">В настройках:</a> изменён вид организации:<div class="changes">'.$v['value'].'</div>';

		default: return $v['type'];
	}
}//history_types()
*/
function history_types($v, $filter) {
	switch($v['type']) {
		case 2: return $filter['zayav_id'] ? 'Заявка удалена.' : 'Удалена заявка '.$v['zayav_link'].'.';
		case 6: return
			'Внесён платёж '.
			($v['value2'] ? '<span class="oplata">'._invoice($v['value2']).'</span> ' : '').
			'на сумму <b>'.$v['value'].'</b> руб. '.
			($v['value1'] ? '<span class="prim">('.$v['value1'].')</span> ' : '').
			($v['zayav_id'] && !$filter['zayav_id'] ? 'по заявке '.$v['zayav_link'].'. ' : '').
			($v['zp_id'] ? '<br />Продана запчасть '.$v['zp_link'].'. ' : '');

		case 9:
			return 'Удалён платёж '.
				($v['value2'] ? '<span class="oplata">'._invoice($v['value2']).'</span> ' : '').
				'на сумму <b>'.$v['value'].'</b> руб. '.
				($v['value1'] ? '<span class="prim">('.$v['value1'].')</span> ' : '').
				($v['zayav_id'] && !$filter['zayav_id'] ? ' у заявки '.$v['zayav_link'] : '').
				($v['zp_id'] ? ' (Продажа запчасти '.$v['zp_link'].')' : '').
				'.';
		case 15: return 'Произведено списание запчасти '.$v['zp_link'].'';
		case 16: return 'Произведён возврат запчасти '.$v['zp_link'].'';
		case 18: return 'Внесено наличие запчасти '.$v['zp_link'].' в количестве '.$v['value'].' шт.';
		case 19:
			return 'Восстановлен платёж '.
				($v['value2'] ? '<span class="oplata">'._invoice($v['value2']).'</span> ' : '').
				'на сумму <b>'.$v['value'].'</b> руб. '.
				($v['value1'] ? '<span class="prim">('.$v['value1'].')</span> ' : '').
				($v['zayav_id'] && !$filter['zayav_id'] ? ' у заявки '.$v['zayav_link'] : '').
				($v['zp_id'] ? ' (Продажа запчасти '.$v['zp_link'].')' : '').
				'.';
		case 20:
			return 'Создано новое задание'.
				($v['zayav_id'] && !$filter['zayav_id'] ? ' для заявки '.$v['zayav_link'] : '').
				($v['client_id']  && !$filter['client_id'] ? ' для клиента '.$v['client_link'] : '').
				'.';
		case 27: return 'Восстановлено начисление на сумму <b>'.$v['value'].'</b> руб. '.
						($v['value1'] ? '('.$v['value1'].')' : '').
						($filter['zayav_id'] ? '' : ' у заявки '.$v['zayav_link']).
						'.';

		case 46: return 'Автоматическое начисление з/п сотруднику <u>'._viewer($v['value1'], 'name').'</u> '.
						'в размере <b>'.$v['value'].'</b> руб. <em>('.$v['value2'].')</em>.';

		case 54: return
			($filter['zayav_id'] ?
				'Заявка на заправку картриджей создана' :
				'Создана новая заявка '.$v['zayav_link'].' на заправку картриджей'.
				($filter['client_id'] ? '' : ' для клиента '.$v['client_link'])
			).
			'.';
		case 55: return ($filter['zayav_id'] ? 'Д' : 'К заявке '.$v['zayav_link'].' д').'обавлены картриджи: '.$v['value'].'.';
		case 56: return 'Удалён картридж <u>'.$v['value'].'</u> '.($filter['zayav_id'] ? '' : 'у заявки '.$v['zayav_link']).'.';
		case 57: return
			'Операции с картриджем <u>'.$v['value'].'</u>'.
			($filter['zayav_id'] ? '' : ' по заявке '.$v['zayav_link']).
			':<div class="changes">'.$v['value1'].'</div>';


		case 59: return
			'Сформирован счёт № <b>'.$v['value'].'</b> от <u>'.FullData($v['value2']).' г.</u> на сумму '.$v['value1'].' руб.'.
			($filter['zayav_id'] ? '' : ' по заявке '.$v['zayav_link']).'.';

		case 1004: return 'В настройках: организация удалена.';

		case 1010: return 'В настройках: удаление счёта <u>'.$v['value'].'</u>.';

		case 1011: return 'В настройках: внесение нового вида платежа <u>'.$v['value'].'</u>.';
		case 1012: return 'В настройках: изменение вида платежа <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1013: return 'В настройках: удаление вида платежа <u>'.$v['value'].'</u>.';

		case 1017: return '<a href="'.URL.'&p=setup&d=service&d1=cartridge">В настройках:</a> внесение нового картриджа <u>'.$v['value'].'</u>.';
		case 1018: return '<a href="'.URL.'&p=setup&d=service&d1=cartridge">В настройках:</a> изменение данных картриджа <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1019: return '<a href="'.URL.'&p=setup&d=service&d1=cartridge">В настройках:</a> объединены картриджи <u>'.$v['value'].'</u> и <u>'.$v['value1'].'</u>.';

		case 1021: return '<a href="'.URL.'&p=setup&d=info">В настройках:</a> изменён вид организации:<div class="changes">'.$v['value'].'</div>';

		default: return $v['type'];
	}
}//history_types()

function statistic() {
	$sql = "SELECT
				SUM(`sum`) AS `sum`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `dtime`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`>0
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$prihod = array();
	while($r = mysql_fetch_assoc($q))
		$prihod[] = array(strtotime($r['dtime']) * 1000, intval($r['sum']));

	$sql = "SELECT
				SUM(`sum`)*-1 AS `sum`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `dtime`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`<0
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$expense = array();
	while($r = mysql_fetch_assoc($q))
		$expense[] = array(strtotime($r['dtime']) * 1000, intval($r['sum']));

	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `mon`
			FROM `client`
			WHERE `ws_id`=".WS_ID."
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$client = array();
	while($r = mysql_fetch_assoc($q))
		$client[] = array(strtotime($r['mon']) * 1000, intval($r['count']));

	//Новые заявки
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-%d') AS `day`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m-%d')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$zayav = array();
	while($r = mysql_fetch_assoc($q))
		$zayav[] = array((strtotime($r['day']) + 40000) * 1000, intval($r['count']));

	//Выполненные заявки
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-%d') AS `day`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `zayav_status`=2
			GROUP BY DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-%d')
			ORDER BY `zayav_status_dtime`";
	$q = query($sql);
	$zayav_ok = array();
	while($r = mysql_fetch_assoc($q))
		$zayav_ok[] = array((strtotime($r['day']) + 40000) * 1000, intval($r['count']));

	//Отменённые заявки
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-%d') AS `day`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `zayav_status`=3
			GROUP BY DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-%d')
			ORDER BY `zayav_status_dtime`";
	$q = query($sql);
	$zayav_fail = array();
	while($r = mysql_fetch_assoc($q))
		$zayav_fail[] = array((strtotime($r['day']) + 40000) * 1000, intval($r['count']));

	//Выданные устройства
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`device_place_dtime`, '%Y-%m-%d') AS `day`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `device_place`=2
			  AND `device_place_dtime`!='0000-00-00 00:00:00'
			GROUP BY DATE_FORMAT(`device_place_dtime`, '%Y-%m-%d')
			ORDER BY `device_place_dtime`";
	$q = query($sql);
	$zayav_sent = array();
	while($r = mysql_fetch_assoc($q))
		$zayav_sent[] = array((strtotime($r['day']) + 40000) * 1000, intval($r['count']));



	//Новые заявки - месяц
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `mon`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$zayavmon = array();
	while($r = mysql_fetch_assoc($q))
		$zayavmon[] = array(strtotime($r['mon']) * 1000, intval($r['count']));

	//Выполненные заявки - месяц
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-15') AS `mon`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `zayav_status`=2
			GROUP BY DATE_FORMAT(`zayav_status_dtime`, '%Y-%m')
			ORDER BY `zayav_status_dtime`";
	$q = query($sql);
	$zayavmon_ok = array();
	while($r = mysql_fetch_assoc($q))
		$zayavmon_ok[] = array(strtotime($r['mon']) * 1000, intval($r['count']));

	//Отменённые заявки - месяц
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-15') AS `mon`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `zayav_status`=3
			GROUP BY DATE_FORMAT(`zayav_status_dtime`, '%Y-%m')
			ORDER BY `zayav_status_dtime`";
	$q = query($sql);
	$zayavmon_fail = array();
	while($r = mysql_fetch_assoc($q))
		$zayavmon_fail[] = array(strtotime($r['mon']) * 1000, intval($r['count']));

	//Выданные устройства - месяц
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`device_place_dtime`, '%Y-%m-15') AS `mon`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `device_place`=2
			  AND `device_place_dtime`!='0000-00-00 00:00:00'
			GROUP BY DATE_FORMAT(`device_place_dtime`, '%Y-%m')
			ORDER BY `device_place_dtime`";
	$q = query($sql);
	$zayavmon_sent = array();
	while($r = mysql_fetch_assoc($q))
		$zayavmon_sent[] = array(strtotime($r['mon']) * 1000, intval($r['count']));

	return
	'<script type="text/javascript" src="/.vkapp/.js/highstock.js"></script>'.
	'<div id="statistic"></div>'.
	'<br /><br />'.
	'<div id="client-count"></div>'.
	'<br /><br />'.
	'<div id="zayav-count"></div>'.
	'<br /><br />'.
	'<div id="zayav-count-mon"></div>'.
	'<script type="text/javascript">'.
		'var statPrihod='.json_encode($prihod).','.
			'statRashod='.json_encode($expense).','.
			'CLIENT_COUNT='.json_encode($client).','.
			'ZAYAV_COUNT='.json_encode($zayav).','.
			'ZAYAV_OK='.json_encode($zayav_ok).','.
			'ZAYAV_FAIL='.json_encode($zayav_fail).','.
			'ZAYAV_SENT='.json_encode($zayav_sent).','.
			'ZAYAVMON_COUNT='.json_encode($zayavmon).','.
			'ZAYAVMON_OK='.json_encode($zayavmon_ok).','.
			'ZAYAVMON_FAIL='.json_encode($zayavmon_fail).','.
			'ZAYAVMON_SENT='.json_encode($zayavmon_sent).';'.
	'</script>'.
	'<script type="text/javascript" src="'.APP_HTML.'/js/statistic.js"></script>';
}//statistic()

function salary_worker_bonus($worker_id, $year, $week) {// формирование бонуса по платежам
	$first_day = date('Y-m-d', ($week - 1) * 7 * 86400 + strtotime('1/1/' . $year) - date('w', strtotime('1/1/' . $year)) * 86400 + 86400);
	$last_day = date('Y-m-d', $week * 7 * 86400 + strtotime('1/1/' . $year) - date('w', strtotime('1/1/' . $year)) * 86400);

	$sql = "SELECT *
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`>0
			  AND `dtime_add`>'".$first_day." 00:00:00'
			  AND `dtime_add`<='".$last_day." 23:59:59'
			ORDER BY `dtime_add` DESC";
	$q = query($sql);
	$money = array();
	while($r = mysql_fetch_assoc($q))
		$money[$r['id']] = $r;

	if(empty($money))
		return '';

	$money = _zayavValToList($money);

	$zayavExpense = array();
	foreach($money as $r)
		if($r['zayav_id'])
			$zayavExpense[$r['zayav_id']] = 0;

	$sql = "SELECT
				`zayav_id`,
				SUM(`sum`) AS `sum`
			FROM `zayav_expense`
			WHERE `zayav_id` IN (".implode(',', array_keys($zayavExpense)).")
			GROUP BY `zayav_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zayavExpense[$r['zayav_id']] = round($r['sum']);

	$send = $first_day.' - '.$last_day.'<br /><br />'.
		'<table class="_spisok">'.
			'<tr><th>Заявка'.
				'<th>Сумма'.
				'<th>Расход'.
				'<th>Бонус';
	$bonusSum = 0;
	foreach($money as $r) {
		$expense = $r['zayav_id'] ? $zayavExpense[$r['zayav_id']] : 0;
		$bonus = round(($r['sum'] - $expense) * __viewerRules($worker_id, 'RULES_MONEY_PROCENT') / 100);
		$send .=
			'<tr><td>'.($r['zayav_id'] ? $r['zayav_link'] : $r['prim']).
				'<td><b>'.round($r['sum']).'</b>'.
				'<td><input type="text" class="i-expense" value="'.($expense ? $expense : '').'" />'.
				'<td class="bns" val="'.$r['id'].'">'.$bonus;
		$bonusSum += $bonus;
	}
	$send .= '</table>';

	return $send;
}//salary_worker_bonus()
function salary_worker_bonus_show($expense) {// просмотр бонусов по платежам
	$bonus = array();
	$sql = "SELECT
				`ze`.`id`,
				`ze`.`expense`,
				`ze`.`bonus`,
				`m`.`sum`,
				`m`.`zayav_id`,
				`m`.`prim`,
				`m`.`dtime_add`
			FROM `zayav_expense_bonus` `ze`,
				 `money` `m`
			WHERE `ze`.`money_id`=`m`.`id`
			  AND `ze`.`expense_id`=".$expense['id'];
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$bonus[$r['id']] = $r;

	$bonus = _zayavValToList($bonus);

	$send = '<table class="_spisok">'.
			'<tr><th>Дата платежа'.
				'<th>Описание'.
				'<th>Сумма'.
				'<th>Расход'.
				'<th>Бонус';
	foreach($bonus as $r) {
		$send .=
			'<tr><td>'.FullDataTime($r['dtime_add']).
				'<td>'.($r['zayav_id'] ? 'Заявка '.$r['zayav_link'] : $r['prim']).
				'<td><b>'.round($r['sum']).'</b>'.
				'<td>'.($r['expense'] ? $r['expense'] : '').
				'<td>'.$r['bonus'];
	}
	$send .= '</table>';

	return $send;
}//salary_worker_bonus()

