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
				$v = array();
				if(HASH_VALUES) {
					$ex = explode('.', HASH_VALUES);
					foreach($ex as $r) {
						$arr = explode('=', $r);
						$v[$arr[0]] = $arr[1];
					}
				} else {
					foreach($_COOKIE as $k => $val) {
						$arr = explode('zayav_', $k);
						if(isset($arr[1]))
							$v[$arr[1]] = $val;
					}
				}
				$v = array(
					'find' => isset($v['find']) ? unescape($v['find']) : '',
					'sort' => isset($v['sort']) ? intval($v['sort']) : 1,
					'desc' => isset($v['desc']) && intval($v['desc']) == 1 ? 1 : 0,
					'status' => isset($v['status']) ? intval($v['status']) : 0,
					'diff' => isset($v['diff']) && intval($v['diff']) == 1 ? 1 : 0,
					'zpzakaz' => isset($v['zpzakaz']) ? intval($v['zpzakaz']) : 0,
					'device' => isset($v['device']) ? intval($v['device']) : 0,
					'vendor' => isset($v['vendor']) ? intval($v['vendor']) : 0,
					'model' => isset($v['model']) ? intval($v['model']) : 0,
					'place' => isset($v['place']) ? $v['place'] : 0,
					'devstatus' => isset($v['devstatus']) ? $v['devstatus'] : 0
				);
				$html .= zayav_list($v);
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
				$v = array();
				if(HASH_VALUES) {
					$ex = explode('.', HASH_VALUES);
					foreach($ex as $r) {
						$arr = explode('=', $r);
						$v[$arr[0]] = $arr[1];
					}
				} else
					$v = $_GET;

				$v = zpfilter($v);
				$v['find'] = unescape($v['find']);
				$html .= zp_list(zp_data(1, $v));
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
			case 'devstatus': $html .= sa_devstatus(); break;
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