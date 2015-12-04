<?php
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
function zayavStatusButton($z, $class) {
	return
		'<div id="zayav-status-button">'.
			'<h1 style="background-color:#'._zayavStatusColor($z['zayav_status']).'" class="'.$class.'">'.
				_zayavStatusName($z['zayav_status']).
			'</h1>'.
			'<span>от '.FullDataTime($z['zayav_status_dtime'], 1).'</span>'.
		'</div>';
}//zayavStatusButton()
function zayavStatusChange($zayav_id, $status) {//изменение статуса заявки и внесение истории (для внесения начисления)
	$sql = "SELECT *
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `id`=".$zayav_id;
	$z = query_assoc($sql);

	if($z['zayav_status'] != $status) {
		$sql = "UPDATE `zayav`
				SET `zayav_status`=".$status.",`zayav_status_dtime`=CURRENT_TIMESTAMP
				WHERE `id`=".$zayav_id;
		query($sql);
		_history(array(
			'type_id' => 71,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'v1' => $z['zayav_status'],
			'v2' => $status,
		));
	}
}//zayavStatusChange()
function _zayavValToList($arr) {//вставка данных заявок в массив по zayav_id
	$ids = array();
	$arrIds = array();
	foreach($arr as $key => $r)
		if(!empty($r['zayav_id'])) {
			$ids[$r['zayav_id']] = 1;
			$arrIds[$r['zayav_id']][] = $key;
		}
	if(empty($ids))
		return $arr;

	$sql = "SELECT *
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `id` IN (".implode(',', array_keys($ids)).")";
	$zayav = query_arr($sql);

	if(!isset($r['client_phone'])) {
		foreach($zayav as $r)
			foreach($arrIds[$r['id']] as $id)
				$arr[$id] += array('client_id' => $r['client_id']);
		$arr = _clientValToList($arr);
	}

	foreach($zayav as $r) {
		foreach($arrIds[$r['id']] as $id) {
			$dolg = $r['accrual_sum'] - $r['oplata_sum'];
			$arr[$id] += array(
				'zayav_link' =>
					'<a href="'.URL.'&p=zayav&d=info&id='.$r['id'].'" class="zayav_link">'.
						'№'.$r['nomer'].
						'<div class="tooltip">'._zayavTooltip($r, $arr[$id]).'</div>'.
					'</a>',
				'zayav_color' => //подсветка заявки на основании статуса
					'<a href="'.URL.'&p=zayav&d=info&id='.$r['id'].'" class="zayav_link color" style="background-color:#'._zayavStatusColor($r['zayav_status']).'">'.
						'№'.$r['nomer'].
						'<div class="tooltip">'._zayavTooltip($r, $arr[$id]).'</div>'.
					'</a>',
				'zayav_dolg' => $dolg ? '<span class="zayav-dolg'._tooltip('Долг по заявке', -45).$dolg.'</span>' : ''
			);
		}
	}

	return $arr;
}//_zayavValToList()
function _zayavCountToClient($spisok) {//прописывание квадратиков с количеством заявок в список клиентов
	$ids = implode(',', array_keys($spisok));
	/*
	// общее количество заявок
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
*/
	//заявки, ожидающие выполнения
	$sql = "SELECT
				`client_id` AS `id`,
				COUNT(`id`) AS `count`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `zayav_status`=1
			  AND `client_id` IN (".$ids.")
			GROUP BY `client_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['zayav_wait'] = $r['count'];

	//выполненные заявки
	$sql = "SELECT
				`client_id` AS `id`,
				COUNT(`id`) AS `count`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `zayav_status`=2
			  AND `client_id` IN (".$ids.")
			GROUP BY `client_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['zayav_ready'] = $r['count'];

	//отменённые заявки
	$sql = "SELECT
				`client_id` AS `id`,
				COUNT(`id`) AS `count`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `zayav_status`=3
			  AND `client_id` IN (".$ids.")
			GROUP BY `client_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['zayav_fail'] = $r['count'];

	return $spisok;
}//_zayavCountToClient()
function _zayavTooltip($z, $v) {
	return $html =
		'<table>'.
			'<tr><td><div class="image">'._zayavImg($z).'</div>'.
				'<td class="inf">'.
					'<div style="background-color:#'._zayavStatusColor($z['zayav_status']).'" '.
						'class="tstat'._tooltip('Статус заявки: '._zayavStatusName($z['zayav_status']), -7, 'l').
					'</div>'.
					_deviceName($z['base_device_id']).
					'<div class="tname">'._vendorName($z['base_vendor_id'])._modelName($z['base_model_id']).'</div>'.
/*
		($z['cartridge'] ? '<b>Картриджи</b><br />'.
			$z['cartridge_count'].' шт.'
			:

		).
*/
			'<table>'.
				'<tr><td class="label top">Клиент:'.
					'<td>'.$v['client_name'].
						($v['client_phone'] ? '<br />'.$v['client_phone'] : '').
				'<tr><td class="label">Баланс:'.
					'<td><span class="bl" style=color:#'.($v['client_balans'] < 0 ? 'A00' : '090').'>'.$v['client_balans'].'</span>'.
			'</table>'.
		'</table>';
}
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
function zayavPlaceCheck($zayav_id, $place_id=0, $place_name='') {// Обновление местонахождения заявки

	// - внесение нового местонахождения, если place_id = 0
	// - обновление place_id, если отличается от текущего в заявке
	
	if(!_num($zayav_id))
		return 0;
	if(!$place_id && empty($place_name))
		return 0;

	$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
	if(!$z = query_assoc($sql))
		return 0;

	$gv = 0;

	if(!$place_id && !empty($place_name)) {
		$sql = "SELECT `id`
			FROM `zayav_device_place`
			WHERE `ws_id`=".WS_ID."
			  AND `place`='".$place_name."'
			LIMIT 1";
		if (!$place_id = query_value($sql)) {
			$sql = "INSERT INTO `zayav_device_place` (
					`ws_id`,
					`place`
				) VALUES (
					".WS_ID.",
					'".addslashes($place_name)."'
				)";
			query($sql);
			$place_id = mysql_insert_id();
			$gv++;
		}
	}
	
	if($place_id != $z['device_place']) {
		$sql = "UPDATE `zayav`
				SET `device_place`=".$place_id.",
					`device_place_dtime`=CURRENT_TIMESTAMP
				WHERE `id`=".$zayav_id;
		query($sql);

		if($z['device_place']) { //история вносится, если это не первое внесение заявки
			_history(array(
				'type_id' => 29,
				'client_id' => $z['client_id'],
				'zayav_id' => $zayav_id,
				'v1' => '<table>'._historyChange('', _devPlace($z['device_place']), _devPlace($place_id)).'</table>'
			));

			if($place_id == 2)
				_vkCommentAdd('zayav', $zayav_id, 'Передано клиенту.');

			//удаление пустых местонахождений
			$sql = "DELETE FROM `zayav_device_place` WHERE `id` IN (
						SELECT id FROM (
							SELECT
								`p`.`id`,
								COUNT(`z`.`id`) `count`
							FROM `zayav_device_place` `p`
								LEFT JOIN  `zayav` `z`
								ON `p`.`id`=`z`.`device_place`
							WHERE `p`.`ws_id`=".WS_ID."
							GROUP BY `p`.`id`
						) `t` WHERE !`count`
					)";
			query($sql);
			$gv += mysql_affected_rows();
		}

		if($gv)
			GvaluesCreate();
	}

	return $place_id;
}//zayavPlaceCheck()

function zayav_add($v=array()) {
/*	$sql = "SELECT `id`,`name` FROM `setup_fault` ORDER BY SORT";
	$q = query($sql);
	$fault = '<table>';
	$k = 0;
	while($r = mysql_fetch_assoc($q))
		$fault .= (++$k%2 ? '<tr>' : '').'<td>'._check('f_'.$r['id'], $r['name']);
	$fault .= '</table>';
*/
	$client_id = 0;
	$back = 'zayav';

	if(!empty($_GET['back'])) {
		$back = explode('_', $_GET['back']);
		if($back[0] == 'client') {
			$client_id = _num(@$back[1]);
			$back = 'client'.($client_id ? '&d=info&id='.$client_id : '');
		} else
			$back = $back[0];
	}

	return
//	'<script type="text/javascript">'.
//		'var FAULT='.query_selJson("SELECT `id`,`name` FROM `setup_fault` ORDER BY SORT").
//	'</script>'.
	'<div id="zayavAdd">'.
		'<div class="headName">Внесение новой заявки</div>'.
		'<table style="border-spacing:8px">'.
			'<tr><td class="label">Клиент:		    <td><input type="hidden" id="client_id" value="'.$client_id.'" />'.
			'<tr><td class="label topi">Устройство: <td><table><td id="dev"><td id="device_image"></table>'.
				'<tr><td class="label">IMEI:		<td><input type="text" id="imei" maxlength="20"'.(isset($v['imei']) ? ' value="'.$v['imei'].'"' : '').' />'.
			'<tr><td class="label">Серийный номер:  <td><input type="text" id="serial" maxlength="30"'.(isset($v['serial']) ? ' value="'.$v['serial'].'"' : '').' />'.
			'<tr><td class="label">Цвет:<td id="colors">'.
			'<tr class="tr_equip dn"><td class="label">Комплектация:<td class="equip_spisok">'.
			'<tr><td class="label topi">Местонахождение устройства<br />после внесения заявки:<td><input type="hidden" id="place" value="-1" />'.
			'<tr><td class="label">Диагностика:<td>'._check('diagnost').
//			'<tr><td class="label top">Неисправности:<td id="fault">'.$fault.
			'<tr><td class="label topi">Описание неисправности:<td><textarea id="comm"></textarea>'.
			'<tr><td class="label">Предварительная стоимость:<td><input type="text" class="money" id="pre_cost" maxlength="11" /> руб.'.
			'<tr><td class="label">Срок:<td>'._zayavFinish().
		'</table>'.

		'<div class="vkButton"><button>Внести</button></div>'.
		'<div class="vkCancel" val="'.$back.'"><button>Отмена</button></div>'.
	'</div>';
}//zayav_add()

function zayavCookie($action='set', $v='device') {//сохранение страницы с заявками в куках, чтобы было понятно, куда возвращаться
	$key = APP_ID.'_'.VIEWER_ID.'_zayav_type';

	if($action == 'get') {
		if(empty($_GET['d']))
			return @$_COOKIE[$key];
		return $_GET['d'];
	}

	setcookie($key, $v, time() + 3600, '/');
	return '';
}//zayavCookie()

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
		'diagnost' => 0,
		'diff' => 0,
		'place' => 0,
	);
	$filter = array(
		'page' => _num(@$v['page']) ? $v['page'] : 1,
		'limit' => _num(@$v['limit']) ? $v['limit'] : 20,
		'client_id' => _num(@$v['client_id']),
		'find' => trim(@$v['find']),
		'sort' => _num(@$v['sort']),
		'desc' => _bool(@$v['desc']),
		'status' => _num(@$v['status']),
		'finish' => preg_match(REGEXP_DATE, @$v['finish']) ? $v['finish'] : $default['finish'],
		'zpzakaz' => _num(@$v['zpzakaz']),
		'executer' => intval(@$v['executer']),
		'device' => @$v['device'],
		'vendor' => _num(@$v['vendor']),
		'model' => _num(@$v['model']),
		'diagnost' => _bool(@$v['diagnost']),
		'diff' => _bool(@$v['diff']),
		'place' => intval(@$v['place']),
		'clear' => ''
	);
	foreach($default as $k => $r)
		if($r != $filter[$k]) {
			$filter['clear'] = '<a class="clear">Очистить фильтр</a>';
			break;
		}
	return $filter;
}//zayavFilter()
function zayav_spisok($v) {
	$filter = zayavFilter($v);
	$filter = _filterJs('ZAYAV', $filter);

	define('ZAYAV_PAGE1', $filter['page'] == 1);


	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`ws_id`=".WS_ID."
		 AND !`deleted`
		 AND !`cartridge`
		 AND `zayav_status`";

	if($filter['find']) {
		$engRus = _engRusChar($filter['find']);
		$cond .= " AND (`find` LIKE '%".$filter['find']."%'".
			($engRus ? " OR `find` LIKE '%".$engRus."%'" : '').")";
		$reg = '/('.$filter['find'].')/i';
		if($engRus)
			$regEngRus = '/('.$engRus.')/i';

		if(ZAYAV_PAGE1 && _num($filter['find']))
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
			$cond .= " AND `base_device_id` IN (".$filter['device'].")";
		if($filter['vendor'])
			$cond .= " AND `base_vendor_id`=".$filter['vendor'];
		if($filter['model'])
			$cond .= " AND `base_model_id`=".$filter['model'];
		if($filter['diagnost'])
			$cond .= " AND `zayav_status`=1 AND `diagnost`";
		if($filter['place']) {
			$cond .= " AND `device_place`=".$filter['place'];

		}
	}

	$sql = "SELECT COUNT(*) FROM `zayav` USE INDEX (`i_zayav_status`) WHERE ".$cond;
	$all = query_value($sql);

	$zayav = array();
	$images = array();
	if(isset($nomer)) {
		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND `zayav_status` AND `nomer`=".$nomer;
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
			'spisok' => $filter['js'].'<div class="_empty">Заявок не найдено</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => 'Показан'._end($all, 'а', 'о').' '.$all.' заяв'._end($all, 'ка', 'ки', 'ок').$filter['clear'],
		'spisok' => $filter['js'],
		'filter' => $filter
	);

	if(!ZAYAV_PAGE1)
		setcookie('zback_spisok_page', $page, time() + 3600, '/');
	if(!empty($_COOKIE['zback_info']) && ZAYAV_PAGE1 && !empty($_COOKIE['zback_spisok_page']) && $_COOKIE['zback_spisok_page'] > 1) {
		setcookie('zback_info', '', time() - 3600, '/');
		$page = $_COOKIE['zback_spisok_page'];
		$limit *= $_COOKIE['zback_spisok_page'];
	}
	$sql = "SELECT
	            *,
				'' AS `note`,
				'' AS `schet`
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `".($filter['sort'] == 2 ? 'zayav_status_dtime' : 'dtime_add')."` ".($filter['desc'] ? 'ASC' : 'DESC')."
			LIMIT "._startLimit($filter);
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
		$zayav = _clientValToList($zayav);

	$zayav = _schetToZayav($zayav);

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
		$diff = round($r['accrual_sum'] - $r['oplata_sum'], 2);
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
   (!$filter['client_id'] ? '<tr><td class="label">Клиент:<td>'.$r['client_go'] : '').
							'<tr><td class="label">Дата подачи:'.
								'<td>'.FullData($r['dtime_add'], 1).
			($r['zayav_status'] == 2 ? '<b class="date-ready'._tooltip('Дата выполнения', -47).FullData($r['zayav_status_dtime'], 1, 1).'</b>' : '').
									($r['accrual_sum'] || round($r['oplata_sum'], 2) ?
										'<div class="balans'.($diff ? ' diff' : '').'">'.
											'<span class="acc'._tooltip('Начислено', -39).$r['accrual_sum'].'</span>/'.
											'<span class="opl'._tooltip($diff ? ($diff > 0 ? 'Недо' : 'Пере').'плата '.abs($diff).' руб.' : 'Оплачено', -17, 'l').round($r['oplata_sum'], 2).'</span>'.
										'</div>'
									: '').
 			  ($r['imei'] ? '<tr><td class="label">IMEI:<td>'.$r['imei'] : '').
		    ($r['serial'] ? '<tr><td class="label">Серийный номер:<td>'.$r['serial'] : '').
	(isset($zpZakaz[$id]) ? '<tr><td class="label">Заказаны з/п:<td class="zz">'.implode(', ', $zpZakaz[$id]) : '').
			 ($r['schet'] ? '<tr><td class="label topi">Счета на оплату:<td>'.$r['schet'] : '').
						'</table>'.
					'<td class="image">'.$img.
				'</table>'.
				'<div class="note">'.@$r['note'].'</div>'.
			'</div>';
	}

	 $send['spisok'] .= _next($filter + array(
			'type' => 2,
			'all' => $all
		));
	return $send;
}//zayav_spisok()
function zayav_list($v) {
	zayavCookie();

	$data = zayav_spisok($v);
	$v = $data['filter'];

	$status = _zayavStatusName();
	$status[1] .= '<div id="srok">Срок: '._zayavFinish($v['finish']).'</div>';

	return
	'<div id="zayav">'.

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
						_check('diagnost', 'Диагностика', $v['diagnost']).
						_check('diff', 'Неоплаченные заявки', $v['diff']).
						'<div class="findHead">Заказаны запчасти</div>'.
						_radio('zpzakaz', array(0=>'Все заявки',1=>'Да',2=>'Нет'), $v['zpzakaz'], 1).
						'<div class="findHead">Исполнитель</div><input type="hidden" id="executer" value="'.$v['executer'].'" />'.
						'<div class="findHead">Устройство</div><div id="dev"></div>'.
						'<div class="findHead">Нахождение устройства</div><input type="hidden" id="place" value="'.$v['place'].'" />'.
					'</div>'.
		'</table>'.
		'<script type="text/javascript">'.
			'var Z={'.
				'device_ids:['._zayavBaseDeviceIds().'],'.
				'vendor_ids:['._zayavBaseVendorIds().'],'.
				'model_ids:['._zayavBaseModelIds().'],'.
				'cookie_id:'.(!empty($_COOKIE['zback_info']) ? $_COOKIE['zback_info'] : 0).
			'};'.
		'</script>'.
	'</div>';
}//zayav_list()

function zayavBalansUpdate($zayav_id) {//Обновление баланса заявки
	if(empty($zayav_id))
		return;

	$sql = "SELECT IFNULL(SUM(`sum`),0)
			FROM `_money_accrual`
			WHERE `app_id`=".APP_ID."
			  AND `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `zayav_id`=".$zayav_id;
	$accrual = query_value($sql, GLOBAL_MYSQL_CONNECT);

	$sql = "SELECT IFNULL(SUM(`sum`),0)
			FROM `_money_income`
			WHERE `app_id`=".APP_ID."
			  AND `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `zayav_id`=".$zayav_id;
	$income = query_value($sql, GLOBAL_MYSQL_CONNECT);

	$sql = "SELECT IFNULL(SUM(`sum`),0)
			FROM `_money_refund`
			WHERE `app_id`=".APP_ID."
			  AND `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `zayav_id`=".$zayav_id;
	$refund = query_value($sql, GLOBAL_MYSQL_CONNECT);

	$income -= $refund;

	$sql = "UPDATE `zayav`
			SET `accrual_sum`=".$accrual.",
				`oplata_sum`=".$income."
			WHERE `ws_id`=".WS_ID."
			  AND `id`=".$zayav_id;
	query($sql);
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
function zayav_info() {
	zayavCookie();

	if(!$zayav_id = _num(@$_GET['id']))
		return _err('Страницы не существует');

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
		return _err('Заявки не существует.');

	if($z['cartridge'])
		return zayav_cartridge_info($z);

	define('MODEL', _vendorName($z['base_vendor_id'])._modelName($z['base_model_id']));
	define('DOPL', $z['accrual_sum'] - $z['oplata_sum']);

	$status = _zayavStatusName();
	unset($status[0]);
	$history = _history(array('zayav_id'=>$zayav_id));

	return '<script type="text/javascript">'.
		'var STATUS='._selJson($status).','.
		'ZAYAV={'.
			'id:'.$zayav_id.','.
			'nomer:'.$z['nomer'].','.
			'head:"№<b>'.$z['nomer'].'</b>",'.
			'client_id:'.$z['client_id'].','.
			'client_link:"'.addslashes(_clientVal($z['client_id'], 'link')).'",'.
			'device:'.$z['base_device_id'].','.
			'vendor:'.$z['base_vendor_id'].','.
			'model:'.$z['base_model_id'].','.
			'status:'.$z['zayav_status'].','.
			'dev_place:'.$z['device_place'].','.
			'imei:"'.$z['imei'].'",'.
			'serial:"'.$z['serial'].'",'.
			'color_id:'.$z['color_id'].','.
			'color_dop:'.$z['color_dop'].','.
			'equip:"'.addslashes(devEquipCheck($z['base_device_id'], $z['equip'])).'",'.
			'diagnost:'.$z['diagnost'].','.
			'pre_cost:'.$z['pre_cost'].','.
			'images:"'.addslashes(_imageAdd(array('owner'=>'zayav'.$zayav_id))).'",'.
			'zp_avai:'.zayav_zp_avai($z).','.
//			'worker_zp:'.round(($acc_sum - $expense_sum) * 0.3).
		'},'.
		'PRINT={'.
			'dtime:"'.FullDataTime($z['dtime_add']).'",'.
			'device:"'._deviceName($z['base_device_id']).'<b>'._vendorName($z['base_vendor_id'])._modelName($z['base_model_id']).'</b>",'.
			'color:"'._color($z['color_id'], $z['color_dop']).'",'.
			($z['imei'] ? 'imei:"'.$z['imei'].'",' : '').
			($z['serial'] ? 'serial:"'.$z['serial'].'",' : '').
			($z['equip'] ? 'equip:"'.zayavEquipSpisok($z['equip']).'",' : '').
			'client:"'.addslashes(_clientVal($z['client_id'], 'link')).'",'.
			'telefon:"'._clientVal($z['client_id'], 'phone').'",'.
			'defect:"'.addslashes(str_replace("\n", ' ', query_value("SELECT `txt` FROM `vk_comment` WHERE `status` AND `table_name`='zayav' AND `table_id`=".$zayav_id." AND !`parent_id` ORDER BY `id` DESC"))).'"'.
		'};'.
	'</script>'.
	'<div id="zayav-info">'.
		'<div id="dopLinks">'.
			'<a class="link info sel">Информация</a>'.
			'<a class="link zedit">Редактирование</a>'.
			'<a class="link _accrual-add">Начислить</a>'.
			'<a class="link _income-add">Принять платёж</a>'.
			'<a class="link hist">История</a>'.
		'</div>'.
		'<table class="itab">'.
			'<tr class="z-info"><td id="left">'.
				'<div class="headName">Заявка №'.$z['nomer'].'<input type="hidden" id="zayav-action" /></div>'.
				'<table class="tabInfo">'.
					'<tr><td class="label">Устройство: <td>'._deviceName($z['base_device_id']).'<a><b>'.MODEL.'</b></a>'.
					'<tr><td class="label">Клиент:	 <td>'._clientVal($z['client_id'], 'go').
					'<tr><td class="label">Дата приёма:'.
						'<td><span class="'._tooltip('Заявку '.(_viewerAdded($z['viewer_id_add'])), -70).FullDataTime($z['dtime_add']).'</span>'.
  ($z['pre_cost'] ? '<tr><td class="label">Стоимость:<td><b class="'._tooltip('Предварительная стоимость ремонта', -10, 'l').$z['pre_cost'].'</b> руб.' : '').
                    '<tr><td class="label">Срок:<td>'._zayavFinish($z['day_finish']).
                    '<tr><td class="label">Исполнитель:'.
						'<td id="executer_td"><input type="hidden" id="executer_id" value="'.$z['executer_id'].'" />'.
  ($z['zayav_status'] == 1 && $z['diagnost'] ?
					'<tr><td colspan="2">'._button('diagnost-ready', 'Внести результаты диагностики', 300)
  : '').
					'<tr><td class="label">Статус:<td>'.zayavStatusButton($z, 'status_place').
					'<tr class="acc_tr'.($z['accrual_sum'] ? '' : ' dn').'"><td class="label">Начислено: <td><b class="acc">'.$z['accrual_sum'].'</b> руб.'.
					'<tr class="op_tr'.(round($z['oplata_sum'], 2) ? '' : ' dn').'"><td class="label">Оплачено:	<td><b class="op">'.round($z['oplata_sum'], 2).'</b> руб.'.
						'<span class="dopl'.(DOPL ? '' : ' dn')._tooltip('Необходимая доплата', -60).(DOPL > 0 ? '+' : '').DOPL.'</span>'.
				'</table>'.

				'<div id="kvit_spisok">'.zayav_kvit($zayav_id).'</div>'.

				_zayavInfoAccrual($zayav_id).
				_zayav_expense($zayav_id).
				_remind_zayav($zayav_id).
				_zayavInfoMoney($zayav_id).
				_vkComment('zayav', $zayav_id).

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
						'<tr><th>Нахождение:<td><a class="dev_place">'._devPlace($z['device_place']).'</a>'.
					'</table>'.
				'</dev>'.

				'<div class="headBlue">'.
					'<a class="goZp" href="'.URL.'&p=zp&device='.$z['base_device_id'].'&vendor='.$z['base_vendor_id'].'&model='.$z['base_model_id'].'">Список запчастей</a>'.
					'<a class="zpAdd add">добавить</a>'.
				'</div>'.
				'<div id="zpSpisok">'.zayav_zp($z).'</div>'.

			'<tr class="z-hist">'.
				'<td><div class="headName">Заявка №'.$z['nomer'].' - история действий</div>'.
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



// ---===! zayav cartridge !===--- Картриджи

function zayav_cartridge($v) {
	zayavCookie('set', 'cartridge');

	$data = zayav_cartridge_spisok($v);
	$v = $data['filter'];

	$status = _zayavStatusName();

	return
	'<div id="zayav-cartridge">'.

		'<div id="dopLinks">'.
			'<a class="link" href="'.URL.'&p=zayav&d=device">Оборудование</a>'.
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
					'<div class="findHead">Расчёт</div>'.
					_radio('paytype', array(0=>'Все заявки',1=>'Наличный',2=>'Безналиный'), $v['paytype'], 1).
					_check('noschet', 'Счета не выписаны', $v['noschet']).
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
		'status' => 0,
		'paytype' => 0,
		'noschet' => 0
	);
	$filter = array(
		'page' => _num(@$v['page']) ? $v['page'] : 1,
		'limit' => _num(@$v['limit']) ? $v['limit'] : 20,
		'client_id' => _num(@$v['client_id']),
		'sort' => _num(@$v['sort']) ? $v['sort'] : 1,
		'desc' => _bool(@$v['desc']),
		'status' => _num(@$v['status']),
		'paytype' => _num(@$v['paytype']),
		'noschet' => _bool(@$v['noschet']),
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
	$filter = _filterJs('CARTRIDGE', $filter);

	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`ws_id`=".WS_ID." AND !`deleted` AND `cartridge`";

	if($filter['client_id'])
		$cond .= " AND `client_id`=".$filter['client_id'];
	if($filter['status'])
		$cond .= " AND `zayav_status`=".$filter['status'];
	if($filter['paytype'])
		$cond .= " AND `pay_type`=".$filter['paytype'];
	if($filter['noschet'])
		$cond .= " AND !`schet`";

	$r = query_assoc("SELECT COUNT(*) `count`,SUM(`cartridge_count`) `sum` FROM `zayav` WHERE ".$cond);
	$all = $r['count'];

	if(!$all)
		return array(
			'all' => 0,
			'result' => 'Заявок не найдено'.$filter['clear'],
			'spisok' => $filter['js'].'<div class="_empty">Заявок не найдено</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' =>
			'Показан'._end($all, 'а', 'о').' '.$all.' заяв'._end($all, 'ка', 'ки', 'ок').
			'<span id="c-sum">('.$r['sum'].' картридж'._end($r['sum'], '', 'а', 'ей').')</span>'.
			$filter['clear'],
		'spisok' => $filter['js'],
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;

	$zayav = array();

	$sql = "SELECT
	            *,
				'' AS `note`,
				'' AS `schet`
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
		$zayav = _clientValToList($zayav);

	$zayav = _schetToZayav($zayav);


	foreach($zayav as $id => $r) {
		$diff = round($r['accrual_sum'] - $r['oplata_sum'], 2);
		$send['spisok'] .=
		'<div class="zayav_unit" id="u'.$id.'" style="background-color:#'._zayavStatusColor($r['zayav_status']).'" val="'.$id.'">'.
			'<h2>#'.$r['nomer'].'</h2>'.
			'<a class="name">Картридж'.(count($r['cart']) > 1 ? 'и' : '').' '.implode(', ', $r['cart']).'</a>'.
			'<table class="utab">'.
				'<tr><td class="label">Количество:'.
					'<td><b>'.$r['cartridge_count'].'</b> шт.'.
			(!$filter['client_id'] ?
				'<tr><td class="label top">Клиент:<td>'.$r['client_link']
			: '').
				'<tr><td class="label">Дата подачи:'.
					'<td>'.FullData($r['dtime_add'], 1).
						($r['accrual_sum'] || round($r['oplata_sum'], 2) ?
							'<div class="balans'.($diff ? ' diff' : '').'">'.
								'<span class="acc'._tooltip('Начислено', -39).$r['accrual_sum'].'</span>/'.
								'<span class="opl'._tooltip($diff ? ($diff > 0 ? 'Недо' : 'Пере').'плата '.abs($diff).' руб.' : 'Оплачено', -17, 'l').round($r['oplata_sum'], 2).'</span>'.
							'</div>'
						: '').
 ($r['schet'] ? '<tr><td class="label">Счета:<td>'.$r['schet'] : '').
			'</table>'.
		'</div>';
	}

	$send['spisok'] .= _next($filter + array(
			'type' => 2,
			'all' => $all
		));

	return $send;
}//zayav_cartridge_spisok()

function zayav_cartridge_info($z) {
	zayavCookie('set', 'cartridge');

	$zayav_id = $z['id'];
	$status = _zayavStatusName();
	unset($status[0]);

	$history = _history(array('zayav_id'=>$zayav_id));

	return
	'<script type="text/javascript">'.
		'var STATUS='._selJson($status).','.
			'ZAYAV={'.
				'id:'.$zayav_id.','.
				'cartridge:1,'.
				'nomer:'.$z['nomer'].','.
				'head:"№<b>'.$z['nomer'].'</b>",'.
				'client_id:'.$z['client_id'].','.
				'client_link:"'.addslashes(_clientVal($z['client_id'], 'link')).'",'.'cartridge_count:'.$z['cartridge_count'].','.
				'pay_type:'.$z['pay_type'].','.
				'status:'.$z['zayav_status'].
			'};'.
	'</script>'.

	'<div id="zayav-cartridge-info">'.
		'<div id="dopLinks">'.
			'<a class="link info sel">Информация</a>'.
			'<a class="link" id="edit">Редактирование</a>'.
			'<a class="link _accrual-add">Начислить</a>'.
			'<a class="link _income-add">Принять платёж</a>'.
			'<a class="link hist">История</a>'.
		'</div>'.

		'<div class="page">'.
			'<div class="headName">'.
				'Заявка №'.$z['nomer'].' - заправка картриджей'.
				'<input type="hidden" id="zayav-action" />'.
			'</div>'.
			'<table id="tab">'.
				'<tr><td class="label">Клиент:	 <td>'._clientVal($z['client_id'], 'go').
				'<tr><td class="label">Дата приёма:'.
					'<td class="dtime_add'._tooltip('Заявку '.(_viewerAdded($z['viewer_id_add'])), -70).FullDataTime($z['dtime_add']).
                '<tr><td class="label">Расчёт:<td>'._payType($z['pay_type']).
				'<tr><td class="label">Статус:<td>'.zayavStatusButton($z, 'cartridge_status').
				'<tr><td class="label">Количество:<td><b>'.$z['cartridge_count'].'</b> шт.'.
				'<tr><td class="label top">Список:<td id="cart-tab">'.zayav_cartridge_info_tab($zayav_id).
			'</table>'.

			_zayavInfoAccrual($zayav_id).
			_zayav_expense($zayav_id).
			_remind_zayav($zayav_id).
			_zayavInfoMoney($zayav_id).
			_vkComment('zayav', $zayav_id).
		'</div>'.

		'<div class="page dn">'.
			'<div class="headName">Заявка №'.$z['nomer'].' - история действий</div>'.
			$history['spisok'].
		'</div>'.

	'</div>';
}//zayav_cartridge_info()
function zayav_cartridge_info_tab($zayav_id) {//список картриджей в инфо по заявке
	$sql = "SELECT *
 			FROM `zayav_cartridge`
 			WHERE `zayav_id`=".$zayav_id."
 			ORDER BY `id`";
	$spisok = query_arr($sql);

	$spisok = _schetValToList($spisok);

	$send = '<table class="_spisok">'.
		'<tr>'.
			'<th>'.
			'<th>Наименование'.
			'<th>Стоимость'.
			'<th>Дата<br />выполнения'.
			'<th>Примечание'.
			'<th>'.
			'<th>'._check('check_all');

	$n = 1;
	foreach($spisok as $r) {
		$prim = array();
		if($r['filling'])
			$prim[] = 'заправлен';
		if($r['restore'])
			$prim[] = 'восстановлен';
		if($r['chip'])
			$prim[] = 'заменён чип';
		$prim = !empty($prim) ? implode(', ', $prim) : '';
		$prim .= ($prim && $r['prim'] ? ', ' : '').'<u>'.$r['prim'].'</u>';

		$ready = $r['filling'] || $r['restore'] || $r['chip'];

		$send .=
			'<tr val="'.$r['id'].'"'.($ready ? ' class="ready"' : '').'>'.
				'<td class="n">'.($n++).
				'<td class="cart-name"><b>'._cartridgeName($r['cartridge_id']).'</b>'.
				'<td class="cost">'.($r['cost'] ? $r['cost'] : '').
				'<td class="dtime">'.($r['dtime_ready'] != '0000-00-00 00:00:00' ? FullDataTime($r['dtime_ready']) : '').
				'<td class="cart-prim">'.$prim.
				'<td class="ed">'.
					($r['schet_id'] ?
						'<div class="nomer">'.$r['schet_nomer'].'</div>'
						:
						'<div class="img_edit cart-edit'._tooltip('Изменить', -33).'</div>'.
						'<div class="img_del cart-del'._tooltip('Удалить', -29).'</div>'.
						'<input type="hidden" class="cart_id" value="'.$r['cartridge_id'].'" />'.
						'<input type="hidden" class="filling" value="'.$r['filling'].'" />'.
						'<input type="hidden" class="restore" value="'.$r['restore'].'" />'.
						'<input type="hidden" class="chip" value="'.$r['chip'].'" />'
					).
				'<td class="ch">'.($ready && !$r['schet_id'] ? _check('ch'.$r['id']) : '');

	}

	$send .=
		'<tr><td colspan="7" class="_next" id="cart-add">'.
			'<span>Добавить картриджи</span>'.
	'</table>';

	return $send;
}//zayav_cartridge_info_tab()
function zayav_cartridge_for_schet($ids) {
	$sql = "SELECT *
			FROM `zayav_cartridge`
			WHERE `id` IN (".$ids.")
			  AND (`filling` OR `restore` OR `chip`)
			  AND `cost`
			  AND !`schet_id`
			ORDER BY `id`";
	$q = query($sql);
	$schet = array();
	$n = 1;
	while($r = mysql_fetch_assoc($q)) {
		$same = 0;//тут будет номер, с которым будет найдено совпадение
		foreach($schet as $sn => $unit) {
			$diff = 0; // пока различий не обнаружено
			foreach($unit as $key => $val) {
				if($key == 'count')
					continue;
				if($r[$key] != $val) {
					$diff = 1;
					break;
				}
			}
			if(!$diff) { //если различий нет, то запоминание номера и выход
				$same = $sn;
				break;
			}
		}

		if($same)
			$schet[$same]['count']++;
		else {
			$schet[$n] = array(
				'cartridge_id' => $r['cartridge_id'],
				'filling' => $r['filling'],
				'restore' => $r['restore'],
				'chip' => $r['chip'],
				'cost' => $r['cost'],
				'prim' => $r['prim'],
				'count' => 1
			);
			$n++;
		}
	}

	$spisok = array();
	foreach($schet as $r) {
		$prim = array();
		if($r['filling'])
			$prim[] = 'заправка';
		if($r['restore'])
			$prim[] = 'восстановление';
		if($r['chip'])
			$prim[] = 'замена чипа у';

		$txt = implode(', ', $prim).' картриджа '._cartridgeName($r['cartridge_id']).($r['prim'] ? ', '.$r['prim'] : '');
		$txt = mb_ucfirst($txt);

		$spisok[] = array(
			'name' => utf8($txt),
			'count' => $r['count'],
			'cost' => $r['cost'],
			'readonly' => 1
		);
	}
	return $spisok;
}//zayav_cartridge_for_schet()

function zayavCartridgeSchetDel($schet_id) {//отвязка картриджей от счёта при удалении счёта
	$sql = "UPDATE `zayav_cartridge`
			SET `schet_id`=0
			WHERE `schet_id`=".$schet_id;
	query($sql);
}//zayavCartridgeSchetDel()
