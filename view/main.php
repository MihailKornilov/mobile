<?php
function _hashRead() {
    $_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 'zayav';
    if(empty($_GET['hash'])) {
        define('HASH_VALUES', false);
        if(isset($_GET['start'])) {// восстановление последней посещённой страницы
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
}//end of _hashRead()
function _hashCookieSet() {
    setcookie('p', $_GET['p'], time() + 2592000, '/');
    setcookie('d', isset($_GET['d']) ? $_GET['d'] : '', time() + 2592000, '/');
    setcookie('d1', isset($_GET['d1']) ? $_GET['d1'] : '', time() + 2592000, '/');
    setcookie('id', isset($_GET['id']) ? $_GET['id'] : '', time() + 2592000, '/');
}//end of _hashCookieSet()
function _cacheClear($ws_id=WS_ID) {
    xcache_unset(CACHE_PREFIX.'setup_global');
    xcache_unset(CACHE_PREFIX.'viewer_'.VIEWER_ID);
    xcache_unset(CACHE_PREFIX.'device_name');
    xcache_unset(CACHE_PREFIX.'vendor_name');
    xcache_unset(CACHE_PREFIX.'model_name_count');
    xcache_unset(CACHE_PREFIX.'zp_name');
    xcache_unset(CACHE_PREFIX.'color_name');
    xcache_unset(CACHE_PREFIX.'device_place');
    xcache_unset(CACHE_PREFIX.'device_status');
    xcache_unset(CACHE_PREFIX.'device_equip');
    if($ws_id) {
        xcache_unset(CACHE_PREFIX.'remind_active'.$ws_id);
        xcache_unset(CACHE_PREFIX.'workshop_'.$ws_id);
        xcache_unset(CACHE_PREFIX.'zayav_base_device'.$ws_id);
        xcache_unset(CACHE_PREFIX.'zayav_base_vendor'.$ws_id);
        xcache_unset(CACHE_PREFIX.'zayav_base_model'.$ws_id);
    }
}//ens of _cacheClear()

function _header() {
    global $html;
    $html =
        '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
        //'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.
        '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.
        '<head>'.
        '<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
        '<title>Hi-tech Service - Приложение '.API_ID.'</title>'.

        //Отслеживание ошибок в скриптах
        (SA ? '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/errors.js?'.VERSION.'"></script>' : '').

        //Стороние скрипты
        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/jquery-2.0.3.min.js"></script>'.
        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/'.(DEBUG ? '' : 'min/').'xd_connection.js"></script>'.

        //Установка начального значения таймера.
        (SA ? '<script type="text/javascript">var TIME=(new Date()).getTime();</script>' : '').

        '<script type="text/javascript">'.
            (LOCAL ? 'for(var i in VK)if(typeof VK[i]=="function")VK[i]=function(){return false};' : '').
            'var G={},'.
                'DOMAIN="'.DOMAIN.'",'.
                'VALUES="'.VALUES.'",'.
                (defined('WS_DEVS') ? 'WS_DEVS=['.WS_DEVS.'],' : '').
                'VIEWER_ID='.VIEWER_ID.';'.
        '</script>'.

        //Подключение стилей VK. Должны стоять до основных стилей сайта
        '<link href="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/'.(DEBUG ? '' : 'min/').'vk.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.

        '<link href="'.SITE.'/css/main.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
        '<script type="text/javascript" src="'.SITE.'/js/main.js?'.VERSION.'"></script>'.

        //Подключение API VK
        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/'.(DEBUG ? '' : 'min/').'vk.js?'.VERSION.'"></script>'.

        (WS_ID ? '<script type="text/javascript" src="'.SITE.'/js/ws.js?'.VERSION.'"></script>' : '').

        //Скрипты и стили для суперадминистратора
        (@$_GET['p'] == 'sa' ? '<link href="'.SITE.'/css/sa.css?'.VERSION.'" rel="stylesheet" type="text/css" />' : '').
        (@$_GET['p'] == 'sa' ? '<script type="text/javascript" src="'.SITE.'/js/sa.js?'.VERSION.'"></script>' : '').

        '<script type="text/javascript" src="'.SITE.'/js/G_values.js?'.G_VALUES.'"></script>'.
        '</head>'.
        '<body>'.
        '<div id="frameBody">'.
        '<iframe id="frameHidden" name="frameHidden"></iframe>'.
        (SA_VIEWER_ID ? '<div class="sa_viewer_msg">Вы вошли под пользователем '._viewer(SA_VIEWER_ID, 'link').'. <a class="leave">Выйти</a></div>' : '');
}//end of _header()

function _footer() {
    global $html, $sqlQuery, $sqlCount, $sqlTime;
    if(SA) {
        $d = empty($_GET['d']) ? '' :'&pre_d='.$_GET['d'];
        $d1 = empty($_GET['d1']) ? '' :'&pre_d1='.$_GET['d1'];
        $id = empty($_GET['id']) ? '' :'&pre_id='.$_GET['id'];
        $html .= '<div id="admin">'.
                ($_GET['p'] != 'sa' && !SA_VIEWER_ID ? '<a href="'.URL.'&p=sa&pre_p='.$_GET['p'].$d.$d1.$id.'">Admin</a> :: ' : '').
                '<a href="http://vkmobile.reformal.ru" target="_blank">Reformal</a> :: '.
                '<a class="debug_toggle'.(DEBUG ? ' on' : '').'">В'.(DEBUG ? 'ы' : '').'ключить Debug</a> :: '.
                '<a id="cache_clear">Очисить кэш ('.VERSION.')</a> :: '.
                'sql <b>'.$sqlCount.'</b> ('.round($sqlTime, 3).') :: '.
                'php '.round(microtime(true) - TIME, 3).' :: '.
                'js <EM></EM>'.
            '</div>'
            .(DEBUG ? $sqlQuery : '');
    }
    $getArr = array(
        'start' => 1,
        'api_url' => 1,
        'api_id' => 1,
        'api_settings' => 1,
        'viewer_id' => 1,
        'viewer_type' => 1,
        'sid' => 1,
        'secret' => 1,
        'access_token' => 1,
        'user_id' => 1,
        'group_id' => 1,
        'is_app_user' => 1,
        'auth_key' => 1,
        'language' => 1,
        'parent_language' => 1,
        'ad_info' => 1,
        'is_secure' => 1,
        'referrer' => 1,
        'lc_name' => 1,
        'hash' => 1
    );
    $gValues = array();
    foreach($_GET as $k => $val) {
        if(isset($getArr[$k]) || empty($_GET[$k])) continue;
        $gValues[] = '"'.$k.'":"'.$val.'"';
    }
    $html .= '<script type="text/javascript">'.
                'hashSet({'.implode(',', $gValues).'});'.
                (SA ? '$("#admin EM").html(((new Date().getTime())-TIME)/1000);' : '').
             '</script>'.
         '</div></BODY></HTML>';
}//end of _footer()

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
}//end of _dopLinks()

function GvaluesCreate() {//Составление файла G_values.js
    $save = 'function SpisokToAss(s){var a=[];for(var n=0;n<s.length;a[s[n].uid]=s[n].title,n++);return a}'.
        'G.color_spisok='.query_selJson("SELECT `id`,`name` FROM setup_color_name ORDER BY name").';G.color_ass=SpisokToAss(G.color_spisok);'.
        'G.fault_spisok='.query_selJson("SELECT `id`,`name` FROM setup_fault ORDER BY sort").';G.fault_ass=SpisokToAss(G.fault_spisok);'.
        'G.zp_name_spisok='.query_selJson("SELECT `id`,`name` FROM setup_zp_name ORDER BY name").';G.zp_name_ass=SpisokToAss(G.zp_name_spisok);'.
        'G.device_status_spisok='.query_selJson("SELECT `id`,`name` FROM setup_device_status ORDER BY sort").';G.device_status_spisok.unshift({uid:0, title:"не известно"});G.device_status_ass=SpisokToAss(G.device_status_spisok);'.
        'G.device_place_spisok='.query_selJson("SELECT `id`,`name` FROM setup_device_place ORDER BY sort").';G.device_place_ass=SpisokToAss(G.device_place_spisok);'.

        'G.device_spisok='.query_selJson("SELECT `id`,`name` FROM base_device ORDER BY sort").';G.device_ass=SpisokToAss(G.device_spisok);'.
        'G.device_rod_spisok='.query_selJson("SELECT `id`,name_rod FROM base_device ORDER BY sort").';G.device_rod_ass=SpisokToAss(G.device_rod_spisok);'.
        'G.device_mn_spisok='.query_selJson("SELECT `id`,name_mn FROM base_device ORDER BY sort").';G.device_mn_ass=SpisokToAss(G.device_mn_spisok);';

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
    $save .= 'G.vendor_spisok={'.implode(',', $v).'};'.
             'G.vendor_ass=[];'.
             'G.vendor_ass[0]="";'.
             'for(var k in G.vendor_spisok){for(var n=0;n<G.vendor_spisok[k].length;n++){var sp=G.vendor_spisok[k][n];G.vendor_ass[sp.uid]=sp.title;}}';


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
        $m[] =  $n.':['.implode(',',$model[$n]).']';
    $save .= 'G.model_spisok={'.implode(',',$m).'};'.
             'G.model_ass=[];'.
             'G.model_ass[0]="";'.
             'for(var k in G.model_spisok){for(var n=0;n<G.model_spisok[k].length;n++){var sp=G.model_spisok[k][n];G.model_ass[sp.uid]=sp.title;}}';

    $fp = fopen(PATH_FILES.'../js/G_values.js','w+');
    fwrite($fp, $save);
    fclose($fp);

    query("UPDATE `setup_global` SET `g_values`=`g_values`+1");
}//end of GvaluesCreate()

function _selJson($arr) {
    $send = array();
    foreach($arr as $uid => $title)
        $send[] = '{uid:'.$uid.',title:"'.$title.'"}';
    return '['.implode(',',$send).']';
}//end of _selJson()

function _imageResize($x_cur, $y_cur, $x_new, $y_new) { // изменение размера изображения
    $x = $x_new;
    $y = $y_new;
    // если ширина больше или равна высоте
    if ($x_cur >= $y_cur) {
        if ($x > $x_cur) { $x = $x_cur; } // если новая ширина больше, чем исходная, то X остаётся исходным
        $y = round($y_cur / $x_cur * $x);
        if ($y > $y_new) { // если новая высота в итоге осталась меньше исходной, то подравнивание по Y
            $y = $y_new;
            $x = round($x_cur / $y_cur * $y);
        }
    }

    // если выстоа больше ширины
    if ($y_cur > $x_cur) {
        if ($y > $y_cur) { $y = $y_cur; } // если новая высота больше, чем исходная, то Y остаётся исходным
        $x = round($x_cur / $y_cur * $y);
        if ($x > $x_new) { // если новая ширина в итоге осталась меньше исходной, то подравнивание по X
            $x = $x_new;
            $y = round($y_cur / $x_cur * $x);
        }
    }

    $send['x'] = $x;
    $send['y'] = $y;
    return $send;
}//end of _imageResize()
function _getImg($type, $arr, $size='small', $x_new=10000, $y_new=10000, $class=false) {
    $id = false;
    if(!is_array($arr)) {
        $id = 'IMG_'.strtoupper($type).$arr;
        $arr = array($arr);
    }
    if($id && defined($id))
        return array(
            'success' => constant($id.'_RES'),
            'img' => constant($id)
        );
    $send = array(
        'success' => false,
        'img' => '<img src="/img/nofoto-'.$size.'.gif"'.($x_new < 10000 ? ' width="'.$x_new.'"' : '').'>'
    );
    $owners = array();
    foreach($arr as $k => $r) {
        $arr[$k] = "'".$type.$r."'";
        $owners[$type.$r] = false;
    }
    $sql = "SELECT *
            FROM `images`
            WHERE `status`=1
              AND `sort`=0
              AND `owner` IN (".implode(',', $arr).")
            GROUP BY `owner`";
    $q = query($sql);
    while($r = mysql_fetch_assoc($q)) {
        $s = _imageResize($r[$size.'_x'], $r[$size.'_y'], $x_new, $y_new);
        $c = 'IMG_'.strtoupper($r['owner']);
        define($c, '<img src="'.$r['link'].'-'.$size.'.jpg"'.
                        'width="'.$s['x'].'"'.
                        'height="'.$s['y'].'" '.
              ($class ? 'class="'.$class.'"' : '').
                        'val="'.$r['owner'].'">');
        define($c.'_RES', true);
        $owners[$r['owner']] = constant($c);
    }
    foreach($owners as $k => $ow)
        if(!$ow) {
            $c = 'IMG_'.strtoupper($k);
            define($c, $send['img']);
            define($c.'_RES', false);
        }
    if($id)
        return array(
            'success' => constant($id.'_RES'),
            'img' => constant($id)
        );
    return $send;
}//end of _getImg()
function _zayavImg($zayav_id, $size='small', $x_new=10000, $y_new=10000, $class=false) {
    $res = _getImg('zayav', $zayav_id, $size, $x_new, $y_new, $class);
    if($res['success'])
        return $res['img'];
    $sql = "SELECT `base_model_id` FROM `zayavki` WHERE `id`=".$zayav_id." LIMIT 1";
    $r = mysql_fetch_assoc(query($sql));
    return _modelImg($r['base_model_id'], $size, $x_new, $y_new, $class);
}//end of _zayavImg()
function _modelImg($model_id, $size='small', $x_new=10000, $y_new=10000, $class=false) {
    $res = _getImg('dev', $model_id, $size, $x_new, $y_new, $class);
    return $res['img'];
}//end of _modelImg()
function _zpImg($zp_id, $size='small', $x_new=10000, $y_new=10000, $class=false) {
    $res = _getImg('zp', $zp_id, $size, $x_new, $y_new, $class);
    return $res['img'];
}//end of _modelImg()

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
}//end of _deviceName()
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
}//end of _vendorName()
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
}//end of _modelName()
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
}//end of _zpName()
function _zpCompatId($zp_id) {
    $sql = "SELECT `id`,`compat_id` FROM `zp_catalog` WHERE `id`=".intval($zp_id);
    $zp = mysql_fetch_assoc(query($sql));
    return $zp['compat_id'] ? $zp['compat_id'] : $zp['id'];
}//end of _zpCompatId()
function _zpAvaiSet($zp_id) { // Обновление количества наличия запчасти
    $zp_id = _zpCompatId($zp_id);
    $count = query_value("SELECT IFNULL(SUM(`count`),0) FROM `zp_move` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id." LIMIT 1");
    query("DELETE FROM `zp_avai` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id);
    if($count > 0)
        query("INSERT INTO `zp_avai` (`ws_id`,`zp_id`,`count`) VALUES (".WS_ID.",".$zp_id.",".$count.")");
    return $count;
}//end of _zpAvaiSet()
function _colorName($color_id) {
    if(!defined('COLOR_LOADED')) {
        $key = CACHE_PREFIX.'color_name';
        $zp = xcache_get($key);
        if(empty($zp)) {
            $sql = "SELECT `id`,`name` FROM `setup_color_name` ORDER BY `id` ASC";
            $q = query($sql);
            while($r = mysql_fetch_assoc($q))
                $zp[$r['id']] = $r['name'];
            xcache_set($key, $zp, 86400);
        }
        foreach($zp as $id => $name)
            define('COLOR_'.$id, $name);
        define('COLOR_0', '');
        define('COLOR_LOADED', true);
    }
    return constant('COLOR_'.$color_id);
}//end of _colorName()
function _devPlace($place_id) {
    if(!defined('PLACE_LOADED')) {
        $key = CACHE_PREFIX.'device_place';
        $zp = xcache_get($key);
        if(empty($zp)) {
            $sql = "SELECT `id`,`name` FROM `setup_device_place` ORDER BY `id` ASC";
            $q = query($sql);
            while($r = mysql_fetch_assoc($q))
                $zp[$r['id']] = $r['name'];
            xcache_set($key, $zp, 86400);
        }
        foreach($zp as $id => $name)
            define('PLACE_'.$id, $name);
        define('PLACE_0', '');
        define('PLACE_LOADED', true);
    }
    return constant('PLACE_'.$place_id);
}//end of _devPlace()
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
        define('DEV_STATUS_0', 'не известно');
        define('DEV_STATUS_LOADED', true);
    }
    return constant('DEV_STATUS_'.$status_id);
}//end of _devStatus()

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
}//end of equipCache()
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
}//end of devEquipCheck()



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
}//end of ws_create_info()
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
            '<TR><TD class="label top">Категории устройств,<br />ремонтом которых<br />Вы занимаетесь:<TD id="devs">'.$checkDevs.
        '</TABLE>'.

        '<div class="vkButton"><button>Готово</button></div>'.
        '<div class="vkCancel"><button>Отмена</button></div>'.
        '<script type="text/javascript" src="'.SITE.'/js/ws_create_step1.js?'.VERSION.'"></script>'.
    '</div>';
}//end of ws_create_step1()