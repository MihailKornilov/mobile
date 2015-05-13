<?php

// ---===! report !===--- Секция отчётов
/*
function report() {
	$d = empty($_GET['d']) ? 'history' : $_GET['d'];
	$d1 = '';
	$pages = array(
		'history' => 'История действий',
		'remind' => 'Напоминания'.REMIND_ACTIVE.'<div class="img_add _remind-add"></div>',
		'money' => 'Деньги',
		'salary' => 'З/п сотрудников',
		'stat' => 'Статистика'
	);

	$rightLink = '<div class="rightLink">';
	if($pages)
		foreach($pages as $p => $name)
			$rightLink .= '<a href="'.URL.'&p=report&d='.$p.'"'.($d == $p ? ' class="sel"' : '').'>'.$name.'</a>';
	$rightLink .= '</div>';

	$right = '';
	switch($d) {
		default: $d = 'history';
		case 'histoty':
			$data = history();
			$left = $data['spisok'];
			$right .= history_right();
			break;
		case 'remind':
			$remind = _remind();
			$left = $remind['spisok'];
			$right .= $remind['right'];
			break;
		case 'money':
			$d1 = empty($_GET['d1']) ? 'income' : $_GET['d1'];
			switch($d1) {
				default:
					$d1 = 'income';
					switch(@$_GET['d2']) {
						case 'all': $left = income_all(); break;
						case 'year':
							if(empty($_GET['year']) || !preg_match(REGEXP_YEAR, $_GET['year'])) {
								$left = 'Указан некорректный год.';
								break;
							}
							$left = income_year(intval($_GET['year']));
							break;
						case 'month':
							if(empty($_GET['mon']) || !preg_match(REGEXP_YEARMONTH, $_GET['mon'])) {
								$left = 'Указан некорректный месяц.';
								break;
							}
							$left = income_month($_GET['mon']);
							break;
						default:
							if(!_calendarDataCheck(@$_GET['day']))
								$_GET['day'] = _calendarWeek();
							$left = income_day($_GET['day']);
							$right = income_right($_GET['day']);
					}
					break;
				case 'expense':
					$left = expense();
					$right .= expense_right();
					break;
				case 'invoice': $left = invoice(); break;
			}
			$left =
				'<div id="dopLinks">'.
					'<a class="link'.($d1 == 'income' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=income">Платежи</a>'.
					'<a class="link'.($d1 == 'expense' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=expense">Расходы</a>'.
					'<a class="link'.($d1 == 'invoice' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=invoice">Счета</a>'.
				'</div>'.
				$left;
			break;
		case 'salary':
			if($worker_id = _isnum(@$_GET['id'])) {
				$v = salaryFilter(array(
					'worker_id' => $worker_id,
					'mon' => intval(@$_GET['mon']),
					'year' => intval(@$_GET['year']),
					'acc_id' => intval(@$_GET['acc_id'])
				));
				$left = salary_worker($v);
				if(defined('WORKER_OK'))
					$right = '<input type="hidden" id="year" value="'.$v['year'].'" />'.
							 '<div id="monthList">'.salary_monthList($v).'</div>';
			} else
				$left = salary();
			break;
		case 'stat': $left = statistic(); break;
	}

	return
	'<table class="tabLR '.($d1 ? $d1 : $d).'" id="report">'.
		'<tr><td class="left"'.($d == 'remind' ? ' id="remind-spisok"' : '').'>'.$left.
			'<td class="right">'.
				$rightLink.
				$right.
	'</table>';
}//report()
*/
function report() {
	$d = empty($_GET['d']) ? 'history' : $_GET['d'];
	$d1 = '';
	$pages = array(
		'history' => 'История действий',
		'remind' => 'Напоминания'.REMIND_ACTIVE.'<div class="img_add _remind-add"></div>',
		'money' => 'Деньги',
		'salary' => 'З/п сотрудников',
		'stat' => 'Статистика'
	);

	$rightLink = '<div class="rightLink">';
	if($pages)
		foreach($pages as $p => $name)
			$rightLink .= '<a href="'.URL.'&p=report&d='.$p.'"'.($d == $p ? ' class="sel"' : '').'>'.$name.'</a>';
	$rightLink .= '</div>';

	$right = '';
	switch($d) {
		default: $d = 'history';
		case 'histoty':
			$data = history();
			$left = $data['spisok'];
			$right .= history_right();
			break;
		case 'remind':
			$remind = _remind();
			$left = $remind['spisok'];
			$right .= $remind['right'];
			break;
		case 'money':
			$d1 = empty($_GET['d1']) ? 'income' : $_GET['d1'];
			switch($d1) {
				default:
					$d1 = 'income';
					switch(@$_GET['d2']) {
						case 'all': $left = income_all(); break;
						case 'year':
							if(empty($_GET['year']) || !preg_match(REGEXP_YEAR, $_GET['year'])) {
								$left = 'Указан некорректный год.';
								break;
							}
							$left = income_year(intval($_GET['year']));
							break;
						case 'month':
							if(empty($_GET['mon']) || !preg_match(REGEXP_YEARMONTH, $_GET['mon'])) {
								$left = 'Указан некорректный месяц.';
								break;
							}
							$left = income_month($_GET['mon']);
							break;
						default:
							if(!_calendarDataCheck(@$_GET['day']))
								$_GET['day'] = _calendarWeek();
							$left = income_day($_GET['day']);
							$right = income_right($_GET['day']);
					}
					break;
				case 'expense':
					$left = expense();
					$right .= expense_right();
					break;
				case 'schet':
					$left = report_schet();
					$right .= _check('paid', 'Не оплачено');
					break;
				case 'invoice': $left = invoice(); break;
			}
			$left =
				'<div id="dopLinks">'.
				'<a class="link'.($d1 == 'income' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=income">Платежи</a>'.
				'<a class="link'.($d1 == 'expense' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=expense">Расходы</a>'.
				'<a class="link'.($d1 == 'schet' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=schet">Счета на оплату</a>'.
				'<a class="link'.($d1 == 'invoice' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=invoice">Расчётные счета</a>'.
				'</div>'.
				$left;
			break;
		case 'salary':
			if($worker_id = _isnum(@$_GET['id'])) {
				$v = salaryFilter(array(
					'worker_id' => $worker_id,
					'mon' => intval(@$_GET['mon']),
					'year' => intval(@$_GET['year']),
					'acc_id' => intval(@$_GET['acc_id'])
				));
				$left = salary_worker($v);
				if(defined('WORKER_OK'))
					$right = '<input type="hidden" id="year" value="'.$v['year'].'" />'.
						'<div id="monthList">'.salary_monthList($v).'</div>';
			} else
				$left = salary();
			break;
		case 'stat': $left = statistic(); break;
	}

	return
		'<table class="tabLR '.($d1 ? $d1 : $d).'" id="report">'.
		'<tr><td class="left">'.$left.
			'<td class="right">'.
				$rightLink.
				$right.
		'</table>';
}//report()

function history_insert($v) {
	if(empty($v['ws_id']))
		$v['ws_id'] = WS_ID;
	$type = $v['type'];
	unset($v['type']);
	_historyInsert($type, $v);
}//history_insert()
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
			'Счёт № <b>'.$v['value'].'</b> передан клиенту '.FullData($v['value1'], 1).'.'.
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
		case 1019: return '<a href="'.URL.'&p=setup&d=service&d1=cartridge">В настройках:</a> удаленён картридж <u>'.$v['value'].'</u>.';

		case 1020: return '<a href="'.URL.'&p=setup&d=rekvisit">В настройках:</a> изменены реквизиты организации:<div class="changes">'.$v['value'].'</div>';

		case 1021: return '<a href="'.URL.'&p=setup&d=info">В настройках:</a> изменён вид организации:<div class="changes">'.$v['value'].'</div>';

		default: return $v['type'];
	}
}//history_types()
function history_group($action) {
	switch($action) {
		case 1: return '3,10,11';
		case 2: return '1,2,4,5,6,7,8,9,13';
		case 3: return '13,14,15,16,17,18';
		case 4: return '6,9,12,19';
	}
	return 0;
}//history_types_group()
function history_right() {
	$sql = "SELECT DISTINCT `viewer_id_add`
			FROM `history`
			WHERE `ws_id`=".WS_ID."
			  AND `viewer_id_add`";
	$q = query($sql);
	$viewer = array();
	while($r = mysql_fetch_assoc($q))
		$viewer[] = $r['viewer_id_add'];
	$workers = array();
	foreach($viewer as $id)
		$workers[] = '{uid:'.$id.',title:"'._viewer($id, 'name').'"}';
	return
	'<script type="text/javascript">var WORKERS=['.implode(',', $workers).'];</script>'.
	'<div class="findHead">Сотрудник</div><input type="hidden" id="viewer_id_add">'.
	'<div class="findHead">Действие</div><input type="hidden" id="action">';
}//history_right()
function history($v=array()) {
	return _history(
		'history_types',
		array('_clientLink', '_zayavNomerLink', '_zpLink'),
		$v,
		array(
			'ws_id' => WS_ID,
			'client_id' => !empty($v['client_id']) && _isnum($v['client_id']) ? intval($v['client_id']) : 0,
			'zayav_id' => !empty($v['zayav_id']) && _isnum($v['zayav_id']) ? intval($v['zayav_id']) : 0,
			'zp_id' => !empty($v['zp_id']) && _isnum($v['zp_id']) ? intval($v['zp_id']) : 0
		)
	);
}//history()

function income_path($data) {
	$ex = explode(':', $data);
	$d = explode('-', $ex[0]);
	define('YEAR', $d[0]);
	define('MON', @$d[1]);
	define('DAY', @$d[2]);
	$to = '';
	if(!empty($ex[1])) {
		$d = explode('-', $ex[1]);
		$to = ' - '.intval($d[2]).
			($d[1] != MON ? ' '._monthFull($d[1]) : '').
			($d[0] != YEAR ? ' '.$d[0] : '');
	}
	return
		'<a href="'.URL.'&p=report&d=money&d1=income&d2=all">Год</a> » '.(YEAR ? '' : '<b>За всё время</b>').
		(MON ? '<a href="'.URL.'&p=report&d=money&d1=income&d2=year&year='.YEAR.'">'.YEAR.'</a> » ' : '<b>'.YEAR.'</b>').
		(DAY ? '<a href="'.URL.'&p=report&d=money&d1=income&d2=month&mon='.YEAR.'-'.MON.'">'._monthDef(MON, 1).'</a> » ' : (MON ? '<b>'._monthDef(MON, 1).'</b>' : '')).
		(DAY ? '<b>'.intval(DAY).$to.'</b>' : '');
}//income_path()
function income_all() {//Суммы платежей по годам
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y') AS `year`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`<0
			GROUP BY DATE_FORMAT(`dtime_add`,'%Y')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$expense = array();
	while($r = mysql_fetch_assoc($q))
		$expense[$r['year']] = round(abs($r['sum']), 2);

	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y') AS `year`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`>0
			GROUP BY DATE_FORMAT(`dtime_add`,'%Y')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['year']] = '<tr>'.
			'<td><a href="'.URL.'&p=report&d=money&d1=income&d2=year&year='.$r['year'].'">'.$r['year'].'</a>'.
			'<td class="r"><b>'._sumSpace($r['sum']).'</b>'.
			'<td class="r">'.(isset($expense[$r['year']]) ? _sumSpace($expense[$r['year']]) : '').
			'<td class="r">'.(isset($expense[$r['year']]) ? _sumSpace($r['sum'] - $expense[$r['year']]) : '');

	return
	'<div class="headName">Суммы платежей по годам</div>'.
	'<table class="_spisok">'.
		'<tr><th>Год'.
			'<th>Платежи'.
			'<th>Расход'.
			'<th>Чистый доход'.
			implode('', $spisok).
	'</table>';
}//income_all()
function income_year($year) {//Суммы платежей по месяцам
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%m') AS `mon`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`<0
			  AND `dtime_add` LIKE '".$year."%'
			GROUP BY DATE_FORMAT(`dtime_add`,'%m')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$expense = array();
	while($r = mysql_fetch_assoc($q))
		$expense[$r['mon']] = round(abs($r['sum']), 2);


	$spisok = array();
	for($n = 1; $n <= (strftime('%Y', time()) == $year ? intval(strftime('%m', time())) : 12); $n++)
		$spisok[$n] =
			'<tr><td class="r grey">'._monthDef($n, 1).
				'<td class="r">';
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%m') AS `mon`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`>0
			  AND `dtime_add` LIKE '".$year."%'
			GROUP BY DATE_FORMAT(`dtime_add`,'%m')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[intval($r['mon'])] =
			'<tr><td class="r"><a href="'.URL.'&p=report&d=money&d1=income&d2=month&mon='.$year.'-'.$r['mon'].'">'._monthDef($r['mon'], 1).'</a>'.
				'<td class="r"><b>'._sumSpace($r['sum']).'</b>'.
				'<td class="r">'.(isset($expense[$r['mon']]) ? _sumSpace($expense[$r['mon']]) : '').
				'<td class="r">'.(isset($expense[$r['mon']]) ? _sumSpace($r['sum'] - $expense[$r['mon']]) : '');

	return
	'<div class="headName">Суммы платежей по месяцам за '.$year.' год</div>'.
	'<div class="inc-path">'.income_path($year).'</div>'.
	'<table class="_spisok">'.
		'<tr><th>Месяц'.
			'<th>Платежи'.
			'<th>Расход'.
			'<th>Чистый доход'.
			implode('', $spisok).
	'</table>';
}//income_year()
function income_month($mon) {
	$path = income_path($mon);
	$spisok = array();
	for($n = 1; $n <= (strftime('%Y', time()) == YEAR ? intval(strftime('%d', time())) : date('t', strtotime($mon.'-01'))); $n++)
		$spisok[$n] =
			'<tr><td class="r grey">'.$n.'.'.MON.'.'.YEAR.
			'<td class="r">';
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%d') AS `day`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`>0
			  AND `dtime_add` LIKE '".$mon."%'
			GROUP BY DATE_FORMAT(`dtime_add`,'%d')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[intval($r['day'])] =
			'<tr><td class="r"><a href="'.URL.'&p=report&d=money&d1=income&day='.$mon.'-'.$r['day'].'">'.intval($r['day']).'.'.MON.'.'.YEAR.'</a>'.
			'<td class="r"><b>'._sumSpace($r['sum']).'</b>';

	return
	'<div class="headName">Суммы платежей по дням за '._monthDef(MON, 1).' '.YEAR.'</div>'.
	'<div class="inc-path">'.$path.'</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>Месяц'.
			'<th>Всего'.
			implode('', $spisok).
	'</table>';
}//income_month()
function income_days($month=0) {
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y-%m-%d') AS `day`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`>0
			  AND `dtime_add` LIKE ('".($month ? $month : strftime('%Y-%m'))."%')
			GROUP BY DATE_FORMAT(`dtime_add`,'%d')";
	$q = query($sql);
	$days = array();
	while($r = mysql_fetch_assoc($q))
		$days[$r['day']] = 1;
	return $days;
}//income_days()
function income_right($sel) { //Условия поиска справа для отчётов платежей
	return
		_calendarFilter(array(
			'days' => income_days(),
			'func' => 'income_days',
			'sel' => $sel
		)).
		(VIEWER_ADMIN ? _check('del', 'Удалённые платежи') : '');
}//income_right()
function income_day($day) {
	$data = income_spisok(array('day'=>$day));
	return
		'<div class="headName">Список поступлений<a class="add income-add">Внести платёж</a></div>'.
		'<div class="inc-path">'.income_path($day).'</div>'.
		'<div class="spisok">'.$data['spisok'].'</div>';
}//income_day()
function incomeFilter($v) {
	$send = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 30,
		'client_id' => !empty($v['client_id']) && preg_match(REGEXP_NUMERIC, $v['client_id']) ? $v['client_id'] : 0,
		'zayav_id' => !empty($v['zayav_id']) && preg_match(REGEXP_NUMERIC, $v['zayav_id']) ? $v['zayav_id'] : 0,
		'del' => isset($v['del']) && preg_match(REGEXP_BOOL, $v['del']) ? $v['del'] : 0,
		'day' => '',
		'from' => '',
		'to' => ''
	);
	$send = _calendarPeriod(@$v['day']) + $send;
	return $send;
}//incomeFilter()
function income_spisok($filter=array()) {
	$filter = incomeFilter($filter);

	$cond = "`ws_id`=".WS_ID." AND `sum`>0";

	if($filter['client_id'])
		$cond .= " AND `client_id`=".$filter['client_id'];
	if($filter['zayav_id'])
		$cond .= " AND `zayav_id`=".$filter['zayav_id'];
	if(!$filter['del'] || !VIEWER_ADMIN)
		$cond .= " AND !`deleted`";
	if($filter['day'])
		$cond .= " AND `dtime_add` LIKE '".$filter['day']."%'";
	if($filter['from'])
		$cond .= " AND `dtime_add`>='".$filter['from']." 00:00:00' AND `dtime_add`<='".$filter['to']." 23:59:59'";

	$sql = "SELECT
				COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE ".$cond;
	$send = mysql_fetch_assoc(query($sql));
	$send['filter'] = $filter;
	if(!$send['all'])
		return $send + array('spisok' => '<div class="_empty">Платежей нет.</div>');

	$all = $send['all'];
	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($filter['page'] - 1) * $filter['limit'];


	$sql = "SELECT *
			FROM `money`
			WHERE ".$cond."
			ORDER BY `dtime_add` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$money = array();
	while($r = mysql_fetch_assoc($q))
		$money[$r['id']] = $r;
	//$money = _viewer($money);
	$money = _zayavNomerLink($money);
	$money = _zpLink($money);

	$send['spisok'] = $page > 1 ? '' :
		'<input type="hidden" id="money_limit" value="'.$filter['limit'].'" />'.
		'<input type="hidden" id="money_client_id" value="'.$filter['client_id'].'" />'.
		'<input type="hidden" id="money_zayav_id" value="'.$filter['zayav_id'].'" />'.
		'<div class="_moneysum">'.
			'Показан'._end($all, '', 'о').' <b>'.$all.'</b> платеж'._end($all, '', 'а', 'ей').
			' на сумму <b>'._sumSpace($send['sum']).'</b> руб.'.
		'</div>'.
		'<table class="_spisok _money">'.
			'<tr><th>Сумма'.
				'<th>Описание'.
				'<th>Дата'.
				'<th>';

	foreach($money as $r)
		$send['spisok'] .= income_unit($r, $filter);
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'" id="income_next"><td colspan="4">'.
				'<span>Показать ещё '.$c.' платеж'._end($c, '', 'а', 'ей').'</span>';
	}
	if($page == 1)
		$send['spisok'] .= '</table>';
	return $send;
}//income_spisok()
function income_unit($r) {
	$about = '';
	if($r['zayav_id'])
		$about = 'Заявка '.$r['zayav_link'];
	if($r['zp_id'])
		$about = 'Продажа запчасти '.$r['zp_link'];
	$about .= ($about ? '. ' : '').$r['prim'];
	return
		'<tr'.($r['deleted'] ? ' class="deleted"' : '').' val="'.$r['id'].'">'.
			'<td class="sum">'._sumSpace($r['sum']).
			'<td><span class="type">'._invoice($r['invoice_id']).':</span> '.$about.
			'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -20).FullDataTime($r['dtime_add']).
			'<td class="ed">'.
				'<div class="img_del income-del'._tooltip('Удалить платёж', -54).'</div>'.
				'<div class="img_rest income-rest'._tooltip('Восстановить платёж', -69).'</div>';
}//income_unit()
function income_insert($v) {
	$v = array(
		'client_id' => _num(@$v['client_id']),
		'zayav_id' => _num(@$v['zayav_id']),
		'zp_id' => _num(@$v['zp_id']),
		'schet_id' => _num(@$v['schet_id']),
		'invoice_id' => _num($v['invoice_id']),
		'sum' => _cena($v['sum']),
		'prepay' => _bool(@$v['prepay']),
		'prim' => _txt(@$v['prim'])
	);

	if($v['zayav_id']) {
		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$v['zayav_id'];
		if(!$r = mysql_fetch_assoc(query($sql)))
			return false;
		$v['client_id'] = $r['client_id'];
	}

	$sql = "INSERT INTO `money` (
				`ws_id`,
				`client_id`,
				`zayav_id`,
				`zp_id`,
				`schet_id`,
				`invoice_id`,
				`sum`,
				`prepay`,
				`prim`,
				`viewer_id_add`
			) VALUES (
				".WS_ID.",
				".$v['client_id'].",
				".$v['zayav_id'].",
				".$v['zp_id'].",
				".$v['schet_id'].",
				".$v['invoice_id'].",
				".$v['sum'].",
				".$v['prepay'].",
				'".addslashes($v['prim'])."',
				".VIEWER_ID."
			)";
	query($sql);

	invoice_history_insert(array(
		'action' => 1,
		'table' => 'money',
		'id' => mysql_insert_id()
	));
	clientBalansUpdate($v['client_id']);
	zayavBalansUpdate($v['zayav_id']);

	history_insert(array(
		'type' => 6,
		'client_id' => $v['client_id'],
		'zayav_id' => $v['zayav_id'],
		'zp_id' => $v['zp_id'],
		'value' => $v['sum'],
		'value1' => $v['prim'],
		'value2' => $v['invoice_id']
	));

	return $v;
}//income_insert()

