<?php
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
			'show' => 1
		),
		array(
			'name' => 'Настройки',
			'page' => 'setup',
			'show' => 1
		)
	);

	$send = '<div id="mainLinks">';
	foreach($links as $l)
		if($l['show']) {
			$sel = $l['page'] == $_GET['p'] ? ' class="sel"' : '';
			$cart = $l['page'] == 'zayav' && @$_GET['from'] == 'cartridge' ? '&d=cartridge' : '';//возврат на страницу с картриджами из заявки
			$send .=
				'<a href="'.URL.'&p='.$l['page'].$cart.'"'.$sel.'>'.
					$l['name'].
				'</a>';
		}
	$send .= pageHelpIcon().'</div>';
	$html .= $send;
}//_mainLinks()

function _expense($type_id=false, $i='name') {//Список изделий для заявок
	if(!defined('EXPENSE_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'expense'.WS_ID;
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT * FROM `setup_expense` ORDER BY `sort`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = array(
					'name' => $r['name'],
					'worker' => $r['show_worker']
				);
			xcache_set($key, $arr, 86400);
		}
		if(!defined('EXPENSE_LOADED')) {
			foreach($arr as $id => $r) {
				define('EXPENSE_'.$id, $r['name']);
				define('EXPENSE_WORKER_'.$id, $r['worker']);
			}
			define('EXPENSE_0', '');
			define('EXPENSE_WORKER_0', 0);
			define('EXPENSE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'worker')
		return constant('EXPENSE_WORKER_'.$type_id);
	return constant('EXPENSE_'.$type_id);
}//_expense()
function _invoice($type_id=false, $i='name') {//Список изделий для заявок
	if(!defined('INVOICE_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'invoice'.WS_ID;
		$arr = xcache_get($key);
		if(empty($arr)) {
			$arr = array();
			$sql = "SELECT *
					FROM `invoice`
					WHERE `ws_id`=".WS_ID."
					ORDER BY `id`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q)) {
				$r['start'] = round($r['start'], 2);
				$arr[$r['id']] = $r;
			}
			xcache_set($key, $arr, 86400);
		}
		if(!defined('INVOICE_LOADED')) {
			foreach($arr as $id => $r) {
				define('INVOICE_'.$id, $r['name']);
				define('INVOICE_START_'.$id, $r['start']);
			}
			define('INVOICE_0', '');
			define('INVOICE_START_0', 0);
			define('INVOICE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'start')
		return constant('INVOICE_START_'.$type_id);
	return constant('INVOICE_'.$type_id);
}//_invoice()

function viewerAdded($viewer_id) {//Вывод сотрудника, который вносил запись с учётом пола
	return 'Вн'.(_viewer($viewer_id, 'sex') == 1 ? 'есла' : 'ёс').' '._viewer($viewer_id, 'name');
}
function _viewerRules($viewer_id=VIEWER_ID, $rule='') {
	$key = CACHE_PREFIX.'viewer_rules_'.$viewer_id;
	$wr = xcache_get($key);
	if(empty($wr)) {
		$rules = query_ass("SELECT `key`,`value` FROM `vk_user_rules` WHERE `viewer_id`=".$viewer_id);
		$admin = _viewer($viewer_id, 'admin');
		$wr = _setupRules($rules, $admin);
		xcache_set($key, $wr, 86400);
	}
	return $rule ? $wr[$rule] : $wr;
}//_viewerRules()


// ---===! client !===--- Секция клиентов

function _clientLink($arr, $fio=0, $tel=0) {//Добавление имени и ссылки клиента в массив или возврат по id
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
		$sql = "SELECT *
		        FROM `client`
				WHERE `ws_id`=".WS_ID."
				  AND `id` IN (".implode(',', $clientArr).")";
		$q = query($sql);
		if(!is_array($arr)) {
			if($r = mysql_fetch_assoc($q))
				return
					$fio ? $r['fio']
					:
					'<a val="'.$r['id'].'" class="go-client-info'.($r['deleted'] ? ' deleted' : '').($tel && $r['telefon'] ? _tooltip($r['telefon'], -2, 'l') : '">').$r['fio'].'</a>';
			return '';
		}
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id) {
				$arr[$id]['client_link'] = '<a val="'.$r['id'].'" class="go-client-info'.($r['deleted'] ? ' deleted' : '').($tel && $r['telefon'] ? _tooltip($r['telefon'], -2, 'l') : '">').$r['fio'].'</a>';
				$arr[$id]['client_fio'] = $r['fio'];
			}
	}
	return $arr;
}//_clientLink()
function _clientValues($arr) {//данные о заявке, подставляемые в список
	$ids = array();
	$arrIds = array();
	foreach($arr as $key => $r)
		if(!empty($r['client_id'])) {
			$ids[$r['client_id']] = 1;
			$arrIds[$r['client_id']][] = $key;
		}
	if(empty($ids))
		return $arr;

	$sql = "SELECT *
		        FROM `client`
				WHERE `ws_id`=".WS_ID."
				  AND `id` IN (".implode(',', array_keys($ids)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($arrIds[$r['id']] as $id) {
			$arr[$id] += array(
				'client_link' => '<a val="'.$r['id'].'" class="go-client-info'.($r['deleted'] ? ' deleted' : '').'">'.$r['fio'].'</a>',
				'client_telefon' => $r['telefon'],
				'client_fio' => $r['fio']
			);
		}
	return $arr;
}//_clientValues()
function clientFilter($v) {
	$default = array(
		'page' => 1,
		'find' => '',
		'dolg' => 0,
		'active' => 0,
		'comm' => 0,
		'opl' => 0
	);
	$filter = array(
		'page' => _isnum(@$v['page']) ? $v['page'] : 1,
		'find' => trim(@$v['find']),
		'dolg' => _isbool(@$v['dolg']),
		'active' => _isbool(@$v['active']),
		'comm' => _isbool(@$v['comm']),
		'opl' => _isbool(@$v['opl']),
		'clear' => ''
	);
	foreach($default as $k => $r)
		if($r != $filter[$k]) {
			$filter['clear'] = '<a id="filter_clear">Очистить фильтр</a>';
			break;
		}
	return $filter;
}//clientFilter()
function client_data($v=array()) {
	$filter = clientFilter($v);
	$cond = "`ws_id`=".WS_ID." AND !`deleted`";
	$reg = '';
	$regEngRus = '';
	$dolg = 0;
	$plus = 0;
	if($filter['find']) {
		$engRus = _engRusChar($filter['find']);
		$cond .= " AND (`fio` LIKE '%".$filter['find']."%'
					 OR `telefon` LIKE '%".$filter['find']."%'
					 ".($engRus ?
						   "OR `fio` LIKE '%".$engRus."%'
							OR `telefon` LIKE '%".$engRus."%'"
						: '')."
					 )";
		$reg = '/('.$filter['find'].')/i';
		if($engRus)
			$regEngRus = '/('.$engRus.')/i';
	} else {
		if($filter['dolg']) {
			$cond .= " AND `balans`<0";
			$dolg = abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `balans`<0"));
		}
		if($filter['active']) {
			$ids = query_ids("SELECT DISTINCT `client_id`
							FROM `zayav`
							WHERE `ws_id`=".WS_ID."
							  AND `zayav_status`=1");
			$cond .= " AND `id` IN (".$ids.")";
		}
		if($filter['comm']) {
			$ids = query_ids("SELECT DISTINCT `table_id`
							FROM `vk_comment`
							WHERE `status` AND `table_name`='client'");
			$cond .= " AND `id` IN (".$ids.")";
		}
		if($filter['opl']) {
			$cond .= " AND `balans`>0";
			$plus = abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE !`deleted` AND `balans`>0"));
		}
	}

	$all = query_value("SELECT COUNT(`id`) AS `all` FROM `client` WHERE ".$cond." LIMIT 1");
	if(!$all)
		return array(
			'all' => 0,
			'result' => 'Клиентов не найдено.'.$filter['clear'],
			'spisok' => '<div class="_empty">Клиентов не найдено.</div>',
			'filter' => $filter
		);

	$send['all'] = $all;
	$send['result'] = 'Найден'._end($all, ' ', 'о ').$all.' клиент'._end($all, '', 'а', 'ов').
					  ($dolg ? '<em>(Общая сумма долга = '.$dolg.' руб.)</em>' : '').
					  ($plus ? '<em>(Сумма = '.$plus.' руб.)</em>' : '').
					  $filter['clear'];
	$send['filter'] = $filter;

	$page = $filter['page'];
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
		if($filter['find']) {
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
			  AND `zayav_status`
			  AND `client_id` IN (".implode(',', array_keys($spisok)).")
			GROUP BY `client_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['zayav_count'] = $r['count'];

	$sql = "SELECT
				`table_id` AS `id`
			FROM `vk_comment`
			WHERE `status`
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
		$send['spisok'] .= '<div class="_next" val="'.($page + 1).'"><span>Показать ещё '.$c.' клиент'._end($c, 'а', 'а', 'ов').'</span></div>';
	}
	return $send;
}//client_data()
function client_list($v) {
	$data = client_data($v);
	$v = $data['filter'];
	return '<div id="client">'.
		'<div id="find"></div>'.
		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate"><a>Новый клиент</a></div>'.
					'<div class="filter'.($v['find'] ? ' dn' : '').'">'.
					   _check('dolg', 'Должники', $v['dolg']).
					   _check('active', 'С активными заявками', $v['active']).
					   _check('comm', 'Есть заметки', $v['comm']).
					   _check('opl', 'Внесена предоплата', $v['opl']).
					'</div>'.
		  '</table>'.
		'</div>'.
		'<script type="text/javascript">'.
			'var C={'.
				'find:"'.$v['find'].'"'.
			'};'.
		'</script>';
}//client_list()

function client_info($client_id) {
	$sql = "SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND `id`=".$client_id;
	if(!$client = mysql_fetch_assoc(query($sql)))
		return _noauth('Клиента не существует');
	if($client['deleted'])
		if($client['join_id'])
			return _noauth('Клиент <b>'.$client['fio'].'</b> был объединён с клиентом '._clientLink($client['join_id']).'.');
		else
			return _noauth('Клиент был удалён.');

	$zayavData = zayav_spisok(array(
		'client_id' => $client_id,
		'limit' => 10
	));
	$commCount = query_value("SELECT COUNT(`id`)
							  FROM `vk_comment`
							  WHERE `status`=1
								AND `parent_id`=0
								AND `table_name`='client'
								AND `table_id`=".$client_id);

	$moneyCount = query_value("SELECT COUNT(`id`)
							   FROM `money`
							   WHERE `ws_id`=".WS_ID."
								 AND `deleted`=0
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
				  AND `deleted`=0
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
			$money .= '<tr><td class="sum"><b>'.round($r['sum'], 2).'</b>'.
						  '<td>'.$about.
						  '<td class="dtime" title="Внёс: '._viewer($r['viewer_id_add'], 'name').'">'.FullDataTime($r['dtime_add']);
		}
		$money .= '</table>';
	}

	$remind = _remind_spisok(array('client_id'=>$client_id));

	$history = history(array('client_id'=>$client_id,'limit'=>15));

	return
		'<script type="text/javascript">'.
			'var CLIENT={'.
					'id:'.$client_id.','.
					'fio:"'.addslashes($client['fio']).'"'.
				'},'.
				'DEVICE_IDS=['._zayavBaseDeviceIds($client_id).'],'.
				'VENDOR_IDS=['._zayavBaseVendorIds($client_id).'],'.
				'MODEL_IDS=['._zayavBaseModelIds($client_id).'];'.
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
						'<a class="link sel" val="zayav">Заявки'.($zayavData['all'] ? ' <b class="count">'.$zayavData['all'].'</b>' : '').'</a>'.
						'<a class="link" val="money">Платежи'.($moneyCount ? ' <b class="count">'.$moneyCount.'</b>' : '').'</a>'.
						'<a class="link" val="remind">Напоминания'.($remind['all'] ? ' <b class="count">'.$remind['all'].'</b>' : '').'</a>'.
						'<a class="link" val="comm">Заметки'.($commCount ? ' <b class="count">'.$commCount.'</b>' : '').'</a>'.
						'<a class="link" val="hist">История'.($history['all'] ? ' <b class="count">'.$history['all'].'</b>' : '').'</a>'.
					'</div>'.
					'<div id="zayav_spisok">'.$zayavData['spisok'].'</div>'.
					'<div id="money_spisok">'.$money.'</div>'.
					'<div id="remind-spisok">'.$remind['spisok'].'</div>'.
					'<div id="comments">'._vkComment('client', $client_id).'</div>'.
					'<div id="histories">'.$history['spisok'].'</div>'.
				'<td class="right">'.
					'<div class="rightLink">'.
						'<a id="zayav-add" val="client_'.$client_id.'"'.(SERVIVE_CARTRIDGE ? ' class="cartridge"' : '').'><b>Новая заявка</b></a>'.
						'<a class="_remind-add">Новое напоминание</a>'.
						'<a class="cedit">Редактировать</a>'.
					'</div>'.
					'<div id="zayav_filter">'.
						'<div id="zayav_result">'.$zayavData['result'].'</div>'.
						'<div class="findHead">Статус заявки</div>'.
						_rightLink('status', _zayavStatusName()).
						_check('diff', 'Неоплаченные заявки').
						'<div class="findHead">Устройство</div><div id="dev"></div>'.
					'</div>'.
			'</table>'.
		'</div>';
}//client_info()
function clientBalansUpdate($client_id, $ws_id=WS_ID) {//Обновление баланса клиента
	if(!$client_id)
		return 0;
	$prihod = query_value("SELECT IFNULL(SUM(`sum`),0)
						   FROM `money`
						   WHERE `ws_id`=".$ws_id."
							 AND !`deleted`
							 AND `client_id`=".$client_id."
							 AND !`zp_id`
							 AND `sum`>0");
	$acc = query_value("SELECT IFNULL(SUM(`sum`),0)
						FROM `accrual`
						WHERE `ws_id`=".$ws_id."
						  AND !`deleted`
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
function _zayavNomerLinkForming($v) {
	$class = (!$v['nohint'] ? 'zayav_link' : '').
			 ($v['deleted'] ? ' deleted' : '');
	return
		'<a href="'.URL.'&p=zayav&d=info&id='.$v['id'].'"'.
			($class ? ' class="'.$class.'"' : '').
			(!$v['nohint'] ? ' val="'.$v['id'].'"' : '').
		'>'.
			'№'.$v['nomer'].
			(!$v['nohint'] ? '<div class="tooltip empty"></div>' : '').
		'</a>';
}//_zayavNomerLinkForming()
function _zayavNomerLink($arr, $noHint=0) { //Вывод номеров заявок с возможностью отображения дополнительной информации при наведении
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
	            `nomer`,
	            `deleted`,
	            ".$noHint." `nohint`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `id` IN (".implode(',', $zayavArr).")";
		$q = query($sql);
		if(!is_array($arr)) {
			if($r = mysql_fetch_assoc($q))
				return _zayavNomerLinkForming($r);
			return '';
		}
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id)
				$arr[$id]['zayav_link'] = _zayavNomerLinkForming($r);
	}
	return $arr;
}//_zayavNomerLink()
function _zayavLink($arr, $noHint=0) {
	$ids = array();
	$arrIds = array();
	foreach($arr as $key => $r)
		if(!empty($r['zayav_id'])) {
			$ids[$r['zayav_id']] = 1;
			$arrIds[$r['zayav_id']][] = $key;
		}
	if(empty($ids))
		return $arr;
	$sql = "SELECT
	            *,
	            ".$noHint." `nohint`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `id` IN (".implode(',', array_keys($ids)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($arrIds[$r['id']] as $id) {
			$arr[$id] += array(
				'zayav_link' => _zayavNomerLinkForming($r),
				'zayav_status_color' => _zayavStatusColor($r['zayav_status']),
				'zayav_add' => FullData($r['dtime_add'], 1)
			);
		}
	return $arr;
}//_zayavLink()
function _zayavValues($arr, $noHint=0) {//данные о заявке, подставляемые в список
	$ids = array();
	$arrIds = array();
	foreach($arr as $key => $r)
		if(!empty($r['zayav_id'])) {
			$ids[$r['zayav_id']] = 1;
			$arrIds[$r['zayav_id']][] = $key;
		}
	if(empty($ids))
		return $arr;
	$sql = "SELECT
	            *,
	            ".$noHint." AS `nohint`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `id` IN (".implode(',', array_keys($ids)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($arrIds[$r['id']] as $id) {
			$arr[$id] += array(
				'zayav_link' => _zayavNomerLinkForming($r),
				'zayav_status_color' => _zayavStatusColor($r['zayav_status']),
				'zayav_add' => FullData($r['dtime_add'], 1)
			);
		}
	return $arr;
}//_zayavValues()
function _zayavBaseDeviceIds($client_id=0) { //список id устройств, которые используются в заявках
	$ids = array();
	$sql = "SELECT `b`.`id`
			FROM `zayav` `z` USE INDEX(`i_zayav_status`),
				 `base_device` `b`
			WHERE `b`.`id`=`z`.`base_device_id`
			  AND `z`.`zayav_status`
			  AND `z`.`ws_id`=".WS_ID."
			  ".($client_id ? "AND `z`.`client_id`=".$client_id : '')."
			GROUP BY `b`.`id`
			ORDER BY `b`.`sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ids[] = $r['id'];
	return implode(',', $ids);
}//_zayavBaseDeviceIds()
function _zayavBaseVendorIds($client_id=0) { //список id производителей, которые используются в заявках
	$ids = array();
	$sql = "SELECT `b`.`id`
			FROM `zayav` `z`,
				 `base_vendor` `b`
			WHERE `b`.`id`=`z`.`base_vendor_id`
			  AND `z`.`zayav_status`
			  AND `z`.`ws_id`=".WS_ID."
			  ".($client_id ? "AND `z`.`client_id`=".$client_id : '')."
			GROUP BY `b`.`id`
			ORDER BY `b`.`sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ids[] = $r['id'];
	return implode(',', $ids);
}//_zayavBaseVendorIds()
function _zayavBaseModelIds($client_id=0) { //список id производителей, которые используются в заявках
	$ids = array();
	$sql = "SELECT `b`.`id`
			FROM `zayav` `z`,
				 `base_model` `b`
			WHERE `b`.`id`=`z`.`base_model_id`
			  AND `z`.`zayav_status`
			  AND `z`.`ws_id`=".WS_ID."
			  ".($client_id ? "AND `z`.`client_id`=".$client_id : '')."
			GROUP BY `b`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ids[] = $r['id'];
	return implode(',', $ids);
}//_zayavBaseModelIds()
function _zayavImg($z, $size='s') {
	$v = array(
		'owner' => array('zayav'.$z['id'], 'dev'.$z['base_model_id'])
	);
	if($size == 'b')
		$v += array(
			'size' => 'b',
			'x' => 200,
			'y' => 320,
			'view' => 1
		);
	$img = _imageGet($v);
	return $img['zayav'.$z['id']]['id'] ? $img['zayav'.$z['id']]['img'] : $img['dev'.$z['base_model_id']]['img'];
}//_zayavImg()
function _zayavExpense($type_id=false, $i='name') {//Список расходов заявки
	if(!defined('ZE_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'zayav_expense'.WS_ID;
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT * FROM `setup_zayav_expense` ORDER BY `sort`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = array(
					'name' => $r['name'],
					'dop' => $r['dop']
				);
			xcache_set($key, $arr, 86400);
		}
		if(!defined('ZE_LOADED')) {
			foreach($arr as $id => $r) {
				define('ZE_'.$id, $r['name']);
				define('ZE_TXT_'.$id, $r['dop'] == 1);
				define('ZE_WORKER_'.$id, $r['dop'] == 2);
				define('ZE_ZP_'.$id, $r['dop'] == 3);
			}
			define('ZE_0', '');
			define('ZE_TXT_0', '');
			define('ZE_WORKER_0', 0);
			define('ZE_ZP_0', 0);
			define('ZE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'txt')
		return constant('ZE_TXT_'.$type_id);
	if($i == 'worker')
		return constant('ZE_WORKER_'.$type_id);
	if($i == 'zp')
		return constant('ZE_ZP_'.$type_id);
	return constant('ZE_'.$type_id);
}//_zayavExpense()
function _zayavExpenseDop($id=false) {
	$arr =  array(
		0 => 'нет',
		1 => 'текстовое поле',
		2 => 'список сотрудников',
		3 => 'список запчастей'
	);
	return $id !== false ? $arr[$id] : $arr;
}//_zayavExpenseDop()
function _zayavFinish($day='0000-00-00') {
	return
		'<input type="hidden" id="day_finish" value="'.$day.'" />'.
		'<div class="day-finish-link"><span>'.($day == '0000-00-00' ? 'не указан' : FullData($day, 1, 0, 1)).'</span></div>';
}//_zayavFinish()
function _zayavFinishCalendar($selDay='0000-00-00', $mon='', $zayav_spisok=0) {
	if(!$mon)
		$mon = $selDay != '0000-00-00' ? substr($selDay, 0, 7) : strftime('%Y-%m');
	$day = $mon.'-01';
	$ex = explode('-', $day);
	$SHOW_YEAR = $ex[0];
	$SHOW_MON = $ex[1];

	$back = $SHOW_MON - 1;
	$back = !$back ? ($SHOW_YEAR - 1).'-12' : $SHOW_YEAR.'-'.($back < 10 ? 0 : '').$back;
	$next = $SHOW_MON + 1;
	$next = $next > 12 ? ($SHOW_YEAR + 1).'-01' : $SHOW_YEAR.'-'.($next < 10 ? 0 : '').$next;

	$send =
		'<div id="zayav-finish-calendar">'.
			'<table id="fc-head">'.
				'<tr><td class="ch" val="'.$back.'">&laquo;'.
					'<td><span>'._monthDef($SHOW_MON).' '.$SHOW_YEAR.'</span> '.
					'<td class="ch" val="'.$next.'">&raquo;'.
			'</table>'.
			'<table id="fc-mon">'.
				'<tr id="week-name">'.
					'<td>пн<td>вт<td>ср<td>чт<td>пт<td>сб<td>вс';

	$sql = "SELECT
				DATE_FORMAT(`day_finish`,'%Y-%m-%d') AS `day`,
				COUNT(`id`) AS `count`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `zayav_status`=1
			  AND `day_finish` LIKE ('".$mon."%')
			GROUP BY DATE_FORMAT(`day_finish`,'%d')";
	$q = query($sql);
	$days = array();
	while($r = mysql_fetch_assoc($q))
		$days[$r['day']] = $r['count'];

	$unix = strtotime($day);
	$dayCount = date('t', $unix);   // Количество дней в месяце
	$week = date('w', $unix);       // Номер первого дня недели
	if(!$week)
		$week = 7;

	$curDay = strftime('%Y-%m-%d');
	$curUnix = strtotime($curDay); // Текущий день для выделения прошедших дней

	$send .= '<tr>'.($week - 1 ? '<td colspan="'.($week - 1).'">' : '');

	for($n = 1; $n <= $dayCount; $n++) {
		$day = $mon.'-'.($n < 10 ? '0' : '').$n;
		$cur = $curDay == $day ? ' cur' : '';
		$sel = $selDay == $day ? ' sel' : '';
		$old = $unix + $n * 86400 <= $curUnix ? ' old' : '';
		$val = $old ? '' : ' val="'.$day.'"';
		$send .=
			'<td class="d '.$cur.$old.$sel.'"'.$val.'>'.
				($cur ? '<u>'.$n.'</u>' : $n).
				(isset($days[$day]) ? ': <b'.($old && $zayav_spisok ? ' class="fc-old-sel" val="'.$day.'"' : '').'>'.$days[$day].'</b>' : '');
		$week++;
		if($week > 7)
			$week = 1;
		if($week == 1 && $n < $dayCount)
			$send .= '<tr>';
	}
	$send .= '</table>'.
			($zayav_spisok && $selDay != '0000-00-00' ? '<div id="fc-cancel" val="0000-00-00">День не указан</div>' : '').
		'</div>';

	return $send;
}//_zayavFinishCalendar()


function zayav_add($v=array()) {
	$sql = "SELECT `id`,`name` FROM `setup_fault` ORDER BY SORT";
	$q = query($sql);
	$fault = '<table>';
	$k = 0;
	while($r = mysql_fetch_assoc($q))
		$fault .= (++$k%2 ? '<tr>' : '').'<td>'._check('f_'.$r['id'], $r['name']);
	$fault .= '</table>';

	$client_id = 0;
	$back = 'zayav';

	if(!empty($_GET['back'])) {
		$back = explode('_', $_GET['back']);
		if($back[0] == 'client') {
			$client_id = _isnum(@$back[1]);
			$back = 'client'.($client_id ? '&d=info&id='.$client_id : '');
		} else
			$back = $back[0];
	}

	return '<div id="zayavAdd">'.
		'<div class="headName">Внесение новой заявки</div>'.
		'<table style="border-spacing:8px">'.
			'<tr><td class="label">Клиент:		    <td><INPUT TYPE="hidden" id="client_id" value="'.$client_id.'" />'.
			'<tr><td class="label topi">Устройство: <td><table><td id="dev"><td id="device_image"></table>'.
				'<tr><td class="label">IMEI:		<td><INPUT type="text" id="imei" maxlength="20"'.(isset($v['imei']) ? ' value="'.$v['imei'].'"' : '').' />'.
			'<tr><td class="label">Серийный номер:  <td><INPUT type="text" id="serial" maxlength="30"'.(isset($v['serial']) ? ' value="'.$v['serial'].'"' : '').' />'.
			'<tr><td class="label">Цвет:'.
				'<td><INPUT TYPE="hidden" id="color_id" />'.
					'<span class="color_dop dn"><tt>-</tt><INPUT TYPE="hidden" id="color_dop" /></span>'.
			'<tr class="tr_equip dn"><td class="label">Комплектация:<td class="equip_spisok">'.
			'<tr><td class="label topi">Местонахождение устройства<br />после внесения заявки:<td><input type="hidden" id="place" value="-1" />'.
			'<tr><td class="label top">Неисправности:<td id="fault">'.$fault.
			'<tr><td class="label topi">Заметка:	 <td><textarea id="comm"></textarea>'.
			'<tr><td class="label">Предварительная стоимость:<td><input type="text" class="money" id="pre_cost" maxlength="11" /> руб.'.
			'<tr><td class="label">Срок:<td>'._zayavFinish().
		'</table>'.

		'<div class="vkButton"><button>Внести</button></div>'.
		'<div class="vkCancel" val="'.$back.'"><button>Отмена</button></div>'.
	'</div>';
}//zayav_add()

function zayavFilter($v) {
	$default = array(
		'page' => 1,
		'limit' => 20,
		'client_id' => 0,
		'find' => '',
		'sort' => 1,
		'desc' => 0,
		'status' => 0,
		'finish' => '0000-00-00',
		'zpzakaz' => 0,
		'executer' => 0,
		'device' => 0,
		'vendor' => 0,
		'model' => 0,
		'diff' => 0,
		'place' => 0,
	);
	$filter = array(
		'page' => _num(@$v['page']) ? $v['page'] : 1,
		'limit' => _num(@$v['limit']) ? $v['limit'] : 20,
		'client_id' => _num(@$v['client_id']),
		'find' => trim(@$v['find']),
		'sort' => _num(@$v['sort']) ? $v['sort'] : 1,
		'desc' => _bool(@$v['desc']),
		'status' => _isnum(@$v['status']),
		'finish' => preg_match(REGEXP_DATE, @$v['finish']) ? $v['finish'] : $default['finish'],
		'zpzakaz' => _num(@$v['zpzakaz']),
		'executer' => intval(@$v['executer']),
		'device' => _num(@$v['device']),
		'vendor' => _num(@$v['vendor']),
		'model' => _num(@$v['model']),
		'diff' => _bool(@$v['diff']),
		'place' => trim(@$v['place']),
		//'place' => !empty($v['place']) ? win1251(urldecode(htmlspecialchars(trim($v['place'])))) : '',
		'clear' => ''
	);
	if(!$filter['client_id'])
		foreach($default as $k => $r)
			if($r != $filter[$k]) {
				$filter['clear'] = '<a class="clear">Очистить фильтр</a>';
				break;
			}
	return $filter;
}//zayavFilter()
function zayav_spisok($v) {
	$filter = zayavFilter($v);

	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`ws_id`=".WS_ID." AND !`deleted` AND !`cartridge` AND `zayav_status`";

	if($filter['find']) {
		$engRus = _engRusChar($filter['find']);
		$cond .= " AND (`find` LIKE '%".$filter['find']."%'".
			($engRus ? " OR `find` LIKE '%".$engRus."%'" : '').")";
		$reg = '/('.$filter['find'].')/i';
		if($engRus)
			$regEngRus = '/('.$engRus.')/i';

		if($page ==1 && _isnum($filter['find']))
			$nomer = intval($filter['find']);
	} else {
		if($filter['client_id'])
			$cond .= " AND `client_id`=".$filter['client_id'];
		if($filter['status']) {
			$cond .= " AND `zayav_status`=".$filter['status'];
			if($filter['status'] == 1 && $filter['finish'] != '0000-00-00')
				$cond .= " AND `day_finish`='".$filter['finish']."'";
		}
		if($filter['diff'])
			$cond .= " AND `accrual_sum`!=`oplata_sum`";
		if($filter['zpzakaz']) {
			$ids = query_ids("SELECT `zayav_id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID);
			$cond .= " AND `id` ".($filter['zpzakaz'] == 2 ? 'NOT' : '')." IN (".$ids.")";
		}
		if($filter['executer'])
			$cond .= " AND `executer_id`=".($filter['executer'] < 0 ? 0 : $filter['executer']);
		if($filter['device'])
			$cond .= " AND `base_device_id`=".$filter['device'];
		if($filter['vendor'])
			$cond .= " AND `base_vendor_id`=".$filter['vendor'];
		if($filter['model'])
			$cond .= " AND `base_model_id`=".$filter['model'];
		if($filter['place']) {
			if(_isnum($filter['place']))
				$cond .= " AND `device_place`=".$filter['place'];
			elseif($filter['place'] == -1)
				$cond .= " AND !`device_place` AND !LENGTH(`device_place_other`)";
			else
				$cond .= " AND !`device_place` AND `device_place_other`='".$filter['place']."'";
		}
	}

	$all = query_value("SELECT COUNT(*) FROM `zayav` USE INDEX (`i_zayav_status`) WHERE ".$cond." LIMIT 1");

	$zayav = array();
	$images = array();
	if(isset($nomer)) {
		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND `zayav_status` AND `nomer`=".$nomer." LIMIT 1";
		if($r = mysql_fetch_assoc(query($sql))) {
			$all++;
			$limit--;
			$r['nomer_find'] = 1;
			$zayav[$r['id']] = $r;
			$images[] = 'zayav'.$r['id'];
			$images[] = 'dev'.$r['base_model_id'];
		}
	}

	if(!$all)
		return array(
			'all' => 0,
			'result' => 'Заявок не найдено'.$filter['clear'],
			'spisok' => '<div class="_empty">Заявок не найдено</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => 'Показан'._end($all, 'а', 'о').' '.$all.' заяв'._end($all, 'ка', 'ки', 'ок').$filter['clear'],
		'spisok' => '',
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;
	$limit_save = $limit;
	if($page > 1)
		setcookie('zback_spisok_page', $page, time() + 3600, '/');
	if(!empty($_COOKIE['zback_info']) && $page == 1 && !empty($_COOKIE['zback_spisok_page']) && $_COOKIE['zback_spisok_page'] > 1) {
		setcookie('zback_info', '', time() - 3600, '/');
		$page = $_COOKIE['zback_spisok_page'];
		$limit *= $_COOKIE['zback_spisok_page'];
	}
	$sql = "SELECT
	            *,
				'' AS `note`
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `".($filter['sort'] == 2 ? 'zayav_status_dtime' : 'dtime_add')."` ".($filter['desc'] ? 'ASC' : 'DESC')."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if(isset($nomer) && $nomer == $r['nomer'])
			continue;
		$zayav[$r['id']] = $r;
		$images[] = 'zayav'.$r['id'];
		$images[] = 'dev'.$r['base_model_id'];
	}

	$zayavIds = implode(',', array_keys($zayav));

	if(!$filter['client_id'])
		$zayav = _clientLink($zayav, 0, 1);

	$images = _imageGet(array(
		'owner' => $images,
		'view' => 1
	));

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
			  AND `status`
			ORDER BY `id` ASC";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zayav[$r['table_id']]['note'] = $r['txt'];

	foreach($zayav as $id => $r) {
		$r['model'] = _modelName($r['base_model_id']);
		$img = $images['zayav'.$id]['id'] ? $images['zayav'.$id]['img'] : $images['dev'.$r['base_model_id']]['img'];
		if($filter['find']) {
			if(preg_match($reg, $r['model']))
				$r['model'] = preg_replace($reg, "<em>\\1</em>", $r['model'], 1);
			if($regEngRus && preg_match($regEngRus, $r['model']))
				$r['model'] = preg_replace($regEngRus, '<em>\\1</em>', $r['model'], 1);
			$r['imei'] = preg_match($reg, $r['imei']) ? preg_replace($reg, "<em>\\1</em>", $r['imei'], 1) : '';
			$r['serial'] = preg_match($reg, $r['serial']) ? preg_replace($reg, "<em>\\1</em>", $r['serial'], 1) : '';
		} else {
			$r['imei'] = '';
			$r['serial'] = '';
		}
		$diff = $r['accrual_sum'] - $r['oplata_sum'];
		$send['spisok'] .=
			'<div class="zayav_unit" id="u'.$id.'" style="background-color:#'._zayavStatusColor($r['zayav_status']).'" val="'.$id.'">'.
				'<table width="100%">'.
					'<tr><td valign=top>'.
						'<h2'.(isset($r['nomer_find']) ? ' class="finded"' : '').'>#'.$r['nomer'].'</h2>'.
						'<a class="name">'.
							_deviceName($r['base_device_id']).
							'<b>'._vendorName($r['base_vendor_id']).$r['model'].'</b>'.
						'</a>'.
						'<table class="utab">'.
   (!$filter['client_id'] ? '<tr><td class="label">Клиент:<td>'.$r['client_link'] : '').
							'<tr><td class="label">Дата подачи:'.
								'<td>'.FullData($r['dtime_add'], 1).
									($r['accrual_sum'] || $r['oplata_sum'] ?
										'<div class="balans'.($diff ? ' diff' : '').'">'.
											'<span class="acc'._tooltip('Начислено', -39).$r['accrual_sum'].'</span>/'.
											'<span class="opl'._tooltip($diff ? ($diff > 0 ? 'Недо' : 'Пере').'плата '.abs($diff).' руб.' : 'Оплачено', -17, 'l').$r['oplata_sum'].'</span>'.
										'</div>'
									: '').
			  ($r['imei'] ? '<tr><td class="label">IMEI:<td>'.$r['imei'] : '').
		    ($r['serial'] ? '<tr><td class="label">Серийный номер:<td>'.$r['serial'] : '').
	(isset($zpZakaz[$id]) ? '<tr><td class="label">Заказаны з/п:<td class="zz">'.implode(', ', $zpZakaz[$id]) : '').
						'</table>'.
					'<td class="image">'.$img.
				'</table>'.
				'<div class="note">'.$r['note'].'</div>'.
			'</div>';
	}

	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit_save : $c;
		$send['spisok'] .=
			'<div class="_next" val="'.($page + 1).'">'.
				'<span>Показать ещё '.$c.' заяв'._end($c, 'ку', 'ки', 'ок').'</span>'.
			'</div>';
	}
	return $send;
}//zayav_spisok()
function zayav_list($v) {
	$data = zayav_spisok($v);
	$v = $data['filter'];
	$place_other = array();
	$sql = "SELECT DISTINCT `device_place_other` AS `other`
			FROM `zayav`
			WHERE LENGTH(`device_place_other`)
			  AND `zayav_status`
			  AND `ws_id`=".WS_ID;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$place_other[] = '"'.$r['other'].'"';

	$status = _zayavStatusName();
	$status[1] .= '<div id="srok">Срок: '._zayavFinish($v['finish']).'</div>';

	return '<div id="zayav">'.

	(SERVIVE_CARTRIDGE ?
		'<div id="dopLinks">'.
			'<a class="link sel">Оборудование</a>'.
			'<a class="link" href="'.URL.'&p=zayav&d=cartridge">Картриджи</a>'.
		'</div>'
	: '').

		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td id="spisok">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate">'.
						'<a id="zayav-add" val="zayav"'.(SERVIVE_CARTRIDGE ? ' class="cartridge"' : '').'>Новая заявка</a>'.
					'</div>'.
					'<div id="find"></div>'.
					'<div class="findHead">Порядок</div>'.
					_radio('sort', array(1=>'По дате добавления',2=>'По обновлению статуса'), $v['sort']).
					_check('desc', 'Обратный порядок', $v['desc']).
					'<div class="condLost'.(!empty($v['find']) ? ' hide' : '').'">'.
						'<div class="findHead">Статус заявки</div>'.
						_rightLink('status', $status, $v['status']).
						_check('diff', 'Неоплаченные заявки', $v['diff']).
						'<div class="findHead">Заказаны запчасти</div>'.
						_radio('zpzakaz', array(0=>'Все заявки',1=>'Да',2=>'Нет'), $v['zpzakaz'], 1).
						'<div class="findHead">Исполнитель</div><input type="hidden" id="executer" value="'.$v['executer'].'" />'.
						'<div class="findHead">Устройство</div><div id="dev"></div>'.
						'<div class="findHead">Нахождение устройства</div><input type="hidden" id="device_place" value="'.$v['place'].'" />'.
					'</div>'.
		'</table>'.
		'<script type="text/javascript">'.
			'var Z={'.
				'device_ids:['._zayavBaseDeviceIds().'],'.
				'vendor_ids:['._zayavBaseVendorIds().'],'.
				'model_ids:['._zayavBaseModelIds().'],'.
				'place_other:['.implode(',', $place_other).'],'.
				'find:"'.$v['find'].'",'.
				'device_id:'.$v['device'].','.
				'vendor_id:'.$v['vendor'].','.
				'model_id:'.$v['model'].','.
				'cookie_id:'.(!empty($_COOKIE['zback_info']) ? $_COOKIE['zback_info'] : 0).
			'};'.
		'</script>'.
	'</div>';
}//zayav_list()


