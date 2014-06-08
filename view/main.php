<?php
function _hashRead() {
	$_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 'zayav';
	if(empty($_GET['hash'])) {
		define('HASH_VALUES', false);
		if(APP_START) {// �������������� ��������� ���������� ��������
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
	xcache_unset(CACHE_PREFIX.'device_status');
	xcache_unset(CACHE_PREFIX.'device_equip');
	xcache_unset(CACHE_PREFIX.'invoice');
	xcache_unset(CACHE_PREFIX.'income');
	xcache_unset(CACHE_PREFIX.'expense');
	xcache_unset(CACHE_PREFIX.'zayav_expense');
	xcache_unset(CACHE_PREFIX.'remind_active'.$ws_id);
	xcache_unset(CACHE_PREFIX.'workshop_'.$ws_id);
	GvaluesCreate();
	query("UPDATE `setup_global` SET `script_style`=`script_style`+1");
}//_cacheClear()

function _header() {
	global $html;
	$html =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
//		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.
		'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.
		'<head>'.
		'<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
		'<title>Hi-tech Service - ���������� '.API_ID.'</title>'.

		//������������ ������ � ��������
		(SA ? '<script type="text/javascript" src="'.GSITE.'/js/errors.js?'.VERSION.'"></script>' : '').

		//�������� �������
		'<script type="text/javascript" src="'.GSITE.'/js/jquery-2.0.3.min.js"></script>'.
		'<script type="text/javascript" src="'.GSITE.'/vk/xd_connection'.(DEBUG ? '' : '.min').'.js"></script>'.

		//��������� ���������� �������� �������.
		(SA ? '<script type="text/javascript">var TIME=(new Date()).getTime();</script>' : '').

		'<script type="text/javascript">'.
			(LOCAL ? 'for(var i in VK)if(typeof VK[i]=="function")VK[i]=function(){return false};' : '').
			'var DOMAIN="'.DOMAIN.'",'.
				'VALUES="'.VALUES.'",'.
				(defined('WS_DEVS') ? 'WS_DEVS=['.WS_DEVS.'],' : '').
				'VIEWER_ID='.VIEWER_ID.';'.
		'</script>'.

		//����������� api VK. ����� VK ������ ������ �� �������� ������ �����
		'<link href="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/vk'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
		'<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/vk'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.

		'<link href="'.SITE.'/css/main'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
		'<script type="text/javascript" src="'.SITE.'/js/main'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.

		(WS_ID ? '<script type="text/javascript" src="'.SITE.'/js/ws'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>' : '').
		(@$_GET['p'] == 'setup' ? '<script type="text/javascript" src="'.SITE.'/js/setup'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>' : '').

		//������� � ����� ��� �������������������
		(@$_GET['p'] == 'sa' ? '<link href="'.SITE.'/css/sa'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" rel="stylesheet" type="text/css" />' : '').
		(@$_GET['p'] == 'sa' ? '<script type="text/javascript" src="'.SITE.'/js/sa'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>' : '').

		'<script type="text/javascript" src="'.SITE.'/js/G_values.js?'.G_VALUES.'"></script>'.
		'</head>'.
		'<body>'.
		'<div id="frameBody">'.
			'<iframe id="frameHidden" name="frameHidden"></iframe>'.
			(SA_VIEWER_ID ? '<div class="sa_viewer_msg">�� ����� ��� ������������� '._viewer(SA_VIEWER_ID, 'link').'. <a class="leave">�����</a></div>' : '');
}//_header()

function _dopLinks($p, $data, $d=false, $d1=false) {//�������������� ���� �� ����� ����
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

function GvaluesCreate() {//����������� ����� G_values.js
	$save = 'function _toAss(s){var a=[];for(var n=0;n<s.length;a[s[n].uid]=s[n].title,n++);return a}'.
	"\n".'var COLOR_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_color_name` ORDER BY `name` ASC").','.
		"\n".'COLORPRE_SPISOK='.query_selJson("SELECT `id`,`predlog` FROM `setup_color_name` ORDER BY `predlog` ASC").','.
		"\n".'INVOICE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `invoice` ORDER BY `id`").','.
		"\n".'INCOME_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_income` ORDER BY `sort`").','.
		"\n".'EXPENSE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_expense` ORDER BY `sort` ASC").','.
		"\n".'EXPENSE_WORKER='.query_ptpJson("SELECT `id`,`show_worker` FROM `setup_expense` WHERE `show_worker`").','.
		"\n".'FAULT_ASS='.query_ptpJson("SELECT `id`,`name` FROM `setup_fault` ORDER BY `sort`").','.
		"\n".'ZPNAME_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_zp_name` ORDER BY `name`").','.
		"\n".'DEVSTATUS_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_device_status` ORDER BY `sort`").','.
		"\n".'DEVPLACE_SPISOK='._selJson(_devPlace()).','.
		"\n".'DEV_SPISOK='.query_selJson("SELECT `id`,`name` FROM `base_device` ORDER BY `sort`").','.
		"\n".'DEV_ASS=_toAss(DEV_SPISOK),'.
		"\n".'ZE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_zayav_expense` ORDER BY `sort`").','.
		"\n".'ZE_TXT='.query_ptpJson("SELECT `id`,1 FROM `setup_zayav_expense` WHERE `dop`=1").','.
		"\n".'ZE_WORKER='.query_ptpJson("SELECT `id`,1 FROM `setup_zayav_expense` WHERE `dop`=2").','.
		"\n".'ZE_ZP='.query_ptpJson("SELECT `id`,1 FROM `setup_zayav_expense` WHERE `dop`=3").','.
		"\n".'ZE_DOP='._selJson(_zayavExpenseDop()).','.
		"\n".'COUNTRY_SPISOK=['.
			'{uid:1,title:"������"},'.
			'{uid:2,title:"�������"},'.
			'{uid:3,title:"��������"},'.
			'{uid:4,title:"���������"},'.
			'{uid:5,title:"�����������"},'.
			'{uid:6,title:"�������"},'.
			'{uid:7,title:"������"},'.
			'{uid:8,title:"�������"},'.
			'{uid:11,title:"����������"},'.
			'{uid:12,title:"������"},'.
			'{uid:13,title:"�����"},'.
			'{uid:14,title:"�������"},'.
			'{uid:15,title:"�������"},'.
			'{uid:16,title:"�����������"},'.
			'{uid:17,title:"���������"},'.
			'{uid:18,title:"����������"}],'.
		'COUNTRY_ASS=_toAss(COUNTRY_SPISOK),'.
		"\n".'CITY_SPISOK=['.
			'{uid:1,title:"������",content:"<b>������</b>"},'.
			'{uid:2,title:"�����-���������",content:"<b>�����-���������</b>"},'.
			'{uid:35,title:"������� ��������"},'.
			'{uid:10,title:"���������"},'.
			'{uid:49,title:"������������"},'.
			'{uid:60,title:"������"},'.
			'{uid:61,title:"�����������"},'.
			'{uid:72,title:"���������"},'.
			'{uid:73,title:"����������"},'.
			'{uid:87,title:"��������"},'.
			'{uid:95,title:"������ ��������"},'.
			'{uid:99,title:"�����������"},'.
			'{uid:104,title:"����"},'.
			'{uid:110,title:"�����"},'.
			'{uid:119,title:"������-��-����"},'.
			'{uid:123,title:"������"},'.
			'{uid:125,title:"�������"},'.
			'{uid:151,title:"���"},'.
			'{uid:158,title:"���������"}];';

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

	$fp = fopen(PATH.'js/G_values.js', 'w+');
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
function _zpAvaiSet($zp_id) { // ���������� ���������� ������� ��������
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
		1 => '� ����������',
		2 => '� �������'
	);
	if($place_id == false)
		return $arr;
	return isset($arr[$place_id]) ? $arr[$place_id] : '';
}//_devPlace()
function _devStatus($status_id) {
	if(!defined('DEV_STATUS_LOADED')) {
		$key = CACHE_PREFIX.'device_status';
		$zp = xcache_get($key);
		if(empty($zp)) {
			$sql = "SELECT `id`,`name` FROM `setup_device_status` ORDER BY `id` ASC";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$zp[$r['id']] = $r['name'];
			xcache_set($key, $zp, 86400);
		}
		foreach($zp as $id => $name)
			define('DEV_STATUS_'.$id, $name);
		define('DEV_STATUS_0', '�� ��������');
		define('DEV_STATUS_LOADED', true);
	}
	return constant('DEV_STATUS_'.$status_id);
}//_devStatus()

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
function devEquipCheck($device_id=0, $ids='') {//��������� ������ ������������ � ���� ��������� ��� �������� ��� �������������� ������
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



// ---===! ws_create !===--- ������ �������� ����������

function ws_create_info() {
	return
	'<div class="ws-create-info">'.
		'<div class="txt">'.
			'<h3>����� ���������� � ���������� Hi-Tech Service!</h3>'.
			'������ ���������� �������� ���������� ��� ����� ������� ��������� ���������, '.
			'���, ���������, ����������� � ������ ���������������� ���������� � ������� �������.<br />'.
			'<br />'.
			'<U>��� ������ ��������� �����:</U><br />'.
			'- ����� ���������� ���� (�������, �������� ���������� � ��������, ������� ����� ���������� � ������);<br />'.
			'- ����� ���� ���������, �������� � ������;<br />'.
			'- ��������� ������ �� ����������� ������;<br />'.
			'- ��������� ������� � ����� ���� �������� �������;<br />'.
			'- ��������, �������� ���������� � ���������.<br />'.
			'<br />'.
			'��� ����, ����� ������ ������������ �����������, ���������� ������� ���� ����������.'.
		'</div>'.
		'<div class="vkButton"><button onclick="location.href=\''.URL.'&p=wscreate&d=step1\'">���������� � �������� ����������</button></div>'.
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
			'��� ������ ���������� ������� �������� ����� ���������� � �����, � ������� �� ����������.<br />'.
			'����������� � ��������� ��������� ����� ����� �������� ��� �������� �������.'.
		'</div>'.
		'<div class="headName">�������� ����������</div>'.
		'<TABLE class="tab">'.
			'<TR><TD class="label">�������� �����������:<TD><INPUT type="text" id="org_name" maxlength="100">'.
			'<TR><TD class="label">������:<TD><INPUT type="hidden" id="countries" value="'.VIEWER_COUNTRY_ID.'">'.
			'<TR><TD class="label">�����:<TD><INPUT type="hidden" id="cities" value="0">'.
			'<TR><TD class="label">������� �������������:<TD><b>'.VIEWER_NAME.'</b>'.
			'<TR><TD class="label topi">��������� ���������,<br />�������� �������<br />�� �����������:<TD id="devs">'.$checkDevs.
		'</TABLE>'.

		'<div class="vkButton"><button>������</button></div>'.
		'<div class="vkCancel"><button>������</button></div>'.
		'<script type="text/javascript" src="'.SITE.'/js/ws_create_step1'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.
	'</div>';
}//ws_create_step1()









/*
function to_new_images() {//������� �������� � ����� ������
	define('IMLINK', 'http://'.DOMAIN.'/files/images/');
	define('IMPATH', PATH.'files/images/');
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
*/
