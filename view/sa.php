<?php
function sa_index() {
	return
		'<div><b>Устройства и запчасти:</b></div>'.
		'<a href="'.URL.'&p=sa&d=tovar_category">Категории товаров</a>'.
		'<a href="'.URL.'&p=sa&d=device">Устройства / Производители / Модели</a>'.
		'<a href="'.URL.'&p=sa&d=equip">Комплектация и наименования запчастей для устройств</a>'.
		'<a href="'.URL.'&p=sa&d=fault">Виды неисправностей</a>'.
		//'<a href="'.URL.'&p=sa&d=dev-spec">Характеристики устройств для информации</a>'.
		'<br />'.

		'<a href="'.URL.'&p=sa&d=color">Цвета для устройств и запчастей</a>';
}//sa_index()


function sa_tovar_category() {
	return
		'<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">Администрирование</a> » Категории товаров</div>'.
		'<div id="sa-tovar-category">'.
			'<div class="headName">Категории товаров<a class="add">Новая категория</a></div>'.
			'<div id="spisok">'.sa_tovar_category_spisok().'</div>'.
		'</div>';
}//sa_tovar_category()
function sa_tovar_category_spisok() {
	$sql = "SELECT
	            *,
				'' `dev`
			FROM `tovar_category`
			ORDER BY `sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Категории не определены.';
	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']] = $r;

	$sql = "SELECT *
			FROM `base_device`
			WHERE `category_id`
			ORDER BY `sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['category_id']]['dev'] .= '<div class="dev">'.$r['name'].'</div>';

	$send =
		'<table class="_spisok">'.
			'<tr><th>Наименование'.
				'<th>';
	foreach($spisok as $id => $r)
		$send .=
			'<tr val="'.$id.'">'.
				'<td class="td-name">'.
					'<div class="img_add'._tooltip('Прикрепить устройство', -75).'</div>'.
					'<div class="name">'.$r['name'].'</div>'.
					$r['dev'].
				'<td class="ed">'.
					'<div class="img_edit"></div>';
	$send .= '</table>';
	return $send;
}//sa_tovar_category_spisok()


