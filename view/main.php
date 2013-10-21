<?php
function _vkUserUpdate($uid=VIEWER_ID) {//Обновление пользователя из Контакта
    require_once(DOCUMENT_ROOT.'/include/vkapi.class.php');
    $VKAPI = new vkapi($_GET['api_id'], SECRET);
    $res = $VKAPI->api('users.get',array('uids' => $uid, 'fields' => 'photo,sex,country,city'));
    $u = $res['response'][0];
    $u['first_name'] = win1251($u['first_name']);
    $u['last_name'] = win1251($u['last_name']);
    $u['country_id'] = isset($u['country']) ? $u['country'] : 0;
    $u['city_id'] = isset($u['city']) ? $u['city'] : 0;
    $u['menu_left_set'] = 0;

    // установил ли приложение
    $app = $VKAPI->api('isAppUser', array('uid'=>$uid));
    $u['app_setup'] = $app['response'];

    // поместил ли в левое меню
    //$mls = $VKAPI->api('getUserSettings', array('uid'=>$uid));
    $u['menu_left_set'] = 0;//($mls['response']&256) > 0 ? 1 : 0;

    $sql = 'INSERT INTO `vk_user` (
                `viewer_id`,
                `first_name`,
                `last_name`,
                `sex`,
                `photo`,
                `app_setup`,
                `menu_left_set`,
                `country_id`,
                `city_id`
            ) VALUES (
                '.$uid.',
                "'.$u['first_name'].'",
                "'.$u['last_name'].'",
                '.$u['sex'].',
                "'.$u['photo'].'",
                '.$u['app_setup'].',
                '.$u['menu_left_set'].',
                '.$u['country_id'].',
                '.$u['city_id'].'
            ) ON DUPLICATE KEY UPDATE
                `first_name`="'.$u['first_name'].'",
                `last_name`="'.$u['last_name'].'",
                `sex`='.$u['sex'].',
                `photo`="'.$u['photo'].'",
                `app_setup`='.$u['app_setup'].',
                `menu_left_set`='.$u['menu_left_set'].',
                `country_id`='.$u['country_id'].',
                `city_id`='.$u['city_id'];
    query($sql);
    return $u;
}//end of _vkUserUpdate()

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
function _cacheClear() {
    xcache_unset('vkmobile_setup_global');
    xcache_unset('vkmobile_viewer_'.VIEWER_ID);
    xcache_unset('vkmobile_remind_active');
    xcache_unset('vkmobile_device_name');
    xcache_unset('vkmobile_vendor_name');
    xcache_unset('vkmobile_model_name_count');
    xcache_unset('vkmobile_zp_name');
    xcache_unset('vkmobile_color_name');
    xcache_unset('vkmobile_device_place');
    xcache_unset('vkmobile_device_status');
    if(WS_ID) {
        xcache_unset('vkmobile_workshop_'.WS_ID);
        xcache_unset('vkmobile_zayav_base_device'.WS_ID);
        xcache_unset('vkmobile_zayav_base_vendor'.WS_ID);
        xcache_unset('vkmobile_zayav_base_model'.WS_ID);
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
        '<title> Приложение 2031819 Hi-tech Service </title>'.
        '<link href="'.SITE.'/css/global.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
        (SA ? '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/errors.js?'.VERSION.'"></script>' : '').
        '<script type="text/javascript" src="'.SITE.'/js/jquery-2.0.3.min.js"></script>'.
        '<script type="text/javascript" src="'.SITE.'/js/xd_connection.js"></script>'.
        '<script type="text/javascript" src="'.SITE.'/js/highstock.js"></script>'.
        '<script type="text/javascript" src="'.SITE.'/js/vkapi.js?'.VERSION.'"></script>'.
        '<script type="text/javascript" src="'.SITE.'/include/globalScript.js?'.VERSION.'"></script>'.
        '<script type="text/javascript">'.
            (LOCAL ? 'for(var i in VK)if(typeof VK[i]=="function")VK[i]=function(){return false};' : '').
            'G.domain="'.DOMAIN.'";'.
            'G.values="'.VALUES.'";'.
            'G.vku={'.
                'viewer_id:'.VIEWER_ID.','.
                'name:"'.VIEWER_NAME.'",'.
                (WS_ID ? 'ws_id:'.WS_ID.',' : '').
                'country_id:'.VIEWER_COUNTRY_ID.','.
                'city_id:'.VIEWER_CITY_ID.
            '};'.
            (defined('WS_DEVS') ? 'G.ws={devs:['.WS_DEVS.']};' : '').
        '</script>'.
        '<script type="text/javascript" src="'.SITE.'/js/global.js?'.VERSION.'"></script>'.
        (WS_ID ? '<script type="text/javascript" src="'.SITE.'/js/ws.js?'.VERSION.'"></script>' : '').
        '<script type="text/javascript" src="'.SITE.'/js/G_values.js?'.G_VALUES.'"></script>'.
        '</head>'.
        '<body>'.
        '<div id="frameBody">'.
        '<iframe id="frameHidden" name="frameHidden"></iframe>';
}//end of _header()

function _footer() {
    global $html, $sqlQuery, $sqls;
    if(SA) {
        $d = empty($_GET['d']) ? '' :'&pre_d='.$_GET['d'];
        $d1 = empty($_GET['d1']) ? '' :'&pre_d1='.$_GET['d1'];
        $id = empty($_GET['id']) ? '' :'&pre_id='.$_GET['id'];
        $html .= '<div id="admin">'.
                ($_GET['p'] != 'sa' ? '<a href="'.URL.'&p=sa&pre_p='.$_GET['p'].$d.$d1.$id.'">Admin</a> :: ' : '').
                //'<a href="https://github.com/MihailKornilov/vkmobile/issues" target="_blank">Issues</a> :: '.
                '<a href="http://vkmobile.reformal.ru" target="_blank">Reformal</a> :: '.
                '<a class="debug_toggle'.(DEBUG ? ' on' : '').'">В'.(DEBUG ? 'ы' : '').'ключить Debug</a> :: '.
                '<a id="cache_clear">Очисить кэш ('.VERSION.')</a> :: '.
                'sql '.$sqlQuery.' :: '.
                'php '.round(microtime(true) - TIME, 3).' :: '.
                'js <EM></EM>'.
            '</div>'
            .(DEBUG ? $sqls : '');
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
        'hash' => 1,
        'my_page' => 1
    );
    $gValues = array();
    foreach($_GET as $k => $val) {
        if(isset($getArr[$k]) || empty($_GET[$k])) continue;
        $gValues[] = '"'.$k.'":"'.$val.'"';
    }
    $html .= '<script type="text/javascript">'.
                'hashSet({'.implode(',', $gValues).'});'.
                (SA ? '$("#admin EM").html(((new Date().getTime())-G.T)/1000);' : '').
             '</script>'.
         '</div></BODY></HTML>';
}//end of _footer()

function _rightLinks($p, $data, $d='') {
    $page = false;
    foreach($data as $link) {
        if($d == $link['d']) {
            $page = true;
            break;
        }
    }
    $send =  '<div class="rightLinks">';
    foreach($data as $link) {
        if($page)
            $sel = $d == $link['d'] ?  ' class="sel"' : '';
        else
            $sel = isset($link['sel']) ? ' class="sel"' : '';
        $send .= '<a href="'.URL.'&p='.$p.'&d='.$link['d'].'"'.$sel.'>'.$link['name'].'</a>';
    }
    $send .= '</div>';
    return $send;
}//end of _rightLinks()
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

function _checkbox($id, $txt='', $value=0) {
    return '<input type="hidden" id="'.$id.'" value="'.$value.'" />'.
        '<div class="check'.$value.'" id="'.$id.'_check">'.$txt.'</div>';
}//end of _checkbox()

function _end($count, $o1, $o2, $o5=false) {
    if($o5 === false) $o5 = $o2;
    if($count / 10 % 10 == 1)
        return $o5;
    else
        switch($count % 10) {
            case 1: return $o1;
            case 2: return $o2;
            case 3: return $o2;
            case 4: return $o2;
        }
    return $o5;
}//end of _end()

function win1251($txt) { return iconv('UTF-8','WINDOWS-1251',$txt); }
function utf8($txt) { return iconv('WINDOWS-1251','UTF-8',$txt); }
function curTime() { return strftime('%Y-%m-%d %H:%M:%S',time()); }

function GvaluesCreate() {//Составление файла G_values.js
    $save = 'function SpisokToAss(s){var a=[];for(var n=0;n<s.length;a[s[n].uid]=s[n].title,n++);return a}'.
        'G.status_spisok='.query_selJson("SELECT `id`,`name` FROM `setup_zayavki_status` ORDER BY id").';G.status_ass=SpisokToAss(G.status_spisok);'.
        'G.status_color_ass='.query_ptpJson("SELECT `id`,`bg` FROM setup_zayavki_status").';'.
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

function _monthFull($n) {
    $mon = array(
        1 => 'января',
        2 => 'февраля',
        3 => 'марта',
        4 => 'апреля',
        5 => 'мая',
        6 => 'июня',
        7 => 'июля',
        8 => 'августа',
        9 => 'сентября',
        10 => 'октября',
        11 => 'ноября',
        12 => 'декабря'
    );
    return $mon[intval($n)];
}//end of _monthFull
function _monthCut($n) {
    $mon = array(
        1 => 'янв',
        2 => 'фев',
        3 => 'мар',
        4 => 'апр',
        5 => 'май',
        6 => 'июн',
        7 => 'июл',
        8 => 'авг',
        9 => 'сен',
        10 => 'окт',
        11 => 'ноя',
        12 => 'дек'
    );
    return $mon[intval($n)];
}//end of _monthCut
function FullData($value, $noyear=false) {//14 апреля 2010
    $d = explode('-', $value);
    return
        abs($d[2]).' '.
        _monthFull($d[1]).
        (!$noyear || date('Y') != $d[0] ? ' '.$d[0] : '');
}//end of FullData()
function FullDataTime($value, $cut=false) {//14 апреля 2010 в 12:45
    $arr = explode(' ',$value);
    $d = explode('-',$arr[0]);
    $t = explode(':',$arr[1]);
    return
        abs($d[2]).' '.
        ($cut ? _monthCut($d[1]) : _monthFull($d[1])).
        (date('Y') == $d[0] ? '' : ' '.$d[0]).
        ' в '.$t[0].':'.$t[1];
}//end of FullDataTime()

function _vkComment($table, $id=0) {
    $sql = "SELECT *
            FROM `vk_comment`
            WHERE `status`=1
              AND `table_name`='".$table."'
              AND `table_id`=".intval($id)."
            ORDER BY `dtime_add` ASC";
    $count = 'Заметок нет';
    $units = '';
    $q = query($sql);
    if(mysql_num_rows($q)) {
        $comm = array();
        $v = array();
        while($r = mysql_fetch_assoc($q)) {
            if(!$r['parent_id'])
                $comm[$r['id']] = $r;
            elseif(isset($comm[$r['parent_id']]))
                $comm[$r['parent_id']]['childs'][] = $r;
            $v[$r['viewer_id_add']] = $r['viewer_id_add'];
        }
        $count = count($comm);
        $count = 'Всего '.$count.' замет'._end($count, 'ка', 'ки','ок');
        $v = _viewersInfo($v);
        $comm = array_reverse($comm);
        foreach($comm as $n => $r) {
            $childs = array();
            if(!empty($r['childs']))
                foreach($r['childs'] as $c)
                    $childs[] = _vkCommentChild($c['id'], $v[$c['viewer_id_add']], $c['txt'], $c['dtime_add']);
            $units .= _vkCommentUnit($r['id'], $v[$r['viewer_id_add']], $r['txt'], $r['dtime_add'], $childs, ($n+1));
        }
    }
    return '<div class="vkComment" val="'.$table.'_'.$id.'">'.
        '<div class=headBlue><div class="count">'.$count.'</div>Заметки</div>'.
        '<div class="add">'.
            '<textarea>Добавить заметку...</textarea>'.
            '<div class="vkButton"><button>Добавить</button></div>'.
        '</div>'.
        $units.
    '</div>';
}//end of _vkComment
function _vkCommentUnit($id, $viewer, $txt, $dtime, $childs=array(), $n=0) {
    return '<div class="cunit" val="'.$id.'">'.
        '<table class="t">'.
            '<tr><td class="ava">'.$viewer['photo'].
                '<td class="i">'.$viewer['link'].
                    ($viewer['id'] == VIEWER_ID || VIEWER_ADMIN ? '<div class="img_del unit_del" title="Удалить заметку"></div>' : '').
                    '<div class="ctxt">'.$txt.'</div>'.
                    '<div class="cdat">'.FullDataTime($dtime, 1).
                        '<SPAN'.($n == 1  && !empty($childs) ? ' class="hide"' : '').'> | '.
                            '<a>'.(empty($childs) ? 'Комментировать' : 'Комментарии ('.count($childs).')').'</a>'.
                        '</SPAN>'.
                    '</div>'.
                    '<div class="cdop'.(empty($childs) ? ' empty' : '').($n == 1 && !empty($childs) ? '' : ' hide').'">'.
                        implode('', $childs).
                        '<div class="cadd">'.
                            '<textarea>Комментировать...</textarea>'.
                            '<div class="vkButton"><button>Добавить</button></div>'.
                        '</div>'.
                    '</div>'.
        '</table></div>';
}//end of _vkCommentUnit()
function _vkCommentChild($id, $viewer, $txt, $dtime) {
    return '<div class="child" val="'.$id.'">'.
        '<table class="t">'.
            '<tr><td class="dava">'.$viewer['photo'].
                '<td class="di">'.$viewer['link'].
                    ($viewer['id'] == VIEWER_ID || VIEWER_ADMIN ? '<div class="img_del child_del" title="Удалить комментарий"></div>' : '').
                    '<div class="dtxt">'.$txt.'</div>'.
                    '<div class="ddat">'.FullDataTime($dtime, 1).'</div>'.
        '</table></div>';
}//end of _vkCommentChild()

function _curMonday() { //Понедельник в текущей неделе
    // Номер текущего дня недели
    $time = time();
    $curDay = date("w", $time);
    if($curDay == 0) $curDay = 7;
    // Приведение дня к понедельнику
    $time -= 86400 * ($curDay - 1);
    return strftime('%Y-%m-%d', $time);
}//end of _curMonday()
function _curSunday() { //Воскресенье в текущей неделе
    $time = time();
    $curDay = date("w", $time);
    if($curDay == 0) $curDay = 7;
    $time += 86400 * (7 - $curDay);
    return strftime('%Y-%m-%d', $time);

}//end of _curSunday()

function _engRusChar($word) { //Перевод символов раскладки с английского на русский
    $char = array(
        'q' => 'й',
        'w' => 'ц',
        'e' => 'у',
        'r' => 'к',
        't' => 'е',
        'y' => 'н',
        'u' => 'г',
        'i' => 'ш',
        'o' => 'щ',
        'p' => 'з',
        '[' => 'х',
        ']' => 'ъ',
        'a' => 'ф',
        's' => 'ы',
        'd' => 'в',
        'f' => 'а',
        'g' => 'п',
        'h' => 'р',
        'j' => 'о',
        'k' => 'л',
        'l' => 'д',
        ';' => 'ж',
        "'" => 'э',
        'z' => 'я',
        'x' => 'ч',
        'c' => 'с',
        'v' => 'м',
        'b' => 'и',
        'n' => 'т',
        'm' => 'ь',
        ',' => 'б',
        '.' => 'ю'
    );
    $send = '';
    for($n = 0; $n < strlen($word); $n++)
        if(isset($char[$word[$n]]))
            $send .= $char[$word[$n]];
    return $send;
}
function unescape($str){
    $escape_chars = '0410 0430 0411 0431 0412 0432 0413 0433 0490 0491 0414 0434 0415 0435 0401 0451 0404 0454 '.
        '0416 0436 0417 0437 0418 0438 0406 0456 0419 0439 041A 043A 041B 043B 041C 043C 041D 043D '.
        '041E 043E 041F 043F 0420 0440 0421 0441 0422 0442 0423 0443 0424 0444 0425 0445 0426 0446 '.
        '0427 0447 0428 0448 0429 0449 042A 044A 042B 044B 042C 044C 042D 044D 042E 044E 042F 044F';
    $russian_chars = 'А а Б б В в Г г Ґ ґ Д д Е е Ё ё Є є Ж ж З з И и І і Й й К к Л л М м Н н О о П п Р р С с Т т У у Ф ф Х х Ц ц Ч ч Ш ш Щ щ Ъ ъ Ы ы Ь ь Э э Ю ю Я я';
    $e = explode(' ', $escape_chars);
    $r = explode(' ', $russian_chars);
    $rus_array = explode('%u', $str);
    $new_word = str_replace($e, $r, $rus_array);
    $new_word = str_replace('%20', ' ', $new_word);
    return implode($new_word);
}

function _viewerName($id=VIEWER_ID, $link=false) {
    $key = 'vkmobile_viewer_name_'.$id;
    $name = xcache_get($key);
    if(empty($name)) {
        $sql = "SELECT CONCAT(`first_name`,' ',`last_name`) AS `name` FROM `vk_user` WHERE `viewer_id`=".$id." LIMIT 1";
        $r = mysql_fetch_assoc(query($sql));
        $name = $r['name'];
        xcache_set($key, $name, 86400);
    }
    return $link ? '<a href="http://vk.com/id'.$id.'" target="_blank">'.$name.'</a>' : $name;
}//end of _viewerName()
function _viewersInfo($arr=VIEWER_ID) {
    if(empty($arr))
        return array();
    $id = false;
    if(!is_array($arr)) {
        $id = $arr;
        $arr = array($arr);
    }
    $sql = "SELECT * FROM `vk_user` WHERE `viewer_id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['viewer_id']] = array(
            'id' => $r['viewer_id'],
            'name' => $r['first_name'].' '.$r['last_name'],
            'link' => '<a href="http://vk.com/id'.$r['viewer_id'].'" target="_blank" class="vlink">'.$r['first_name'].' '.$r['last_name'].'</a>',
            'photo' => '<img src="'.$r['photo'].'">'
        );
    return $id ? $send[$id] : $send;
}//end of _viewersInfo()

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
        $device = xcache_get('vkmobile_device_name');
        if(empty($device)) {
            $sql = "SELECT `id`,`name`,`name_rod` FROM `base_device` ORDER BY `id`";
            $q = query($sql);
            while($r = mysql_fetch_assoc($q))
                $device[$r['id']] = array($r['name'], $r['name_rod']);
            xcache_set('vkmobile_device_name', $device, 86400);
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
        $vendor = xcache_get('vkmobile_vendor_name');
        if(empty($vendor)) {
            $sql = "SELECT `id`,`name` FROM `base_vendor`";
            $q = query($sql);
            while($r = mysql_fetch_assoc($q))
                $vendor[$r['id']] = $r['name'];
            xcache_set('vkmobile_vendor_name', $vendor, 86400);
        }
        foreach($vendor as $id => $name)
            define('VENDOR_NAME_'.$id, $name);
        define('VENDOR_LOADED', true);
    }
    return defined('VENDOR_NAME_'.$vendor_id) ? constant('VENDOR_NAME_'.$vendor_id).' ' : '';
}//end of _vendorName()
function _modelName($model_id) {
    if(!defined('MODEL_LOADED')) {
        $count = xcache_get('vkmobile_model_name_count');
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
                    xcache_set('vkmobile_model_name'.$count, $model);
                    $rows = 0;
                    $count++;
                    $model = array();
                }
            }
            if(!empty($model))
                xcache_set('vkmobile_model_name'.$count, $model, 86400);
            xcache_set('vkmobile_model_name_count', $count, 86400);
        }
        for($n = 0; $n <= $count; $n++) {
            $model = xcache_get('vkmobile_model_name'.$n);
            foreach($model as $id => $name)
                define('MODEL_NAME_'.$id, $name);
        }
        define('MODEL_LOADED', true);
    }
    return defined('MODEL_NAME_'.$model_id) ? constant('MODEL_NAME_'.$model_id) : '';
}//end of _modelName()
function _zpName($name_id) {
    if(!defined('ZP_NAME_LOADED')) {
        $key = 'vkmobile_zp_name';
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
    $count = query_value("SELECT SUM(`count`) FROM `zp_move` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id." LIMIT 1");
    query("DELETE FROM `zp_avai` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id);
    if($count > 0)
        query("INSERT INTO `zp_avai` (`ws_id`,`zp_id`,`count`) VALUES (".WS_ID.",".$zp_id.",".$count.")");
    return $count;
}//end of _zpAvaiSet()
function _colorName($color_id) {
    if(!defined('COLOR_LOADED')) {
        $key = 'vkmobile_color_name';
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
        $key = 'vkmobile_device_place';
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
        $key = 'vkmobile_device_status';
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
        $checkDevs .= _checkbox($r['id'], $r['name_mn']);

    return
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