function zayavBalansUpdate($zayav_id, $ws_id=WS_ID) {//Обновление баланса клиента
	if(!$zayav_id)
		return false;
	$opl = query_value("SELECT IFNULL(SUM(`sum`),0)
						   FROM `money`
						   WHERE `ws_id`=".$ws_id."
							 AND !`deleted`
							 AND `zayav_id`=".$zayav_id."
							 AND `sum`>0");
	$acc = query_value("SELECT IFNULL(SUM(`sum`),0)
						FROM `accrual`
						WHERE `ws_id`=".$ws_id."
						  AND !`deleted`
						  AND `zayav_id`=".$zayav_id);
	query("UPDATE `zayav` SET `accrual_sum`=".$acc.",`oplata_sum`=".$opl." WHERE `id`=".$zayav_id);
	return array(
		'acc' => round($acc, 2),
		'opl' => round($opl, 2),
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
	//Установка id заявки, если переход со списка заявок
	if(!empty($_COOKIE['zback_spisok']))
		setcookie('zback_info', $zayav_id, time() + 3600, '/');

	$sql = "SELECT *
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `zayav_status`
			  AND `id`=".$zayav_id;
	if(!$z = mysql_fetch_assoc(query($sql)))
		return 'Заявки не существует.';

	if($z['cartridge'])
		return zayav_cartridge_info($z);

	define('MODEL', _vendorName($z['base_vendor_id'])._modelName($z['base_model_id']));
	define('DOPL', $z['accrual_sum'] - $z['oplata_sum']);

	$status = _zayavStatusName();
	unset($status[0]);
	$expense = zayav_expense_spisok($z, 'all');
	$history = history(array('zayav_id'=>$zayav_id));
	$acc_sum = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE !`deleted` AND `zayav_id`=".$zayav_id);
	$expense_sum = query_value("SELECT SUM(`sum`) FROM `zayav_expense` WHERE `zayav_id`=".$zayav_id." AND `category_id`!=1");

	return '<script type="text/javascript">'.
		'var STATUS='._selJson($status).','.
		'ZAYAV={'.
			'id:'.$zayav_id.','.
			'nomer:'.$z['nomer'].','.
			'head:"№<b>'.$z['nomer'].'</b>",'.
			'client_id:'.$z['client_id'].','.
			'device:'.$z['base_device_id'].','.
			'vendor:'.$z['base_vendor_id'].','.
			'model:'.$z['base_model_id'].','.
			'status:'.$z['zayav_status'].','.
			'dev_place:'.$z['device_place'].','.
			'place_other:"'.$z['device_place_other'].'",'.
			'imei:"'.$z['imei'].'",'.
			'serial:"'.$z['serial'].'",'.
			'color_id:'.$z['color_id'].','.
			'color_dop:'.$z['color_dop'].','.
			'equip:"'.addslashes(devEquipCheck($z['base_device_id'], $z['equip'])).'",'.
			'pre_cost:'.$z['pre_cost'].','.
			'images:"'.addslashes(_imageAdd(array('owner'=>'zayav'.$zayav_id))).'",'.
			'expense:['.$expense['json'].'],'.
			'zp_avai:'.zayav_zp_avai($z).','.
			'worker_zp:'.round(($acc_sum - $expense_sum) * 0.3).
		'},'.
		'PRINT={'.
			'dtime:"'.FullDataTime($z['dtime_add']).'",'.
			'device:"'._deviceName($z['base_device_id']).'<b>'._vendorName($z['base_vendor_id'])._modelName($z['base_model_id']).'</b>",'.
			'color:"'._color($z['color_id'], $z['color_dop']).'",'.
			($z['imei'] ? 'imei:"'.$z['imei'].'",' : '').
			($z['serial'] ? 'serial:"'.$z['serial'].'",' : '').
			($z['equip'] ? 'equip:"'.zayavEquipSpisok($z['equip']).'",' : '').
			'client:"'._clientLink($z['client_id'], 1).'",'.
			'telefon:"'.query_value("SELECT `telefon` FROM `client` WHERE id=".$z['client_id']).'",'.
			'defect:"'.addslashes(str_replace("\n", ' ', query_value("SELECT `txt` FROM `vk_comment` WHERE `status` AND `table_name`='zayav' AND `table_id`=".$zayav_id." AND !`parent_id` ORDER BY `id` DESC"))).'"'.
		'};'.
	'</script>'.
	'<div id="zayav-info">'.
		'<div id="dopLinks">'.
			'<a class="img_del delete'.(!empty($money) ?  ' dn': '').'"></a>'.
			'<a class="link info sel">Информация</a>'.
			'<a class="link zedit">Редактирование</a>'.
			'<a class="link acc_add">Начислить</a>'.
			'<a class="link income-add">Принять платёж</a>'.
			'<a class="link hist">История</a>'.
		'</div>'.
		'<table class="itab">'.
			'<tr class="z-info"><td id="left">'.
				'<div class="headName">'.
					'Заявка №'.$z['nomer'].
	  (WS_ID != 3 ? '<a class="img_print'._tooltip('Распечатать квитанцию', -75).'</a>' :
					'<a href="'.APP_HTML.'/view/kvit_comtex.php?'.VALUES.'&id='.$zayav_id.'" class="img_xls'._tooltip('Распечатать квитанцию в xls', -168, 'r').'</a>'
	  ).
				'</div>'.
				'<table class="tabInfo">'.
					'<tr><td class="label">Устройство: <td>'._deviceName($z['base_device_id']).'<a><b>'.MODEL.'</b></a>'.
					'<tr><td class="label">Клиент:	 <td>'._clientLink($z['client_id'], 0, 1).
					'<tr><td class="label">Дата приёма:'.
						'<td class="dtime_add'._tooltip('Заявку вн'.(_viewer($z['viewer_id_add'], 'sex') == 1 ? 'есла' : 'ёс').' '._viewer($z['viewer_id_add'], 'name'), -70).FullDataTime($z['dtime_add']).
  ($z['pre_cost'] ? '<tr><td class="label">Стоимость:<td><b class="'._tooltip('Предварительная стоимость ремонта', -10, 'l').$z['pre_cost'].'</b> руб.' : '').
                    '<tr><td class="label">Срок:<td>'._zayavFinish($z['day_finish']).
                    '<tr><td class="label">Исполнитель:'.
						'<td id="executer_td"><input type="hidden" id="executer_id" value="'.$z['executer_id'].'" />'.
					'<tr><td class="label">Статус:'.
						'<td><div id="status" style="background-color:#'._zayavStatusColor($z['zayav_status']).'" class="status_place">'.
								_zayavStatusName($z['zayav_status']).
							'</div>'.
							'<div id="status_dtime">от '.FullDataTime($z['zayav_status_dtime'], 1).'</div>'.
					'<tr class="acc_tr'.($z['accrual_sum'] ? '' : ' dn').'"><td class="label">Начислено: <td><b class="acc">'.$z['accrual_sum'].'</b> руб.'.
					'<tr class="op_tr'.($z['oplata_sum'] ? '' : ' dn').'"><td class="label">Оплачено:	<td><b class="op">'.$z['oplata_sum'].'</b> руб.'.
						'<span class="dopl'.(DOPL ? '' : ' dn')._tooltip('Необходимая доплата', -60).(DOPL > 0 ? '+' : '').DOPL.'</span>'.
				'</table>'.
				'<div id="kvit_spisok">'.zayav_kvit($zayav_id).'</div>'.
				'<div class="headBlue">Расходы<div id="ze-edit" class="img_edit'._tooltip('Изменить расходы по заявке', -88).'</div></div>'.
				'<div id="ze_acc">'.zayav_acc_sum($z).'</div>'.
				$expense['html'].
				'<div class="headBlue rm">'.
					'<a href="'.URL.'&p=report&d=remind"><b>Напоминания</b></a>'.
					'<div class="img_add _remind-add'._tooltip('Новое напоминание', -60).'</div>'.
				'</div>'.
				'<div id="remind-spisok">'._remind_spisok(array('zayav_id'=>$z['id']), 'spisok').'</div>'.
				_vkComment('zayav', $z['id']).
				'<div class="headBlue mon">Начисления и платежи'.
					'<a class="add income-add">Принять платёж</a>'.
					'<em>::</em>'.
					'<a class="add acc_add">Начислить</a>'.
				'</div>'.
				'<div id="money_spisok">'.zayav_info_money($zayav_id).'</div>'.

			'<td id="right">'.
				'<div id="foto">'._zayavImg($z, 'b').'</div>'.
				'<div class="headBlue">Информация об устройстве</div>'.
				'<div class="devContent">'.
					'<div class="devName">'._deviceName($z['base_device_id']).'<br />'.'<a>'.MODEL.'</a></div>'.
					'<table class="devInfo">'.
						($z['imei'] ? '<tr><th>imei:		 <td>'.$z['imei'] : '').
						($z['serial'] ? '<tr><th>serial:	 <td>'.$z['serial'] : '').
						($z['equip'] ? '<tr><th valign="top">Комплект:<td>'.zayavEquipSpisok($z['equip']) : '').
						($z['color_id'] ? '<tr><th>Цвет:  <td>'._color($z['color_id'], $z['color_dop']) : '').
						'<tr><th>Нахождение:<td><a class="dev_place">'.($z['device_place'] ? @_devPlace($z['device_place']) : $z['device_place_other']).'</a>'.
					'</table>'.
				'</dev>'.

				'<div class="headBlue">'.
					'<a class="goZp" href="'.URL.'&p=zp&device='.$z['base_device_id'].'&vendor='.$z['base_vendor_id'].'&model='.$z['base_model_id'].'">Список запчастей</a>'.
					'<a class="zpAdd add">добавить</a>'.
				'</div>'.
				'<div id="zpSpisok">'.zayav_zp($z).'</div>'.

			'<tr class="z-hist"><td>'.
				'<div class="headName">Заявка №'.$z['nomer'].' - история действий</div>'.
				$history['spisok'].
		'</table>'.
	'</div>';
}//zayav_info()
function zayav_kvit($zayav_id) {
	$sql = "SELECT * FROM `zayav_kvit` WHERE `ws_id`=".WS_ID." AND `active` AND `zayav_id`=".$zayav_id." ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';
	$send = '<div class="headBlue">Квитанции</div>'.
			'<table class="_spisok _money">';
	$n = 1;
	while($r = mysql_fetch_assoc($q))
		$send .=
			'<tr><td><a class="zayav_kvit" val="'.$r['id'].'">Квитанция '.($n++).'</a>. '.
					'<span class="kvit_defect">'.$r['defect'].'</span>'.
				'<td class="dtime">'.FullDataTime($r['dtime_add']);
	$send .= '</table>';
	return $send;
}//zayav_kvit()
function zayav_acc_sum($z) {
	return $z['accrual_sum'] ? 'Общая сумма начислений: <b>'.$z['accrual_sum'].'</b> руб.' : 'Начислений нет.';
}//zayav_acc_sum()
function zayav_expense_test($v) {// Проверка корректности данных расходов заявки при внесении в базу
	if(empty($v))
		return array();
	$send = array();
	$ex = explode(',', $v);
	foreach($ex as $r) {
		$ids = explode(':', $r);
		if(!_isnum($ids[0]))
			return false;
		if((_zayavExpense($ids[0], 'worker') || _zayavExpense($ids[0], 'zp')) && !_isnum($ids[1]))
			return false;
		if(_zayavExpense($ids[0], 'txt'))
			$ids[1] = win1251(htmlspecialchars(trim($ids[1])));
		if(!_zayavExpense($ids[0], 'txt') && !_zayavExpense($ids[0], 'worker') && !_zayavExpense($ids[0], 'zp'))
			$ids[1] = '';
		if(!_cena($ids[2]) && $ids[2] != 0)
			return false;
		$send[] = $ids;
	}
	return $send;
}//zayav_expense_test()
function zayav_expense_spisok($z, $type='html') {//Получение списка расходов заявки
	$sql = "SELECT * FROM `zayav_expense` WHERE `zayav_id`=".$z['id']." ORDER BY `id`";
	$q = query($sql);
	$arr = array();
	while($r = mysql_fetch_assoc($q))
		$arr[$r['id']] = $r;

	$arr = _zpLink($arr);

	$html = '<table class="ze-spisok">';
	$json = array();
	$array = array();
	$expense_sum = 0;
	foreach($arr as $r) {
		$sum = round($r['sum'], 2);
		$expense_sum += $sum;
		$html .= '<tr>'.
			'<td class="name">'._zayavExpense($r['category_id']).
			'<td>'.(_zayavExpense($r['category_id'], 'txt') ? $r['txt'] : '').
				(_zayavExpense($r['category_id'], 'worker') ?
					'<a class="go-report-salary" val="'.$r['worker_id'].':'.$r['year'].':'.$r['mon'].':'.$r['id'].'">'.
						_viewer($r['worker_id'], 'name').
					'</a>'
				: '').
				(_zayavExpense($r['category_id'], 'zp') ? $r['zp_short'] : '').
			'<td class="sum">'.$sum.' р.';
		$json[] = '['.
			$r['category_id'].','.
			(_zayavExpense($r['category_id'], 'txt') ? '"'.$r['txt'].'"' : '').
			(_zayavExpense($r['category_id'], 'worker') ? intval($r['worker_id']) : '').
			(_zayavExpense($r['category_id'], 'zp') ? intval($r['zp_id']) : '').','.
			$sum.
		']';
		$array[] = array(
			intval($r['category_id']),
				(_zayavExpense($r['category_id'], 'txt') ? $r['txt'] : '').
				(_zayavExpense($r['category_id'], 'worker') ? intval($r['worker_id']) : '').
				(_zayavExpense($r['category_id'], 'zp') ? intval($r['zp_id']) : ''),
			$sum
		);
	}
	if(!empty($arr))
		$html .= '<tr><td colspan="2" class="itog">Итог:<td class="sum"><b>'.$expense_sum.'</b> р.'.
				 '<tr><td colspan="2" class="itog">Остаток:<td class="sum">'.($z['accrual_sum'] - $expense_sum).' р.';
	$html .= '</table>';
	switch($type) {
		default:
		case 'html': return $html;
		case 'json': return implode(',', $json);
		case 'array': return $array;
		case 'all': return array(
			'html' => $html,
			'json' => implode(',', $json),
			'array' => $array
		);
	}
}//zayav_expense_spisok()
function zayav_info_money($zayav_id) {
	$sql = "(
		SELECT
			'acc' AS `type`,
			`id`,
			`sum`,
			`prim`,
			`dtime_add`,
			`viewer_id_add`
		FROM `accrual`
		WHERE `ws_id`=".WS_ID."
		  AND !`deleted`
		  AND `zayav_id`=".$zayav_id."
	) UNION (
		SELECT
			'op' AS `type`,
			`id`,
			`sum`,
			`prim`,
			`dtime_add`,
			`viewer_id_add`
		FROM `money`
		WHERE `ws_id`=".WS_ID."
		  AND !`deleted`
		  AND `sum`>0
		  AND `zayav_id`=".$zayav_id."
	)
	ORDER BY `dtime_add`";
	$q = query($sql);
	$send = '';
	if(mysql_num_rows($q)) {
		$send = '<table class="_spisok _money">';
		while($r = mysql_fetch_assoc($q))
			$send .= $r['type'] == 'acc' ? zayav_accrual_unit($r) : zayav_oplata_unit($r);
		$send .= '</table>';
	}

	return $send;
}//zayav_money()
function zayav_accrual_unit($r) {
	return '<tr><td class="sum '.$r['type']._tooltip('Начисление', -3).round($r['sum'], 2).
		'<td>'.$r['prim'].
		'<td class="dtime'._tooltip('Начислил '._viewer($r['viewer_id_add'], 'name'), -40).FullDataTime($r['dtime_add']).
		'<td class="ed">'.
			'<div val="'.$r['id'].'" class="img_del acc_del'._tooltip('Удалить начисление', -64).'</div>';
}//zayav_accrual_unit()
function zayav_oplata_unit($r) {
	return '<tr val="'.$r['id'].'">'.
		'<td class="sum '.$r['type']._tooltip('Платёж', 8).round($r['sum'], 2).
		'<td>'.$r['prim'].
		'<td class="dtime'._tooltip('Платёж внёс '._viewer($r['viewer_id_add'], 'name'), -60).FullDataTime($r['dtime_add']).
		'<td class="ed">'.
			'<div class="img_del income-del'._tooltip('Удалить платёж', -54).'</div>'.
			'<div class="img_rest income-rest'._tooltip('Восстановить платёж', -69).'</div>';
}//zayav_oplata_unit()
function zayav_zp($z) {
	$sql = "SELECT *
			FROM `zp_catalog`
			WHERE `base_device_id`=".$z['base_device_id']."
			  AND `base_vendor_id`=".$z['base_vendor_id']."
			  AND `base_model_id`=".$z['base_model_id'];
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '<div class="_empty">Для '.MODEL.' запчастей нет.</div>';

	$zp = array();
	$ids = array();
	while($r = mysql_fetch_assoc($q)) {
		$id = $r['compat_id'] ? $r['compat_id'] : $r['id'];
		$zp[$id] = $r;
		$ids[$r['id']] = $r['id'];
		$ids[$r['compat_id']] = $r['compat_id'];
	}
	unset($ids[0]);

	$img = array();
	foreach($ids as $id)
		$img[] = 'zp'.$id;
	$img = _imageGet(array('owner' => $img));

	$ids = implode(',', $ids);
	$sql = "SELECT `zp_id` AS `id`,`count` FROM `zp_avai` WHERE `zp_id` IN (".$ids.")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zp[$r['id']]['avai'] = $r['count'];
	$sql = "SELECT `zp_id` AS `id`,`count`
				FROM `zp_zakaz`
				WHERE `zp_id` IN (".$ids.")
				  AND `zayav_id`=".$z['id'];
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zp[$r['id']]['zakaz'] = $r['count'];

	$send = '';
	foreach($zp as $id => $r)
		$send .=
			'<div class="unit" val="'.$r['id'].'">'.
				'<div class="image"><div>'.$img['zp'.$id]['img'].'</div></div>'.
				($r['bu'] ? '<span class="bu">Б/у</span>' : '').
				'<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'"><b>'._zpName($r['name_id']).'</b> '.MODEL.'</a>'.
				($r['version'] ? '<div class="version">'.$r['version'].'</div>' : '').
				($r['color_id'] ? '<div class="color">Цвет: '._color($r['color_id']).'</div>' : '').
				'<div>'.
					(isset($r['zakaz']) ? '<a class="zakaz_ok">Заказано!</a>' : '<a class="zakaz">Заказать</a>').
					(isset($r['avai']) && $r['avai'] ? '<b class="avai">Наличие: '.$r['avai'].'</b> <a class="set">Установить</a>' : '').
				'</div>'.
			'</div>';

	return $send;
}//zayav_zp()
function zayav_zp_avai($z) {
	$sql = "SELECT
				`id`,
				`compat_id`,
				`name_id`,
				`version`
			FROM `zp_catalog` `c`
			WHERE `c`.`base_device_id`=".$z['base_device_id']."
			  AND `c`.`base_vendor_id`=".$z['base_vendor_id']."
			  AND `c`.`base_model_id`=".$z['base_model_id'];
	$q = query($sql);
	$send = array();
	while($r = mysql_fetch_assoc($q))
		$send[] = '{'.
			'uid:'.($r['compat_id'] ? $r['compat_id'] : $r['id']).','.
			'title:"'.addslashes(_zpName($r['name_id'])).'"'.
			($r['version'] ? ',content:"'.addslashes(_zpName($r['name_id']).'<span>'.$r['version'].'</span>').'"' : '').
		'}';
	return '['.implode(',',$send).']';
}//zayav_zp_avai()
function zayav_msg_to_client($zayav_id) {//сообщение о передачи устройства клиенту после внесения платежа
	$parent_id = 0;
	$sql = "SELECT `id`,`parent_id`
			FROM `vk_comment`
			WHERE `table_name`='zayav'
			  AND `table_id`=".$zayav_id."
			  AND `status`
			ORDER BY `id` DESC
			LIMIT 1";
	if($r = query_assoc($sql))
		$parent_id = $r['parent_id'] ? $r['parent_id'] : $r['id'];
	$sql = "INSERT INTO `vk_comment` (
				`table_name`,
				`table_id`,
				`txt`,
				`parent_id`,
				`viewer_id_add`
			) VALUES (
				'zayav',
				".$zayav_id.",
				'Передано клиенту.',
				".$parent_id.",
				".VIEWER_ID."
			)";
	query($sql);
	return utf8(_vkComment('zayav', $zayav_id));
}//zayav_msg_to_client()



// ---===! zayav cartridge !===--- Картриджи

function zayav_cartridge() {
	$data = zayav_cartridge_spisok();
	$v = $data['filter'];

	$status = _zayavStatusName();

	return
	'<div id="zayav-cartridge">'.

		'<div id="dopLinks">'.
			'<a class="link" href="'.URL.'&p=zayav">Оборудование</a>'.
			'<a class="link sel">Картриджи</a>'.
		'</div>'.

		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td id="spisok">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate">'.
						'<a id="zayav-add" val="zayav"'.(SERVIVE_CARTRIDGE ? ' class="cartridge"' : '').'>Новая заявка</a>'.
					'</div>'.
					'<div class="findHead">Порядок</div>'.
					_radio('sort', array(1=>'По дате добавления',2=>'По обновлению статуса'), $v['sort']).
					_check('desc', 'Обратный порядок', $v['desc']).
					'<div class="findHead">Статус заявки</div>'.
					_rightLink('status', $status, $v['status']).
		'</table>'.
	'</div>';
}//zayav_cartridge()
function zayavCartridgeFilter($v) {
	$default = array(
		'page' => 1,
		'limit' => 20,
		'client_id' => 0,
		'sort' => 1,
		'desc' => 0,
		'status' => 0
	);
	$filter = array(
		'page' => _isnum(@$v['page']) ? $v['page'] : 1,
		'limit' => _isnum(@$v['limit']) ? $v['limit'] : 20,
		'client_id' => _isnum(@$v['client_id']),
		'sort' => _isnum(@$v['sort']) ? $v['sort'] : 1,
		'desc' => _isbool(@$v['desc']),
		'status' => _isnum(@$v['status']),
		'clear' => ''
	);
	if(!$filter['client_id'])
		foreach($default as $k => $r)
			if($r != $filter[$k]) {
				$filter['clear'] = '<a class="clear">Очистить фильтр</a>';
				break;
			}
	return $filter;
}//zayavCartridgeFilter()
function zayav_cartridge_spisok($v=array()) {
	$filter = zayavCartridgeFilter($v);

	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`ws_id`=".WS_ID." AND !`deleted` AND `cartridge`";

	if($filter['client_id'])
		$cond .= " AND `client_id`=".$filter['client_id'];
	if($filter['status'])
		$cond .= " AND `zayav_status`=".$filter['status'];

	$all = query_value("SELECT COUNT(*) FROM `zayav` WHERE ".$cond." LIMIT 1");

	$zayav = array();

	if(!$all)
		return array(
			'all' => 0,
			'result' => 'Заявок не найдено'.$filter['clear'],
			'spisok' => '<div class="_empty">Заявок не найдено</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => 'Показан'._end($all, 'а', 'о').' '.$all.' заяв'._end($all, 'ка', 'ки', 'ок').$filter['clear'],
		'spisok' => '',
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;
	$limit_save = $limit;

	$sql = "SELECT
	            *,
				'' AS `note`
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `".($filter['sort'] == 2 ? 'zayav_status_dtime' : 'dtime_add')."` ".($filter['desc'] ? 'ASC' : 'DESC')."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$r['cart'] = array();
		$zayav[$r['id']] = $r;
	}

	$sql = "SELECT * FROM `zayav_cartridge` WHERE `zayav_id` IN (".implode(',', array_keys($zayav)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zayav[$r['zayav_id']]['cart'][$r['cartridge_id']] = '<b>'._cartridgeName($r['cartridge_id']).'</b>';

	if(!$filter['client_id'])
		$zayav = _clientLink($zayav, 0, 1);

	foreach($zayav as $id => $r) {
		$diff = $r['accrual_sum'] - $r['oplata_sum'];
		$send['spisok'] .=
		'<div class="zayav_unit cart" id="u'.$id.'" style="background-color:#'._zayavStatusColor($r['zayav_status']).'" val="'.$id.'">'.
			'<h2>#'.$r['nomer'].'</h2>'.
			'<a class="name">Картридж'.(count($r['cart']) > 1 ? 'и' : '').' '.implode(', ', $r['cart']).'</a>'.
			'<table class="utab">'.
				'<tr><td valign=top>'.
				(!$filter['client_id'] ? '<tr><td class="label">Клиент:<td>'.$r['client_link'] : '').
				'<tr><td class="label">Дата подачи:'.
					'<td>'.FullData($r['dtime_add'], 1).
						($r['accrual_sum'] || $r['oplata_sum'] ?
							'<div class="balans'.($diff ? ' diff' : '').'">'.
								'<span class="acc'._tooltip('Начислено', -39).$r['accrual_sum'].'</span>/'.
								'<span class="opl'._tooltip($diff ? ($diff > 0 ? 'Недо' : 'Пере').'плата '.abs($diff).' руб.' : 'Оплачено', -17, 'l').$r['oplata_sum'].'</span>'.
							'</div>'
						: '').
		'</table>'.
		'</div>';
	}

	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit_save : $c;
		$send['spisok'] .=
			'<div class="_next" val="'.($page + 1).'">'.
				'<span>Показать ещё '.$c.' заяв'._end($c, 'ку', 'ки', 'ок').'</span>'.
			'</div>';
	}
	return $send;
}//zayav_cartridge_spisok()

function zayav_cartridge_info($z) {
	$zayav_id = $z['id'];
	$status = _zayavStatusName();
	unset($status[0]);

	$expense = zayav_expense_spisok($z, 'all');
	$history = history(array('zayav_id'=>$zayav_id));

	return
	'<script type="text/javascript">'.
		'var STATUS='._selJson($status).','.
			'ZAYAV={'.
				'id:'.$zayav_id.','.
				'cartridge:1,'.
				'nomer:'.$z['nomer'].','.
				'head:"№<b>'.$z['nomer'].'</b>",'.
				'client_id:'.$z['client_id'].','.
				'cartridge_count:'.$z['cartridge_count'].','.
				'status:'.$z['zayav_status'].
			'};'.
	'</script>'.

	'<div id="zayav-info">'.
		'<div id="dopLinks">'.
			'<a class="img_del delete'.(!empty($money) ?  ' dn': '').'"></a>'.
			'<a class="link info sel">Информация</a>'.
			'<a class="link zc-edit">Редактирование</a>'.
			'<a class="link acc_add">Начислить</a>'.
			'<a class="link income-add">Принять платёж</a>'.
			'<a class="link hist">История</a>'.
		'</div>'.

		'<table class="itab">'.
			'<tr class="z-info"><td id="left">'.

				'<div class="headName">'.
					'Заявка №'.$z['nomer'].' - заправка картриджей'.
					'<a href="'.APP_HTML.'/view/kvit_cartridge.php?'.VALUES.'&id='.$zayav_id.'" class="img_xls'._tooltip('Распечатать квитанцию в xls', -168, 'r').'</a>'.
					'<a href="'.APP_HTML.'/view/kvit_schet.php?'.VALUES.'&id='.$zayav_id.'" class="img_doc"></a>'.
				'</div>'.
				'<table class="tabInfo">'.
					'<tr><td class="label r">Клиент:	 <td>'._clientLink($z['client_id'], 0, 1).
					'<tr><td class="label r">Дата приёма:'.
						'<td class="dtime_add'._tooltip('Заявку вн'.(_viewer($z['viewer_id_add'], 'sex') == 1 ? 'есла' : 'ёс').' '._viewer($z['viewer_id_add'], 'name'), -70).FullDataTime($z['dtime_add']).
					'<tr><td class="label r">Статус:'.
						'<td><div id="status" style="background-color:#'._zayavStatusColor($z['zayav_status']).'" class="cartridge_status">'.
								_zayavStatusName($z['zayav_status']).
							'</div>'.
							'<div id="status_dtime">от '.FullDataTime($z['zayav_status_dtime'], 1).'</div>'.
					'<tr><td class="label r">Количество:<td><b>'.$z['cartridge_count'].'</b> шт.'.
					'<tr><td class="label r top">Список:<td id="cart-tab">'.zayav_cartridge_info_tab($zayav_id).
				'</table>'.

	'<div class="headBlue">Расходы<div id="ze-edit" class="img_edit'._tooltip('Изменить расходы по заявке', -166, 'r').'</div></div>'.
	'<div id="ze_acc">'.zayav_acc_sum($z).'</div>'.
	$expense['html'].

				_vkComment('zayav', $z['id']).
				'<div class="headBlue mon">Начисления и платежи'.
					'<a class="add income-add">Принять платёж</a>'.
					'<em>::</em>'.
					'<a class="add acc_add">Начислить</a>'.
				'</div>'.
				'<div id="money_spisok">'.zayav_info_money($zayav_id).'</div>'.

			'<tr class="z-hist"><td>'.
				'<div class="headName">Заявка №'.$z['nomer'].' - история действий</div>'.
				$history['spisok'].
		'</table>'.
	'</div>';
}//zayav_cartridge_info()
function zayav_cartridge_info_tab($zayav_id) {//список картриджей в инфо по заявке
	$sql = "SELECT * FROM `zayav_cartridge` WHERE `zayav_id`=".$zayav_id." ORDER BY `id`";
	$q = query($sql);
	$send = '<table class="_spisok _money">'.
		'<tr>'.
			'<th>Наименование'.
			'<th>Стоимость'.
			'<th>Дата<br />выполнения'.
			'<th>Примечание'.
			'<th>';

	while($r = mysql_fetch_assoc($q)) {
		$prim = array();
		if($r['filling'])
			$prim[] = 'заправлен';
		if($r['restore'])
			$prim[] = 'восстановлен';
		if($r['chip'])
			$prim[] = 'заменён чип';
		$prim = !empty($prim) ? implode(', ', $prim) : '';
		$prim .= ($prim && $r['prim'] ? ', ' : '').'<u>'.$r['prim'].'</u>';
		$send .=
			//'<tr style="background-color:#'._zayavStatusColor($r['status']).'" val="'.$r['id'].'">'.
			'<tr val="'.$r['id'].'"'.($r['filling'] || $r['restore'] || $r['chip'] ? ' class="ready"' : '').'>'.
				'<td class="cart-name"><b>'._cartridgeName($r['cartridge_id']).'</b>'.
				'<td class="cost">'.($r['cost'] ? $r['cost'] : '').
				'<td class="dtime">'.($r['dtime_ready'] != '0000-00-00 00:00:00' ? FullDataTime($r['dtime_ready']) : '').
				'<td class="cart-prim">'.$prim.
				'<td class="ed">'.
					'<div class="img_edit cart-edit'._tooltip('Изменить', -33).'</div>'.
					'<div class="img_del cart-del'._tooltip('Удалить', -29).'</div>'.
					'<input type="hidden" class="cart_id" value="'.$r['cartridge_id'].'" />'.
					'<input type="hidden" class="filling" value="'.$r['filling'].'" />'.
					'<input type="hidden" class="restore" value="'.$r['restore'].'" />'.
					'<input type="hidden" class="chip" value="'.$r['chip'].'" />';
	}

	$send .= '<tr><td colspan="5" class="_next" id="cart-add">'.
				'<span>Добавить картриджи</span>';

	$send .= '</table>';

	return $send;
}//zayav_cartridge_info_tab()



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
			foreach($ass[$r['id']] as $id) {
				$arr[$id]['zp_link'] =
					'<a val="'.$r['id'].'" class="go-zp-info">'.
						'<b>'._zpName($r['name_id']).'</b> для '.
						_deviceName($r['base_device_id'], 1).
						_vendorName($r['base_vendor_id']).
						_modelName($r['base_model_id']).
					'</a>';
				$arr[$id]['zp_short']  =
					'<a val="'.$r['id'].'" class="go-zp-info">'.
						'<b>'._zpName($r['name_id']).'</b> '.
						_vendorName($r['base_vendor_id']).
						_modelName($r['base_model_id']).
					'</a>';
			}
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
				".$zp['base_device_id'].",
				".$zp['base_vendor_id'].",
				".$zp['base_model_id'].",
				".$zp['bu'].",
				'".addslashes($zp['version'])."',
				".$zp['color_id'].",
				".$zp['compat_id'].",
				".VIEWER_ID.",
				'".addslashes(_modelName($zp['base_model_id']).' '.$zp['version'])."'
			)";
	query($sql);
	return mysql_insert_id();
}//zpAddQuery()

