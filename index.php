<?php
require_once('include/conf.php');
require_once('config.php');
require_once('view/main.php');

if (!$AUTH) { echo "Ошибка авторизации, <A href='http://vk.com/app".$_GET['api_id']."'>попробуйте снова</A>."; exit(); }

if ($_GET['start']) {
  require_once('include/vkapi.class.php');
  $VKAPI = new vkapi($_GET['api_id'], SECRET); 

  $res = $VKAPI->api('users.get',array('uids' => $_GET['viewer_id'], 'fields' => 'photo,sex,country,city'));
  $vku->first_name = win1251($res['response'][0]['first_name']);
  $vku->last_name = win1251($res['response'][0]['last_name']);
  $vku->sex = $res['response'][0]['sex'];
  $vku->photo = $res['response'][0]['photo'];
  $vku->country_id = isset($res['response'][0]['country']) ? $res['response'][0]['country'] : 0;
  $vku->city_id = isset($res['response'][0]['city']) ? $res['response'][0]['city'] : 0;
  $vku->enter_last = curTime();

  if (isset($vku->viewer_id)) {
    $VK->Query("update vk_user set
first_name='".$vku->first_name."',
last_name='".$vku->last_name."',
sex='".$vku->sex."',
photo='".$vku->photo."',
country_id=".$vku->country_id.",
city_id=".$vku->city_id.",
enter_last=current_timestamp where viewer_id=".$vku->viewer_id);
  } else {
    $vku->viewer_id = $_GET['viewer_id'];
    $vku->ws_id = 0;
    $VK->Query("insert into vk_user (
viewer_id,
first_name,
last_name,
sex,
photo,
country_id,
city_id,
enter_last
) values (
".$vku->viewer_id.",
'".$vku->first_name."',
'".$vku->last_name."',
'".$vku->sex."',
'".$vku->photo."',
".$vku->country_id.",
".$vku->city_id.",
current_timestamp)");
  }

  // восстановление последней посещённой страницы
  if (isset($_COOKIE['my_page'])) { $_GET['my_page'] = $_COOKIE['my_page']; }
  if (isset($_COOKIE['id'])) { $_GET['id'] = $_COOKIE['id']; }
} else {
  setcookie('my_page', $_GET['my_page'], time() + 2592000, '/');
  setcookie('id', $_GET['id'], time() + 2592000, '/');
}





if ($_GET['hash']) {
  $ex = explode('_',$_GET['hash']);
  $_GET['my_page'] = $ex[0];
  $_GET['id'] = isset($ex[1]) ? $ex[1] : '';
}



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

  case 'remClient':     include('remont/client/spisok/client_tpl.php');break;          // список клиентов
  case 'remClientInfo': include('remont/client/info/clientInfo_tpl.php');break;     // информация о клиенте

  case 'remZayavkiInfo':include('remont/zayavki/info/zayavkiInfo_tpl.php');break;

  case 'remZp':         include('remont/zp/zp_tpl.php');break; // запчасти
    
  case 'remDevice':     include('remont/device/device_tpl.php');break;
  case 'remDeviceView': include('remont/device/view/deviceView_tpl.php');break;
  case 'remDeviceEdit': include('remont/device/view/deviceEdit_tpl.php');break;

  case 'remSetup':      include('remont/setup/ws/ws_tpl.php');break;
  case 'remSetupWorker':include('remont/setup/worker/worker_tpl.php');break;

  case 'nopage':        include('nopage_tpl.php');break;      // несуществующая страница

  // создание мастерской
  case 'wsIndex':       include('workshop/wsIndex_tpl.php');break;
  case 'wsStep1':       include('workshop/wsStep1_tpl.php');break;
}

if(isset($_GET['p'])) {
    _header();
    _mainLinks();
    switch(@$_GET['p']) {
        case 'zayav':
            switch(@$_GET['d']) {
                case 'add': $html .= zayav_add(); break;
                default:
                    $values = array(
                        'find' => isset($_COOKIE['zayav_find']) ? unescape($_COOKIE['zayav_find']) : '',
                        'sort' => isset($_COOKIE['zayav_sort']) ? intval($_COOKIE['zayav_sort']) : 1,
                        'desc' => isset($_COOKIE['zayav_desc']) && intval($_COOKIE['zayav_desc']) == 1 ? 1 : 0,
                        'status' => isset($_COOKIE['zayav_status']) ? intval($_COOKIE['zayav_status']) : 0,
                        'device' => isset($_COOKIE['zayav_device']) ? intval($_COOKIE['zayav_device']) : 0,
                        'vendor' => isset($_COOKIE['zayav_vendor']) ? intval($_COOKIE['zayav_vendor']) : 0,
                        'model' => isset($_COOKIE['zayav_model']) ? intval($_COOKIE['zayav_model']) : 0,
                        'place' => isset($_COOKIE['zayav_place']) ? $_COOKIE['zayav_place'] : 0,
                        'dev_status' => isset($_COOKIE['zayav_dev_status']) ? $_COOKIE['zayav_dev_status'] : 0
                    );
                    $html .= show_zayav_list(get_zayav_list(1, zayavfilter($values)), $values);
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
            if(@$_GET['d1'] != 'stat') {
                $report = '<table cellspacing=0 class="tabLR"><tr>'.
                    '<td class="left">'.$dl.$report.'</td>'.
                    '<td class="right">'.$rl.'</td>'.
                    '</tr></table>';
            } else
                $report = $dl.$report;
            $html .= $report;
        break;
    }
    _footer();
    mysql_close();
    echo $html;
}

if(empty($_GET['my_page']) && empty($_GET['p']))
    include('remont/zayavki/spisok/zayavki_tpl.php');