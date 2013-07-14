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
  $_GET['id'] = $ex[1];
}



switch ($_GET['my_page']) {
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

  case 'remZayavki':    include('remont/zayavki/spisok/zayavki_tpl.php');break;
  case 'remZayavkiAdd': include('remont/zayavki/add/zayavkiAdd_tpl.php');break;
  case 'remZayavkiInfo':include('remont/zayavki/info/zayavkiInfo_tpl.php');break;

  case 'remZp':         include('remont/zp/zp_tpl.php');break; // запчасти
    
  case 'remDevice':     include('remont/device/device_tpl.php');break;
  case 'remDeviceView': include('remont/device/view/deviceView_tpl.php');break;
  case 'remDeviceEdit': include('remont/device/view/deviceEdit_tpl.php');break;

  case 'remSetup':      include('remont/setup/ws/ws_tpl.php');break;
  case 'remSetupWorker':include('remont/setup/worker/worker_tpl.php');break;

  case 'catZp':         include('catalog/zp/catalogZp_tpl.php');break;
  case 'catZpAdd':      include('catalog/zp/catalogZpAdd_tpl.php');break;
  case 'catZpView':     include('catalog/zp/catalogZpView_tpl.php');break;
  case 'catZpEdit':     include('catalog/zp/catalogZpEdit_tpl.php');break;

  case 'remReport':     include('remont/report/report_tpl.php');break; // отчёты

  case 'nopage':        include('nopage_tpl.php');break;      // несуществующая страница

  // создание мастерской
  case 'wsIndex':       include('workshop/wsIndex_tpl.php');break;
  case 'wsStep1':       include('workshop/wsStep1_tpl.php');break;
}

if(isset($_GET['p'])) {
    _header();
    _mainLinks();
    switch(@$_GET['p']) {
        case 'report':
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
            $html .= _dopLinks('report', $links, @$_GET['d']);
            switch(@$_GET['d']){
                case 'prihod': $html .= 'Приход'; break;
                case 'rashod': $html .= 'Расходы'; break;
                case 'kassa': $html .= 'Касса'; break;
                default: $html .= statistic();
            }
        break;
    }
    _footer();
    mysql_close();
    echo $html;
}