function zpFilter($v) {
	return array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? intval($v['page']) : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? intval($v['limit']) : 100,
		'sort' => !empty($v['sort']) && preg_match(REGEXP_NUMERIC, $v['sort']) ? intval($v['sort']) : 0,
		'find' => !empty($v['find']) ? htmlspecialchars(trim($v['find'])) : '',
		'menu' => !empty($v['menu']) && preg_match(REGEXP_NUMERIC, $v['menu']) ? intval($v['menu']) : 0,
		'name' => !empty($v['name']) && preg_match(REGEXP_NUMERIC, $v['name']) ? intval($v['name']) : 0,
		'device' => !empty($v['device']) && preg_match(REGEXP_NUMERIC, $v['device']) ? intval($v['device']) : 0,
		'vendor' => !empty($v['vendor']) && preg_match(REGEXP_NUMERIC, $v['vendor']) ? intval($v['vendor']) : 0,
		'model' => !empty($v['model']) && preg_match(REGEXP_NUMERIC, $v['model']) ? intval($v['model']) : 0,
		'bu' => !empty($v['bu']) && preg_match(REGEXP_BOOL, $v['bu']) ? intval($v['bu']) : 0,
	);
}//zpFilter()
function zp_spisok($v) {
	$filter = zpFilter($v);
	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`c`.`id`";

	if(empty($filter['find']) && !$filter['model'])
		$cond .= " AND (!`compat_id` OR `compat_id`=`c`.`id`)";
	if(!empty($filter['find'])) {
		$cond .= " AND `find` LIKE '%".$filter['find']."%'";
		$reg = '/('.$filter['find'].')/i';
	}
	switch($filter['menu']) {
		case '1':
			$sql = "SELECT `zp_id` FROM `zp_avai` WHERE `ws_id`=".WS_ID;
			$ids = query_ids($sql);
			$cond .= " AND `c`.`id` IN (".$ids.")";
			break;
		case '2':
			$sql = "SELECT `zp_id` FROM `zp_avai` WHERE `ws_id`=".WS_ID;
			$ids = query_ids($sql);
			$cond .= " AND `c`.`id` NOT IN (".$ids.")";
			break;
		case '3':
			$sql = "SELECT `zp_id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." GROUP BY `zp_id`";
			$ids = query_ids($sql);
			$cond .= " AND `c`.`id` IN (".$ids.")";
			break;
		case '4':
			return zp_price($v);
			break;
	}
	if($filter['name'])
		$cond .= " AND `name_id`=".$filter['name'];
	if($filter['device'])
		$cond .= " AND `base_device_id`=".$filter['device'];
	if($filter['vendor'])
		$cond .= " AND `base_vendor_id`=".$filter['vendor'];
	if($filter['model'])
		$cond .= " AND `base_model_id`=".$filter['model'];
	if($filter['bu'])
		$cond .= " AND `bu`";
	$sort = "`s`.`name`,`d`.`sort`,`v`.`sort`,`c`.`find`";
	if($filter['sort'])
		$sort = "`c`.`id` DESC";

	$all = query_value("SELECT COUNT(`c`.`id`) FROM `zp_catalog` `c` WHERE ".$cond);
	if(!$all)
		return array(
			'all' => 0,
			'result' => 'Запчастей не найдено',
			'spisok' => '<div class="_empty">Запчастей не найдено</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => 'Показан'._end($all, 'а ', 'о ').$all.' запчаст'._end($all, 'ь', 'и', 'ей').
					($filter['menu'] == 3 ? '<a id="xls-zakaz" href="'.APP_HTML.'/view/xls_zakaz.php?'.VALUES.'">Экспорт в xsl</a>' : ''),
		'spisok' => '',
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT
	            `c`.*,
	            0 AS `avai`,
	            0 AS `zakaz`,
	            '' AS `zz`,
				`p`.`cena`
			FROM `zp_catalog` `c`

				LEFT JOIN `setup_zp_name` `s`
				ON `c`.`name_id`=`s`.`id`

				LEFT JOIN `base_device` `d`
				ON `c`.`base_device_id`=`d`.`id`

				LEFT JOIN `base_vendor` `v`
				ON `c`.`base_vendor_id`=`v`.`id`

				LEFT JOIN `zp_price` `p`
				ON `c`.`price_id`=`p`.`id`

			WHERE ".$cond."
			ORDER BY ".$sort."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$ids = array();
	$compat = array();
	$img = array();
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
		$img[] = 'zp'.$r['id'];
		$img[] = 'zp'.$r['compat_id'];
		$spisok[$r['id']] = $r;
	}

	// количество по наличию
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

	// количество по заказу
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

	$send['spisok'] =
		$page == 1 ?
		'<table class="_spisok">'.
			'<tr><th>Наименование'.
				'<th>Нал.'.
				'<th>Заказ'
		: '';
	foreach($spisok as $id => $r) {
		$send['spisok'] .= '<tr val="'.$id.'">'.
			'<td><a href="'.URL.'&p=zp&d=info&id='.$id.'" class="name">'.
					_zpName($r['name_id']).' '.
					_vendorName($r['base_vendor_id']).$r['model'].
				'</a>'.
				($r['version'] ? '<span class="version">'.$r['version'].'</span>' : '').
				($r['color_id'] ? '<u class="color">'._color($r['color_id']).'</u>' : '').
				($r['cena'] ? '<b class="cena">'.round($r['cena']).'</b>' : '').
			'<td class="zp-avai">'.($r['avai'] ? $r['avai'] : '').
			'<td class="zp-zakaz">'.
				'<tt>—</tt>'.
				'<span class="zcol'.($r['zakaz'] ? '' : ' no').'">'.$r['zakaz'].'</span>'.
				'<tt>+</tt>';
	}

	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'">'.
				'<td colspan="4">'.
					'<span>Показать ещё '.$c.' запчаст'._end($c, 'ь', 'и', 'ей').'</span>';
	}

	$send['spisok'] .= $page == 1 ?  '</table>' : '';

	return $send;
}//zp_spisok()
function zp_price($v) {
	$filter = zpFilter($v);
	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`id`";

	if(!empty($filter['find'])) {
		$cond .= " AND `name` LIKE '%".$filter['find']."%'";
		$reg = '/('.$filter['find'].')/i';
	}

	$all = query_value("SELECT COUNT(`id`) FROM `zp_price` WHERE ".$cond);
	if(!$all)
		return array(
			'all' => 0,
			'result' => 'Запчастей не найдено',
			'spisok' => '<div class="_empty">Запчастей не найдено</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => 'Показан'._end($all, 'а ', 'о ').$all.' запчаст'._end($all, 'ь', 'и', 'ей'),
		'spisok' => '',
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT
				*,
				0 AS `changed`
			FROM `zp_price`
			WHERE ".$cond."
			ORDER BY `name`
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if(!empty($filter['find'])) {
			if(preg_match($reg, $r['name']))
				$r['name'] = preg_replace($reg, "<em>\\1</em>", $r['name'], 1);
		}
		$spisok[$r['id']] = $r;
	}

	$sql = "SELECT *
			FROM `zp_price_upd`
			WHERE `price_id` IN (".implode(',', array_keys($spisok)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['price_id']]['changed'] = 1;

	$send['spisok'] =
		$page == 1 ?
		'<table class="_spisok">'.
			'<tr><th>Код'.
				'<th>Наименование.'.
				'<th>Цена'.
				'<th>Заказ'
		: '';
	foreach($spisok as $id => $r) {
		$send['spisok'] .= '<tr val="'.$id.'">'.
			'<td class="articul">'.($r['avai'] ? $r['articul'] : '<s>'.$r['articul'].'</s>').
			'<td class="name">'.
				($r['changed'] ? '<a class="price-info" val="'.$id.'">'.$r['name'].'</a>' : '<div class="nam">'.$r['name'].'</div>').
			'<td class="price-cena">'.round($r['cena']).
			'<td class="zp-zakaz">';
			//	'<tt>—</tt>'.
			//	'<span class="zcol'.($r['zakaz'] ? '' : ' no').'">'.$r['zakaz'].'</span>'.
			//	'<tt>+</tt>';
	}

	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'">'.
				'<td colspan="4">'.
					'<span>Показать ещё '.$c.' запчаст'._end($c, 'ь', 'и', 'ей').'</span>';
	}

	$send['spisok'] .= $page == 1 ?  '</table>' : '';

	return $send;
}//zp_price()

