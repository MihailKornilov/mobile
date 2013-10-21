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
        if(!WS_ID)
            header('Location:'.URL.'&p=wscreate');
        _mainLinks();
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

    case 'sa':
        if(!SA)
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
            default: $html .= sa_index();
        }
        break;

    default: header('Location:'.URL.'&p=zayav');
}

_footer();
mysql_close();
echo $html;