function expenseFilter($v) {
	$send = array(
		'page' => _isnum(@$v['page']) ? $v['page'] : 1,
		'limit' => _isnum(@$v['limit']) ? $v['limit'] : 30,
		'category' => _isnum(@$v['category']),
		'worker' => _isnum(@$v['worker']),
		//'invoice_id' => !empty($v['invoice_id']) && preg_match(REGEXP_NUMERIC, $v['invoice_id']) ? $v['invoice_id'] : 0,
		'year' => !empty($v['year']) && preg_match(REGEXP_NUMERIC, $v['year']) ? $v['year'] : strftime('%Y'),
		'month' => isset($v['month']) ? $v['month'] : intval(strftime('%m'))
		//'del' => isset($v['del']) && preg_match(REGEXP_BOOL, $v['del']) ? $v['del'] : 0
	);
	$mon = array();
	if(!empty($send['month']))
		foreach(explode(',', $send['month']) as $r)
			$mon[$r] = 1;
	$send['month'] = $mon;
	return $send;
}//expenseFilter()
function expense_right() {
	$sql = "SELECT DISTINCT `worker_id` AS `viewer_id_add`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND `sum`<0
			  AND `worker_id`";
	$q = query($sql);
	$viewer = array();
	while($r = mysql_fetch_assoc($q)) {
		$r['id'] = $r['viewer_id_add'];
		$viewer[$r['viewer_id_add']] = $r;
	}
	$viewer = _viewer($viewer);
	$workers = array();
	foreach($viewer as $id => $w)
		$workers[] = '{uid:'.$id.',title:"'.$w['viewer_name'].'"}';
	return '<script type="text/javascript">var WORKERS=['.implode(',', $workers).'];</script>'.
		'<div class="findHead">Категория</div>'.
		'<input type="hidden" id="category">'.
		'<div class="findHead">Сотрудник</div>'.
		'<input type="hidden" id="worker">'.
		'<input type="hidden" id="year">'.
		'<div id="monthList">'.expenseMonthSum().'</div>';
}//expense_right()
function expenseMonthSum($v=array()) {
	$filter = expenseFilter($v);
	$sql = "SELECT
				DISTINCT(DATE_FORMAT(`dtime_add`,'%m')) AS `month`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`<0
			  AND `dtime_add` LIKE '".$filter['year']."-%'".
			  ($filter['category'] ? " AND `expense_id`=".$filter['category'] : '').
			  ($filter['worker'] ? " AND `worker_id`=".$filter['worker'] : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%m')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$res = array();
	while($r = mysql_fetch_assoc($q))
		$res[intval($r['month'])] = abs($r['sum']);
	$send = '';
	for($n = 1; $n <= 12; $n++)
		$send .= _check(
			'c'.$n,
			_monthDef($n).(isset($res[$n]) ? '<span class="sum">'.$res[$n].'</span>' : ''),
			isset($filter['month'][$n]),
			1
		);
	return $send;
}//expenseMonthSum()
function expense() {
	$data = expense_spisok();
	$year = array();
	for($n = 2014; $n <= strftime('%Y'); $n++)
		$year[$n] = $n;
	return
	'<script type="text/javascript">'.
		'var WORKERS='.query_selJson("SELECT `viewer_id`,CONCAT(`first_name`,' ',`last_name`) FROM `vk_user` WHERE `ws_id`=".WS_ID).','.
			'MON_SPISOK='._selJson(_monthDef(0, 1)).','.
			'YEAR_SPISOK='._selJson($year).';'.
	'</script>'.
	'<div class="headName">Список расходов организации<a class="add">Внести новый расход</a></div>'.
	'<div id="spisok">'.$data['spisok'].'</div>';
}//expense()
function expense_spisok($v=array()) {
	$filter = expenseFilter($v);
	$dtime = array();
	foreach($filter['month'] as $mon => $k)
		$dtime[] = "`dtime_add` LIKE '".$filter['year']."-".($mon < 10 ? 0 : '').$mon."%'";
	$cond = "`ws_id`=".WS_ID."
			 AND !`deleted`
			 AND `sum`<0".
			 (!empty($dtime) ? " AND (".implode(' OR ', $dtime).")" : '').
			 ($filter['category'] ? ' AND `expense_id`='.$filter['category'] : '').
			 ($filter['worker'] ? " AND `worker_id`=".$filter['worker'] : '');

	$sql = "SELECT
				COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE ".$cond;
	$send = mysql_fetch_assoc(query($sql));
	$send['filter'] = $filter;
	if(!$send['all'])
		return $send + array('spisok' => '<div class="_empty">Расходов нет.</div>');

	$all = $send['all'];
	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($page - 1) * $limit;

	$send['spisok'] = $page == 1 ?
		'<div class="summa">'.
			'Показан'._end($all, 'а', 'о').' <b>'.$all.'</b> запис'._end($all, 'ь', 'и', 'ей').
			' на сумму <b>'.abs($send['sum']).'</b> руб.'.
			(empty($dtime) ? ' за всё время.' : '').
		'</div>'.
		'<table class="_spisok _money">'.
			'<tr><th>Сумма'.
				'<th>Описание'.
				'<th>Дата'.
				'<th>'
	: '';
	$sql = "SELECT *
			FROM `money`
			WHERE ".$cond."
			ORDER BY `dtime_add` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$expense = array();
	while($r = mysql_fetch_assoc($q))
		$expense[$r['id']] = $r;
	$expense = _viewer($expense);
	foreach($expense as $r)
		$send['spisok'] .= '<tr'.($r['deleted'] ? ' class="deleted"' : '').'>'.
			'<td class="sum"><b>'.abs($r['sum']).'</b>'.
			'<td>'.($r['expense_id'] ? '<span class="type">'._expense($r['expense_id']).($r['prim'] || $r['worker_id'] ? ':' : '').'</span> ' : '').
				   ($r['worker_id'] ? '<u>'._viewer($r['worker_id'], 'name').'</u>'.
				   ($r['prim'] ? ', ' : '') : '').$r['prim'].
			'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -20).FullDataTime($r['dtime_add']).
			'<td class="ed"><div val="'.$r['id'].'" class="img_del'._tooltip('Удалить', -29).'</div>';
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'"><td colspan="4">'.
				'<span>Показать ещё '.$c.' запис'._end($c, 'ь', 'и', 'ей').'</span>';
	}
	if($page == 1)
		$send['spisok'] .= '</table>';
	return $send;
}//expense_spisok()

function reportSchetFilter($v) {
	$send = array(
		'page' => _isnum(@$v['page']) ? $v['page'] : 1,
		'limit' => _isnum(@$v['limit']) ? $v['limit'] : 100
	);
	return $send;
}//expenseFilter()
function report_schet() {
	$data = report_schet_spisok();
	return
		'<div class="headName">Список счетов на оплату</div>'.
		'<div id="spisok">'.$data['spisok'].'</div>';
}//report_schet()
function report_schet_spisok($v=array()) {
	$filter = reportSchetFilter($v);
	$cond = "`ws_id`=".WS_ID;

	$sql = "SELECT
				COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `zayav_schet`
			WHERE ".$cond;
	$send = mysql_fetch_assoc(query($sql));
	$send['filter'] = $filter;
	if(!$send['all'])
		return $send + array('spisok' => '<div class="_empty">Счетов нет.</div>');

	$all = $send['all'];
	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($page - 1) * $limit;

	$send['spisok'] =
		'<div id="result">'.
			'Показан'._end($all, '', 'о').' <b>'.$all.'</b> сч'._end($all, 'ёт', 'ёта', 'етов').
			' на сумму <b>'.$send['sum'].'</b> руб.'.
		'</div>'.
		'<table class="_spisok _money">';

	$sql = "SELECT *
			FROM `zayav_schet`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);

	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']] = $r;

	$spisok = _zayavValues($spisok);

	foreach($spisok as $r)
		$send['spisok'] .= schet_unit($r);

	$send['spisok'] .= '</table>';
	return $send;
}//report_schet_spisok()

function _invoiceBalans($invoice_id, $start=false) {// Получение текущего баланса счёта
	if($start === false)
		$start = _invoice($invoice_id, 'start');
	$income = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `money` WHERE !`deleted` AND `ws_id`=".WS_ID." AND `invoice_id`=".$invoice_id);
	$from = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `invoice_transfer` WHERE `ws_id`=".WS_ID." AND `invoice_from`=".$invoice_id);
	$to = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `invoice_transfer` WHERE `ws_id`=".WS_ID." AND `invoice_to`=".$invoice_id);
	return round($income - $start - $from + $to, 2);
}//_invoiceBalans()
function invoice() {
	return
		'<div class="headName">'.
			'Счета'.
			'<a class="add transfer">Перевод между счетами</a>'.
			'<span>::</span>'.
			'<a href="'.URL.'&p=setup&d=invoice" class="add">Управление счетами</a>'.
		'</div>'.
		'<div id="invoice-spisok">'.invoice_spisok().'</div>'.
		(RULES_HISTORYTRANSFER ? '<div class="headName">История переводов</div>' : '').
		'<div class="transfer-spisok">'.transfer_spisok().'</div>';
}//invoice()
function invoice_spisok() {
	$invoice = _invoice();
	if(empty($invoice))
		return 'Счета не определены.';

	$send = '<table class="_spisok">';
	foreach($invoice as $r)
		$send .= '<tr>'.
			'<td class="name">'.
				'<b>'.$r['name'].'</b>'.
				'<div class="about">'.$r['about'].'</div>'.
		($r['start'] != -1 ?
			'<td class="balans"><b>'._sumSpace(_invoiceBalans($r['id'])).'</b> руб.'.
			'<td><div val="'.$r['id'].'" class="img_note'._tooltip('Посмотреть историю операций', -95).'</div>'
		: '').
		//(VIEWER_ADMIN || $r['start'] != -1 ?
			'<td><a class="invoice_set" val="'.$r['id'].'">Установить<br />текущую<br />сумму</a>'.
		(VIEWER_ADMIN && $r['start'] != -1 ?
			'<td><a class="invoice_reset" val="'.$r['id'].'">Сбросить<br />сумму</a>'
		: '');
	$send .= '</table>';
	return $send;
}//invoice_spisok()
function transfer_spisok($v=array()) {
	if(!RULES_HISTORYTRANSFER)
		return  '';
	$v = array(
		//	'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		//	'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 15,
	);
	$sql = "SELECT *
	        FROM `invoice_transfer`
	        WHERE `ws_id`=".WS_ID."
	        ORDER BY `id` DESC";
	$q = query($sql);
	$send = '<table class="_spisok _money">'.
		'<tr><th>Cумма'.
			'<th>Со счёта'.
			'<th>На счёт'.
			'<th>Подробно'.
			'<th>Дата';
	while($r = mysql_fetch_assoc($q))
		$send .=
			'<tr><td class="sum">'._sumSpace($r['sum']).
				'<td>'.($r['invoice_from'] ? '<span class="type">'._invoice($r['invoice_from']).'</span>' : '').
					($r['worker_from'] && $r['invoice_from'] ? '<br />' : '').
					($r['worker_from'] ? _viewer($r['worker_from'], 'name') : '').
				'<td>'.($r['invoice_to'] ? '<span class="type">'._invoice($r['invoice_to']).'</span>' : '').
					($r['worker_to'] && $r['invoice_to'] ? '<br />' : '').
					($r['worker_to'] ? _viewer($r['worker_to'], 'name') : '').
				'<td class="about">'.$r['about'].
				'<td class="dtime">'.FullDataTime($r['dtime_add'], 1);
	$send .= '</table>';
	return $send;
}//transfer_spisok()
function invoice_history($v) {
	$v = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 15,
		'invoice_id' => intval($v['invoice_id'])
	);
	$send = $v['page'] == 1 ?
				'<div>Счёт <u>'._invoice($v['invoice_id']).'</u>:</div>'.
				'<input type="hidden" id="invoice_history_id" value="'.$v['invoice_id'].'" />'
			: '';

	$all = query_value("SELECT COUNT(*) FROM `invoice_history` WHERE `ws_id`=".WS_ID." AND `invoice_id`=".$v['invoice_id']);
	if(!$all)
		return $send.'<br />Истории нет.';

	$start = ($v['page'] - 1) * $v['limit'];
	$sql = "SELECT `h`.*,
				   IFNULL(`m`.`zayav_id`,0) AS `zayav_id`,
				   IFNULL(`m`.`zp_id`,0) AS `zp_id`,
				   IFNULL(`m`.`expense_id`,0) AS `expense_id`,
				   IFNULL(`m`.`worker_id`,0) AS `worker_id`,
				   IFNULL(`m`.`prim`,'') AS `prim`,
				   IFNULL(`i`.`invoice_from`,0) AS `invoice_from`,
				   IFNULL(`i`.`invoice_to`,0) AS `invoice_to`,
				   IFNULL(`i`.`worker_from`,0) AS `worker_from`,
				   IFNULL(`i`.`worker_to`,0) AS `worker_to`
			FROM `invoice_history` `h`
				LEFT JOIN `money` `m`
				ON `m`.`ws_id`=".WS_ID." AND `h`.`table`='money' AND `h`.`table_id`=`m`.`id`
				LEFT JOIN `invoice_transfer` `i`
				ON `i`.`ws_id`=".WS_ID." AND `h`.`table`='invoice_transfer' AND `h`.`table_id`=`i`.`id`
			WHERE `h`.`ws_id`=".WS_ID."
			  AND `h`.`invoice_id`=".$v['invoice_id']."
			ORDER BY `h`.`id` DESC
			LIMIT ".$start.",".$v['limit'];
	$q = query($sql);
	$history = array();
	while($r = mysql_fetch_assoc($q))
		$history[$r['id']] = $r;

	$history = _zayavNomerLink($history);
	$history = _zpLink($history);

	if($v['page'] == 1)
		$send .= '<table class="_spisok _money invoice-history">'.
			'<tr><th>Действие'.
				'<th>Сумма'.
				'<th>Баланс'.
				'<th>Описание'.
				'<th>Дата';
	foreach($history as $r) {
		$about = '';
		if($r['zayav_id'])
			$about = 'Заявка '.$r['zayav_link'].'. ';
		if($r['zp_id'])
			$about = 'Продажа запчасти '.$r['zp_link'].'. ';
		$about .= $r['prim'].' ';
		$worker = $r['worker_id'] ? '<u>'._viewer($r['worker_id'], 'name').'</u> ' : '';
		$expense = $r['expense_id'] ? '<span class="type">'._expense($r['expense_id']).(!trim($about) && !$worker ? '' : ': ').'</span> ' : '';
		if($r['invoice_from'] != $r['invoice_to']) {//Счета не равны, перевод внешний
			if(!$r['invoice_to'])//Деньги были переданы руководителю
				$about .= 'Передача сотруднику '._viewer($r['worker_to'], 'name');
			elseif(!$r['invoice_from'])//Деньги были получены от руководителя
				$about .= 'Получение от сотрудника '._viewer($r['worker_from'], 'name');
			elseif($r['invoice_id'] == $r['invoice_from'])//Просматриваемый счёт общий - оправитель
				$about .= 'Отправление на счёт <span class="type">'._invoice($r['invoice_to']).'</span>';
			elseif($r['invoice_id'] == $r['invoice_to'])//Просматриваемый счёт общий - получатель
				$about .= 'Поступление со счёта <span class="type">'._invoice($r['invoice_from']).'</span>';;
		} else {//Счета равны, перевод внутренний
			if($r['invoice_id'] == $r['worker_from'])//Просматриваемый счёт сотрудника - оправитель
				$about .= 'Отправление на счёт <span class="type">'._invoice($r['invoice_to']).'</span> '._viewer($r['worker_to'], 'name');
			if($r['invoice_id'] == $r['worker_to'])//Просматриваемый счёт сотрудника - получатель
				$about .= 'Поступление со счёта <span class="type">'._invoice($r['invoice_from']).'</span> '._viewer($r['worker_from'], 'name');
		}
		$send .=
			'<tr><td class="action">'.invoiceHistoryAction($r['action']).
				'<td class="sum">'.($r['sum'] != 0 ? _sumSpace($r['sum']) : '').
				'<td class="balans">'._sumSpace($r['balans']).
				'<td>'.$expense.$worker.$about.
				'<td class="dtime">'.FullDataTime($r['dtime_add']);
	}

	if($start + $v['limit'] < $all) {
		$c = $all - $start - $v['limit'];
		$c = $c > $v['limit'] ? $v['limit'] : $c;
		$send .=
			'<tr class="_next" val="'.($v['page'] + 1).'"><td colspan="5">'.
			'<span>Показать ещё '.$c.' запис'._end($c, 'ь', 'и', 'ей').'</span>';
	}
	if($v['page'] == 1)
		$send .= '</table>';
	return $send;
}//invoice_history()
function invoiceHistoryAction($id, $i='name') {//Варианты действий в истории счетов
	$action = array(
		1 => array(
			'name' => 'Внесение платежа',
			'znak' => ''
		),
		2 => array(
			'name' => 'Удаление платежа',
			'znak' => '-'
		),
		3 => array(
			'name' => 'Восстановление платежа',
			'znak' => ''
		),
		4 => array(
			'name' => 'Перевод между счетами',
			'znak' => ''
		),
		5 => array(
			'name' => 'Установка текущей суммы',
			'znak' => ''
		),
		6 => array(
			'name' => 'Внесение расхода',
			'znak' => '-'
		),
		7 => array(
			'name' => 'Удаление расхода',
			'znak' => ''
		),
		8 => array(
			'name' => 'Восстановление расхода',
			'znak' => '-'
		),
		9 => array(
			'name' => 'Редактирование расхода',
			'znak' => ''
		)
	);
	return $action[$id][$i];
}//invoiceHistoryAction()
function invoice_history_insert($v) {
	$v = array(
		'action' => $v['action'],
		'table' => empty($v['table']) ? '' : $v['table'],
		'id' => empty($v['id']) ? 0 : $v['id'],
		'sum' => empty($v['sum']) ? 0 : $v['sum'],
		'worker_id' => empty($v['worker_id']) ? 0 : $v['worker_id'],
		'invoice_id' => empty($v['invoice_id']) ? 0 : $v['invoice_id']
	);

	if($v['table']) {
		$r = query_assoc("SELECT * FROM `".$v['table']."` WHERE `id`=".$v['id']);
		$v['sum'] = abs($r['sum']);
		switch($v['table']) {
			case 'money':
				$v['invoice_id'] = $r['invoice_id'];
				$v['sum'] = invoiceHistoryAction($v['action'], 'znak').$v['sum'];
				break;
			case 'invoice_transfer':
				if(!$r['invoice_from'] && !$r['invoice_to'])
					return;
				if(!$r['invoice_from']) {//взятие средств у руководителя
					$v['invoice_id'] = $r['invoice_to'];
					if($r['worker_to'])
						invoice_history_insert_sql($r['worker_to'], $v);
					break;
				}
				if(!$r['invoice_to']) {//передача средств руководителю
					$v['invoice_id'] = $r['invoice_from'];
					$v['sum'] *= -1;
					if($r['worker_from'])
						invoice_history_insert_sql($r['worker_from'], $v);
					break;
				}
				//Передача из банка в наличные и на счета сотрудников
				$v['invoice_id'] = $r['invoice_from'];
				invoice_history_insert_sql($r['invoice_to'], $v);
				break;
		}
	}
	invoice_history_insert_sql($v['invoice_id'], $v);
}//invoice_history_insert()
function invoice_history_insert_sql($invoice_id, $v) {
	if(_invoice($invoice_id, 'start') == -1)
		return;
	$sql = "INSERT INTO `invoice_history` (
				`ws_id`,
				`action`,
				`table`,
				`table_id`,
				`invoice_id`,
				`sum`,
				`balans`,
				`viewer_id_add`
			) VALUES (
				".WS_ID.",
				'".$v['action']."',
				'".$v['table']."',
				".$v['id'].",
				".$invoice_id.",
				".$v['sum'].",
				"._invoiceBalans($invoice_id).",
				".VIEWER_ID."
			)";
	query($sql);
}

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

function salary() {
	return
		'<div class="headName">Начисления зарплаты сотрудников</div>'.
		'<div id="spisok">'.salary_spisok().'</div>';
}//salary()
function salary_spisok() {
	$sql = "SELECT
				`u`.`viewer_id`,
				`u`.`rate_sum`,
				`u`.`rate_period`,
				CONCAT(`u`.`first_name`,' ',`u`.`last_name`) AS `name`,
				IFNULL(SUM(`m`.`sum`),0) AS `zp`
			FROM `vk_user` AS `u`
				LEFT JOIN `money` AS `m`
				ON `u`.`viewer_id`=`m`.`worker_id`
					AND `m`.`ws_id`=".WS_ID."
					AND !`m`.`deleted`
					AND `m`.`worker_id`
					AND `m`.`sum`<0
			WHERE `u`.`ws_id`=".WS_ID."
			GROUP BY `u`.`viewer_id`
			ORDER BY `u`.`dtime_add`";
	$q = query($sql);
	$worker = array();
	while($r = mysql_fetch_assoc($q))
		$worker[$r['viewer_id']] = $r;

	//Начисления с заявками
	$sql = "SELECT
 				`e`.`worker_id`,
				IFNULL(SUM(`e`.`sum`),0) AS `sum`
			FROM `zayav_expense` AS `e`,
			 	 `zayav` AS `z`
			WHERE `z`.`ws_id`=".WS_ID."
			  AND `e`.`ws_id`=".WS_ID."
			  AND `e`.`worker_id`
			  AND `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			GROUP BY `e`.`worker_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$worker[$r['worker_id']]['zp'] += $r['sum'];

	//Начисления без заявок
	$sql = "SELECT
 				`u`.`viewer_id`,
				IFNULL(SUM(`e`.`sum`),0) AS `ze`
			FROM `vk_user` AS `u`
				LEFT JOIN `zayav_expense` AS `e`
				ON `u`.`viewer_id`=`e`.`worker_id`
					AND `e`.`ws_id`=".WS_ID."
					AND `e`.`worker_id`
					AND !`e`.`zayav_id`
			WHERE `u`.`ws_id`=".WS_ID."
			GROUP BY `u`.`viewer_id`
			ORDER BY `u`.`dtime_add`";
	$q = query($sql);

	$send = '<table class="_spisok">'.
		'<tr><th>Фио'.
			'<th>Ставка'.
			'<th>Баланс';
	while($r = mysql_fetch_assoc($q)) {
		$w = $worker[$r['viewer_id']];
		$start = _viewer($r['viewer_id'], 'salary_balans_start');
		$balans = $start == -1 ? '' : round($w['zp'] + $r['ze'] + $start, 2);
		$send .=
			'<tr><td class="fio"><a href="'.URL.'&p=report&d=salary&id='.$r['viewer_id'].'" class="name">'.$w['name'].'</a>'.
				'<td class="rate">'.($w['rate_sum'] == 0 ? '' : '<b>'.round($w['rate_sum'], 2).'</b>/'.salaryPeriod($w['rate_period'])).
				'<td class="balans" style="color:#'.($balans < 0 ? 'A00' : '090').'">'.$balans;
	}
	$send .= '</table>';
	return $send;
}//salary_spisok()
function salary_monthList($v) {
	$filter = salaryFilter($v);

	$acc = array();
	$zp = array();
	for($n = 1; $n <= 12; $n++) {
		$acc[$n] = 0;
		$zp[$n] = 0;
	}

	//Получение сумм автоматичиских, ручных начислений и по заявкам
	$sql = "SELECT
	            `mon`,
				SUM(`sum`) AS `sum`
			FROM `zayav_expense`
			WHERE `ws_id`=".WS_ID."
			  AND `worker_id`=".$filter['worker_id']."
			  AND `year`=".$filter['year']."
			GROUP BY `mon`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$acc[intval($r['mon'])] = round($r['sum']);

	//Получение сумм зп
	$sql = "SELECT
	            `mon`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `worker_id`=".$filter['worker_id']."
			  AND `year`=".$filter['year']."
			GROUP BY `mon`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zp[intval($r['mon'])] = abs(round($r['sum'], 2));

	$mon = array();
	foreach(_monthDef(0, 1) as $i => $r)
		$mon[$i] = $r.($acc[$i] || $zp[$i]? '<em>'.$acc[$i].'/'.$zp[$i].'</em>' : '');
	return _radio('salmon', $mon, $filter['mon'], 1);
}//salary_monthList()
function salaryFilter($v) {
	$v = array(
		'worker_id' => _isnum(@$v['worker_id']),
		'mon' => _isnum(@$v['mon']) ? intval($v['mon']) : intval(strftime('%m')),
		'year' => _isnum(@$v['year']) ? intval($v['year']) : intval(strftime('%Y')),
		'acc_id' => _isnum(@$v['acc_id'])
	);
	$v['month'] = _monthDef($v['mon'], 1).' '.$v['year'];
	$v['year-mon'] = $v['year'].'-'.($v['mon'] < 10 ? 0 : '').$v['mon'];
	return $v;
}//salaryFilter()
function salaryPeriod($v=false) {
	$arr = array(
		1 => 'месяц',
		2 => 'неделя',
		3 => 'день'
	);
	if($v == false)
		return $arr;
	return $arr[$v];
}//salaryPeriod()
function salary_worker($v) {
	$filter = salaryFilter($v);
	if(!query_value("SELECT COUNT(*) FROM `vk_user` WHERE `viewer_id`=".$filter['worker_id']))
		return '<h2>Сотрудника не существует.</h2>';
	define('WORKER_OK', true);
	$year = array();
	for($n = 2014; $n <= $filter['year']; $n++)
		$year[$n] = $n;

	return
	'<script type="text/javascript">'.
		'var WORKER_ID='.$filter['worker_id'].','.
			'MON='.$filter['mon'].','.
			'MON_SPISOK='._selJson(_monthDef(0, 1)).','.
			'YEAR='.$filter['year'].','.
			'YEAR_SPISOK='._selJson($year).','.
			'RATE={'.
				'sum:'.round(_viewer($filter['worker_id'], 'rate_sum'), 2).','.
				'period:'._viewer($filter['worker_id'], 'rate_period').','.
				'day:'._viewer($filter['worker_id'], 'rate_day').
			'},'.
			'PROCENT='._viewerRules($filter['worker_id'], 'RULES_MONEY_PROCENT').';'.
	'</script>'.
	'<div class="headName">'._viewer($filter['worker_id'], 'name').': история з/п за <em>'.$filter['month'].'</em>.</div>'.
	'<div id="spisok">'.salary_worker_spisok($filter).'</div>';
}//salary_worker()
function salary_worker_spisok($v) {
	$filter = salaryFilter($v);

	if(!$filter['worker_id'])
		return 'Некорректный id сотрудника';

	$start = _viewer($filter['worker_id'], 'salary_balans_start');
	if($start != -1) {
		$sMoney = query_value("
			SELECT IFNULL(SUM(`sum`),0)
			FROM `money`
			WHERE `worker_id`=".$filter['worker_id']."
			  AND `sum`<0
			  AND !`deleted`");
		$sExpense = query_value("
			SELECT IFNULL(SUM(`sum`),0)
			FROM `zayav_expense`
			WHERE `mon`
			  AND `worker_id`=".$filter['worker_id']);
		$balans = round($sMoney + $sExpense + $start, 2);
		$balans = '<b style="color:#'.($balans < 0 ? 'A00' : '090').'">'.$balans.'</b> руб.';
	} else
		$balans = '<a class="start-set">установить</a>';

	$rate_sum = _cena(_viewer($filter['worker_id'], 'rate_sum'));
	$rate_period = _viewer($filter['worker_id'], 'rate_period');
	$rate_day = _viewer($filter['worker_id'], 'rate_day');
	$send =
	'<div class="uhead">'.
		'<h1>'.
			'Ставка: '.
				($rate_sum
					? '<b>'.$rate_sum.'</b> руб.'.
					  '<span>('.
						($rate_period == 1 ? $rate_day.'-е число месяца' : '').
						($rate_period == 2 ? 'еженедельно, '.$rate_day.'-й день недели' : '').
						($rate_period == 3 ? 'ежедневно' : '').
					  ')</span>'
					: 'нет'
				).
			'<a class="rate-set">Изменить ставку</a>'.
		'</h1>'.
		'Баланс: '.$balans.
		'<div class="a">'.
	  (SA ? '<a class="bonus">Бонус по платежам</a> :: ' : '').
			'<a class="up">Начислить</a> :: '.
			'<a class="zp_add">Выдать з/п</a> :: '.
			'<a class="deduct">Внести вычет</a>'.
		'</div>'.
	'</div>'.
	'<div id="salary-sel">&nbsp;</div>';

	$send .= salary_worker_acc($filter);
	$send .= salary_worker_zp($filter);
	return $send;
}//salary_worker_spisok()
function salary_worker_acc($v) {
	$sql = "(SELECT
				'Начисление' AS `type`,
				`e`.`id`,
			    `e`.`sum`,
				'' AS `about`,
				`e`.`zayav_id`,
				`e`.`dtime_add`
			FROM `zayav_expense` `e`,
				 `zayav` `z`
			WHERE `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			  AND `e`.`year`=".$v['year']."
			  AND `e`.`mon`=".$v['mon']."
			  AND `e`.`worker_id`=".$v['worker_id']."
			  AND `e`.`sum`>0
			GROUP BY `e`.`id`
		) UNION (
			SELECT
				'Начисление' AS `type`,
				`id`,
			    `sum`,
				`txt` AS `about`,
				0 AS `zayav_id`,
				`dtime_add`
			FROM `zayav_expense`
			WHERE !`zayav_id`
			  AND `worker_id`=".$v['worker_id']."
			  AND `sum`>0
			  AND `year`=".$v['year']."
			  AND `mon`=".$v['mon']."
		) UNION (
			SELECT
				'Вычет' AS `type`,
				`id`,
			    `sum`,
				`txt` AS `about`,
				0 AS `zayav_id`,
				`dtime_add`
			FROM `zayav_expense`
			WHERE `worker_id`=".$v['worker_id']."
			  AND `sum`<0
			  AND `year`=".$v['year']."
			  AND `mon`=".$v['mon']."
		)
		ORDER BY `id` DESC";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';
	$spisok = array();
	while($r = mysql_fetch_assoc($q)) {
		$key = strtotime($r['dtime_add']);
		while(isset($spisok[$key]))
			$key++;
		$spisok[$key] = $r;
	}

	$spisok = _zayavLink($spisok);
	krsort($spisok);

	$send = '<table class="_spisok _money">'.
		'<tr>'.
			'<th>Вид'.
			'<th>Сумма'.
			'<th>Описание'.
			'<th>';

	foreach($spisok as $r) {
		$about = $r['zayav_id'] ?
			'<span class="s-zayav" style="background-color:#'.$r['zayav_status_color'].'">'.$r['zayav_link'].'</span>'.
			'<tt>от '.$r['zayav_add'].'</tt>'
			:
			$r['about'];
		$send .=
			'<tr val="'.$r['id'].'" class="'.($v['acc_id'] == $r['id'] ? ' show' : '').'">'.
				'<td class="type">'.$r['type'].
				'<td class="sum">'.round($r['sum'], 2).
				'<td class="about">'.$about.
				'<td class="ed">'.
					($r['type'] == 'Начисление' && !$r['zayav_id'] ? '<div class="img_del ze_del'._tooltip('Удалить', -29).'</div>' : '').
					($r['type'] == 'Вычет' ? '<div class="img_del deduct_del'._tooltip('Удалить', -29).'</div>' : '');
	}
	$send .= '</table>';
	return $send;
}//salary_worker_acc()
function salary_worker_zp($v) {
	$sql = "SELECT *
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `worker_id`=".$v['worker_id']."
			  AND `sum`<0
			  AND `year`=".$v['year']."
			  AND `mon`=".$v['mon']."
			ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';
	$zp = '';
	$summa = 0;
	while($r = mysql_fetch_assoc($q)) {
		$sum = abs(round($r['sum'], 2));
		$summa += $sum;
		$zp .= '<tr>'.
			'<td class="sum">'.$sum.
			'<td class="about"><span class="type">'._invoice($r['invoice_id']).(empty($r['prim']) ? '' : ':').'</span> '.$r['prim'].
			'<td class="dtime">'.FullDataTime($r['dtime_add']).
			'<td class="ed"><div val="'.$r['id'].'" class="img_del zp_del'._tooltip('Удалить', -29).'</div>';
	}
	$send =
		'<div class="zp-head">'.
			'<b>З/п за '.$v['month'].'</b>:'.
			'<span><a class="zp_add">Выдать з/п</a> :: Сумма: <b>'.$summa.'</b> руб.</span>'.
		'</div>'.
		'<table class="_spisok _money">'.
			'<tr><th>Сумма'.
				'<th>Описание'.
				'<th>Дата'.
				'<th>'.
				$zp.
		'</table>';

	return $send;
}//salary_worker_zp()
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

	$money = _zayavNomerLink($money);

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
		$bonus = round(($r['sum'] - $expense) * _viewerRules($worker_id, 'RULES_MONEY_PROCENT') / 100);
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

	$bonus = _zayavNomerLink($bonus);

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

