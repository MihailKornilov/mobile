<?php
function _hashRead() {
	$_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 'zayav';
	if(empty($_GET['hash'])) {
		define('HASH_VALUES', false);
		if(APP_START) {// восстановление последней посещённой страницы
			$_GET['p'] = isset($_COOKIE['p']) ? $_COOKIE['p'] : $_GET['p'];
			$_GET['d'] = isset($_COOKIE['d']) ? $_COOKIE['d'] : '';
			$_GET['d1'] = isset($_COOKIE['d1']) ? $_COOKIE['d1'] : '';
			$_GET['id'] = isset($_COOKIE['id']) ? $_COOKIE['id'] : '';
		} else
			_hashCookieSet();
		return;
	}
	$ex = explode('.', $_GET['hash']);
	$r = explode('_', $ex[0]);
	unset($ex[0]);
	define('HASH_VALUES', empty($ex) ? false : implode('.', $ex));
	$_GET['p'] = $r[0];
	unset($_GET['d']);
	unset($_GET['d1']);
	unset($_GET['id']);
	switch($_GET['p']) {
		case 'client':
			if(isset($r[1]))
				if(preg_match(REGEXP_NUMERIC, $r[1])) {
					$_GET['d'] = 'info';
					$_GET['id'] = intval($r[1]);
				}
			break;
		case 'zayav':
			if(isset($r[1]))
				if(preg_match(REGEXP_NUMERIC, $r[1])) {
					$_GET['d'] = 'info';
					$_GET['id'] = intval($r[1]);
				} else {
					$_GET['d'] = $r[1];
					if(isset($r[2]))
						$_GET['id'] = intval($r[2]);
				}
			break;
		case 'zp':
			if(isset($r[1]))
				if(preg_match(REGEXP_NUMERIC, $r[1])) {
					$_GET['d'] = 'info';
					$_GET['id'] = intval($r[1]);
				}
			break;
		default:
			if(isset($r[1])) {
				$_GET['d'] = $r[1];
				if(isset($r[2]))
					$_GET['d1'] = $r[2];
			}
	}
	_hashCookieSet();
}//_hashRead()
function _hashCookieSet() {
	setcookie('p', $_GET['p'], time() + 2592000, '/');
	setcookie('d', isset($_GET['d']) ? $_GET['d'] : '', time() + 2592000, '/');
	setcookie('d1', isset($_GET['d1']) ? $_GET['d1'] : '', time() + 2592000, '/');
	setcookie('id', isset($_GET['id']) ? $_GET['id'] : '', time() + 2592000, '/');
}//_hashCookieSet()
function _cacheClear($ws_id=WS_ID) {
	$sql = "SELECT `viewer_id` FROM `vk_user` WHERE `ws_id`=".$ws_id;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		xcache_unset(CACHE_PREFIX.'viewer_'.$r['viewer_id']);
		xcache_unset(CACHE_PREFIX.'viewer_rules_'.$r['viewer_id']);
		//xcache_unset(CACHE_PREFIX.'pin_enter_count'.$r['viewer_id']);
	}
	xcache_unset(CACHE_PREFIX.'setup_global');
	xcache_unset(CACHE_PREFIX.'device_name');
	xcache_unset(CACHE_PREFIX.'vendor_name');
	xcache_unset(CACHE_PREFIX.'model_name_count');
	xcache_unset(CACHE_PREFIX.'zp_name');
	xcache_unset(CACHE_PREFIX.'color_name');
	xcache_unset(CACHE_PREFIX.'device_equip');
	xcache_unset(CACHE_PREFIX.'invoice'.$ws_id);
	xcache_unset(CACHE_PREFIX.'expense'.$ws_id);
	xcache_unset(CACHE_PREFIX.'zayav_expense'.$ws_id);
	xcache_unset(CACHE_PREFIX.'remind_active'.$ws_id);
	xcache_unset(CACHE_PREFIX.'workshop_'.$ws_id);
	xcache_unset(CACHE_PREFIX.'cartridge'.$ws_id);
	GvaluesCreate();
	query("UPDATE `setup_global` SET `script_style`=`script_style`+1");
}//_cacheClear()

