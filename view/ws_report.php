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

