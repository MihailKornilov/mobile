<?php
function _cacheClear($ws_id=WS_ID) {
	xcache_unset(CACHE_PREFIX.'device_name');
	xcache_unset(CACHE_PREFIX.'vendor_name');
	xcache_unset(CACHE_PREFIX.'model_name_count');
	xcache_unset(CACHE_PREFIX.'zp_name');
	xcache_unset(CACHE_PREFIX.'color_name');
	xcache_unset(CACHE_PREFIX.'device_equip');
	xcache_unset(CACHE_PREFIX.'zayav_expense'.$ws_id);
	xcache_unset(CACHE_PREFIX.'remind_active'.$ws_id);
	xcache_unset(CACHE_PREFIX.'workshop_'.$ws_id);
	xcache_unset(CACHE_PREFIX.'cartridge'.$ws_id);
	GvaluesCreate($ws_id);
}//_cacheClear()
function _appScripts() {
	return

		'<link rel="stylesheet" type="text/css" href="'.APP_HTML.'/css/main'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" />'.
		'<script type="text/javascript" src="'.APP_HTML.'/js/main'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.

		(WS_ID ?
			'<script type="text/javascript">'.
				'var WS_DEVS=['.WS_DEVS.'],'.
					'WS_TYPE=['.WS_TYPE.'];'.
			'</script>'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/G_values.js?'.G_VALUES.'"></script>'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/G_values_'.WS_ID.'.js?'.G_VALUES.'"></script>'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/ws'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/ws_tovar'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/ws_zp'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/ws_report'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'
		: '').

		//Стили и скрипты для настроек
		(@$_GET['p'] == 'setup' ?
			'<link rel="stylesheet" type="text/css" href="'.APP_HTML.'/css/setup'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" />'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/setup'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'
		: '').

		//Стили и скрипты для суперадминистратора
		(@$_GET['p'] == 'sa' ?
			'<link rel="stylesheet" type="text/css" href="'.APP_HTML.'/css/sa'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" />'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/sa'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'
		: '');
}//_appScripts()

function GvaluesCreate($ws_id=WS_ID) {//Составление файла G_values.js
	$save =
		 'var COLOR_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_color_name` ORDER BY `name` ASC").','.
		"\n".'COLORPRE_SPISOK='.query_selJson("SELECT `id`,`predlog` FROM `setup_color_name` ORDER BY `predlog` ASC").','.
		"\n".'FAULT_ASS='.query_assJson("SELECT `id`,`name` FROM `setup_fault` ORDER BY `sort`").','.
		"\n".'ZPNAME_SPISOK='.Gvalues_obj('setup_zp_name', '`name`', 'device_id').','.

		"\n".'TOVAR_CATEGORY_SPISOK='.query_selJson("SELECT `id`,`name` FROM `tovar_category` ORDER BY `sort` ASC").','.

		"\n".'DEV_SPISOK='.query_selJson("SELECT `id`,`name` FROM `base_device` ORDER BY `sort`").','.
		"\n".'DEV_ASS=_toAss(DEV_SPISOK),'.

		"\n".'VENDOR_SPISOK='.Gvalues_obj('base_vendor', '`device_id`,`sort`', 'device_id').','.
		"\n".'VENDOR_ASS={0:""};'.
		"\n".'for(k in VENDOR_SPISOK){for(n=0;n<VENDOR_SPISOK[k].length;n++){var sp=VENDOR_SPISOK[k][n];VENDOR_ASS[sp.uid]=sp.title;}}'.

		"\n".'MODEL_SPISOK='.Gvalues_obj('base_model', '`vendor_id`,`name`', 'vendor_id').','.
		"\n".'MODEL_ASS={0:""};'.
		"\n".'for(k in MODEL_SPISOK){for(n=0;n<MODEL_SPISOK[k].length;n++){var sp=MODEL_SPISOK[k][n];MODEL_ASS[sp.uid]=sp.title;}}'.

		"\n".'CARTRIDGE_TYPE='._selJson(_cartridgeType()).','.

		"\n".'PAY_TYPE='._selJson(_payType()).';';

	$fp = fopen(APP_PATH.'/js/G_values.js', 'w+');
	fwrite($fp, $save);
	fclose($fp);

	//составление файла G_values для конкретной мастерской
	$save =
		'var CARTRIDGE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_cartridge` WHERE `ws_id`=".$ws_id." ORDER BY `name`").','.
		"\n".'CARTRIDGE_FILLING='.query_assJson("SELECT `id`,`cost_filling` FROM `setup_cartridge` WHERE `ws_id`=".$ws_id).','.
		"\n".'CARTRIDGE_RESTORE='.query_assJson("SELECT `id`,`cost_restore` FROM `setup_cartridge` WHERE `ws_id`=".$ws_id).','.
		"\n".'CARTRIDGE_CHIP='.query_assJson("SELECT `id`,`cost_chip` FROM `setup_cartridge` WHERE `ws_id`=".$ws_id).','.
		"\n".'DEVPLACE_SPISOK='._selJson(_devPlace()).';';

	$fp = fopen(APP_PATH.'/js/G_values_'.$ws_id.'.js', 'w+');
	fwrite($fp, $save);
	fclose($fp);

	//обновление значения версии файла G_values.js
	$sql = "UPDATE `_setup`
			SET `value`=`value`+1
			WHERE `app_id`=".APP_ID."
			  AND `key`='G_VALUES'";
	query($sql, GLOBAL_MYSQL_CONNECT);
}//GvaluesCreate()

