<?php
require_once('config.php');

//if (!$AUTH) { echo "Ошибка авторизации, <A href='http://vk.com/app".$_GET['api_id']."'>попробуйте снова</A>."; exit(); }

/*
switch($_GET['my_page']) {
  // суперадминистратор
  case 'superAdmin':    include('superadmin/saIndex_tpl.php');break;
  case 'saVkUser':      include('superadmin/vk_user/vk_user_tpl.php');break;
  case 'saWS':          include('superadmin/ws/ws_tpl.php');break;
  case 'saFault':       include('superadmin/fault/saFault_tpl.php');break;      // Виды неисправностей
  case 'saDevice':      include('superadmin/device/setupDevice_tpl.php');break;
  case 'saDevSpec':     include('superadmin/device/specific/deviceSpecific_tpl.php');break;
  case 'saDevStatus':   include('superadmin/device/status/deviceStatus_tpl.php');break;
  case 'saDevPlace':    include('superadmin/device/place/devicePlace_tpl.php');break;
  case 'saVendor':      include('superadmin/vendor/setupVendor_tpl.php');break;
  case 'saModel':       include('superadmin/model/setupModel_tpl.php');break;
  case 'saZp':          include('superadmin/zp/setupZp_tpl.php');break;
  case 'saColor':       include('superadmin/color/setupColor_tpl.php');break;  // цвета для устройств и запчастей

  case 'nopage':        include('nopage_tpl.php');break;      // несуществующая страница

  case 'wsIndex':       include('workshop/wsIndex_tpl.php');break;
  case 'wsStep1':       include('workshop/wsStep1_tpl.php');break;
    default: unset($_GET['my_page']);
}
*/

_hashRead();
_header();

if(!WS_ID) {
    switch($_GET['p']) {
        default:
        case 'wscreate':
            switch(@$_GET['d']) {
                case 'step1': $html .= ws_create_step1(); break;
                default: $html .= ws_create_info();
            }
            break;
    }
}

if(WS_ID) {
    _mainLinks();
    switch($_GET['p']) {
        case 'client':
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
        default:
        case 'zayav':
            switch(@$_GET['d']) {
                case 'add': $html .= zayav_add(); break;
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
            $links = array(
                array(
                    'name' => 'История действий',
                    'd' => 'history',
                    'sel' => 1
                ),
                array(
                    'name' => 'Задания'.REMIND_ACTIVE.'<div class="img_add report_remind_add"></div>',
                    'd' => 'remind'
                ),
                array(
                    'name' => 'Деньги',
                    'd' => 'money'
                )
            );
            $rl = _rightLinks('report', $links, @$_GET['d']);
            $dl = '';
            switch(@$_GET['d']) {
                case 'remind':
                    $report = report_remind();
                    $rl .= report_remind_right();
                    break;
                case 'money':
                    switch(@$_GET['d1']) {
                        case 'rashod':
                            $report = report_rashod();
                            $rl .= report_rashod_right();
                            break;
                        case 'kassa':
                            $report = report_kassa();
                            $rl .= report_kassa_right();
                            break;
                        case 'stat': $report = statistic(); break;
                        default: // prihod
                            $report = report_prihod();
                            $rl .= report_prihod_right();
                    }
                    $links = array(
                        array(
                            'name' => 'Поступления',
                            'd' => 'prihod',
                            'sel' => 1
                        ),
                        array(
                            'name' => 'Расходы',
                            'd' => 'rashod'
                        ),
                        array(
                            'name' => 'Касса',
                            'd' => 'kassa'
                        ),
                        array(
                            'name' => 'Статистика',
                            'd' => 'stat'
                        )
                    );
                    $d1 = isset($_GET['d1']) ? $_GET['d1'] : 'prihod';
                    $dl = _dopLinks('report', $links, 'money', $d1);
                    break;
                default: // history
                    $report = report_history();
                    $rl .= report_history_right();
            }
            if(@$_GET['d1'] != 'stat')
                $report = '<table class="tabLR"><tr><td class="left">'.$dl.$report.'<td class="right">'.$rl.'</table>';
            else
                $report = $dl.$report;
            $html .= $report;
            break;
        case 'setup':
            $d = isset($_GET['d']) ? $_GET['d'] : 'main';
            $links = array(
                array(
                    'name' => 'Основные настройки',
                    'd' => 'main',
                    'sel' => 1
                ),
                array(
                    'name' => 'Сотрудники',
                    'd' => 'workers'
                )
            );
            $html .= _dopLinks('setup', $links, $d);
            switch($d) {
                case 'main':
                default:
                    $html .= setup_main();
                    break;
                case 'workers':
                    $html .= setup_workers();
                    break;
            }
            break;
    }
}
_footer();
mysql_close();
echo $html;