function sa_device() {
	return '<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">Администрирование</a> » Устройства</div>'.
	'<script type="text/javascript">var devEquip = \''.devEquipCheck().'\';</script>'.
	'<div class="sa-device">'.
		'<div class="headName">Список устройств<a class="add">Добавить новое наименование</a></div>'.
		'<div class="spisok">'.sa_device_spisok().'</div>'.
	'</div>';
}//sa_device()
function sa_device_spisok() {
	$sql = "SELECT
				`bd`.`id` AS `id`,
				`bd`.`name` AS `name`,
				COUNT(`bv`.`id`) AS `vendor_count`
			FROM `base_device` AS `bd`
				LEFT JOIN `base_vendor` AS `bv`
				ON `bd`.`id`=`bv`.`device_id`
			GROUP BY `bd`.`id`
			ORDER BY `bd`.`sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Устройств нет.';
	$devs = array();
	while($r = mysql_fetch_assoc($q))
		$devs[$r['id']] = $r;

	$sql = "SELECT
				`bd`.`id` AS `id`,
				COUNT(`bm`.`id`) AS `count`
			FROM `base_device` AS `bd`,
				 `base_model` AS `bm`
			WHERE `bd`.`id`=`bm`.`device_id`
			GROUP BY `bd`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$devs[$r['id']]['model_count'] = $r['count'];

	$sql = "SELECT
				`bd`.`id` AS `id`,
				COUNT(`z`.`id`) AS `count`
			FROM `base_device` AS `bd`,`zayav` AS `z`
			WHERE `bd`.`id`=`z`.`base_device_id` AND `z`.`status`>0
			GROUP BY `bd`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$devs[$r['id']]['zayav_count'] = $r['count'];

	$spisok =
		'<table class="_spisok">'.
			'<tr><th class="name">Наименование устройства'.
				'<th class="ven">Кол-во<br />производителей'.
				'<th class="mod">Кол-во<br />моделей'.
				'<th class="zayav">Кол-во<br />заявок'.
				'<th class="edit">'.
		'</table>'.
		'<dl class="_sort" val="base_device">';
	foreach($devs as $id => $r)
		$spisok .= '<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name"><a href="'.URL.'&p=sa&d=vendor&id='.$id.'">'.$r['name'].'</a>'.
					'<td class="ven">'.($r['vendor_count'] ? $r['vendor_count'] : '').
					'<td class="mod">'.(isset($r['model_count']) ? $r['model_count'] : '').
					'<td class="zayav">'.(isset($r['zayav_count']) ? $r['zayav_count'] : '').
					'<td class="edit">'.
						'<div class="img_edit"></div>'.
						($r['vendor_count'] || isset($r['model_count'])  || isset($r['zayav_count']) ? '' : '<div class="img_del"></div>').
			'</table>';
	$spisok .= '</dl>';
	return $spisok;
}//sa_device_spisok()
function sa_vendor() {
	if(empty($_GET['id']) || !preg_match(REGEXP_NUMERIC, $_GET['id']))
		return 'Ошибка id. <a href="'.URL.'&p=sa&d=device">Назад</a>.';
	$device_id = intval($_GET['id']);
	$sql = "SELECT * FROM `base_device` WHERE `id`=".$device_id;
	if(!$dev = mysql_fetch_assoc(query($sql)))
		return 'Устройства id = '.$device_id.' не существует. <a href="'.URL.'&p=sa&d=device">Назад</a>.';
	return
	'<script type="text/javascript">var DEVICE_ID='.$device_id.';</script>'.
	'<div class="path">'.
		sa_cookie_back().
		'<a href="'.URL.'&p=sa">Администрирование</a> » '.
		'<a href="'.URL.'&p=sa&d=device">Устройства</a> » '.
		$dev['name'].
	'</div>'.
	'<div class="sa-vendor">'.
		'<div class="headName">Список производителей для "'.$dev['name'].'"<a class="add">Добавить</a></div>'.
		'<div class="spisok">'.sa_vendor_spisok($device_id).'</div>'.
	'</div>';
}//sa_vendor()
function sa_vendor_spisok($device_id) {
	$sql = "SELECT
				`bv`.`id`,
				`bv`.`name`,
				`bv`.`bold`,
				COUNT(`bm`.`id`) AS `model_count`
			FROM `base_vendor` AS `bv`
				 LEFT JOIN `base_model` AS `bm`
				 ON `bv`.`id`=`bm`.`vendor_id`
			WHERE `bv`.`device_id`=".$device_id."
			GROUP BY `bv`.`id`
			ORDER BY `bv`.`sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Производителей нет.';

	$vens = array();
	while($r = mysql_fetch_assoc($q))
		$vens[$r['id']] = $r;

	$sql = "SELECT
				`v`.`id` AS `id`,
				COUNT(`z`.`id`) AS `count`
			FROM `base_vendor` AS `v`,
				 `zayav` AS `z`
			WHERE `v`.`device_id`=".$device_id."
			  AND `v`.`id`=`z`.`base_vendor_id`
			  AND `z`.`status`>0
			GROUP BY `v`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$vens[$r['id']]['zayav_count'] = $r['count'];

	$spisok =
	'<table class="_spisok">'.
		'<tr><th class="name">Наименование устройства'.
			'<th class="mod">Кол-во<br />моделей'.
			'<th class="zayav">Кол-во<br />заявок'.
			'<th class="edit">'.
	'</table>'.
	'<dl class="_sort" val="base_vendor">';
	foreach($vens as $id => $r)
		$spisok .= '<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name'.($r['bold'] ? ' b' : '').'"><a href="'.URL.'&p=sa&d=model&vendor_id='.$id.'">'.$r['name'].'</a>'.
					'<td class="mod">'.($r['model_count'] ? $r['model_count'] : '').
					'<td class="zayav">'.(isset($r['zayav_count']) ? $r['zayav_count'] : '').
					'<td class="edit">'.
						'<div class="img_edit"></div>'.
						($r['model_count']  || isset($r['zayav_count']) ? '' : '<div class="img_del"></div>').
			'</table>';
	$spisok .= '</dl>';
	return $spisok;
}//sa_vendor_spisok()
function sa_model() {
	if(empty($_GET['vendor_id']) || !preg_match(REGEXP_NUMERIC, $_GET['vendor_id']))
		return 'Ошибка vendor_id. <a href="'.URL.'&p=sa&d=device">Назад</a>.';
	$vendor_id = intval($_GET['vendor_id']);
	$sql = "SELECT * FROM `base_vendor` WHERE `id`=".$vendor_id;
	if(!$ven = mysql_fetch_assoc(query($sql)))
		return 'Произодителя id = '.$vendor_id.' не существует. <a href="'.URL.'&p=sa&d=device">Назад</a>.';
	return
	'<script type="text/javascript">var VENDOR_ID='.$vendor_id.';</script>'.
	'<div class="path">'.
		sa_cookie_back().
		'<a href="'.URL.'&p=sa">Администрирование</a> » '.
		'<a href="'.URL.'&p=sa&d=device">Устройства</a> » '.
		'<a href="'.URL.'&p=sa&d=vendor&id='.$ven['device_id'].'">'._deviceName($ven['device_id']).'</a> » '.
		$ven['name'].
	'</div>'.
	'<div class="sa-model">'.
		'<div class="headName">Список моделей для "'._deviceName($ven['device_id']).$ven['name'].'"<a class="add">Добавить</a></div>'.
		'<div id="find"></div>'.
		'<div class="spisok">'.sa_model_spisok(array('vendor_id'=>$vendor_id)).'</div>'.
	'</div>';
}//sa_model()
function sa_model_spisok($v) {
	$filter = array(
		'page' => _num(@$v['page']) ? $v['page'] : 1,
		'limit' => _num(@$v['limit']) ? $v['limit'] : 20,
		'find' => unescape(@$v['find']),
		'vendor_id' => _num(@$v['vendor_id'])
	);

	$page = $filter['page'];
	$limit = $filter['limit'];

	$cond = "`m`.`vendor_id`=".$filter['vendor_id'].
			($filter['find'] ? " AND `m`.`name` LIKE '%".$filter['find']."%'" : '');

	$sql = "SELECT COUNT(*)
			FROM `base_model` `m`
			WHERE ".$cond;
	$all = query_value($sql);
	if(!$all)
		return 'Моделей нет.';

	$start = ($page - 1) * $limit;
	$sql = "SELECT
				`m`.`id`,
				`m`.`name`,
				COUNT(`z`.`id`) AS `zayav`,
				0 AS `zp`
			FROM `base_model` AS `m`
				 LEFT JOIN `zayav` AS `z`
				 ON `m`.`id`=`z`.`base_model_id`
			WHERE ".$cond."
			GROUP BY `m`.`id`
			ORDER BY `m`.`name`
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$model = array();
	$img = array();
	while($r = mysql_fetch_assoc($q)) {
		$model[$r['id']] = $r;
		$img[] = 'dev'.$r['id'];
	}

	$sql = "SELECT
				`m`.`id`,
				COUNT(`zp`.`id`) AS `zp`
			FROM `base_model` AS `m`
				 LEFT JOIN `zp_catalog` AS `zp`
				 ON `m`.`id`=`zp`.`base_model_id`
			WHERE ".$cond."
				AND `m`.`id` IN (".implode(',', array_keys($model)).")
			GROUP BY `m`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$model[$r['id']]['zp'] = $r['zp'];

	$img = _imageGet(array(
		'owner' => $img,
		'view' => 1,
		'x' => 60,
		'y' => 45
	));

	$send = $page == 1 ?
		'<div class="count">Найдено: <b>'.$all.'</b></div>'.
		'<table class="_spisok">'.
			'<tr><th>'.
				'<th class="name">Наименование модели'.
				'<th class="zayav">Кол-во<br />заявок'.
				'<th class="zp">Кол-во<br />запчастей'.
				'<th class="edit">'
		: '';
	$reg = '/('.$filter['find'].')/i';
	foreach($model as $id => $r) {
		$name = $r['name'];
		if($filter['find'])
			$r['name'] = preg_replace($reg, "<em>\\1</em>", $r['name'], 1);
		$send .= '<tr val="'.$id.'"><td class="img">'.$img['dev'.$id]['img'].
					   '<td class="name">'.
							'<a href="'.URL.'&p=sa&d=modelInfo&id='.$id.'">'._vendorName($filter['vendor_id']).'<b>'.$r['name'].'</b></a>'.
							'<div class="dn">'.$name.'</div>'.
					   '<td class="zayav">'.($r['zayav'] ? $r['zayav'] : '').
					   '<td class="zp">'.($r['zp'] ? $r['zp'] : '').
					   '<td class="edit">'.
						   '<div class="img_edit"></div>'.
						   ($r['zayav'] || $r['zp'] ? '' : '<div class="img_del"></div>');
	}
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send .= '<tr><td colspan="5" class="_next" val="'.($page + 1).'">'.
					'<span>Показать ещё '.$c.' модел'._end($c, 'ь', 'и', 'ей').'</span>';
	}
	$send .= $page == 1 ? '</table>' : '';
	return $send;
}//sa_model_spisok()


