<?php
require_once('config.php');
require_once('view/ws.php');

_hashRead();
_header();

switch($_GET['p']) {
	case 'wscreate':
		if(WS_ID)
			header('Location:'.URL.'&p=zayav');
		switch(@$_GET['d']) {
			case 'step1': $html .= ws_create_step1(); break;
			default: $html .= ws_create_info();
		}
		break;

	case 'client':
		if(!WS_ID)
			header('Location:'.URL.'&p=wscreate');
		_mainLinks();
		switch(@$_GET['d']) {
			case 'info':
				if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
					$html .= 'Страницы не существует';
					break;
				}
				$html .= client_info(intval($_GET['id']));
				break;
			default:
				$html .= client_list(client_data());
		}
		break;
	case 'zayav':
		if(!WS_ID)
			header('Location:'.URL.'&p=wscreate');
		_mainLinks();
		switch(@$_GET['d']) {
			case 'add':
				$v = array();
				if(isset($_GET['imei']) && preg_match(REGEXP_WORD, $_GET['imei']))
					$v['imei'] = strtoupper(htmlspecialchars(trim($_GET['imei'])));
				if(isset($_GET['serial']) && preg_match(REGEXP_WORD, $_GET['serial']))
					$v['serial'] = strtoupper(htmlspecialchars(trim($_GET['serial'])));
				$html .= zayav_add($v);
				break;
			case 'info':
				if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
					$html .= 'Страницы не существует';
					break;
				}
				$html .= zayav_info(intval($_GET['id']));
				break;
			default:
				$values = array();
				if(HASH_VALUES) {
					$ex = explode('.', HASH_VALUES);
					foreach($ex as $r) {
						$arr = explode('=', $r);
						$values[$arr[0]] = $arr[1];
					}
				} else {
					foreach($_COOKIE as $k => $val) {
						$arr = explode('zayav_', $k);
						if(isset($arr[1]))
							$values[$arr[1]] = $val;
					}
				}
				$values = array(
					'find' => isset($values['find']) ? unescape($values['find']) : '',
					'sort' => isset($values['sort']) ? intval($values['sort']) : 1,
					'desc' => isset($values['desc']) && intval($values['desc']) == 1 ? 1 : 0,
					'status' => isset($values['status']) ? intval($values['status']) : 0,
					'diff' => isset($values['diff']) && intval($values['diff']) == 1 ? 1 : 0,
					'zpzakaz' => isset($values['zpzakaz']) ? intval($values['zpzakaz']) : 0,
					'device' => isset($values['device']) ? intval($values['device']) : 0,
					'vendor' => isset($values['vendor']) ? intval($values['vendor']) : 0,
					'model' => isset($values['model']) ? intval($values['model']) : 0,
					'place' => isset($values['place']) ? $values['place'] : 0,
					'devstatus' => isset($values['devstatus']) ? $values['devstatus'] : 0
				);
				$html .= zayav_list(zayav_data(1, zayavfilter($values)), $values);
		}
		break;
	case 'zp':
		if(!WS_ID)
			header('Location:'.URL.'&p=wscreate');
		_mainLinks();
		switch(@$_GET['d']) {
			case 'info':
				if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
					$html .= 'Страницы не существует';
					break;
				}
				$html .= zp_info(intval($_GET['id']));
				break;
			default:
				$values = array();
				if(HASH_VALUES) {
					$ex = explode('.', HASH_VALUES);
					foreach($ex as $r) {
						$arr = explode('=', $r);
						$values[$arr[0]] = $arr[1];
					}
				} else
					$values = $_GET;

				$values = zpfilter($values);
				$values['find'] = unescape($values['find']);
				$html .= zp_list(zp_data(1, $values));
		}
		break;
	case 'report':
		if(!WS_ID)
			header('Location:'.URL.'&p=wscreate');
		_mainLinks();
		$html .= report();
		break;
	case 'setup':
		if(!WS_ID)
			header('Location:'.URL.'&p=wscreate');
		_mainLinks();
		$html .= setup();
		break;

	case 'sa':
		if(!SA || SA_VIEWER_ID)
			header('Location:'.URL.'&p=zayav');
		require_once('view/sa.php');
		switch(@$_GET['d']) {
			case 'ws':
				if(isset($_GET['id']) && preg_match(REGEXP_NUMERIC, $_GET['id'])) {
					$html .= sa_ws_info(intval($_GET['id']));
					break;
				}
				$html .= sa_ws();
				break;
			case 'device': $html .= sa_device(); break;
			case 'vendor': $html .= sa_vendor(); break;
			case 'model': $html .= sa_model(); break;
			case 'equip': $html .= sa_equip(); break;
			case 'fault': $html .= sa_fault(); break;
			case 'color': $html .= sa_color(); break;
			case 'zpname': $html .= sa_zpname(); break;
			default: $html .= sa_index();
		}
		break;

	default: header('Location:'.URL.'&p=zayav');
}

_footer();
mysql_close();
echo $html;