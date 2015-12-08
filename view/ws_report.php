<?php

// ---===! report !===--- Секция отчётов
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
				DATE_FORMAT(`status_dtime`, '%Y-%m-%d') AS `day`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `status`=2
			GROUP BY DATE_FORMAT(`status_dtime`, '%Y-%m-%d')
			ORDER BY `status_dtime`";
	$q = query($sql);
	$zayav_ok = array();
	while($r = mysql_fetch_assoc($q))
		$zayav_ok[] = array((strtotime($r['day']) + 40000) * 1000, intval($r['count']));

	//Отменённые заявки
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`status_dtime`, '%Y-%m-%d') AS `day`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `status`=3
			GROUP BY DATE_FORMAT(`status_dtime`, '%Y-%m-%d')
			ORDER BY `status_dtime`";
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
				DATE_FORMAT(`status_dtime`, '%Y-%m-15') AS `mon`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `status`=2
			GROUP BY DATE_FORMAT(`status_dtime`, '%Y-%m')
			ORDER BY `status_dtime`";
	$q = query($sql);
	$zayavmon_ok = array();
	while($r = mysql_fetch_assoc($q))
		$zayavmon_ok[] = array(strtotime($r['mon']) * 1000, intval($r['count']));

	//Отменённые заявки - месяц
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`status_dtime`, '%Y-%m-15') AS `mon`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `status`=3
			GROUP BY DATE_FORMAT(`status_dtime`, '%Y-%m')
			ORDER BY `status_dtime`";
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