function _header() {
	global $html;
	$html =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
		'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.

		'<head>'.
		'<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
		'<title>Hi-tech Service - Приложение '.APP_ID.'</title>'.

		_api_scripts().

		(defined('WS_DEVS') ? '<script type="text/javascript">var WS_DEVS=['.WS_DEVS.'];</script>' : '').

		'<script type="text/javascript" src="'.APP_HTML.'/js/G_values.js?'.G_VALUES.'"></script>'.
		'<script type="text/javascript" src="'.APP_HTML.'/js/G_values_'.WS_ID.'.js?'.G_VALUES.'"></script>'.

		'<link rel="stylesheet" type="text/css" href="'.APP_HTML.'/css/main'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" />'.
		'<script type="text/javascript" src="'.APP_HTML.'/js/main'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.

		(WS_ID ? '<script type="text/javascript" src="'.APP_HTML.'/js/ws'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>' : '').

		//Стили и скрипты для настроек
		(@$_GET['p'] == 'setup' ?
			'<link rel="stylesheet" type="text/css" href="'.APP_HTML.'/css/setup'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" />'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/setup'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'
		: '').

		//Стили и скрипты для суперадминистратора
		(@$_GET['p'] == 'sa' ?
			'<link rel="stylesheet" type="text/css" href="'.APP_HTML.'/css/sa'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" />'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/sa'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'
		: '').

		'</head>'.
		'<body>'.
		'<div id="frameBody">'.
			'<iframe id="frameHidden" name="frameHidden"></iframe>'.
			(SA_VIEWER_ID ? '<div class="sa_viewer_msg">Вы вошли под пользователем '._viewer(SA_VIEWER_ID, 'link').'. <a class="leave">Выйти</a></div>' : '');
}//_header()

function _dopLinks($p, $data, $d=false, $d1=false) {//Дополнительное меню на сером фоне
	$s = $d1 ? $d1 : $d;
	$page = false;
	foreach($data as $link) {
		if($s == $link['d']) {
			$page = true;
			break;
		}
	}
	$send = '<div id="dopLinks">';
	foreach($data as $link) {
		if($page)
			$sel = $s == $link['d'] ?  ' sel' : '';
		else
			$sel = isset($link['sel']) ? ' sel' : '';
		$ld = $d1 ? $d.'&d1='.$link['d'] : $link['d'];
		$send .= '<a href="'.URL.'&p='.$p.'&d='.$ld.'" class="link'.$sel.'">'.$link['name'].'</a>';
	}
	$send .= '</div>';
	return $send;
}//_dopLinks()

function GvaluesCreate() {//Составление файла G_values.js
	$save = 'function _toAss(s){var a=[];for(var n=0;n<s.length;a[s[n].uid]=s[n].title,n++);return a}'.
	"\n".'var COLOR_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_color_name` ORDER BY `name` ASC").','.
		"\n".'COLORPRE_SPISOK='.query_selJson("SELECT `id`,`predlog` FROM `setup_color_name` ORDER BY `predlog` ASC").','.
		"\n".'FAULT_ASS='.query_ptpJson("SELECT `id`,`name` FROM `setup_fault` ORDER BY `sort`").','.
		"\n".'ZPNAME_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_zp_name` ORDER BY `name`").','.
		"\n".'DEVPLACE_SPISOK='._selJson(_devPlace()).','.
		"\n".'DEV_SPISOK='.query_selJson("SELECT `id`,`name` FROM `base_device` ORDER BY `sort`").','.
		"\n".'DEV_ASS=_toAss(DEV_SPISOK),'.
		"\n".'ZE_DOP='._selJson(_zayavExpenseDop()).','.
		"\n".'SALARY_PERIOD='._selJson(salaryPeriod()).','.
		"\n".'COUNTRY_SPISOK=['.
			'{uid:1,title:"Россия"},'.
			'{uid:2,title:"Украина"},'.
			'{uid:3,title:"Беларусь"},'.
			'{uid:4,title:"Казахстан"},'.
			'{uid:5,title:"Азербайджан"},'.
			'{uid:6,title:"Армения"},'.
			'{uid:7,title:"Грузия"},'.
			'{uid:8,title:"Израиль"},'.
			'{uid:11,title:"Кыргызстан"},'.
			'{uid:12,title:"Латвия"},'.
			'{uid:13,title:"Литва"},'.
			'{uid:14,title:"Эстония"},'.
			'{uid:15,title:"Молдова"},'.
			'{uid:16,title:"Таджикистан"},'.
			'{uid:17,title:"Туркмения"},'.
			'{uid:18,title:"Узбекистан"}],'.
		'COUNTRY_ASS=_toAss(COUNTRY_SPISOK),'.
		"\n".'CITY_SPISOK=['.
			'{uid:1,title:"Москва",content:"<b>Москва</b>"},'.
			'{uid:2,title:"Санкт-Петербург",content:"<b>Санкт-Петербург</b>"},'.
			'{uid:35,title:"Великий Новгород"},'.
			'{uid:10,title:"Волгоград"},'.
			'{uid:49,title:"Екатеринбург"},'.
			'{uid:60,title:"Казань"},'.
			'{uid:61,title:"Калининград"},'.
			'{uid:72,title:"Краснодар"},'.
			'{uid:73,title:"Красноярск"},'.
			'{uid:87,title:"Мурманск"},'.
			'{uid:95,title:"Нижний Новгород"},'.
			'{uid:99,title:"Новосибирск"},'.
			'{uid:104,title:"Омск"},'.
			'{uid:110,title:"Пермь"},'.
			'{uid:119,title:"Ростов-на-Дону"},'.
			'{uid:123,title:"Самара"},'.
			'{uid:125,title:"Саратов"},'.
			'{uid:151,title:"Уфа"},'.
			'{uid:158,title:"Челябинск"}];';

	$sql = "SELECT * FROM `base_vendor` ORDER BY `device_id`,`sort`";
	$q = query($sql);
	$vendor = array();
	while($r = mysql_fetch_assoc($q)) {
		if(!isset($vendor[$r['device_id']]))
			$vendor[$r['device_id']] = array();
		$vendor[$r['device_id']][] = '{'.
			'uid:'.$r['id'].','.
			'title:"'.$r['name'].'"'.($r['bold'] ? ','.
			'content:"<B>'.$r['name'].'</B>"' : '').
		'}';
	}
	$v = array();
	foreach($vendor as $n => $sp)
		$v[] = $n.':['.implode(',', $vendor[$n]).']';
	$save .= "\n".'VENDOR_SPISOK={'.implode(',', $v).'};'.
		"\n".'VENDOR_ASS={0:""};'.
		"\n".'for(k in VENDOR_SPISOK){for(n=0;n<VENDOR_SPISOK[k].length;n++){var sp=VENDOR_SPISOK[k][n];VENDOR_ASS[sp.uid]=sp.title;}}';

	$sql = "SELECT * FROM `base_model` ORDER BY `vendor_id`,`name`";
	$q = query($sql);
	$model = array();
	while($r = mysql_fetch_assoc($q)) {
		if(!isset($model[$r['vendor_id']]))
			$model[$r['vendor_id']] = array();
		$model[$r['vendor_id']][] = '{uid:'.$r['id'].',title:"'.$r['name'].'"}';
	}
	$m = array();
	foreach($model as $n => $sp)
		$m[] = $n.':['.implode(',',$model[$n]).']';
	$save .= "\n".'MODEL_SPISOK={'.implode(',',$m).'};'.
		"\n".'MODEL_ASS={0:""};'.
		"\n".'for(k in MODEL_SPISOK){for(n=0;n<MODEL_SPISOK[k].length;n++){var sp=MODEL_SPISOK[k][n];MODEL_ASS[sp.uid]=sp.title;}}';

	$fp = fopen(APP_PATH.'/js/G_values.js', 'w+');
	fwrite($fp, $save);
	fclose($fp);

	//составление файла G_values для конкретной мастерской
	$save =
		'var INVOICE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `invoice` WHERE `ws_id`=".WS_ID." ORDER BY `id`").','.
		"\n".'EXPENSE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_expense` WHERE `ws_id`=".WS_ID." ORDER BY `sort` ASC").','.
		"\n".'EXPENSE_WORKER='.query_ptpJson("SELECT `id`,`show_worker` FROM `setup_expense` WHERE `ws_id`=".WS_ID." AND `show_worker`").','.
		"\n".'CARTRIDGE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_cartridge` WHERE `ws_id`=".WS_ID." ORDER BY `name`").','.
		"\n".'CARTRIDGE_FILLING='.query_ptpJson("SELECT `id`,`cost_filling` FROM `setup_cartridge` WHERE `ws_id`=".WS_ID).','.
		"\n".'CARTRIDGE_RESTORE='.query_ptpJson("SELECT `id`,`cost_restore` FROM `setup_cartridge` WHERE `ws_id`=".WS_ID).','.
		"\n".'CARTRIDGE_CHIP='.query_ptpJson("SELECT `id`,`cost_chip` FROM `setup_cartridge` WHERE `ws_id`=".WS_ID).','.
		"\n".'WORKER_SPISOK='.query_selJson("SELECT `viewer_id`,CONCAT(`first_name`,' ',`last_name`) FROM `vk_user`
											 WHERE `ws_id`=".WS_ID."
											 ORDER BY `dtime_add`").','.
		"\n".'ZE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_zayav_expense` WHERE `ws_id`=".WS_ID." ORDER BY `sort`").','.
		"\n".'ZE_TXT='.query_ptpJson("SELECT `id`,1 FROM `setup_zayav_expense` WHERE `ws_id`=".WS_ID." AND `dop`=1").','.
		"\n".'ZE_WORKER='.query_ptpJson("SELECT `id`,1 FROM `setup_zayav_expense` WHERE `ws_id`=".WS_ID." AND `dop`=2").','.
		"\n".'ZE_ZP='.query_ptpJson("SELECT `id`,1 FROM `setup_zayav_expense` WHERE `ws_id`=".WS_ID." AND `dop`=3").';';

	$fp = fopen(APP_PATH.'/js/G_values_'.WS_ID.'.js', 'w+');
	fwrite($fp, $save);
	fclose($fp);


	query("UPDATE `setup_global` SET `g_values`=`g_values`+1");
	xcache_unset(CACHE_PREFIX.'setup_global');
}//GvaluesCreate()


function _deviceName($device_id, $rod=false) {
	if(!defined('DEVICE_LOADED')) {
		$key = CACHE_PREFIX.'device_name';
		$device = xcache_get($key);
		if(empty($device)) {
			$sql = "SELECT `id`,`name`,`name_rod` FROM `base_device` ORDER BY `id`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$device[$r['id']] = array($r['name'], $r['name_rod']);
			xcache_set($key, $device, 86400);
		}
		foreach($device as $id => $r) {
			define('DEVICE_NAME_'.$id, $r[0]);
			define('DEVICE_NAME_ROD_'.$id, $r[1]);
		}
		define('DEVICE_NAME_0', '');
		define('DEVICE_NAME_ROD_0', '');
		define('DEVICE_LOADED', true);
	}
	return constant('DEVICE_NAME_'.($rod ? 'ROD_' : '').$device_id).' ';
}//_deviceName()
function _vendorName($vendor_id) {
	if(!defined('VENDOR_LOADED')) {
		$key = CACHE_PREFIX.'vendor_name';
		$vendor = xcache_get($key);
		if(empty($vendor)) {
			$sql = "SELECT `id`,`name` FROM `base_vendor`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$vendor[$r['id']] = $r['name'];
			xcache_set($key, $vendor, 86400);
		}
		foreach($vendor as $id => $name)
			define('VENDOR_NAME_'.$id, $name);
		define('VENDOR_LOADED', true);
	}
	return defined('VENDOR_NAME_'.$vendor_id) ? constant('VENDOR_NAME_'.$vendor_id).' ' : '';
}//_vendorName()
function _modelName($model_id) {
	if(!defined('MODEL_LOADED')) {
		$keyCount = CACHE_PREFIX.'model_name_count';
		$keyName = CACHE_PREFIX.'model_name';
		$count = xcache_get($keyCount);
		if(empty($count)) {
			$sql = "SELECT `id`,`name` FROM `base_model` ORDER BY `id`";
			$q = query($sql);
			$count = 0;
			$rows = 0;
			$model = array();
			while($r = mysql_fetch_assoc($q)) {
				$model[$r['id']] = $r['name'];
				$rows++;
				if($rows == 1000) {
					xcache_set($keyName.$count, $model);
					$rows = 0;
					$count++;
					$model = array();
				}
			}
			if(!empty($model))
				xcache_set($keyName.$count, $model, 86400);
			xcache_set($keyCount, $count, 86400);
		}
		for($n = 0; $n <= $count; $n++) {
			$model = xcache_get($keyName.$n);
			if(!empty($model))
				foreach($model as $id => $name)
					define('MODEL_NAME_'.$id, $name);
		}
		define('MODEL_LOADED', true);
	}
	return defined('MODEL_NAME_'.$model_id) ? constant('MODEL_NAME_'.$model_id) : '';
}//_modelName()
function _zpName($name_id) {
	if(!defined('ZP_NAME_LOADED')) {
		$key = CACHE_PREFIX.'zp_name';
		$zp = xcache_get($key);
		if(empty($zp)) {
			$sql = "SELECT `id`,`name` FROM `setup_zp_name` ORDER BY `id`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$zp[$r['id']] = $r['name'];
			xcache_set($key, $zp, 86400);
		}
		foreach($zp as $id => $name)
			define('ZP_NAME_'.$id, $name);
		define('ZP_NAME_LOADED', true);
	}
	return constant('ZP_NAME_'.$name_id);
}//_zpName()
function _zpCompatId($zp_id) {
	$sql = "SELECT `id`,`compat_id` FROM `zp_catalog` WHERE `id`=".intval($zp_id);
	$zp = mysql_fetch_assoc(query($sql));
	return $zp['compat_id'] ? $zp['compat_id'] : $zp['id'];
}//_zpCompatId()
function _zpAvaiSet($zp_id) { // Обновление количества наличия запчасти
	$zp_id = _zpCompatId($zp_id);
	$count = query_value("SELECT IFNULL(SUM(`count`),0) FROM `zp_move` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id." LIMIT 1");
	query("DELETE FROM `zp_avai` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id);
	if($count > 0)
		query("INSERT INTO `zp_avai` (`ws_id`,`zp_id`,`count`) VALUES (".WS_ID.",".$zp_id.",".$count.")");
	return $count;
}//_zpAvaiSet()
function _color($color_id, $color_dop=0) {
	if(!defined('COLOR_LOADED')) {
		$key = CACHE_PREFIX.'color_name';
		$zp = xcache_get($key);
		if(empty($zp)) {
			$sql = "SELECT * FROM `setup_color_name`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$zp[$r['id']] = array(
					'predlog' => $r['predlog'],
					'name' => $r['name']
				);
			xcache_set($key, $zp, 86400);
		}
		foreach($zp as $id => $r) {
			define('COLORPRE_'.$id, $r['predlog']);
			define('COLOR_'.$id, $r['name']);
		}
		define('COLORPRE_0', '');
		define('COLOR_0', '');
		define('COLOR_LOADED', true);
	}
	if($color_dop)
		return constant('COLORPRE_'.$color_id).' - '.strtolower(constant('COLOR_'.$color_dop));;
	return constant('COLOR_'.$color_id);
}//_color()
function _devPlace($place_id=false) {
	$arr = array(
		1 => 'в мастерской',
		2 => 'у клиента'
	);
	if($place_id == false)
		return $arr;
	return isset($arr[$place_id]) ? $arr[$place_id] : '';
}//_devPlace()

function _cartridgeName($item_id) {
	if(!defined('CARTRIDGE_NAME_LOADED')) {
		$key = CACHE_PREFIX.'cartridge'.WS_ID;
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT `id`,`name` FROM `setup_cartridge` WHERE `ws_id`=".WS_ID;
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = $r['name'];
			xcache_set($key, $arr, 86400);
		}
		foreach($arr as $id => $name)
			define('CARTRIDGE_NAME_'.$id, $name);
		define('CARTRIDGE_NAME_LOADED', true);
	}
	return constant('CARTRIDGE_NAME_'.$item_id);
}//_cartridgeName()

function equipCache() {
	$key = CACHE_PREFIX.'device_equip';
	$spisok = xcache_get($key);
	if(empty($spisok)) {
		$sql = "SELECT * FROM `setup_device_equip` ORDER BY `sort`";
		$q = query($sql);
		$spisok = array();
		while($r = mysql_fetch_assoc($q))
			$spisok[$r['id']] = array(
				'name' => $r['name'],
				'title' => $r['title']
			);
		xcache_set($key, $spisok, 86400);
	}
	return $spisok;
}//equipCache()
function devEquipCheck($device_id=0, $ids='') {//Получение списка комплектаций в виде чекбоксов для внесения или редактирования заявки
	if($device_id) {
		$v = query_value("SELECT `equip` FROM `base_device` WHERE `id`=".$device_id);
		$arr = explode(',', $v);
		$equip = array();
		foreach($arr as $id)
			$equip[$id] = 1;
	}
	$sel = array();
	if($ids) {
		$arr = explode(',', $ids);
		foreach($arr as $id)
			$sel[$id] = 1;
	}
	$send = '';
	foreach(equipCache() as $id => $r)
		if(isset($equip[$id]) || !$device_id)
			$send .= _check('eq_'.$id, $r['name'], isset($sel[$id]) ? 1 : 0);
	return $send;
}//devEquipCheck()



// ---===! ws_create !===--- Секция создания мастерской

function ws_create_info() {
	return
	'<div class="ws-create-info">'.
		'<div class="txt">'.
			'<h3>Добро пожаловать в приложение Hi-Tech Service!</h3>'.
			'Данное приложение является программой для учёта ремонта мобильных телефонов, '.
			'КПК, ноутбуков, телевизоров и другой радиоэлектронной аппаратуры и бытовой техники.<br />'.
			'<br />'.
			'<U>При помощи программы можно:</U><br />'.
			'- вести клиентскую базу (хранить, изменять информацию о клиентах, которые сдают устройства в ремонт);<br />'.
			'- вести учёт устройств, принятых в ремонт;<br />'.
			'- начислять оплату за выполненную работу;<br />'.
			'- принимать платежи и вести учёт денежных средств;<br />'.
			'- получать, изменять информацию о запчастях.<br />'.
			'<br />'.
			'Для того, чтобы начать пользоваться приложением, необходимо создать свою мастерскую.'.
		'</div>'.
		'<div class="vkButton"><button onclick="location.href=\''.URL.'&p=wscreate&d=step1\'">Приступить к созданию мастерской</button></div>'.
	'</div>';
}//ws_create_info()
function ws_create_step1() {
	$sql = "SELECT `id`,`name_mn` FROM `base_device` ORDER BY `sort`";
	$q = query($sql);
	$checkDevs = '';
	while($r = mysql_fetch_assoc($q))
		$checkDevs .= _check($r['id'], $r['name_mn']);

	return
	'<script type="text/javascript">var COUNTRY_ID='.VIEWER_COUNTRY_ID.';</script>'.
	'<div class="ws-create-step1">'.
		'<div class="txt">'.
			'Для начала необходимо указать название Вашей мастерской и город, в котором Вы находитесь.<br />'.
			'Сотрудников и категории устройств можно будет добавить или изменить позднее.'.
		'</div>'.
		'<div class="headName">Создание мастерской</div>'.
		'<TABLE class="tab">'.
			'<TR><TD class="label">Название организации:<TD><INPUT type="text" id="org_name" maxlength="100">'.
			'<TR><TD class="label">Страна:<TD><INPUT type="hidden" id="countries" value="'.VIEWER_COUNTRY_ID.'">'.
			'<TR><TD class="label">Город:<TD><INPUT type="hidden" id="cities" value="0">'.
			'<TR><TD class="label">Главный администратор:<TD><b>'.VIEWER_NAME.'</b>'.
			'<TR><TD class="label topi">Категории устройств,<br />ремонтом которых<br />Вы занимаетесь:<TD id="devs">'.$checkDevs.
		'</TABLE>'.

		'<div class="vkButton"><button>Готово</button></div>'.
		'<div class="vkCancel"><button>Отмена</button></div>'.
		'<script type="text/javascript" src="'.APP_HTML.'/js/ws_create_step1'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.
	'</div>';
}//ws_create_step1()




/*
function remind_to_global() {//перенос напоминаний в глобал
//	query("DELETE FROM `remind`", GLOBAL_MYSQL_CONNECT);
//	query("DELETE FROM `remind_history`", GLOBAL_MYSQL_CONNECT);
//exit;

	$sql = "SELECT * FROM `reminder` LIMIT 500";
	$q = query($sql);
	if(!mysql_num_rows($q))
		die('end');
	$ids = array();
	$arr = array();
	$hist = array();
	while($r = mysql_fetch_assoc($q)) {
		$ids[] = $r['id'];
		$arr[] = "(
			".$r['id'].",
			".APP_ID.",
			".$r['ws_id'].",
			".($r['client_id'] ? $r['client_id'] : 0).",
			".$r['zayav_id'].",
			'".addslashes($r['txt'])."',
			'".$r['day']."',
			".$r['status'].",
			".$r['viewer_id_add'].",
			'".$r['dtime_add']."'
		)";
		foreach(explode('<BR>', $r['history']) as $h) {
			$hist[] = "(
					".$r['id'].",
					'".addslashes($h)."',
					'0000-00-00 00:00:00'
				)";
		}

	}

	$sql = "INSERT INTO `remind` (
				`id`,
				`app_id`,
				`ws_id`,
				`client_id`,
				`zayav_id`,
				`txt`,
				`day`,
				`status`,
				`viewer_id_add`,
				`dtime_add`
			) VALUES ".implode(',', $arr);
	query($sql, GLOBAL_MYSQL_CONNECT);

	$sql = "INSERT INTO `remind_history` (
				`remind_id`,
				`txt_old`,
				`dtime_add`
			) VALUES ".implode(',', $hist);
	query($sql, GLOBAL_MYSQL_CONNECT);

	$sql = "DELETE FROM `reminder` WHERE `id` IN (".implode(',', $ids).")";
	query($sql);
	echo 'deleted 500<br />';
}




function to_new_images() {//Перенос картинок в новый формат
	define('IMLINK', 'http://'.DOMAIN.'/files/images/');
	define('IMPATH', APP_PATH.'files/images/');
	$sql = "SELECT * FROM `images` WHERE !LENGTH(`path`) LIMIT 1000";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$name = str_replace('http://mobile.nyandoma.ru/files/images/', '', $r['link']);

		$small_name = $name.'-s.jpg';
		rename(IMPATH.$name.'-small.jpg', IMPATH.$small_name);

		$big_name = $name.'-b.jpg';
		rename(IMPATH.$name.'-big.jpg', IMPATH.$big_name);

		echo 'id='.$r['id'].' '.$small_name.'<br />';

		$sql = "UPDATE `images`
				SET `path`='".addslashes(IMLINK)."',
					`small_name`='".$small_name."',
					`big_name`='".$big_name."'
				WHERE `id`=".$r['id'];
		query($sql);
	}
}



function histChangeClient() { // правка ссылок c клиентами в истории
	$sql = "SELECT * FROM `history` WHERE type=7 AND `value` LIKE '%href=%' limit 100";
	$q = query($sql);
	$txt = '';
	while($r = mysql_fetch_assoc($q)) {
		$ex = explode('href="', $r['value'], 2);
		$ex1 = explode('">', $ex[1], 2);
		$txt .= $ex1[0].'<br />';
		$worker = explode('&id=', $ex1[0]);

		$value = $ex[0].'class="go-client-info" val="'.$worker[1].'">'.$ex1[1];
		$sql = "UPDATE `history` SET `value`='".addslashes($value)."' where id=".$r['id'];
//		echo '<textarea style="width:700px;height:500px">'.$sql.'</textarea>'.$value;
		query($sql);
	}
	echo $txt;
}
function histChangeZp() { // правка ссылок в истории (href)
	$sql = "SELECT * FROM `history` WHERE type=30 AND `value` LIKE '%href=%' limit 100";
	$q = query($sql);
	$txt = '';
	while($r = mysql_fetch_assoc($q)) {
		$ex = explode('href="', $r['value'], 2);
		$ex1 = explode('">', $ex[1], 2);
		$txt .= $ex1[0].'<br />';
		$value = '';

		$id = explode('&p=zp&d=info&id=', $ex1[0]);
		if(!empty($id[1]))
			$value = $ex[0].'class="go-zp-info" val="'.$id[1].'">'.$ex1[1];

		$id = explode('&p=report&d=salary&id=', $ex1[0]);
		if(!empty($id[1])) {
			$year = explode('&year=', $id[1]);
			$mon = explode('&mon=', $year[1]);
			$acc = explode('&acc_id=', $mon[1]);
			$worker = $year[0];
			$year = $mon[0];
			$mon = $acc[0];
			$acc = $acc[1];
			$value = $ex[0].'class="go-report-salary" val="'.$worker.':'.$year.':'.$mon.':'.$acc.'">'.$ex1[1];
		}

		if(!$value)
			continue;

		$sql = "UPDATE `history` SET `value`='".addslashes($value)."' where id=".$r['id'];
//		echo '<textarea style="width:700px;height:500px">'.$sql.'</textarea>'.$value;
		query($sql);
	}
	echo $txt;
}
*/