<?php
require_once('config.php');

if(!WS_ID) {
	switch(@$_GET['d']) {
		case 'step1': $html = ws_create_step1(); break;
		default: $html = ws_create_info();
	}
	die(_header().$html._footer());
}



$html = _header();
$html .= _menu();
$html .= _global_index();


switch($_GET['p']) {
	case 'zayav':
		switch(zayavCookie('get')) {
			case 'add':
				$v = array();
				if(isset($_GET['imei']) && preg_match(REGEXP_WORD, $_GET['imei']))
					$v['imei'] = strtoupper(htmlspecialchars(trim($_GET['imei'])));
				if(isset($_GET['serial']) && preg_match(REGEXP_WORD, $_GET['serial']))
					$v['serial'] = strtoupper(htmlspecialchars(trim($_GET['serial'])));
				$html .= zayav_add($v);
				break;
			case 'cartridge': $html .= zayav_cartridge(_hashFilter('cartridge')); break;
			case 'info': $html .= zayav_info(); break;
			default: $html .= zayav_list(_hashFilter('zayav'));
		}
		break;
	case 'tovar':
		if(!WS_ID)
			header('Location:'.URL.'&p=wscreate');

		$v = array();
		if(HASH_VALUES) {
			$ex = explode('.', HASH_VALUES);
			foreach($ex as $r) {
				$arr = explode('=', $r);
				$v[$arr[0]] = $arr[1];
			}
		} else
			foreach($_COOKIE as $k => $val) {
				$arr = explode(VIEWER_ID.'_tovar_', $k);
				if(isset($arr[1]))
					$v[$arr[1]] = $val;
			}
		$html .= tovar($v);
		break;
	case 'zp':
		if(!WS_ID)
			header('Location:'.URL.'&p=wscreate');
		switch(@$_GET['d']) {
			case 'info':
				if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
					$html .= 'Страницы не существует';
					break;
				}
				$html .= zp_info(intval($_GET['id']));
				break;
			default:
				$v = array();
				if(HASH_VALUES) {
					$ex = explode('.', HASH_VALUES);
					foreach($ex as $r) {
						$arr = explode('=', $r);
						$v[$arr[0]] = $arr[1];
					}
				} else
					foreach($_COOKIE as $k => $val) {
						$arr = explode(VIEWER_ID.'_zp_', $k);
						if(isset($arr[1]))
							$v[$arr[1]] = $val;
					}

				$v = zpfilter($v);
				$v['find'] = unescape(@$v['find']);
				$html .= zp_list($v);
		}
		break;

	case 'sa':
		if(!SA || SA_VIEWER_ID)
			header('Location:'.URL.'&p=zayav');
		switch(@$_GET['d']) {
			case 'user': $html .= sa_user(); break;
			case 'ws':
				if(isset($_GET['id']) && preg_match(REGEXP_NUMERIC, $_GET['id'])) {
					$html .= sa_ws_info(intval($_GET['id']));
					break;
				}
				$html .= sa_ws();
				break;
			case 'tovar_category': $html .= sa_tovar_category(); break;
			case 'device': $html .= sa_device(); break;
			case 'vendor': $html .= sa_vendor(); break;
			case 'model': $html .= sa_model(); break;
			case 'equip': $html .= sa_equip(); break;
			case 'fault': $html .= sa_fault(); break;
			case 'color': $html .= sa_color(); break;
			case 'zpname': $html .= sa_zpname(); break;
		}
		break;
}

$html .= _footer();

die($html);
