<?php
function _remindActiveSet() { //Получение количества активных напоминаний
	$key = CACHE_PREFIX.'remind_active'.WS_ID;
	$count = xcache_get($key);
	if(!strlen($count)) {
		$sql = "SELECT COUNT(`id`) AS `count`
				FROM `reminder`
				WHERE `ws_id`=".WS_ID."
				  AND `day`<=DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d')
				  AND `status`=1
				  AND (`private`=0 OR `private`=1 AND `viewer_id_add`=".VIEWER_ID.")";
		$r = mysql_fetch_assoc(query($sql));
		$count = $r['count'];
		xcache_set($key, $count, 7200);
	}
	define('REMIND_ACTIVE', $count > 0 ? ' (<b>'.$count.'</b>)' : '');
}//_remindActiveSet()
function _mainLinks() {
	global $html;
	_remindActiveSet();
	$links = array(
		array(
			'name' => 'Клиенты',
			'page' => 'client',
			'show' => 1
		),
		array(
			'name' => 'Заявки',
			'page' => 'zayav',
			'show' => 1
		),
		array(
			'name' => 'Запчасти',
			'page' => 'zp',
			'show' => 1
		),
		array(
			'name' => 'Отчёты'.REMIND_ACTIVE,
			'page' => 'report',
			'show' => VIEWER_ADMIN
		),
		array(
			'name' => 'Установки',
			'page' => 'setup',
			'show' => VIEWER_ADMIN
		)
	);

	$send = '<div id="mainLinks">';
	foreach($links as $l)
		if($l['show'])
			$send .= '<a href="'.URL.'&p='.$l['page'].'"'.($l['page'] == $_GET['p'] ? 'class="sel"' : '').'>'.$l['name'].'</a>';
	$send .= pageHelpIcon().'</div>';
	$html .= $send;
}//_mainLinks()

function _rashod($item_id=false) {//Список изделий для заявок
	if(!defined('RASHOD_LOADED') || $item_id === false) {
		$key = CACHE_PREFIX.'rashod';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT `id`,`name` FROM `setup_rashod_category` ORDER BY `name` ASC";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = $r['name'];
			xcache_set($key, $arr, 86400);
		}
		if(!defined('RASHOD_LOADED')) {
			foreach($arr as $id => $name)
				define('RASHOD_'.$id, $name);
			define('RASHOD_0', '');
			define('RASHOD_LOADED', true);
		}
	}
	return $item_id !== false ? constant('RASHOD_'.$item_id) : $arr;
}//_rashod()

// ---===! client !===--- Секция клиентов

function _clientLink($arr, $fio=0) {//Добавление имени и ссылки клиента в массив или возврат по id
	$clientArr = array(is_array($arr) ? 0 : $arr);
	if(is_array($arr)) {
		$ass = array();
		foreach($arr as $r) {
			$clientArr[$r['client_id']] = $r['client_id'];
			if($r['client_id'])
				$ass[$r['client_id']][] = $r['id'];
		}
		unset($clientArr[0]);
	}
	if(!empty($clientArr)) {
		$sql = "SELECT
					`id`,
					`fio`,
					`deleted`
		        FROM `client`
				WHERE `ws_id`=".WS_ID."
				  AND `id` IN (".implode(',', $clientArr).")";
		$q = query($sql);
		if(!is_array($arr)) {
			if($r = mysql_fetch_assoc($q))
				return $fio ? $r['fio'] : '<a'.($r['deleted'] ? ' class="deleted"' : '').' href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>';
			return '';
		}
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id) {
				$arr[$id]['client_link'] = '<a'.($r['deleted'] ? ' class="deleted"' : '').' href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>';
				$arr[$id]['client_fio'] = $r['fio'];
			}
	}
	return $arr;
}//_clientLink()
function clientFilter($v) {
	if(!preg_match(REGEXP_WORDFIND, win1251($v['fast'])))
		$v['fast'] = '';
	if(!preg_match(REGEXP_BOOL, $v['dolg']))
		$v['dolg'] = 0;
	if(!preg_match(REGEXP_BOOL, $v['active']))
		$v['active'] = 0;
	if(!preg_match(REGEXP_BOOL, $v['comm']))
		$v['comm'] = 0;
	$filter = array(
		'fast' => win1251(htmlspecialchars(trim($v['fast']))),
		'dolg' => intval($v['dolg']),
		'active' => intval($v['active']),
		'comm' => intval($v['comm'])
	);
	return $filter;
}//clientFilter()
function client_data($page=1, $filter=array()) {
	$cond = "`ws_id`=".WS_ID." AND `deleted`=0";
	$reg = '';
	$regEngRus = '';
	if(!empty($filter['fast'])) {
		$engRus = _engRusChar($filter['fast']);
		$cond .= " AND (`fio` LIKE '%".$filter['fast']."%'
					 OR `telefon` LIKE '%".$filter['fast']."%'
					 ".($engRus ?
						   "OR `fio` LIKE '%".$engRus."%'
							OR `telefon` LIKE '%".$engRus."%'"
						: '')."
					 )";
		$reg = '/('.$filter['fast'].')/i';
		if($engRus)
			$regEngRus = '/('.$engRus.')/i';
	} else {
		if(isset($filter['dolg']) && $filter['dolg'] == 1)
			$cond .= " AND `balans`<0";
		if(isset($filter['active']) && $filter['active'] == 1) {
			$sql = "SELECT DISTINCT `client_id`
				FROM `zayav`
				WHERE `ws_id`=".WS_ID."
				  AND `zayav_status`=1";
			$q = query($sql);
			$ids = array();
			while($r = mysql_fetch_assoc($q))
				$ids[] = $r['client_id'];
			$cond .= " AND `id` IN (".(empty($ids) ? 0 : implode(',', $ids)).")";
		}
		if(isset($filter['comm']) && $filter['comm'] == 1) {
			$sql = "SELECT DISTINCT `table_id`
				FROM `vk_comment`
				WHERE `status`=1 AND `table_name`='client'";
			$q = query($sql);
			$ids = array();
			while($r = mysql_fetch_assoc($q))
				$ids[] = $r['table_id'];
			$cond .= " AND `id` IN (".(empty($ids) ? 0 : implode(',', $ids)).")";
		}
	}
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `client` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0) {
		$send['spisok'] = '<div class="_empty">Клиентов не найдено.</div>';
		return $send;
	}
	$limit = 20;
	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT *
			FROM `client`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if(!empty($filter['fast'])) {
			if(preg_match($reg, $r['fio']))
				$r['fio'] = preg_replace($reg, '<em>\\1</em>', $r['fio'], 1);
			if(preg_match($reg, $r['telefon']))
				$r['telefon'] = preg_replace($reg, '<em>\\1</em>', $r['telefon'], 1);
			if($regEngRus && preg_match($regEngRus, $r['fio']))
				$r['fio'] = preg_replace($regEngRus, '<em>\\1</em>', $r['fio'], 1);
			if($regEngRus && preg_match($regEngRus, $r['telefon']))
				$r['telefon'] = preg_replace($regEngRus, '<em>\\1</em>', $r['telefon'], 1);
		}
		$spisok[$r['id']] = $r;
	}

	$sql = "SELECT
				`client_id` AS `id`,
				COUNT(`id`) AS `count`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `zayav_status`>0
			  AND `client_id` IN (".implode(',', array_keys($spisok)).")
			GROUP BY `client_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['zayav_count'] = $r['count'];

	$sql = "SELECT
				`table_id` AS `id`
			FROM `vk_comment`
			WHERE `status`=1
			  AND `table_name`='client'
			  AND `table_id` IN (".implode(',', array_keys($spisok)).")
			GROUP BY `table_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['comm'] = 1;

	$send['spisok'] = '';
	foreach($spisok as $r)
		$send['spisok'] .= '<div class="unit'.(isset($r['comm']) ? ' i' : '').'">'.
			($r['balans'] ? '<div class="balans">Баланс: <b style=color:#'.($r['balans'] < 0 ? 'A00' : '090').'>'.$r['balans'].'</b></div>' : '').
			'<table>'.
			   '<tr><td class="label">Имя:<td><a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>'.
				($r['telefon'] ? '<tr><td class="label">Телефон:<td>'.$r['telefon'] : '').
				(isset($r['zayav_count']) ? '<tr><td class="label">Заявки:<td>'.$r['zayav_count'] : '').
			'</table>'.
		 '</div>';
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .= '<div class="ajaxNext" val="'.($page + 1).'"><span>Показать ещё '.$c.' клиент'._end($c, 'а', 'а', 'ов').'</span></div>';
	}
	return $send;
}//client_data()
function client_list($data) {
	return '<div id="client">'.
		'<div id="find"></div>'.
		'<div class="result">'.client_count($data['all']).'</div>'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate"><a>Новый клиент</a></div>'.
					'<div class="filter">'.
					   _check('dolg', 'Должники').
					   _check('active', 'С активными заявками').
					   _check('comm', 'Есть заметки').
					'</div>'.
		  '</table>'.
		'</div>';
}//client_list()
function client_count($count, $dolg=0) {
	if($dolg)
		$dolg = abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE `deleted`=0 AND `balans`<0 LIMIT 1"));
	return ($count > 0 ?
			'Найден'._end($count, ' ', 'о ').$count.' клиент'._end($count, '', 'а', 'ов').
			($dolg ? '<em>(Общая сумма долга = '.$dolg.' руб.)</em>' : '')
			:
			'Клиентов не найдено');
}//client_count()

