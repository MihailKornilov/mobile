<?php
// ---===! zayav !===--- Секция заявок

function zayavCase1() {
	switch(zayavCookie('get')) {
		case 'add':
			$v = array();
			if(isset($_GET['imei']) && preg_match(REGEXP_WORD, $_GET['imei']))
				$v['imei'] = strtoupper(htmlspecialchars(trim($_GET['imei'])));
			if(isset($_GET['serial']) && preg_match(REGEXP_WORD, $_GET['serial']))
				$v['serial'] = strtoupper(htmlspecialchars(trim($_GET['serial'])));
			return zayav_add($v);
		case 'cartridge': return zayav_cartridge(_hashFilter('cartridge'));
		case 'info': return zayav_info();
	}
	return zayav_list(_hashFilter('zayav'));
}

function zayavStatusChange($zayav_id, $status) {//изменение статуса заявки и внесение истории (для внесения начисления)
	$sql = "SELECT *
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `id`=".$zayav_id;
	$z = query_assoc($sql);

	if($z['status'] != $status) {
		$sql = "UPDATE `zayav`
				SET `status`=".$status.",`status_dtime`=CURRENT_TIMESTAMP
				WHERE `id`=".$zayav_id;
		query($sql);
		_history(array(
			'type_id' => 71,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'v1' => $z['status'],
			'v2' => $status,
		));
	}
}
/*
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
					'<a href="'.URL.'&p=zayav&d=info&id='.$r['id'].'" class="zayav_link color"'._zayavStatus($r['status'], 'bg').'>'.
						'№'.$r['nomer'].
						'<div class="tooltip">'._zayavTooltip($r, $arr[$id]).'</div>'.
					'</a>',
				'zayav_dolg' => $dolg ? '<span class="zayav-dolg'._tooltip('Долг по заявке', -45).$dolg.'</span>' : ''
			);
		}
	}

	return $arr;
}
*/
/*
function _zayavTooltip($z, $v) {
	return
		'<table>'.
			'<tr><td><div class="image">'._zayavImg($z).'</div>'.
				'<td class="inf">'.
					'<div'._zayavStatus($z['status'], 'bg').' '.
						'class="tstat'._tooltip('Статус заявки: '._zayavStatus($z['status']), -7, 'l').
					'</div>'.
					_deviceName($z['base_device_id']).
					'<div class="tname">'._vendorName($z['base_vendor_id'])._modelName($z['base_model_id']).'</div>'.

//		($z['cartridge'] ? '<b>Картриджи</b><br />'.
//			$z['cartridge_count'].' шт.'
//			:
//
//		).

			'<table>'.
				'<tr><td class="label top">Клиент:'.
					'<td>'.$v['client_name'].
						($v['client_phone'] ? '<br />'.$v['client_phone'] : '').
				'<tr><td class="label">Баланс:'.
					'<td><span class="bl" style=color:#'.($v['client_balans'] < 0 ? 'A00' : '090').'>'.$v['client_balans'].'</span>'.
			'</table>'.
		'</table>';
}
*/
function _zayavBaseDeviceIds($client_id=0) { //список id устройств, которые используются в заявках
	$ids = array();
	$sql = "SELECT `b`.`id`
			FROM `zayav` `z` USE INDEX(`i_zayav_status`),
				 `base_device` `b`
			WHERE `b`.`id`=`z`.`base_device_id`
			  AND `z`.`status`
			  AND `z`.`ws_id`=".WS_ID."
			  ".($client_id ? "AND `z`.`client_id`=".$client_id : '')."
			GROUP BY `b`.`id`
			ORDER BY `b`.`sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ids[] = $r['id'];
	return implode(',', $ids);
}
function _zayavBaseVendorIds($client_id=0) { //список id производителей, которые используются в заявках
	$ids = array();
	$sql = "SELECT `b`.`id`
			FROM `zayav` `z`,
				 `base_vendor` `b`
			WHERE `b`.`id`=`z`.`base_vendor_id`
			  AND `z`.`status`
			  AND `z`.`ws_id`=".WS_ID."
			  ".($client_id ? "AND `z`.`client_id`=".$client_id : '')."
			GROUP BY `b`.`id`
			ORDER BY `b`.`sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ids[] = $r['id'];
	return implode(',', $ids);
}
function _zayavBaseModelIds($client_id=0) { //список id производителей, которые используются в заявках
	$ids = array();
	$sql = "SELECT `b`.`id`
			FROM `zayav` `z`,
				 `base_model` `b`
			WHERE `b`.`id`=`z`.`base_model_id`
			  AND `z`.`status`
			  AND `z`.`ws_id`=".WS_ID."
			  ".($client_id ? "AND `z`.`client_id`=".$client_id : '')."
			GROUP BY `b`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ids[] = $r['id'];
	return implode(',', $ids);
}
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
}
function zayavPlaceCheck($zayav_id, $place_id=0, $place_name='') {// Обновление местонахождения заявки

	// - внесение нового местонахождения, если place_id = 0
	// - обновление place_id, если отличается от текущего в заявке
	
	if(!$place_id && empty($place_name))
		return 0;

	$z = _zayavQuery($zayav_id);
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
		$sql = "UPDATE `_zayav`
				SET `device_place`=".$place_id.",
					`device_place_dtime`=CURRENT_TIMESTAMP
				WHERE `id`=".$zayav_id;
		query($sql, GLOBAL_MYSQL_CONNECT);

		if($z['device_place']) { //история вносится, если заявка изменяется
			_history(array(
				'type_id' => 29,
				'client_id' => $z['client_id'],
				'zayav_id' => $zayav_id,
				'v1' => '<table>'._historyChange('', _devPlace($z['device_place']), _devPlace($place_id)).'</table>'
			));

			if($place_id == 2)
				_note(array(
					'add' => 1,
					'comment' => 1,
					'p' => 'zayav',
					'id' => $zayav_id,
					'txt' => 'Передано клиенту.'
				));
/*
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
*/
			$gv += mysql_affected_rows();
		}

		if($gv)
			GvaluesCreate();
	}

	return $place_id;
}