function sa_equip() {
	$sql = "SELECT `id`,`name` FROM `base_device` ORDER BY `sort`";
	$q = query($sql);
	$default_id = 1;
	$dev = '';
	while($r = mysql_fetch_assoc($q))
		$dev .= '<a'.($r['id'] == $default_id ? ' class="sel"' : '').' val="'.$r['id'].'">'.$r['name'].'</a>';
	return
	'<script type="text/javascript">'.
		'var ZPNAME={device_id:'.$default_id.'};'.
	'</script>'.
	'<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">Администрирование</a> » Комплектация устройств и наименования запчастей</div>'.
	'<div class="sa-equip">'.
		'<table>'.
			'<tr><td valign="top"><div class="rightLink">'.$dev.'</dev>'.
				'<td valign="top">'.
					'<div id="eq-spisok">'.sa_equip_spisok($default_id).'</div>'.
					'<div id="zp-spisok">'.sa_zpname_spisok($default_id).'</div>'.
		'</table>'.
	'</div>';
}//sa_equip()
function sa_equip_spisok($device_id) {
	$equip = query_value("SELECT `equip` FROM `base_device` WHERE `id`=".$device_id);
	$arr = explode(',', $equip);
	$equip = array();
	foreach($arr as $id)
		$equip[$id] = 1;

	$spisok = '';
	if(!empty($equip)) {
		$spisok =
			'<table class="_spisok">'.
				'<tr><th class="use">'.
					'<th class="name">Наименование'.
					'<th class="ed">'.
			'</table>'.
			'<dl class="_sort" val="setup_device_equip">';
		foreach(equipCache() as $id => $r)
			$spisok .= '<dd val="'.$id.'">'.
				'<table class="_spisok">'.
					'<tr><td class="use">'._check('c_'.$id, '', isset($equip[$id]) ? 1 : 0).
						'<td class="name">'.$r['name'].
						'<td class="ed">'.
							'<div class="img_edit equip-edit"></div>'.
							'<div class="img_del equip-del"></div>'.
				'</table>';
		$spisok .= '</dl>';
	}
	return
		'<div class="headName">Комплектация<a class="add" id="equip-add">Добавить</a></div>'.
		($spisok ? $spisok : 'Вариантов комплектаций нет');
}//sa_equip_spisok()
function sa_zpname_spisok($device_id) {
	$sql = "SELECT
	            `s`.*,
				COUNT(`c`.`id`) AS `zp`
	        FROM `setup_zp_name` AS `s`
	        	LEFT JOIN `zp_catalog` AS `c`
	        	ON `s`.`id`=`c`.`name_id`
	        WHERE `s`.`device_id`=".$device_id."
	        GROUP BY `s`.`id`
	        ORDER BY `s`.`name`";
	$q = query($sql);
	$send = '<div class="headName">Запчасти<a class="add" id="zpname-add">Добавить</a></div>';
	if(!mysql_num_rows($q))
		return $send.'Список пуст.';

	$send .=
		'<table class="_spisok">'.
			'<tr><th class="name">Наименование'.
				'<th>Кол-во<br />запчастей<br />в каталоге'.
				'<th>';
	while($r = mysql_fetch_assoc($q))
		$send .=
			'<tr val="'.$r['id'].'">'.
				'<td class="name">'.$r['name'].
				'<td class="zp">'.($r['zp'] ? $r['zp'] : '').
				'<td class="ed">'.
					'<div class="img_edit zpname-edit"></div>'.
   ($r['zp'] ? '' : '<div class="img_del zpname-del"></div>');
	$send .= '</table>';
	return $send;
}//sa_equip_spisok()