function _button($id, $name, $width=0) {
	return
	'<div class="vkButton" id="'.$id.'">'.
		'<button'.($width ? ' style="width:'.$width.'px"' : '').'>'.$name.'</button>'.
	'</div>';
}//_button()

function _wsType($i=false, $p=1) {
	/*  $p - падеж
			1 - именительный Кто? Что?
			2 - родительный (нет) Что? Кого?
			3 - дательный (дать) Кому? Чему?
			4 - винительный (вижу) Кого? Что?
			5 - творительный (горжусь) Кем? Чем?
			6 - предложный (думаю) О ком? О чём?

			7 - нахождение Где?
	*/
	$arr[1] = array(
		1 => 'Сервисный центр',
		2 => 'Мастерская',
		3 => 'Магазин'
	);

	$arr[2] = array(
		1 => 'Сервисного центра',
		2 => 'Мастерской',
		3 => 'Магазина'
	);

	$arr[4] = array(
		1 => 'Сервисный центр',
		2 => 'Мастерскую',
		3 => 'Магазин'
	);

	$arr[7] = array(
		1 => 'в сервисном центре',
		2 => 'в мастерской',
		3 => 'в магазине'
	);

	if($i === false)
		return $arr[$p];
	return $arr[$p][$i];
}//_wsType()

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
function _devPlace($place_id=false, $ws_type=WS_TYPE) {
	$arr = array(
		1 => _wsType($ws_type, 7),
		2 => 'у клиента'
	);

	$sql = "SELECT `id`,`place`
			FROM `zayav_device_place`
			WHERE `ws_id`=".WS_ID."
			ORDER BY `place`";
	$arr += query_ass($sql);

	if($place_id === false)
		return $arr;
	return isset($arr[$place_id]) ? $arr[$place_id] : '';
}//_devPlace()
function _payType($type_id=false) {//вид расчёта
	$arr = array(
		1 => 'Наличный',
		2 => 'Безналичный'
	);
	if($type_id === false)
		return $arr;
	return isset($arr[$type_id]) ? $arr[$type_id] : '';
}//_payType()

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
function _cartridgeType($type_id=0) {
	$arr = array(
		1 => 'Лазерные',
		2 => 'Струйные'
	);
	return $type_id ? $arr[$type_id] : $arr;
}//_cartridgeType()

function equipCache() {
	$key = CACHE_PREFIX.'device_equip';
	$spisok = xcache_get($key);
	if(empty($spisok)) {
		$sql = "SELECT * FROM `setup_device_equip` ORDER BY `sort`";
		$q = query($sql);
		$spisok = array();
		while($r = mysql_fetch_assoc($q))
			$spisok[$r['id']] = array(
				'name' => $r['name']
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
			'Для того, чтобы начать пользоваться приложением, необходимо создать свою организацию.'.
		'</div>'.
		'<div class="vkButton"><button onclick="location.href=\''.URL.'&p=wscreate&d=step1\'">Приступить к созданию</button></div>'.
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
			'Для начала необходимо указать название Вашей организации и город, в котором Вы находитесь.<br />'.
			'Сотрудников и категории устройств можно будет добавить или изменить позднее.'.
		'</div>'.
		'<div class="headName">Создание</div>'.
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
mb_internal_encoding('UTF-8');
function mb_ucfirst1($text) {
	return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
}

function to_schet_spisok() {//внесение списка картриджей к счетам
	$sql = "SELECT * FROM `zayav_cartridge` WHERE `schet_id` ORDER BY `id`";
	$q = query($sql);
	$values = array();
	while($r = mysql_fetch_assoc($q)) {
		$prim = array();
		if($r['filling'])
			$prim[] = 'заправка';
		if($r['restore'])
			$prim[] = 'восстановление';
		if($r['chip'])
			$prim[] = 'замена чипа у';

		$txt = implode(', ', $prim).' картриджа '._cartridgeName($r['cartridge_id']).($r['prim'] ? ', '.$r['prim'] : '');
		$txt = utf8($txt);
		$txt = mb_ucfirst1($txt);
		$txt = win1251($txt);

		$values[] = "(".
			$r['schet_id'].",".
			"'".addslashes($txt)."',".
			"1,".
			$r['cost'].",".
			"1".
			")";
	}

	$sql = "INSERT INTO `zayav_schet_spisok` (
					`schet_id`,
					`name`,
					`count`,
					`cost`,
					`cartridge`
				) VALUES ".implode(',', $values);
	query($sql);
}



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