function zayavCookie($action='set', $v='device') {//сохранение страницы с заявками в куках, чтобы было понятно, куда возвращаться
	$key = APP_ID.'_'.VIEWER_ID.'_zayav_type';

	if($action == 'get') {
		if(empty($_GET['d']))
			return @$_COOKIE[$key];
		return $_GET['d'];
	}

	setcookie($key, $v, time() + 3600, '/');
	return '';
}

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
}
function zayavInfoDevice($z) {
	return
	'<div id="zayav-info-device">'.
		'<div id="foto">'._zayavImg($z, 'b').'</div>'.
		'<div class="headBlue">Информация об устройстве</div>'.
		'<div id="content">'.
			'<div id="dev-name">'.
				_deviceName($z['base_device_id']).
				'<br />'.
				'<a>'._vendorName($z['base_vendor_id'])._modelName($z['base_model_id']).'</a>'.
			'</div>'.
			'<table id="info">'.
				($z['imei'] ? '<tr><th>imei:		 <td>'.$z['imei'] : '').
				($z['serial'] ? '<tr><th>serial:	 <td>'.$z['serial'] : '').
				($z['equip'] ? '<tr><th valign="top">Комплект:<td>'.zayavEquipSpisok($z['equip']) : '').
				($z['color_id'] ? '<tr><th>Цвет:  <td>'._color($z['color_id'], $z['color_dop']) : '').
				'<tr><th>Нахождение:<td><a id="zayav-dev-place-change">'._devPlace($z['device_place']).'</a>'.
			'</table>'.
		'</div>'.

		'<div class="headBlue">'.
			'<a id="zp-go" href="'.URL.'&p=zp&device='.$z['base_device_id'].'&vendor='.$z['base_vendor_id'].'&model='.$z['base_model_id'].'">Список запчастей</a>'.
			'<a class="add" id="zp-add">добавить</a>'.
		'</div>'.
		'<div id="zayav-zp-spisok">'.zayav_zp($z).'</div>'.
	'</div>';
}
function zayav_kvit($zayav_id) {
	$sql = "SELECT *
			FROM `zayav_kvit`
			WHERE `ws_id`=".WS_ID."
			  AND `active`
			  AND `zayav_id`=".$zayav_id."
			ORDER BY `id`";
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
				'<td class="dtime">'._dtimeAdd($r);
	$send .= '</table>';
	return '<div id="kvit_spisok">'.$send.'</div>';
}


function zayav_zp($z) {
	$zp = array();
	$ids = array();
	$sql = "SELECT *
			FROM `zp_catalog`
			WHERE `base_device_id`=".$z['base_device_id']."
			  AND `base_vendor_id`=".$z['base_vendor_id']."
			  AND `base_model_id`=".$z['base_model_id'];
    $q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$id = $r['compat_id'] ? $r['compat_id'] : $r['id'];
		$zp[$id] = $r;
		$ids[$r['id']] = $r['id'];
		$ids[$r['compat_id']] = $r['compat_id'];
	}
	unset($ids[0]);

	$MODEL = _vendorName($z['base_vendor_id'])._modelName($z['base_model_id']);

	if(empty($zp))
		return '<div class="_empty">Для '.$MODEL.' запчастей нет.</div>';

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
				'<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'"><b>'._zpName($r['name_id']).'</b> '.$MODEL.'</a>'.
				($r['version'] ? '<div class="version">'.$r['version'].'</div>' : '').
				($r['color_id'] ? '<div class="color">Цвет: '._color($r['color_id']).'</div>' : '').
				'<div>'.
					(isset($r['zakaz']) ? '<a class="zakaz_ok">Заказано!</a>' : '<a class="zakaz">Заказать</a>').
					(isset($r['avai']) && $r['avai'] ? '<b class="avai">Наличие: '.$r['avai'].'</b> <a class="set">Установить</a>' : '').
				'</div>'.
			'</div>';

	return $send;
}
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
}



// ---===! zayav cartridge !===--- Картриджи

function zayavInfoCartridge($zayav_id) {
	return
	'<div id="zayav-cartridge">'.
		'<div class="headBlue">Список картриджей</div>'.
		'<div id="zc-spisok">'.zayavInfoCartridge_spisok($zayav_id).'</div>'.
	'</div>';
}
function zayavInfoCartridge_spisok($zayav_id) {//список картриджей в инфо по заявке
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
			'<th>Информация'.
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
}


function zayav_cartridge_for_schet($ids) {//получение списка картриджей для вставления в счёт
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
}

function zayavCartridgeSchetDel($schet_id) {//отвязка картриджей от счёта при удалении счёта
	$sql = "UPDATE `zayav_cartridge`
			SET `schet_id`=0
			WHERE `schet_id`=".$schet_id;
	query($sql);
}