function sa_fault() {
	return '<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">Администрирование</a> » Виды неисправностей</div>'.
	'<div class="sa-fault">'.
		'<div class="headName">Виды неисправностей<a class="add">Добавить</a></div>'.
		'<div class="spisok">'.sa_fault_spisok().'</div>'.
	'</div>';

}//sa_fault()
function sa_fault_spisok() {
	$sql = "SELECT * FROM `setup_fault` ORDER BY `sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Список пуст.';

	$send =
		'<table class="_spisok">'.
			'<tr><th class="name">Наименование'.
				'<th class="set">'.
		'</table>'.
		'<dl class="_sort" val="setup_fault">';
	while($r = mysql_fetch_assoc($q))
		$send .= '<dd val="'.$r['id'].'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="set">'.
						'<div class="img_edit"></div>'.
						'<div class="img_del"></div>'.
			'</table>';
	$send .= '</dl>';
	return $send;
}//sa_fault_spisok()

function sa_color() {
	return '<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">Администрирование</a> » Цвета</div>'.
	'<div class="sa-color">'.
		'<div class="headName">Цвета для устройств и запчастей<a class="add">Новый цвет</a></div>'.
		'<div class="spisok">'.sa_color_spisok().'</div>'.
	'</div>';
}//sa_color()
function sa_color_spisok() {
	$sql = "SELECT
				`c`.*,
				COUNT(`z`.`id`) AS `zayav`,
				0 AS `zp`
			FROM `setup_color_name` AS `c`
				LEFT JOIN `zayav` AS `z`
				ON `c`.`id`=`z`.`color_id`
			GROUP BY `c`.`id`
			ORDER BY `c`.`name`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Цвета не внесены.';
	$color = array();
	while($r = mysql_fetch_assoc($q))
		$color[$r['id']] = $r;

	$sql = "SELECT
				`c`.`id` AS `id`,
				COUNT(`zp`.`id`) AS `zp`
			FROM `setup_color_name` AS `c`,
				 `zp_catalog` AS `zp`
			WHERE `c`.`id`=`zp`.`color_id`
			GROUP BY `c`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$color[$r['id']]['zp'] = $r['zp'];

	$send =
		'<table class="_spisok">'.
			'<tr><th>Предлог'.
				'<th>Цвет'.
				'<th>Кол-во<br />заявок'.
				'<th>Кол-во<br />запчастей'.
				'<th>';
	foreach($color as $id => $r)
		$send .=
			'<tr val="'.$id.'">'.
				'<td class="pre">'.$r['predlog'].
				'<td class="name">'.$r['name'].
				'<td class="zayav">'.($r['zayav'] ? $r['zayav'] : '').
				'<td class="zp">'.($r['zp'] ? $r['zp'] : '').
				'<td><div class="img_edit"></div>'.
					($r['zayav'] || $r['zp'] ? '' : '<div class="img_del"></div>');
	$send .= '</table>';
	return $send;
}//sa_color_spisok()