function client_info($client_id) {
	$sql = "SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND `id`=".$client_id;
	if(!$client = mysql_fetch_assoc(query($sql)))
		return _noauth('Клиента не существует');
	if($client['deleted'])
		if($client['join_id'])
			return _noauth('Клиент <b>'.$client['fio'].'</b> был объединён с клиентом '._clientLink_($client['join_id']).'.');
		else
			return _noauth('Клиент был удалён.');

	$zayavData = zayav_data(1, array('client'=>$client_id), 10);
	$commCount = query_value("SELECT COUNT(`id`)
							  FROM `vk_comment`
							  WHERE `status`=1
								AND `parent_id`=0
								AND `table_name`='client'
								AND `table_id`=".$client_id);

	$moneyCount = query_value("SELECT COUNT(`id`)
							   FROM `money`
							   WHERE `ws_id`=".WS_ID."
								 AND `status`=1
								 AND `client_id`=".$client_id);
	$money = '<div class="_empty">Платежей нет.</div>';
	if($moneyCount) {
		$money = '<table class="_spisok _money">'.
			'<tr><th class="sum">Сумма'.
			'<th>Описание'.
			'<th class="data">Дата';
		$sql = "SELECT *
				FROM `money`
				WHERE `ws_id`=".WS_ID."
				  AND `status`=1
				  AND `client_id`=".$client_id;
		$q = query($sql);
		$moneyArr = array();
		while($r = mysql_fetch_assoc($q))
			$moneyArr[$r['id']] = $r;
		$moneyArr = _zayavNomerLink($moneyArr);
		foreach($moneyArr as $r) {
			$about = '';
			if($r['zayav_id'])
				$about .= 'Заявка '.$r['zayav_link'].'. ';
			if($r['zp_id'])
				$about = 'Продажа запчасти '.$r['zp_id'].'. ';
			$about .= $r['prim'];
			$money .= '<tr><td class="sum"><b>'.$r['sum'].'</b>'.
						  '<td>'.$about.
						  '<td class="dtime" title="Внёс: '._viewer($r['viewer_id_add'], 'name').'">'.FullDataTime($r['dtime_add']);
		}
		$money .= '</table>';
	}

	$remindData = remind_data(1, array('client'=>$client_id));

	$histCount = query_value("SELECT COUNT(`id`)
							   FROM `history`
							   WHERE `ws_id`=".WS_ID."
								 AND `client_id`=".$client_id);

	return '<script type="text/javascript">'.
		'G.clientInfo = {'.
			'id:'.$client_id.','.
			'fio:"'.$client['fio'].'"'.
		'};'.
		'G.device_ids = ['._zayavDeviveBaseIds('device', $client_id).'];'.
		'G.vendor_ids = ['._zayavDeviveBaseIds('vendor', $client_id).'];'.
		'G.model_ids = ['._zayavDeviveBaseIds('model', $client_id).'];'.
		'</script>'.
		'<div id="clientInfo">'.
			'<table class="tabLR">'.
				'<tr><td class="left">'.
					'<div class="fio">'.$client['fio'].'</div>'.
					'<div class="cinf">'.
						'<table style="border-spacing:2px">'.
							'<tr><td class="label">Телефон:  <td class="telefon">'.$client['telefon'].'</TD>'.
							'<tr><td class="label">Баланс:   <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.$client['balans'].'</b>'.
						'</table>'.
						'<div class="dtime">Клиента внёс '._viewer($client['viewer_id_add'], 'name').' '.FullData($client['dtime_add'], 1).'</div>'.
					'</div>'.
					'<div id="dopLinks">'.
						'<a class="link sel" val="zayav">Заявки'.($zayavData['all'] ? ' (<b>'.$zayavData['all'].'</b>)' : '').'</a>'.
						'<a class="link" val="money">Платежи'.($moneyCount ? ' (<b>'.$moneyCount.'</b>)' : '').'</a>'.
						'<a class="link" val="remind">Задания'.(!empty($remindData) ? ' (<b>'.$remindData['all'].'</b>)' : '').'</a>'.
						'<a class="link" val="comm">Заметки'.($commCount ? ' (<b>'.$commCount.'</b>)' : '').'</a>'.
						'<a class="link" val="hist">История'.($histCount ? ' (<b>'.$histCount.'</b>)' : '').'</a>'.
					'</div>'.
					'<div id="zayav_spisok">'.zayav_spisok($zayavData).'</div>'.
					'<div id="money_spisok">'.$money.'</div>'.
					'<div id="remind_spisok">'.(!empty($remindData) ? report_remind_spisok($remindData) : '<div class="_empty">Заданий нет.</div>').'</div>'.
					'<div id="comments">'._vkComment('client', $client_id).'</div>'.
					'<div id="histories">'.report_history_spisok(1, array('client_id'=>$client_id), 15).'</div>'.
				'<td class="right">'.
					'<div class="rightLink">'.
						'<a class="sel">Информация</a>'.
						'<a class="cedit">Редактировать</a>'.
						'<a href="'.URL.'&p=zayav&d=add&back=client&id='.$client_id.'"><b>Новая заявка</b></a>'.
						'<a class="remind_add">Новое задание</a>'.
					'</div>'.
					'<div id="zayav_filter">'.
						'<div id="zayav_result">'.zayav_count($zayavData['all'], 0).'</div>'.
						'<div class="findHead">Статус заявки</div>'.
						_rightLink('status', _zayavStatusName()).
						_check('diff', 'Неоплаченные заявки').
						'<div class="findHead">Устройство</div><div id="dev"></div>'.
					'</div>'.
			'</table>'.
		'</div>';
}//client_info()
function clientBalansUpdate($client_id, $ws_id=WS_ID) {//Обновление баланса клиента
	$prihod = query_value("SELECT IFNULL(SUM(`sum`),0)
						   FROM `money`
						   WHERE `ws_id`=".$ws_id."
							 AND `status`=1
							 AND `client_id`=".$client_id."
							 AND `sum`>0");
	$acc = query_value("SELECT IFNULL(SUM(`sum`),0)
						FROM `accrual`
						WHERE `ws_id`=".$ws_id."
						  AND `status`=1
						  AND `client_id`=".$client_id);
	$balans = $prihod - $acc;
	query("UPDATE `client` SET `balans`=".$balans." WHERE `id`=".$client_id);
	return $balans;
}//clientBalansUpdate()





// ---===! zayav !===--- Секция заявок

function _zayavStatus($id=false) {
	$arr = array(
		'0' => array(
			'name' => 'Любой статус',
			'color' => 'ffffff'
		),
		'1' => array(
			'name' => 'Ожидает выполнения',
			'color' => 'E8E8FF'
		),
		'2' => array(
			'name' => 'Выполнено!',
			'color' => 'CCFFCC'
		),
		'3' => array(
			'name' => 'Завершить не удалось',
			'color' => 'FFDDDD'
		)
	);
	return $id ? $arr[$id] : $arr;
}//_zayavStatus()
function _zayavStatusName($id=false) {
	$status = _zayavStatus();
	if($id)
		return $status[$id]['name'];
	$send = array();
	foreach($status as $id => $r)
		$send[$id] = $r['name'];
	return $send;
}//_zayavStatusName()
function _zayavStatusColor($id=false) {
	$status = _zayavStatus();
	if($id)
		return $status[$id]['color'];
	$send = array();
	foreach($status as $id => $r)
		$send[$id] = $r['color'];
	return $send;
}//_zayavStatusColor()
function _zayavNomerLinkForming($id, $nomer, $noHint=false) {
	return
		'<a href="'.URL.'&p=zayav&d=info&id='.$id.'"'.(!$noHint ? ' class="zayav_link" val="'.$id.'"' : '').'>'.
			'№'.$nomer.
			(!$noHint ? '<div class="tooltip empty"></div>' : '').
		'</a>';
}//_zayavNomerLinkForming()
function _zayavNomerLink($arr, $noHint=false) { //Вывод номеров заявок с возможностью отображения дополнительной информации при наведении
	$zayavArr = array(is_array($arr) ? 0 : $arr);
	if(is_array($arr)) {
		$ass = array();
		foreach($arr as $r) {
			$zayavArr[$r['zayav_id']] = $r['zayav_id'];
			if($r['zayav_id'])
				$ass[$r['zayav_id']][] = $r['id'];
		}
		unset($zayavArr[0]);
	}
	if(!empty($zayavArr)) {
		$sql = "SELECT
	            `id`,
	            `nomer`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `id` IN (".implode(',', $zayavArr).")";
		$q = query($sql);
		if(!is_array($arr)) {
			if($r = mysql_fetch_assoc($q))
				return _zayavNomerLinkForming($r['id'], $r['nomer'], $noHint);
			return '';
		}
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id)
				$arr[$id]['zayav_link'] = _zayavNomerLinkForming($r['id'], $r['nomer'], $noHint);
	}
	return $arr;
}//_zayavNomerLink()
function _zayavDeviveBaseIds($type='device', $client_id=0) { //список id устройств, производителей и моделей, которые используются в заявках
	if($type == 'vendor' && !ZAYAV_BASE_DEVICE || $type == 'model' && !ZAYAV_BASE_VENDOR)
		return '';
	$key = CACHE_PREFIX.'zayav_base_'.$type.WS_ID;
	if(!$client_id)
		$cache = xcache_get($key);
	if(empty($cache)) {
		$ids = array();
		$sql = "SELECT DISTINCT `base_".$type."_id` AS `id`
			FROM `zayav`
			WHERE `base_".$type."_id`>0
			  AND `zayav_status`>0
			  ".($client_id ? " AND `client_id`=".$client_id : '')."
			  AND `ws_id`=".WS_ID;
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$ids[] = $r['id'];
		$cache = implode(',', $ids);
		if(!$client_id)
			xcache_set($key, $cache, 86400);
	}
	define('ZAYAV_BASE_'.strtoupper($type), !empty($cache));
	if(!ZAYAV_BASE_DEVICE)
		define('ZAYAV_BASE_VENDOR', false);
	return $cache;
}//_zayavDeviveBaseIds()

function zayav_add($v=array()) {
	$sql = "SELECT `id`,`name` FROM `setup_fault` ORDER BY SORT";
	$q = query($sql);
	$fault = '<table>';
	$k = 0;
	while($r = mysql_fetch_assoc($q))
		$fault .= (++$k%2 ? '<tr>' : '').'<td>'._check('f_'.$r['id'], $r['name']);
	$fault .= '</table>';

	$client_id = empty($_GET['id']) ? 0 : intval($_GET['id']);

	switch(@$_GET['back']) {
		case 'client': $back = 'client'.($client_id > 0 ? '&d=info&id='.$client_id : ''); break;
		default: $back = 'zayav';
	}
	return '<div id="zayavAdd">'.
		'<div class="headName">Внесение новой заявки</div>'.
		'<table style="border-spacing:8px">'.
			'<tr><td class="label">Клиент:		<td><INPUT TYPE="hidden" id="client_id" value="'.$client_id.'" />'.
			'<tr><td class="label top">Устройство:<td><table><td id="dev"><td id="device_image"></table>'.
			'<tr><td class="label">IMEI:		  <td><INPUT type="text" id="imei" maxlength="20"'.(isset($v['imei']) ? ' value="'.$v['imei'].'"' : '').' />'.
			'<tr><td class="label">Серийный номер:<td><INPUT type="text" id="serial" maxlength="30"'.(isset($v['serial']) ? ' value="'.$v['serial'].'"' : '').' />'.
			'<tr><td class="label">Цвет:'.
				'<td><INPUT TYPE="hidden" id="color_id" />'.
					'<span class="color_dop dn"><tt>-</tt><INPUT TYPE="hidden" id="color_dop" /></span>'.
			'<tr class="tr_equip dn"><td class="label">Комплектация:<td class="equip_spisok">'.
			'<tr><td class="label top">Местонахождение устройства<br />после внесения заявки:<td><INPUT type="hidden" id="place" value="-1" />'.
			'<tr><td class="label top">Неисправности: <td id="fault">'.$fault.
			'<tr><td class="label top">Заметка:	   <td><textarea id="comm"></textarea>'.
			'<tr><td class="label">Добавить напоминание:<td>'._check('reminder').
		'</table>'.

		'<table id="reminder_tab">'.
			'<tr><td class="label">Содержание: <td><INPUT TYPE="text" id="reminder_txt" />'.
			'<tr><td class="label">Дата:	   <td><INPUT TYPE="hidden" id="reminder_day" />'.
		'</table>'.

		'<div class="vkButton"><button>Внести</button></div>'.
		'<div class="vkCancel" val="'.$back.'"><button>Отмена</button></div>'.
	'</div>';
}//zayav_add()

function zayavFilter($v) {
	if(empty($v['status']) || !preg_match(REGEXP_NUMERIC, $v['status']))
		$v['status'] = 0;
	if(empty($v['zpzakaz']) || !preg_match(REGEXP_NUMERIC, $v['zpzakaz']))
		$v['zpzakaz'] = 0;
	if(empty($v['device']) || !preg_match(REGEXP_NUMERIC, $v['device']))
		$v['device'] = 0;
	if($v['device'] == 0 || !preg_match(REGEXP_NUMERIC, $v['vendor']))
		$v['vendor'] = 0;
	if($v['device'] == 0 || !preg_match(REGEXP_NUMERIC, $v['model']))
		$v['model'] = 0;
	if(empty($v['devstatus']) || !preg_match(REGEXP_NUMERIC, $v['devstatus']) && $v['devstatus'] != -1)
		$v['devstatus'] = 0;
	if(empty($v['client']) || !preg_match(REGEXP_NUMERIC, $v['client']))
		$v['client'] = 0;

	$filter = array();
	$filter['find'] = htmlspecialchars(trim(@$v['find']));
	switch(@$v['sort']) {
		case '2': $filter['sort'] = 'zayav_status_dtime'; break;
		default: $filter['sort'] = 'dtime_add';
	}
	$filter['desc'] = intval(@$v['desc']) == 1 ? 'ASC' : 'DESC';
	$filter['status'] = intval($v['status']);
	$filter['diff'] = intval(@$v['diff']) == 1 ? 1 : 0;
	$filter['zpzakaz'] = intval($v['zpzakaz']);
	$filter['device'] = intval($v['device']);
	$filter['vendor'] = intval($v['vendor']);
	$filter['model'] = intval($v['model']);
	if(isset($v['place']))
		$filter['place'] = win1251(urldecode(htmlspecialchars(trim($v['place']))));
	$filter['devstatus'] = $v['devstatus'];
	if($v['client'])
		$filter['client'] = intval($v['client']);
	return $filter;
}//zayavFilter()
function zayav_data($page=1, $filter=array(), $limit=20) {
	$cond = "`ws_id`=".WS_ID." AND `zayav_status`>0";

	if(empty($filter['sort']))
		$filter['sort'] = 'dtime_add';
	if(empty($filter['desc']))
		$filter['desc'] = 'DESC';
	if(!empty($filter['find'])) {
		$cond .= " AND `find` LIKE '%".$filter['find']."%'";
		if($page ==1 && preg_match(REGEXP_NUMERIC, $filter['find']))
			$nomer = intval($filter['find']);
		$reg = '/('.$filter['find'].')/i';
	} else {
		if(isset($filter['status']) && $filter['status'] > 0)
			$cond .= " AND `zayav_status`=".$filter['status'];
		if(isset($filter['diff']) && $filter['diff'] > 0)
			$cond .= " AND `accrual_sum`!=`oplata_sum`";
		if(isset($filter['zpzakaz']) && $filter['zpzakaz'] > 0) {
			$sql = "SELECT `zayav_id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID;
			$q = query($sql);
			$ids[0] = 0;
			while($r = mysql_fetch_assoc($q))
				$ids[$r['zayav_id']] = $r['zayav_id'];
			$cond .= " AND `id` ".($filter['zpzakaz'] == 2 ? 'NOT' : '')." IN (".implode(',', $ids).")";
		}
		if(isset($filter['device']) && $filter['device'] > 0)
			$cond .= " AND `base_device_id`=".$filter['device'];
		if(isset($filter['vendor']) && $filter['vendor'] > 0)
			$cond .= " AND `base_vendor_id`=".$filter['vendor'];
		if(isset($filter['model']) && $filter['model'] > 0)
			$cond .= " AND `base_model_id`=".$filter['model'];
		if(isset($filter['place']) && $filter['place'] != '0') {
			if(preg_match(REGEXP_NUMERIC, $filter['place']))
				$cond .= " AND `device_place`=".$filter['place'];
			elseif($filter['place'] == -1)
				$cond .= " AND `device_place`=0 AND LENGTH(`device_place_other`)=0";
			else
				$cond .= " AND `device_place`=0 AND `device_place_other`='".$filter['place']."'";
		}
		if(isset($filter['devstatus']) && $filter['devstatus'] != 0)
			$cond .= " AND `device_status`=".($filter['devstatus'] > 0 ? $filter['devstatus'] : 0);
		if(isset($filter['client']) && $filter['client'] > 0)
			$cond .= " AND `client_id`=".$filter['client'];
	}

	$send['all'] = query_value("SELECT COUNT(*) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");

	$zayav = array();
	$images = array();
	if(isset($nomer)) {
		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND `zayav_status`>0 AND `nomer`=".$nomer." LIMIT 1";
		if($r = mysql_fetch_assoc(query($sql))) {
			$send['all']++;
			$limit--;
			$r['nomer_find'] = 1;
			$zayav[$r['id']] = $r;
			$images['zayav'.$r['id']] = '"zayav'.$r['id'].'"';
			if($r['base_model_id'] > 0)
				$images['dev'.$r['base_model_id']] = '"dev'.$r['base_model_id'].'"';
		}
	}
	if(!$send['all'])
		return $send;

	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `".$filter['sort']."` ".$filter['desc']."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if(isset($nomer) && $nomer == $r['nomer'])
			continue;
		$zayav[$r['id']] = $r;
		$images['zayav'.$r['id']] = '"zayav'.$r['id'].'"';
		if($r['base_model_id'] > 0)
			$images['dev'.$r['base_model_id']] = '"dev'.$r['base_model_id'].'"';
	}

	$zayavIds = implode(',', array_keys($zayav));

	if(empty($filter['client']))
	$zayav = _clientLink($zayav);

	$sql = "SELECT `owner`,`link` FROM `images` WHERE `status`=1 AND `sort`=0 AND `owner` IN (".implode(',', $images).")";
	$q = query($sql);
	$imgLinks = array();
	while($r = mysql_fetch_assoc($q))
		$imgLinks[$r['owner']] = $r['link'].'-small.jpg';
	unset($images);

	//Запчасти
	$sql = "SELECT `zayav_id`,`zp_id` FROM `zp_zakaz` WHERE `zayav_id` IN (".$zayavIds.")";
	$q = query($sql);
	$zp = array();
	$zpZakaz = array();
	while($r = mysql_fetch_assoc($q)) {
		$zp[$r['zp_id']] = $r['zp_id'];
		$zpZakaz[$r['zayav_id']][] = $r['zp_id'];
	}
	if(!empty($zp)) {
		$sql = "SELECT `id`,`name_id` FROM `zp_catalog` WHERE `id` IN (".implode(',', $zp).")";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$zp[$r['id']] = $r['name_id'];
		foreach($zpZakaz as $id => $zz)
			foreach($zz as $i => $zpId)
				$zpZakaz[$id][$i] = _zpName($zp[$zpId]);
	}

	//Заметки
	$sql = "SELECT
				`table_id`,
				`txt`
			FROM `vk_comment`
			WHERE `table_name`='zayav'
			  AND `table_id` IN (".$zayavIds.")
			  AND `status`=1
			ORDER BY `id` ASC";
	$articles = array();
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$articles[$r['table_id']] = $r['txt'];

	foreach($zayav as $id => $r) {
		$img = '/img/nofoto-small.gif';
		if(isset($imgLinks['zayav'.$id]))
			$img = $imgLinks['zayav'.$id];
		elseif(isset($imgLinks['dev'.$r['base_model_id']]))
			$img = $imgLinks['dev'.$r['base_model_id']];
		$unit = array(
			'status_color' => _zayavStatusColor($r['zayav_status']),
			'nomer' => $r['nomer'],
			'nomer_find' => isset($r['nomer_find']),
			'device' => _deviceName($r['base_device_id']),
			'vendor' => _vendorName($r['base_vendor_id']),
			'model' => _modelName($r['base_model_id']),
			'dtime' => FullData($r['dtime_add'], 1),
			'img' => $img,
			'article' => isset($articles[$id]) ? $articles[$id] : '',
			'acc' => $r['accrual_sum'],
			'opl' => $r['oplata_sum']
		);
		if(empty($filter['client']))
			$unit['client'] = $r['client_link'];
		if(!empty($filter['find'])) {
			if(preg_match($reg, $unit['model']))
				$unit['model'] = preg_replace($reg, "<em>\\1</em>", $unit['model'], 1);
			if(preg_match($reg, $r['imei']))
				$unit['imei'] = preg_replace($reg, "<em>\\1</em>", $r['imei'], 1);
			if(preg_match($reg, $r['serial']))
				$unit['serial'] = preg_replace($reg, "<em>\\1</em>", $r['serial'], 1);
		}
		if(isset($zpZakaz[$id]))
			$unit['zakaz'] = implode(', ', $zpZakaz[$id]);
		$send['spisok'][$id] = $unit;
	}
	$send['limit'] = $limit;
	if($start + $limit < $send['all'])
		$send['next'] = $page + 1;
	return $send;
}//zayav_data()
function zayav_count($count, $filter_break_show=true) {
	return
		($filter_break_show ? '<a id="filter_break">Сбросить условия поиска</a>' : '').
		($count > 0 ?
			'Показан'._end($count, 'а', 'о').' '.$count.' заяв'._end($count, 'ка', 'ки', 'ок')
			:
			'Заявок не найдено');
}//zayav_count()
function zayav_list($data, $values) {
	$place_other = array();
	$sql = "SELECT DISTINCT `device_place_other` AS `other`
			FROM `zayav`
			WHERE LENGTH(`device_place_other`)>0
			  AND `zayav_status`>0
			  AND `ws_id`=".WS_ID;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$place_other[] = '"'.$r['other'].'"';

	return '<div id="zayav">'.
		'<div class="result">'.zayav_count($data['all']).'</div>'.
		'<table class="tabLR">'.
			'<tr><td id="spisok">'.zayav_spisok($data).
				'<td class="right">'.
					'<div id="buttonCreate"><a HREF="'.URL.'&p=zayav&d=add&back=zayav">Новая заявка</a></div>'.
					'<div id="find"></div>'.
					'<div class="findHead">Порядок</div>'.
					_radio('sort', array(1=>'По дате добавления',2=>'По обновлению статуса'), $values['sort']).
					_check('desc', 'Обратный порядок', $values['desc']).
					'<div class="condLost'.(!empty($values['find']) ? ' hide' : '').'">'.
						'<div class="findHead">Статус заявки</div>'.
						_rightLink('status', _zayavStatusName(), $values['status']).
						_check('diff', 'Неоплаченные заявки', $values['diff']).
						'<div class="findHead">Заказаны запчасти</div>'.
						_radio('zpzakaz', array(0=>'Все заявки',1=>'Да',2=>'Нет'), $values['zpzakaz'], 1).
						'<div class="findHead">Устройство</div><div id="dev"></div>'.
						'<div class="findHead">Нахождение устройства</div><INPUT TYPE="hidden" id="device_place" value="'.$values['place'].'">'.
						'<div class="findHead">Состояние устройства</div><INPUT TYPE="hidden" id="devstatus" value="'.$values['devstatus'].'">'.
					'</div>'.
		'</table>'.
		'<script type="text/javascript">'.
			'G.device_ids = ['._zayavDeviveBaseIds().'];'.
			'G.vendor_ids = ['._zayavDeviveBaseIds('vendor').'];'.
			'G.model_ids = ['._zayavDeviveBaseIds('model').'];'.
			'G.place_other = ['.implode(',', $place_other).'];'.
			'G.zayav_find = "'.unescape($values['find']).'";'.
			'G.zayav_device = '.$values['device'].';'.
			'G.zayav_vendor = '.$values['vendor'].';'.
			'G.zayav_model = '.$values['model'].';'.
		'</script>'.
	'</div>';
}//zayav_list()
function zayav_spisok($data) {
	if(!isset($data['spisok']))
		return '<div class="_empty">Заявок не найдено.</div>';
	$send = '';
	foreach($data['spisok'] as $id => $sp) {
		$send .= '<div class="zayav_unit" style="background-color:#'.$sp['status_color'].'" val="'.$id.'">'.
			'<table width="100%">'.
				'<tr><td valign=top>'.
						'<h2'.($sp['nomer_find'] ? ' class="finded"' : '').'>#'.$sp['nomer'].'</h2>'.
						'<a class="name">'.$sp['device'].' <b>'.$sp['vendor'].' '.$sp['model'].'</b></a>'.
						'<table class="utab">'.
							(isset($sp['client']) ? '<tr><td class="label">Клиент:<td>'.$sp['client'] : '').
							'<tr><td class="label">Дата подачи:'.
								'<td>'.$sp['dtime'].
									  ($sp['acc'] || $sp['opl'] ?
										  '<div class="balans'.($sp['acc'] != $sp['opl'] ? ' diff' : '').'">'.
											'<span class="acc" title="Начисление">'.$sp['acc'].'</span>/'.
											'<span class="opl" title="Платёж">'.$sp['opl'].'</span>'.
										  '</div>' : '').
							(isset($sp['imei']) ? '<tr><td class="label">IMEI:<td>'.$sp['imei'] : '').
							(isset($sp['serial']) ? '<tr><td class="label">Серийный номер:<td>'.$sp['serial'] : '').
							(isset($sp['zakaz']) ? '<tr><td class="label">Заказаны з/п:<td class="zz">'.$sp['zakaz'] : '').
						'</table>'.
					'<td class="image"><IMG src="'.$sp['img'].'" />'.
			'</table>'.
			'<input type="hidden" class="msg" value="'.htmlspecialchars($sp['article']).'">'.
		'</div>';
	}
	if(isset($data['next']))
		$send .= '<div class="ajaxNext" val="'.($data['next']).'"><span>Следующие '.$data['limit'].' заявок</span></div>';
	return $send;
}//zayav_spisok()

function zayavBalansUpdate($zayav_id, $ws_id=WS_ID) {//Обновление баланса клиента
	$opl = query_value("SELECT IFNULL(SUM(`sum`),0)
						   FROM `money`
						   WHERE `ws_id`=".$ws_id."
							 AND `status`=1
							 AND `zayav_id`=".$zayav_id."
							 AND `sum`>0");
	$acc = query_value("SELECT IFNULL(SUM(`sum`),0)
						FROM `accrual`
						WHERE `ws_id`=".$ws_id."
						  AND `status`=1
						  AND `zayav_id`=".$zayav_id);
	query("UPDATE `zayav` SET `accrual_sum`=".$acc.",`oplata_sum`=".$opl." WHERE `id`=".$zayav_id);
	return array(
		'acc' => $acc,
		'opl' => $opl,
		'diff' => $acc - $opl
	);
}//zayavBalansUpdate()
function zayavEquipSpisok($ids) {//Список комплектации через запятую
	if(empty($ids))
		return '';
	$arr = explode(',', $ids);
	$equip = array();
	foreach($arr as $id)
		$equip[$id] = 1;
	$send = array();
	foreach(equipCache() as $id => $r)
		if(isset($equip[$id]))
			$send[] = $r['name'];
	return implode(', ', $send);
}//zayavEquipSpisok()
function zayav_info($zayav_id) {
	$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND `zayav_status`>0 AND `id`=".$zayav_id." LIMIT 1";
	if(!$zayav = mysql_fetch_assoc(query($sql)))
		return 'Заявки не существует.';
	$model = _vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']);
	$sql = "SELECT *
		FROM `accrual`
		WHERE `ws_id`=".WS_ID."
		  AND `status`=1
		  AND `zayav_id`=".$zayav['id']."
		ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$money = array();
	$accSum = 0;
	while($acc = mysql_fetch_assoc($q)) {
		$money[strtotime($acc['dtime_add'])] = zayav_accrual_unit($acc);
		$accSum += $acc['sum'];
	}

	$sql = "SELECT *
		FROM `money`
		WHERE `ws_id`=".WS_ID."
		  AND `status`=1
		  AND `sum`>0
		  AND `zayav_id`=".$zayav['id']."
		ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$opSum = 0;
	while($op = mysql_fetch_assoc($q)) {
		$money[strtotime($op['dtime_add'])] = zayav_oplata_unit($op);
		$opSum += $op['sum'];
	}
	$dopl = $accSum - $opSum;
	ksort($money);

	$sql = "SELECT *
			FROM `zp_catalog`
			WHERE `base_device_id`=".$zayav['base_device_id']."
			  AND `base_vendor_id`=".$zayav['base_vendor_id']."
			  AND `base_model_id`=".$zayav['base_model_id'];
	$q = query($sql);
	if(!mysql_num_rows($q))
		$zpSpisok = '<div class="_empty">Для '.$model.' запчастей нет.</div>';
	else {
		$zpSpisok = '';
		$zp = array();
		$ids = array();
		while($r = mysql_fetch_assoc($q)) {
			$id = $r['compat_id'] ? $r['compat_id'] : $r['id'];
			$zp[$id] = $r;
			$ids[$r['id']] = $r['id'];
			$ids[$r['compat_id']] = $r['compat_id'];
		}
		unset($ids[0]);
		_zpImg($ids, 'small', 80, 80, 'fotoView');
		$ids = implode(',', $ids);
		$sql = "SELECT `zp_id` AS `id`,`count` FROM `zp_avai` WHERE `zp_id` IN (".$ids.")";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$zp[$r['id']]['avai'] = $r['count'];
		$sql = "SELECT `zp_id` AS `id`,`count`
				FROM `zp_zakaz`
				WHERE `zp_id` IN (".$ids.")
				  AND `zayav_id`=".$zayav_id;
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$zp[$r['id']]['zakaz'] = $r['count'];
		foreach($zp as $r)
			$zpSpisok .= zayav_zp_unit($r, $model);
	}

	$status = _zayavStatusName();
	unset($status[0]);
	return '<script type="text/javascript">'.
		'var STATUS='._selJson($status).','.
		'ZAYAV={'.
			'id:'.$zayav_id.','.
			'nomer:'.$zayav['nomer'].','.
			'client_id:'.$zayav['client_id'].','.
			'device:'.$zayav['base_device_id'].','.
			'vendor:'.$zayav['base_vendor_id'].','.
			'model:'.$zayav['base_model_id'].','.
			'z_status:'.$zayav['zayav_status'].','.
			'dev_status:'.$zayav['device_status'].','.
			'dev_place:'.$zayav['device_place'].','.
			'place_other:"'.$zayav['device_place_other'].'",'.
			'imei:"'.$zayav['imei'].'",'.
			'serial:"'.$zayav['serial'].'",'.
			'color_id:'.$zayav['color_id'].','.
			'color_dop:'.$zayav['color_dop'].','.
			'equip:\''.devEquipCheck($zayav['base_device_id'], $zayav['equip']).'\''.
		'};'.
	'</script>'.
	'<div id="zayavInfo">'.
		'<div id="dopLinks">'.
			'<a class="delete'.(!empty($money) ?  ' dn': '').'">Удалить заявку</a>'.
			'<a class="link sel">Информация</a>'.
			'<a class="link zedit">Редактирование</a>'.
			'<a class="link acc_add">Начислить</a>'.
			'<a class="link op_add">Принять платёж</a>'.
		'</div>'.
		'<table class="itab">'.
			'<tr><td id="left">'.
				'<div class="headName">'.
					'Заявка №'.$zayav['nomer'].
					'<a href="'.SITE.'/view/kvit.php?'.VALUES.'&id='.$zayav_id.'" class="img_word" title="Распечатать квитанцию в Microsoft Word"></a>'.
				'</div>'.
				'<table class="tabInfo">'.
					'<tr><td class="label">Устройство: <td>'._deviceName($zayav['base_device_id']).'<a><b>'.$model.'</b></a>'.
					'<tr><td class="label">Клиент:	 <td>'._clientLink($zayav['client_id']).
					'<tr><td class="label">Дата приёма:'.
						'<td class="dtime_add" title="Заявку внёс '._viewer($zayav['viewer_id_add'], 'name').'">'.FullDataTime($zayav['dtime_add']).
					'<tr><td class="label">Статус:'.
						'<td><div id="status" style="background-color:#'._zayavStatusColor($zayav['zayav_status']).'" class="status_place">'.
								_zayavStatusName($zayav['zayav_status']).
							'</div>'.
							'<div id="status_dtime">от '.FullDataTime($zayav['zayav_status_dtime'], 1).'</div>'.
					'<tr class="acc_tr'.($accSum > 0 ? '' : ' dn').'"><td class="label">Начислено: <td><b class="acc">'.$accSum.'</b> руб.'.
					'<tr class="op_tr'.($opSum > 0 ? '' : ' dn').'"><td class="label">Оплачено:	<td><b class="op">'.$opSum.'</b> руб.'.
						'<span class="dopl'.($dopl == 0 ? ' dn' : '').'" title="Необходимая доплата'."\n".'Если значение отрицательное, то это переплата">'.($dopl > 0 ? '+' : '').$dopl.'</span>'.
				'</table>'.
				'<div class="headBlue">Задания<a class="add remind_add">Добавить задание</a></div>'.
				'<div id="remind_spisok">'.report_remind_spisok(remind_data(1, array('zayav'=>$zayav['id']))).'</div>'.
				_vkComment('zayav', $zayav['id']).
				'<div class="headBlue mon">Начисления и платежи'.
					'<a class="add op_add">Принять платёж</a>'.
					'<em>::</em>'.
					'<a class="add acc_add">Начислить</a>'.
				'</div>'.
				'<table class="_spisok _money">'.implode($money).'</table>'.

			'<td id="right">'.
				'<div id="foto">'._zayavImg($zayav_id, 'big', 200, 320, 'fotoView').'</div>'.
				'<div class="fotoUpload">Добавить изображение</div>'.
				'<div class="headBlue">Информация об устройстве</div>'.
				'<div class="devContent">'.
					'<div class="devName">'._deviceName($zayav['base_device_id']).'<br />'.'<a>'.$model.'</a></div>'.
					'<table class="devInfo">'.
						($zayav['imei'] ? '<tr><th>imei:		 <td>'.$zayav['imei'] : '').
						($zayav['serial'] ? '<tr><th>serial:	 <td>'.$zayav['serial'] : '').
						($zayav['equip'] ? '<tr><th valign="top">Комплект:<td>'.zayavEquipSpisok($zayav['equip']) : '').
						($zayav['color_id'] ? '<tr><th>Цвет:  <td>'._color($zayav['color_id'], $zayav['color_dop']) : '').
						'<tr><th>Нахождение:<td><a class="dev_place status_place">'.($zayav['device_place'] ? @_devPlace($zayav['device_place']) : $zayav['device_place_other']).'</a>'.
						'<tr><th>Состояние: <td><a class="dev_status status_place">'._devStatus($zayav['device_status']).'</a>'.
					'</table>'.
				'</dev>'.

				'<div class="headBlue">'.
					'<a class="goZp" href="'.URL.'&p=zp&device='.$zayav['base_device_id'].'&vendor='.$zayav['base_vendor_id'].'&model='.$zayav['base_model_id'].'">Список запчастей</a>'.
					'<a class="zpAdd add">добавить</a>'.
				'</div>'.
				'<div id="zpSpisok">'.$zpSpisok.'</div>'.
		'</table>'.
	'</div>';
}//zayav_info()
function zayav_accrual_unit($acc) {
	return '<tr><td class="sum acc" title="Начисление">'.$acc['sum'].'</td>'.
		'<td>'.$acc['prim'].'</td>'.
		'<td class="dtime" title="Начислил '._viewer(isset($acc['viewer_id_add']) ? $acc['viewer_id_add'] : VIEWER_ID, 'name').'">'.
			FullDataTime(isset($acc['dtime_add']) ? $acc['dtime_add'] : curTime()).
		'</td>'.
		'<td class="del"><div class="img_del acc_del" title="Удалить начисление" val="'.$acc['id'].'"></div></td>'.
	'</tr>';
}//zayav_accrual_unit()
function zayav_oplata_unit($op) {
	return '<tr><td class="sum op" title="Платёж">'.$op['sum'].'</td>'.
		'<td>'.$op['prim'].'</td>'.
		'<td class="dtime" title="Платёж внёс '._viewer(isset($op['viewer_id_add']) ? $op['viewer_id_add'] : VIEWER_ID, 'name').'">'.
			FullDataTime(isset($op['dtime_add']) ? $op['dtime_add'] : curTime()).
		'</td>'.
		'<td class="del"><div class="img_del op_del" title="Удалить платёж" val="'.$op['id'].'"></div></td>'.
	'</tr>';
}//zayav_oplata_unit()
function zayav_zp_unit($r, $model) {
	return '<div class="unit" val="'.$r['id'].'">'.
		'<div class="image"><div>'._zpImg($r['id']).'</div></div>'.
		($r['bu'] ? '<span class="bu">Б/у</span>' : '').
		'<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'"><b>'._zpName($r['name_id']).'</b> '.$model.'</a>'.
		($r['version'] ? '<div class="version">'.$r['version'].'</div>' : '').
		($r['color_id'] ? '<div class="color">Цвет: '._color($r['color_id']).'</div>' : '').
		'<div>'.
			(isset($r['zakaz']) ? '<a class="zakaz_ok">Заказано!</a>' : '<a class="zakaz">Заказать</a>').
			(isset($r['avai']) && $r['avai'] > 0 ? '<b class="avai">Наличие: '.$r['avai'].'</b> <a class="set">Установить</a>' : '').
		'</div>'.
	'</div>';
}//zayav_zp_unit()







// ---===! zp !===--- Секция запчастей

function _zpLink($arr) {
	$ids = array();
	$ass = array();
	foreach($arr as $r) {
		$ids[$r['zp_id']] = $r['zp_id'];
		if($r['zp_id'])
			$ass[$r['zp_id']][] = $r['id'];
	}
	unset($ids[0]);
	if(!empty($ids)) {
		$sql = "SELECT *
	        FROM `zp_catalog`
	        WHERE `id` IN (".implode(',', $ids).")";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id)
				$arr[$id]['zp_link'] =
					'<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'">'.
						'<b>'._zpName($r['name_id']).'</b> для '.
						_deviceName($r['base_device_id'], 1).
						_vendorName($r['base_vendor_id']).
						_modelName($r['base_model_id']).
					'</a>';
	}
	return $arr;
}//_zpLink()

function zpAddQuery($zp) {//Внесение новой запчасти из заявки и из списка запчастей
	if(!isset($zp['compat_id']))
		$zp['compat_id'] = 0;
	$sql = "INSERT INTO `zp_catalog` (
				`name_id`,
				`base_device_id`,
				`base_vendor_id`,
				`base_model_id`,
				`bu`,
				`version`,
				`color_id`,
				`compat_id`,
				`viewer_id_add`,
				`find`
			) VALUES (
				".$zp['name_id'].",
				".$zp['device_id'].",
				".$zp['vendor_id'].",
				".$zp['model_id'].",
				".$zp['bu'].",
				'".$zp['version']."',
				".$zp['color_id'].",
				".$zp['compat_id'].",
				".VIEWER_ID.",
				'".win1251(_modelName($zp['model_id']))." ".$zp['version']."'
			)";
	query($sql);
	return mysql_insert_id();
}//zpAddQuery()

function zpFilter($v) {
	if(empty($v['menu']) || !preg_match(REGEXP_NUMERIC, $v['menu']))
		$v['menu'] = 0;
	if(empty($v['name']) || !preg_match(REGEXP_NUMERIC, $v['name']))
		$v['name'] = 0;
	if(empty($v['device']) || !preg_match(REGEXP_NUMERIC, $v['device']))
		$v['device'] = 0;
	if(empty($v['vendor']) || !preg_match(REGEXP_NUMERIC, $v['vendor']))
		$v['vendor'] = 0;
	if(empty($v['model']) || !preg_match(REGEXP_NUMERIC, $v['model']))
		$v['model'] = 0;
	if(empty($v['bu']) || !preg_match(REGEXP_BOOL, $v['bu']))
		$v['bu'] = 0;

	return array(
		'find' => htmlspecialchars(trim(@$v['find'])),
		'menu' => intval($v['menu']),
		'name' => intval($v['name']),
		'device' => intval($v['device']),
		'vendor' => intval($v['vendor']),
		'model' => intval($v['model']),
		'bu' =>	intval($v['bu'])
	);
}//zpFilter()
function zp_data($page=1, $filter=array(), $limit=20) {
	$cond = "`id`";
	if(empty($filter['find']) && (!isset($filter['model']) || $filter['model'] == 0))
		$cond .= " AND (`compat_id`=0 OR `compat_id`=`id`)";
	if(!empty($filter['find'])) {
		$cond .= " AND `find` LIKE '%".$filter['find']."%'";
		$reg = '/('.$filter['find'].')/i';
	}
	if(isset($filter['menu']))
		switch($filter['menu']) {
			case '1':
				$sql = "SELECT `zp_id` AS `id` FROM `zp_avai` WHERE `ws_id`=".WS_ID;
				$q = query($sql);
				$ids = '0';
				while($r = mysql_fetch_assoc($q))
					$ids .= ','.$r['id'];
				$cond .= " AND `id` IN (".$ids.")";
				break;
			case '2':
				$sql = "SELECT `zp_id` AS `id` FROM `zp_avai` WHERE `ws_id`=".WS_ID;
				$q = query($sql);
				$ids = '0';
				while($r = mysql_fetch_assoc($q))
					$ids .= ','.$r['id'];
				$cond .= " AND `id` NOT IN (".$ids.")";
				break;
			case '3':
				$sql = "SELECT `zp_id` AS `id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." GROUP BY `zp_id`";
				$q = query($sql);
				$ids = '0';
				while($r = mysql_fetch_assoc($q))
					$ids .= ','.$r['id'];
				$cond .= " AND `id` IN (".$ids.")";
				break;
		}
	if(isset($filter['name']) && $filter['name'] > 0)
		$cond .= " AND `name_id`=".$filter['name'];
	if(isset($filter['device']) && $filter['device'] > 0)
		$cond .= " AND `base_device_id`=".$filter['device'];
	if(isset($filter['vendor']) && $filter['vendor'] > 0)
		$cond .= " AND `base_vendor_id`=".$filter['vendor'];
	if(isset($filter['model']) && $filter['model'] > 0)
		$cond .= " AND `base_model_id`=".$filter['model'];
	if(isset($filter['bu']) && $filter['bu'] == 1)
		$cond .= " AND `bu`=1";

	$send['filter'] = $filter;
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zp_catalog` WHERE ".$cond." LIMIT 1");
	if(!$send['all']) {
		$send['spisok'] = '<div class="_empty">Запчастей не найдено.</div>';
		return $send;
	}
	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT
	            *,
	            0 AS `avai`,
	            0 AS `zakaz`,
	            '' AS `zz`
			FROM `zp_catalog`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$ids = array();
	$compat = array();
	while($r = mysql_fetch_assoc($q)) {
		$r['model'] = _modelName($r['base_model_id']);
		if(!empty($filter['find'])) {
			if(preg_match($reg, $r['model']))
				$r['model'] = preg_replace($reg, "<em>\\1</em>", $r['model'], 1);
			if(preg_match($reg, $r['version']))
				$r['version'] = preg_replace($reg, "<em>\\1</em>", $r['version'], 1);
		}
		$r['zp_id'] = $r['compat_id'] ? $r['compat_id'] : $r['id'];
		$compat[$r['zp_id']][] = $r['id'];
		$ids[$r['zp_id']] = $r['zp_id'];
		$spisok[$r['id']] = $r;
	}

	_getImg('zp', $ids);

	// Получение количества по наличию
	$sql = "SELECT
				`zp_id`,
				`count`
			FROM `zp_avai`
			WHERE `ws_id`=".WS_ID."
			  AND `zp_id` IN (".implode(',', $ids).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($compat[$r['zp_id']] as $id)
			$spisok[$id]['avai'] = $r['count'];

	// Получение количества по заказу
	$sql = "SELECT
				`zp_id`,
				SUM(`count`) AS `count`
			FROM `zp_zakaz`
			WHERE `ws_id`=".WS_ID."
			  AND `zp_id` IN (".implode(',', $ids).")
			GROUP BY `zp_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($compat[$r['zp_id']] as $id)
			$spisok[$id]['zakaz'] = $r['count'];

	// Составление ссылок на заявки, для которых сделан заказ
	$sql = "SELECT
				`id`,
				`zp_id`,
				`zayav_id`
			FROM `zp_zakaz`
			WHERE `ws_id`=".WS_ID."
			  AND `zp_id` IN (".implode(',', $ids).")
			  AND `zayav_id`>0";
	$q = query($sql);
	$zakaz = array();
	while($r = mysql_fetch_assoc($q))
		$zakaz[$r['id']] = $r;
	$zakaz = _zayavNomerLink($zakaz);
	foreach($zakaz as $r)
		foreach($compat[$r['zp_id']] as $id)
			$spisok[$id]['zz'][] = $r['zayav_link'];

	$send['spisok'] = '';
	foreach($spisok as $id => $r) {
		$zakazEdit = '<span class="zzedit">ано: <tt>—</tt><b>'.$r['zakaz'].'</b><tt>+</tt></span>';
		$send['spisok'] .= '<div class="unit" val="'.$id.'">'.
			'<table>'.
				'<tr><td class="img">'._zpImg($r['zp_id']).
					'<td class="cont">'.
						($r['bu'] ? '<span class="bu">Б/у</span>' : '').
						'<a href="'.URL.'&p=zp&d=info&id='.$id.'" class="name">'.
							_zpName($r['name_id']).
							' <b>'._vendorName($r['base_vendor_id']).$r['model'].'</b>'.
						'</a>'.
						($r['version'] ? '<div class="version">'.$r['version'].'</div>' : '').
						'<div class="for">для '._deviceName($r['base_device_id'], 1).'</div>'.
						($r['color_id'] ? '<div class="color"><span>Цвет:</span> '._color($r['color_id']).'</div>' : '').
						//($r['compat_id'] == $id ? '<b>главная</b>' : '').
						//($r['compat_id'] > 0 && $r['compat_id'] != $id ? '<b>совместимость</b>' : '').
						($r['zz'] ? '<div class="zz">Заказано для заяв'.(count($r['zz']) > 1 ? 'ок' : 'ки').' '.implode(', ', $r['zz']).'</div>' : '').
					'<td class="action">'.
						($r['avai'] ? '<a class="avai avai_add">В наличии: <b>'.$r['avai'].'</b></a>' : '<a class="hid avai_add">Внести наличие</a>').
						'<a class="zpzakaz'.($r['zakaz'] ? '' : ' hid').'">Заказ<span class="cnt">'.($r['zakaz'] ? 'ано: <b>'.$r['zakaz'].'</b>' : 'ать').'</span>'.$zakazEdit.'</a>'.
			'</table>'.
		'</div>';
	}
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .= '<div class="ajaxNext" val="'.($page + 1).'"><span>Показать ещё '.$c.' запчаст'._end($c, 'ь', 'и', 'ей').'</span></div>';
	}
	return $send;
}//zp_data()
function zp_list($data) {
	$filter = $data['filter'];
	$menu = array(
		0 => 'Общий каталог',
		1 => 'Наличие',
		2 => 'Нет в наличии',
		3 => 'Заказ'
	);
	return '<div id="zp">'.
		'<div class="result">'.zp_count($data).'</div>'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$data['spisok'].
				'<td class="right">'.
					'<div id="find"></div>'.
					_rightLink('menu', $menu, $filter['menu']).
					'<div class="findHead">Наименование</div><INPUT type="hidden" id="zp_name" value="'.$filter['name'].'" />'.
					'<div class="findHead">Устройство</div><div id="dev"></div>'.
					_check('bu', 'Б/у', $filter['bu']).
		'</table>'.
		'<script type="text/javascript">'.
			'G.zp_find = "'.$filter['find'].'";'.
			'G.zp_device = '.$filter['device'].';'.
			'G.zp_vendor = '.$filter['vendor'].';'.
			'G.zp_model = '.$filter['model'].';'.
		'</script>'.
	'</div>';
}//zp_list()
function zp_count($data) {
	$all = $data['all'];
	return ($all > 0 ?
		'Показан'._end($all, 'а ', 'о ').$all.' запчаст'._end($all, 'ь', 'и', 'ей').
		(!$data['filter']['menu'] ? '<a class="add">Внести новую запчасть в каталог</a>' : '')
		:
		'Запчастей не найдено');
}//zp_count()

function zp_info($zp_id) {
	$sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$zp_id;
	if(!$zp = mysql_fetch_assoc(query($sql)))
		return 'Запчасти не существует';

	$compat_id = $zp['compat_id'] ? $zp['compat_id'] : $zp_id;
	if($zp_id != $compat_id) {
		$sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$compat_id;
		$compat = mysql_fetch_assoc(query($sql));
		$zp['color_id'] = $compat['color_id'];
		$zp['bu'] = $compat['bu'];
	}

	$avai = query_value("SELECT `count` FROM `zp_avai` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$compat_id);

	$zakazCount = query_value("SELECT IFNULL(SUM(`count`),0) FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$compat_id);
	$zakazEdit = '<span class="zzedit">ано: <tt>—</tt><b>'.$zakazCount.'</b><tt>+</tt></span>';

	_zpImg($compat_id, 'big', 160, 280, 'fotoView');

	$compatSpisok = zp_compat_spisok($zp_id, $compat_id);
	$compatCount = count($compatSpisok);

	return
	'<script type="text/javascript">'.
		'G.zpInfo = {'.
			'id:'.$zp_id.','.
			'compat_id:'.$compat_id.','.
			'name_id:'.$zp['name_id'].','.
			'device:'.$zp['base_device_id'].','.
			'vendor:'.$zp['base_vendor_id'].','.
			'model:'.$zp['base_model_id'].','.
			'version:"'.$zp['version'].'",'.
			'color_id:'.$zp['color_id'].','.
			($zp['color_id'] ? 'color_name:"'._color($zp['color_id']).'",' : '').
			'bu:'.$zp['bu'].','.
			'name:"'._zpName($zp['name_id']).' <b>'._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).'</b>",'.
			'for:"для '._deviceName($zp['base_device_id'], 1).'",'.
			'count:'.($avai ? $avai : 0).','.
			'img:"'.addslashes(_zpImg($compat_id)).'"'.
		'};'.
	'</script>'.
	'<div id="zpInfo">'.
		'<table class="ztab">'.
			'<tr><td class="left">'.
					'<div class="name">'.
						($zp['bu'] ? '<span>Б/у</span>' : '').
						_zpName($zp['name_id']).
						'<em>'.$zp['version'].'</em>'.
					'</div>'.
					'<div class="for">'.
						'для '._deviceName($zp['base_device_id'], 1).
						' <a>'._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).'</a>'.
					'</div>'.
					'<table class="prop">'.
						($zp['color_id'] ? '<tr><td class="label">Цвет:<td>'._color($zp['color_id']) : '').
						//'<tr><td class="label">id:<td>'.$zp['id'].
						//'<tr><td class="label">compat_id:<td>'.$zp['compat_id'].
					'</table>'.
					'<div class="avai'.($avai ? '' : ' no').'">'.($avai ? 'В наличии '.$avai.' шт.' : 'Нет в наличии.').'</div>'.
					'<div class="added">Добавлено в каталог '.FullData($zp['dtime_add'], 1).'</div>'.
					'<div class="headBlue">Движение</div>'.
					'<div class="move">'.zp_move($compat_id).'</div>'.
				'<td class="right">'.
					'<div id="foto">'._zpImg($compat_id).'</div>'.
					'<div class="rightLink">'.
						'<a class="fotoUpload">Добавить изображение</a>'.
						'<a class="edit">Редактировать</a>'.
						'<a class="avai_add">Внести наличие</a>'.
						'<a class="zpzakaz unit'.($zakazCount ? '' : ' hid').'" val="'.$zp_id.'">'.
							'Заказ<span class="cnt">'.($zakazCount ? 'ано: <b>'.$zakazCount.'</b>' : 'ать').'</span>'.
							$zakazEdit.
						'</a>'.
						'<a class="set"> - установка</a>'.
						'<a class="sale"> - продажа</a>'.
						'<a class="defect"> - брак</a>'.
						'<a class="return"> - возврат</a>'.
						'<a class="writeoff"> - списание</a>'.
					'</div>'.
					'<div class="headBlue">Совместимость<a class="add compat_add">добавить</a></div>'.
					'<div class="compatCount">'.zp_compat_count($compatCount).'</div>'.
					'<div class="compatSpisok">'.($compatCount ? implode($compatSpisok) : '').'</div>'.
		'</table>'.
	'</div>';
}//zp_info()
function zp_move($zp_id, $page=1) {
	$all = query_value("SELECT COUNT(`id`) FROM `zp_move` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id);
	if(!$all)
		return '<div class="unit">Движения запчасти нет.</div>';

	$limit = 10;
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `zp_move`
			WHERE `ws_id`=".WS_ID."
			  AND `zp_id`=".$zp_id."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']] = $r;
	$spisok = _viewer($spisok);
	$spisok = _zayavNomerLink($spisok);
	$spisok = _clientLink($spisok);
	$move = '';
	$type = array(
		'' => 'Приход',
		'set' => 'Установка',
		'sale' => 'Продажа',
		'defect' => 'Брак',
		'return' => 'Возврат',
		'writeoff' => 'Списание'
	);
	$n = 0;
	foreach($spisok as $r) {
		$cena = round($r['cena'], 2);
		$summa = round($r['summa'], 2);
		$count = abs($r['count']);
		$move .= '<div class="unit">'.
			'<div>'.
				(!$n++ && $page == 1 ? '<div class="img_del" val="'.$r['id'].'"></div>' : '').
				$type[$r['type']].' <b>'.$count.'</b> шт. '.
				($summa ? 'на сумму '.$summa.' руб.'.($count > 1 ? ' <span class="cenaed">('.$cena.' руб./шт.)</span> ' : '') : '').
				($r['zayav_id'] ? 'по заявке '.$r['zayav_link'].'.' : '').
				($r['client_id'] ? 'клиенту '.$r['client_link'].'.' : '').
			'</div>'.
			($r['prim'] ? '<div class="prim">'.$r['prim'].'</div>' : '').
			'<div class="dtime" title="Внёс '.$r['viewer_name'].'">'.FullDataTime($r['dtime_add']).'</div>'.
		'</div>';
	}
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$move .= '<div class="ajaxNext" val="'.($page + 1).'"><span>Показать ещё '.$c.' запис'._end($c, 'ь', 'и', 'ей').'</span></div>';
	}
	return $move;
}//zp_move()
function zp_compat_spisok($zp_id, $compat_id=false) {
	if(!$compat_id)
		$compat_id = _zpCompatId($zp_id);
	$sql = "SELECT * FROM `zp_catalog` WHERE `id`!=".$zp_id." AND `compat_id`=".$compat_id;
	$q = query($sql);
	$send = array();
	while($r = mysql_fetch_assoc($q)) {
		$key = explode(' ', _modelName($r['base_model_id']));
		$send[$key[0]] = '<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'">'.
			'<div class="img_del" val="'.$r['id'].'" title="Разорвать совместимость"></div>'.
			_vendorName($r['base_vendor_id'])._modelName($r['base_model_id']).
		'</a>';
	}
	ksort($send);
	return $send;
}//zp_compat_spisok()
function zp_compat_count($c) {
	return $c ? $c.' устройств'._end($c, 'о', 'а', '') : 'Совместимостей нет';
}






// ---===! report !===--- Секция отчётов

function reportMenu($g) {
	switch($g) {
		case 'history': break;
		case 'remind': break;
		case 'money': break;
		default: $g = 'history';
	}
	return '<div class="rightLink">'.
		'<a href="'.URL.'&p=report&d=history"'.($g == 'history' ? ' class="sel"' : '').'>История действий</a>'.
		'<a href="'.URL.'&p=report&d=remind"'.($g == 'remind' ? ' class="sel"' : '').'>'.
			'Задания'.REMIND_ACTIVE.'<div class="img_add report_remind_add"></div>'.
		'</a>'.
		'<a href="'.URL.'&p=report&d=money"'.($g == 'money' ? ' class="sel"' : '').'>Деньги</a>'.
	'</div>';
}//reportMenu()

function history_insert($arr) {
	$sql = "INSERT INTO `history` (
			   `ws_id`,
			   `type`,
			   `value`,
			   `value1`,
			   `client_id`,
			   `zayav_id`,
			   `zp_id`,
			   `viewer_id_add`
			) VALUES (
				".WS_ID.",
				".$arr['type'].",
				'".(isset($arr['value']) ? addslashes($arr['value']) : '')."',
				'".(isset($arr['value1']) ? addslashes($arr['value1']) : '')."',
				".(isset($arr['client_id']) ? $arr['client_id'] : 0).",
				".(isset($arr['zayav_id']) ? $arr['zayav_id'] : 0).",
				".(isset($arr['zp_id']) ? $arr['zp_id'] : 0).",
				".VIEWER_ID."
			)";
	query($sql);
}//history_insert()
function history_types($v) {
	switch($v['type']) {
		case 1: return 'Создана новая заявка '.$v['zayav_link'].' для клиента '.$v['client_link'].'.';
		case 2: return 'Удалена заявка №'.$v['value'].'.';
		case 3: return 'Внесён новый клиент '.$v['client_link'].'.';
		case 4:
			$status = _zayavStatus($v['value']);
			return 'Изменён статус заявки '.$v['zayav_link'].' на <span style="background-color:#'.$status['color'].'">'.$status['name'].'</span>.';
		case 5: return 'Произведено начисление на сумму <b>'.$v['value'].'</b> руб. для заявки '.$v['zayav_link'].'.';
		case 6: return
			'Внесён платёж на сумму <b>'.$v['value'].'</b> руб.'.
			($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').
			($v['zayav_id'] ? ' по заявке '.$v['zayav_link'] : '');
		case 7: return 'Отредактированы данные заявки '.$v['zayav_link'].($v['value'] ? ':<div class="changes">'.$v['value'].'</div>' : '.');
		case 8:
			return 'Удалено начисление на сумму <b>'.$v['value'].'</b> руб. '.
				($v['value1'] ? '('.$v['value1'].')' : '').
				' у заявки '.$v['zayav_link'].'.';
		case 9:
			return 'Удалён платёж на сумму <b>'.$v['value'].'</b> руб. '.
				($v['value1'] ? '('.$v['value1'].')' : '').
				($v['zayav_id'] ? ' у заявки '.$v['zayav_link'] : '').
				($v['zp_id'] ? ' (Продажа запчасти '.$v['zp_link'].')' : '').
				'.';
		case 10: return 'Отдерактированы данные клиента '.$v['client_link'].($v['value'] ? ':<div class="changes">'.$v['value'].'</div>' : '.');
		case 11: return 'Произведено объединение клиентов <i>'.$v['value'].'</i> и '.$v['client_link'].'.';
		case 12: return 'Установлено значение в кассе: '.$v['value'].' руб.';
		case 13: return 'Произведена установка запчасти '.$v['zp_link'].' по заявке '.$v['zayav_link'].'.';
		case 14: return 'Продана запчасть '.$v['zp_link'].' на сумму <b>'.$v['value'].'</b> руб.';
		case 15: return 'Произведено списание запчасти '.$v['zp_link'].'';
		case 16: return 'Произведён возврат запчасти '.$v['zp_link'].'';
		case 17: return 'Забракована запчась '.$v['zp_link'].'';
		case 18: return 'Внесено наличие запчасти '.$v['zp_link'].' в количестве '.$v['value'].' шт.';
		case 19:
			return 'Восстановлен платёж на сумму <b>'.$v['value'].'</b> руб. '.
				($v['value1'] ? '('.$v['value1'].')' : '').
				($v['zayav_id'] ? ' у заявки '.$v['zayav_link'] : '').
				($v['zp_id'] ? ' (Продажа запчасти '.$v['zp_link'].')' : '').
				'.';
		case 20:
			return 'Создано новое задание'.
				($v['zayav_id'] ? ' для заявки '.$v['zayav_link'] : '').
				($v['client_id'] ? ' для клиента '.$v['client_link'] : '').
				'.';
		case 21: return 'Внесён расход на сумму <b>'.$v['value'].'</b> руб.';
		case 22: return 'Удалён расход на сумму <b>'.$v['value'].'</b> руб.';
		case 23: return 'Изменены данные расхода на сумму <b>'.$v['value'].'</b> руб.';
		case 24: return 'Установлено начальное значение в кассе = <b>'.$v['value'].'</b> руб.';
		case 25: return 'Удалена запись в кассе на сумму <b>'.$v['value'].'</b> руб. ('.$v['value1'].')';
		case 26: return 'Восстановлена запись в кассе на сумму <b>'.$v['value'].'</b> руб. ('.$v['value1'].')';
		case 27:
			return 'Восстановлено начисление на сумму <b>'.$v['value'].'</b> руб. '.
				($v['value1'] ? '('.$v['value1'].')' : '').
				' у заявки '.$v['zayav_link'].'.';

		default: return $v['type'];
	}
}//history_types()
function history_types_group($action) {
	switch($action) {
		case 1: return '3,10,11';
		case 2: return '1,2,4,5,6,7,8,9,13';
		case 3: return '13,14,15,16,17,18';
		case 4: return '6,9,12,19';
	}
	return 0;
}//history_types_group()
function report_history_right() {
	$sql = "SELECT DISTINCT `viewer_id_add`
			FROM `history`
			WHERE `ws_id`=".WS_ID;
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
	return '<script type="text/javascript">var workers = ['.implode(',', $workers).'];</script>'.
		'<div class="report_history_rl">'.
			'<div class="findHead">Сотрудник</div>'.
			'<input type="hidden" id="report_history_worker" value="0">'.
			'<div class="findHead">Действие</div>'.
			'<input type="hidden" id="report_history_action" value="0">'.
		'</div>';
}//report_history_right()
function report_history() {
	return '<div id="report_history">'.report_history_spisok().'</div>';
}//report_history()
function report_history_spisok($page=1, $filter=array(), $limit=30) {
	$cond = "`ws_id`=".WS_ID.
		(isset($filter['worker']) ? ' AND `viewer_id_add`='.$filter['worker'] : '').
		(isset($filter['client_id']) ? ' AND `client_id`='.$filter['client_id'] : '').
		(isset($filter['action']) ? ' AND `type` IN ('.history_types_group($filter['action']).')' : '');
	$sql = "SELECT
				COUNT(`id`) AS `all`
			FROM `history`
			WHERE ".$cond;
	$r = mysql_fetch_assoc(query($sql));
	if($r['all'] == 0)
		return 'Истории по указанным условиям нет.';
	$all = $r['all'];
	$start = ($page - 1) * $limit;

	$sql = "SELECT
	            *,
	            '<i>удалённый клиент</i>' AS `client_link`,
	            '<i>удалённая заявка</i>' AS `zayav_link`,
	            '<i>удалённая запчасть</i>' AS `zp_link`
			FROM `history`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$history = array();
	while($r = mysql_fetch_assoc($q))
		$history[$r['id']] = $r;
	$history = _viewer($history);
	$history = _clientLink($history);
	$history = _zayavNomerLink($history);
	$history = _zpLink($history);

	$send = '';
	$txt = '';
	end($history);
	$keyEnd = key($history);
	reset($history);
	foreach($history as $r) {
		if(!$txt) {
			$time = strtotime($r['dtime_add']);
			$viewer_id = $r['viewer_id_add'];
		}
		$txt .= '<div class="txt">'.history_types($r).'</div>';
		$key = key($history);
		if(!$key ||
		   $key == $keyEnd ||
		   $time - strtotime($history[$key]['dtime_add']) > 900 ||
		   $viewer_id != $history[$key]['viewer_id_add']) {
			$send .=
				'<div class="history_unit">'.
					'<div class="head">'.FullDataTime($r['dtime_add']).$r['viewer_link'].'</div>'.
					$txt.
				'</div>';
			$txt = '';
		}
		next($history);
	}
	if($start + $limit < $all)
		$send .= '<div class="ajaxNext" val="'.($page + 1).'"><span>Далее...</span></div>';
	return $send;
}//report_history_spisok()

function report_remind() {
	$data = remind_data();
	$send = '<div id="report_remind">'.
		'<div id="remind_spisok">'.(!empty($data) ? report_remind_spisok($data) : '<div class="_empty">Заданий нет.</div>').'</div>'.
	'</div>';
	return $send;
}//report_remind()
function report_remind_right() {
	return '<div class=findHead>Категории заданий</div>'.
		_radio('remind_status', array(1=>'Активные',2=>'Выполнены',0=>'Отменены'), 1, 1).
		_check('remind_private', 'Личное');
}//report_remind_right()
function remind_data($page=1, $filter=array()) {
	$cond = "`ws_id`=".WS_ID." AND `status`=".(isset($filter['status']) ? intval($filter['status']) : 1);
	if(!empty($filter['private']))
		$cond .= " AND `private`=1";
	if(!empty($filter['zayav']))
		$cond .= " AND `zayav_id`=".intval($filter['zayav']);
	if(!empty($filter['client'])) {
		$client_id = intval($filter['client']);
		$cond .= " AND `client_id`=".$client_id;
		$sql = "SELECT `id` FROM `zayav` WHERE `ws_id`=".WS_ID." AND `zayav_status`>0 AND `client_id`=".$client_id;
		$q = query($sql);
		$zayav_ids = array();
		while($r = mysql_fetch_assoc($q))
			$zayav_ids[] = $r['id'];
		if(!empty($zayav_ids))
			$cond .= " OR `ws_id`=".WS_ID." AND `status`=1 AND `zayav_id` IN (".implode(',', $zayav_ids).")";
	}
	$send['all'] = query_value("SELECT COUNT(`id`) FROM `reminder` WHERE ".$cond);
	if(!$send['all'])
		return array();

	$limit = 20;
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `reminder`
			WHERE ".$cond."
			ORDER BY `day` ASC,`id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$send['spisok'][$r['id']] = $r;
	$send['spisok'] = _clientLink($send['spisok']);
	$send['spisok'] = _zayavNomerLink($send['spisok']);
	if($start + $limit < $send['all'])
		$send['page'] = ++$page;
	$send['filter'] = $filter;
	return $send;
}//remind_data()
function report_remind_spisok($data) {
	if(empty($data['spisok']))
		return '';
	$send = '';
	$today = strtotime(strftime("%Y-%m-%d", time()));
	foreach($data['spisok'] as $r) {
		$day_leave = (strtotime($r['day']) - $today) / 3600 / 24;
		$leave = '';
		if($day_leave < 0)
			$leave = 'просрочен'._end($day_leave * -1, ' ', 'о ').round($day_leave * -1)._end($day_leave * -1, ' день', ' дня', ' дней');
		elseif($day_leave > 2)
			$leave = 'остал'._end($day_leave, 'ся ', 'ось ').$day_leave._end($day_leave, ' день', ' дня', ' дней');
		else
			switch($day_leave) {
				case 0: $leave = 'сегодня'; break;
				case 1: $leave = 'завтра'; break;
				case 2: $leave = 'послезавтра'; break;
			}

		if($r['status'] == 0) $color = 'grey';
		elseif($r['status'] == 2) $color = 'green';
		elseif($day_leave > 0) $color = 'blue';
		elseif($day_leave < 0) $color = 'redd';
		else $color = 'yellow';
		// состояние задачи
		switch($r['status']) {
			case 2: $rem_cond = "<EM>Выполнено.</EM>"; break;
			case 0: $rem_cond = "<EM>Отменено.</EM>"; break;
			default:
				$rem_cond = '<EM>Выполнить '.($day_leave == 0 ? '' : 'до ').'</EM>'.
					($day_leave >= 0 && $day_leave < 3 ? $leave : FullData($r['day'], 1)).
					($day_leave > 2 || $day_leave < 0 ? '<SPAN>, '.$leave.'</SPAN>' : '');
		}
		$send .= '<div class="remind_unit '.$color.'">'.
			'<div class="txt">'.
				($r['private'] ? '<u>Личное.</u> ' : '').
				($r['client_id'] && empty($data['filter']['client']) ? 'Клиент '.$r['client_link'].': ' : '').
				($r['zayav_id'] && empty($data['filter']['zayav']) ? 'Заявка '.$r['zayav_link'].': ' : '').
				'<b>'.$r['txt'].'</b>'.
			'</div>'.
			'<div class="day">'.
				'<div class="action">'.
					($r['status'] == 1 ? '<a class="edit" val="'.$r['id'].'">Действие</a> :: ' : '').
					'<a class="hist_a">История</a>'.
				'</div>'.
				$rem_cond.
				'<div class="hist">'.$r['history'].'</div>'.
			'</div>'.
		'</div>';
	}
	if(isset($data['page']))
		$send .= '<div class="ajaxNext" val="'.$data['page'].'"><span>Показать ещё задания...</span></div>';
	return $send;
}//report_remind_spisok()

function report_prihod_right() { //Условия поиска справа для отчётов
	return '<div class="report_prihod_rl">'.
		'<div class="findHead">Период</div>'.
		'<div class="cal"><EM class="label">от:</EM><INPUT type="hidden" id="report_prihod_day_begin" value="'._curMonday().'"></div>'.
		'<div class="cal"><EM class="label">до:</EM><INPUT type="hidden" id="report_prihod_day_end" value="'._curSunday().'"></div>'.
		(VIEWER_ADMIN ? _check('prihodShowDel', 'Показывать удалённые платежи') : '').
		'</div>';
}//report_prihod_right()
function report_prihod() {
	return
	'<div id="report_prihod">'.
		'<div class="headName">Список поступлений<a class="add">Внести платёж</a></div>'.
		'<div class="spisok">'.report_prihod_spisok(_curMonday(), _curSunday(), 0).'</div>'.
	'</div>';
}//report_prihod()
function report_prihod_spisok($day_begin, $day_end, $del_show=0, $page=1) {
	$limit = 30;
	$cond = "`ws_id`=".WS_ID."
		AND `sum`>0
		AND `dtime_add`>='".$day_begin." 00:00:00'
		AND `dtime_add`<='".$day_end." 23:59:59'
		".($del_show && VIEWER_ADMIN ? '' : ' AND `status`=1');
	$sql = "SELECT
				COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE ".$cond;
	$r = mysql_fetch_assoc(query($sql));
	if($r['all'] == 0)
		return 'Поступления за указанный период отсутствуют.';
	$all = $r['all'];
	$start = ($page - 1) * $limit;

	$send = '';
	if($page == 1)
		$send =
			'<div class="summa">Показан'._end($all, '', 'о').' <b>'.$all.'</b> платеж'._end($all, '', 'а', 'ей').' на сумму <b>'.$r['sum'].'</b> руб.</div>'.
			'<table class="_spisok">'.
				'<tr><th class="sum">Сумма'.
					'<th>Описание'.
					'<th class="data">Дата'.
					'<th class="del">';

	$sql = "SELECT *
			FROM `money`
			WHERE ".$cond."
			ORDER BY `dtime_add` ASC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$money = array();
	while($r = mysql_fetch_assoc($q))
		$money[$r['id']] = $r;
	$money = _viewer($money);
	$money = _zayavNomerLink($money);
	$money = _zpLink($money);
	foreach($money as $r) {
		$about = $r['prim'];
		if($r['zayav_id'])
			$about = 'Заявка '.$r['zayav_link'];
		if($r['zp_id'] > 0)
			$about = 'Продажа запчасти '.$r['zp_link'];
		$dtimeTitle = 'Внёс: '.$r['viewer_name'];
		if($r['status'] == 0)
			$dtimeTitle .= "\n".'Удалил: '.$r['viewer_del'].
				"\n".FullDataTime($r['dtime_del']);
		$send .= '<tr'.($r['status'] == 0 ? ' class="deleted"' : '').'>'.
			'<td class="sum"><b>'.$r['sum'].'</b>'.
			'<td>'.$about.
			'<td class="dtime" title="'.$dtimeTitle.'">'.FullDataTime($r['dtime_add']).
			'<td class="edit">'.($r['status'] == 1 ?
				'<div class="img_del" val="'.$r['id'].'" title="Удалить платёж"></div>' :
				'<div class="img_rest" val="'.$r['id'].'" title="Восстановить платёж"></div>');
	}
	if($start + $limit < $all)
		$send .= '<tr class="ajaxNext" id="report_prihod_next" val="'.($page + 1).'"><td colspan="4"><span>Показать ещё платежи...</span></td></tr>';
	if($page == 1) $send .= '</table>';
	return $send;
}//report_prihod_spisok()

function report_rashod_right() {
	$sql = "SELECT DISTINCT `worker_id` AS `viewer_id_add`
			FROM `money`
			WHERE `ws_id`=".WS_ID." AND `sum`<0 AND `worker_id`>0";
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
		'<input type="hidden" id="rashod_category">'.
		'<div class="findHead">Сотрудник</div>'.
		'<input type="hidden" id="rashod_worker">'.
		'<input type="hidden" id="rashod_year">'.
		'<div id="monthList">'.report_rashod_monthSum().'</div>';
}//report_rashod_right()
function report_rashod_monthSum($year=0, $month=0, $category=0, $worker=0) {
	if(!$year) $year = strftime('%Y', time());
	if(!$month) $month = intval(strftime('%m', time()));
	$sql = "SELECT
				DISTINCT(DATE_FORMAT(`dtime_add`,'%m')) AS `month`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND `status`=1
			  AND `sum`<0
			  AND `dtime_add` LIKE '".$year."-%'
			  ".($worker ? " AND `worker_id`=".$worker : '')."
			  ".($category ? " AND `rashod_category`=".$category : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%m')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$res = array();
	while($r = mysql_fetch_assoc($q))
		$res[intval($r['month'])] = abs($r['sum']);
	$mon = array();
	for($n = 1; $n <= 12; $n++)
		$mon[$n] = _monthDef($n).(isset($res[$n]) ? '<span class="sum">'.$res[$n].'</span>' : '');
	return _radio('monthSum', $mon, $month, 1);
}//report_rashod_monthSum()
function report_rashod() {
	return '<script type="text/javascript">'.
				'var RASHOD_VIEWER='.query_selJson("SELECT `viewer_id`,CONCAT(`first_name`,' ',`last_name`) FROM `vk_user` WHERE `ws_id`=".WS_ID).';'.
			'</script>'.
		'<div id="report_rashod">'.
			'<div class="headName">Список расходов мастерской<a class="add">Внести новый расход</a></div>'.
			'<div id="spisok">'.report_rashod_spisok().'</div>'.
		'</div>';
}//report_rashod()
function report_rashod_spisok($page=1, $month=false, $category=0, $worker=0) {
	if(!$month) $month = strftime('%Y-%m', time());
	$limit = 30;
	$cond = "`ws_id`=".WS_ID."
		AND `status`=1
		AND `sum`<0
		AND `dtime_add` LIKE '".$month."-%'
		".($worker ? " AND `worker_id`=".$worker : '')."
		".($category ? ' AND `rashod_category`='.$category : '');
	$sql = "SELECT
				COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE ".$cond;
	$r = mysql_fetch_assoc(query($sql));
	if($r['all'] == 0)
		return 'Данные отсутствуют.';
	$all = $r['all'];
	$start = ($page - 1) * $limit;

	$send = '';
	if($page == 1) {
		$ex = explode('-', $month);
		$send = '<div class="summa">'.
				'Показан'._end($all, 'а', 'о').' <b>'.$all.'</b> запис'._end($all, 'ь', 'и', 'ей').
				' на сумму <b>'.abs($r['sum']).'</b> руб.'.
				' за '._monthDef($ex[1]).' '.$ex[0].' г.'.
			'</div>'.
			'<table class="_spisok">'.
				'<tr><th class="sum">Сумма'.
					'<th>Описание'.
					'<th class="data">Дата'.
					'<th class="edit">';
	}
	$sql = "SELECT *
			FROM `money`
			WHERE ".$cond."
			ORDER BY `dtime_add` ASC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$rashod = array();
	while($r = mysql_fetch_assoc($q))
		$rashod[$r['id']] = $r;
	$rashod = _viewer($rashod);
	foreach($rashod as $r) {
		$dtimeTitle = 'Внёс: '.$r['viewer_name'];
		if($r['status'] == 0)
			$dtimeTitle .= "\n".'Удалил: '.$r['viewer_del'].
				"\n".FullDataTime($r['dtime_del']);
		$send .= '<tr'.($r['status'] == 0 ? ' class="deleted"' : '').'>'.
			'<td class="sum"><b>'.abs($r['sum']).'</b>'.
			'<td>'.($r['rashod_category'] ? '<em>'._rashod($r['rashod_category']).($r['prim'] || $r['worker_id'] ? ':' : '').'</em>' : '').
				   ($r['worker_id'] ? _viewer($r['worker_id'], 'link').
				   ($r['prim'] ? ', ' : '') : '').$r['prim'].
			'<td class="dtime" title="'.$dtimeTitle.'">'.FullDataTime($r['dtime_add']).
			'<td class="edit">'.($r['status'] == 1 ?
				'<div class="img_edit" val="'.$r['id'].'" title="Редактировать"></div>'.
				'<div class="img_del" val="'.$r['id'].'" title="Удалить"></div>'
				:
				'<div class="img_rest" val="'.$r['id'].'" title="Восстановить"></div>');
	}
	if($start + $limit < $all)
		$send .= '<tr class="ajaxNext" id="report_rashod_next" val="'.($page + 1).'"><td colspan="4"><span>Показать далее...</span></td></tr>';
	if($page == 1) $send .= '</table>';
	return $send;
}//report_rashod_spisok()

function kassa_sum() {
	$sql = "SELECT SUM(`sum`) AS `sum` FROM `kassa` WHERE `ws_id`=".WS_ID." AND `status`=1 LIMIT 1";
	$r = mysql_fetch_assoc(query($sql));
	$kassa_sum = $r['sum'];
	$sql = "SELECT SUM(`sum`) AS `sum` FROM `money` WHERE `ws_id`=".WS_ID." AND `status`=1 AND `kassa`=1 LIMIT 1";
	$r = mysql_fetch_assoc(query($sql));
	return KASSA_START + $kassa_sum + $r['sum'];
}//kassa_sum()
function report_kassa() {
	if(KASSA_START == -1)
		$send = '<div class="set_info">Установите значение, равное текущей сумме денег, находящейся сейчас в мастерской. '.
				'От этого значения будет вестись дальнейший учёт средств, поступающих, либо забирающихся из кассы.<BR>'.
				'<b>Внимание!</b> Данное действие можно произвести только один раз.'.
			'</div>'.
			'<table class="set_tab"><tr>'.
				'<td>Сумма: <INPUT type=text id="set_summa" maxlength=8> руб.</td>'.
				'<td><div class="vkButton" id="set_go"><button>Установить</button></div></td>'.
			'</tr></table>';
	else
		$send = '<div class="in">В кассе: <b id="kassa_summa">'.kassa_sum().'</b> руб. '.
					'<div class="actions"><a>Внести в кассу</a> :: <a>Взять из кассы</a></div>'.
				'</div>'.
				'<div id="spisok">'.report_kassa_spisok().'</div>';
	return '<div id="report_kassa">'.$send.'</div>';
}//report_kassa()
function report_kassa_right() {
	return KASSA_START == -1 ? '' : _check('kassaShowDel', 'Показывать удалённые записи');
}//report_kassa_right()
function report_kassa_spisok($page=1, $del_show=0) {
	$limit = 30;
	$cond = "`ws_id`=".WS_ID."
		 ".($del_show ? '' : ' AND `status`=1');
	$sql = "SELECT COUNT(`id`) AS `all`
			FROM `kassa`
			WHERE ".$cond;
	$r = mysql_fetch_assoc(query($sql));
	if($r['all'] == 0)
		return 'Действий с кассой нет.';
	$all = $r['all'];
	$start = ($page - 1) * $limit;

	$send = '';
	if($page == 1)
		$send = '<div class="all">'.'Показан'._end($all, '', 'о').' <b>'.$all.'</b> запис'._end($all, 'ь', 'и', 'ей').'.</div>'.
			'<table class="_spisok">'.
				'<tr><th class="sum">Сумма'.
					'<th>Описание'.
					'<th class="data">Дата'.
					'<th>';

		$sql = "SELECT *
				FROM `kassa`
				WHERE ".$cond."
				ORDER BY `dtime_add` ASC
				LIMIT ".$start.",".$limit;
		$q = query($sql);
		$money = array();
		while($r = mysql_fetch_assoc($q))
			$money[$r['id']] = $r;
	$money = _viewer($money);
	foreach($money as $r) {
		$send .= '<tr'.($r['status'] == 0 ? ' class="deleted"' : '').'>'.
			'<td class="sum"><b>'.$r['sum'].'</b>'.
			'<td>'.$r['txt'].
			'<td class="dtime" title="Внёс: '.$r['viewer_name'].'">'.FullDataTime($r['dtime_add']).
			'<td class="edit">'.($r['status'] == 1 ?
				'<div class="img_del" val="'.$r['id'].'" title="Удалить"></div>' :
				'<div class="img_rest" val="'.$r['id'].'" title="Восстановить"></div>');
	}
	if($start + $limit < $all)
		$send .= '<tr class="ajaxNext" id="report_kassa_next" val="'.($page + 1).'"><td colspan="4"><span>Показать ещё платежи...</span></td></tr>';
	if($page == 1) $send .= '</table>';
	return $send;
}//report_kassa_spisok()

function statistic() {
	$sql = "SELECT
				SUM(`sum`) AS `sum`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `dtime`
			FROM `money`
			WHERE `status`=1
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
			WHERE `status`=1
			  AND `sum`<0
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$rashod = array();
	while($r = mysql_fetch_assoc($q))
		$rashod[] = array(strtotime($r['dtime']) * 1000, intval($r['sum']));

	return
	'<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/highstock.js"></script>'.
	'<div id="statistic"></div>'.
	'<script type="text/javascript">'.
		'var statPrihod = '.json_encode($prihod).';'.
		'var statRashod = '.json_encode($rashod).';'.
	'</script>'.
	'<script type="text/javascript" src="'.SITE.'/js/statistic.js"></script>';
}//statistic()



// ---===! setup !===--- Секция установок

function setup_main() {
	$sql = "SELECT * FROM `workshop` WHERE `id`=".WS_ID." LIMIT 1";
	if(!$ws = mysql_fetch_assoc(query($sql))) {
		_cacheClear();
		header('Location:'.URL);
	}

	$ex = explode(',', $ws['devs']);
	$devs = array();
	foreach($ex as $d)
		$devs[$d] = $d;

	$sql = "SELECT `id`,`name_mn` FROM `base_device` ORDER BY `sort`";
	$q = query($sql);
	$checkDevs = '';
	while($r = mysql_fetch_assoc($q))
		$checkDevs .= _check($r['id'], $r['name_mn'], isset($devs[$r['id']]) ? 1 : 0);

	return
	'<script type="text/javascript">'.
		'G.org_name = "'.$ws['org_name'].'";'.
	'</script>'.
	'<DIV id="setup_main">'.
		'<DIV class="headName" id="headName">Информация о мастерской</DIV>'.
		'<TABLE class="tab">'.
			'<TR><TD class="label">Название организации:<TD><INPUT type="text" id="org_name" maxlength="100" value="'.$ws['org_name'].'">'.
			'<TR><TD class="label">Город:<TD>'.$ws['city_name'].', '.$ws['country_name'].
			'<TR><TD class="label">Главный администратор:<TD><B>'._viewer($ws['admin_id'], 'name').'</B>'.
		'</TABLE>'.

		'<DIV class="headName">Категории ремонтируемых устройств</DIV>'.
		'<DIV id="devs">'.$checkDevs.'</DIV>'.

		'<DIV class="headName">Удаление мастерской</DIV>'.
		'<div class="del_inf">Мастерская, а также все данные удаляются без возможности восстановления.</div>'.
		'<DIV class="vkButton" id="ws_del"><BUTTON>Удалить мастерскую</BUTTON></DIV>'.
	'</DIV>';
}//setup_main()

function setup_workers() {
	return
	'<DIV id="setup_workers">'.
		'<DIV class="headName">Сотрудники мастерской<a class="add">Добавить нового сотрудника</a></DIV>'.
		'<DIV id="spisok">'.setup_workers_spisok().'</DIV>'.
	'</DIV>';
}//setup_workers()
function setup_workers_spisok() {
	$sql = "SELECT * FROM `vk_user` WHERE `ws_id`=".WS_ID." ORDER BY `dtime_add`";
	$q = query($sql);
	$send = '';
	while($r = mysql_fetch_assoc($q)) {
		$send .=
		'<table class="unit" val="'.$r['viewer_id'].'">'.
			'<tr><td class="photo"><img src="'.$r['photo'].'">'.
				'<td>'.
					(WS_ADMIN != $r['viewer_id'] ? '<div class="img_del"></div>' : '').
					'<a class="name">'.$r['first_name'].' '.$r['last_name'].'</a>'.
					'<div class="adm">'.
						($r['admin'] ?
							'Администратор'.(WS_ADMIN != $r['viewer_id'] ? ' <a class="adm_cancel">отменить</a>' : '')
							: '<a class="adm_set">Назначить администратором</a>').
					'</div>'.
		'</table>';
	}
	return $send;
}//setup_workers()