function zp_list($v) {
	$data = zp_spisok($v);
	$filter = $data['filter'];
	return
		'<div id="zp">'.
			'<div id="zp-head">'.
				'<table id="head-t"><tr>'.
					'<td id="td-find"><div id="find"></div>'.
					'<td><input type="hidden" id="zp_menu" value="'.$filter['menu'].'" />'.
					'<td><div class="vkButton"><button>Внести новую запчасть</button></div>'.
				'</table>'.
				'<table  id="zp-filter"><tr>'.
					'<td id="td-name"><input type="hidden" id="zp_name" value="'.$filter['name'].'" />'.
					'<td id="td-dev"><div id="dev"></div>'.
					'<td id="td-bu">'._check('bu', 'Б/у', $filter['bu']).
						'<a class="clear">Очистить фильтр</a>'.
				'</table>'.
			'</div>'.
			'<div id="sort">Порядок: <input type="hidden" id="zp_sort" value="'.$filter['sort'].'" /></div>'.
			'<div class="result">'.$data['result'].'</div>'.
			'<div id="zp-spisok">'.$data['spisok'].'</div>'.
			'<script type="text/javascript">'.
				'var ZP={'.
					'find:"'.addslashes($filter['find']).'",'.
					'device:'.$filter['device'].','.
					'vendor:'.$filter['vendor'].','.
					'model:'.$filter['model'].
				'};'.
			'</script>'.
		'</div>';
}//zp_list()


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

	$compatSpisok = zp_compat_spisok($zp_id, $compat_id);
	$compatCount = count($compatSpisok);

	$price = $zp['price_id'] ? query_assoc("SELECT * FROM `zp_price` WHERE `id`=".$zp['price_id']) : array();

	return
	'<script type="text/javascript">'.
		'var ZP={'.
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
			'images:"'.addslashes(_imageAdd(array('owner'=>'zp'.$compat_id))).'",'.
			'price_id:'.$zp['price_id'].
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
						($zp['price_id'] ?
							'<tr><td class="label top">Прайс:'.
								'<td><div class="price-name">'.
										'<u>'.$price['articul'].'</u>: '.
										$price['name'].
										' - <b>'.round($price['cena']).'</b>'.
									'</div>'
						: '').
						//'<tr><td class="label">id:<td>'.$zp['id'].
						//'<tr><td class="label">compat_id:<td>'.$zp['compat_id'].
					'</table>'.
					'<div class="avai'.($avai ? '' : ' no').'">'.($avai ? 'В наличии '.$avai.' шт.' : 'Нет в наличии.').'</div>'.
					'<div class="added">Добавлено в каталог '.FullData($zp['dtime_add'], 1).'</div>'.
					'<div class="headBlue">Движение</div>'.
					'<div class="move">'.zp_move($compat_id).'</div>'.
				'<td class="right">'.
					'<div id="foto">'.
						_imageGet(array(
							'owner' => 'zp'.$compat_id,
							'size' => 'b',
							'x' => 200,
							'y' => 320,
							'view' => 1
						)).
					'</div>'.
					'<div class="rightLink">'.
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
				(!$n++ && $page == 1 ? '<div val="'.$r['id'].'" class="img_del'._tooltip('Удалить запись', -50).'</div>' : '').
				$type[$r['type']].' <b>'.$count.'</b> шт. '.
				($summa ? 'на сумму '.$summa.' руб.'.($count > 1 ? ' <span class="cenaed">('.$cena.' руб./шт.)</span> ' : '') : '').
				($r['zayav_id'] ? 'по заявке '.$r['zayav_link'].'.' : '').
				($r['client_id'] ? 'клиенту '.$r['client_link'].'.' : '').
			($r['prim'] ? '<div class="prim">'.$r['prim'].'</div>' : '').
			'<div class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -1, 'l').FullDataTime($r['dtime_add']).'</div>'.
		'</div>';
	}
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$move .= '<div class="_next" val="'.($page + 1).'"><span>Показать ещё '.$c.' запис'._end($c, 'ь', 'и', 'ей').'</span></div>';
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
