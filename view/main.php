<?php
function hashRead($h) {
    if(empty($h)) {
        define('HASH_VALUES', false);
        return;
    }
    $ex = explode('.', $h);
    $r = explode('_', $ex[0]);
    unset($ex[0]);
    define('HASH_VALUES', empty($ex) ? false : implode('.', $ex));
    $_GET['p'] = $r[0];
    switch($_GET['p']) {
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
        default:
            if(isset($r[1])) {
                $_GET['d'] = $r[1];
                if(isset($r[2]))
                    $_GET['d1'] = $r[2];
            }
    }
}//end of hashRead()

function _header() {
    global $html, $vku, $WS;
    $html =
        '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
        //'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.
        '<HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.
        '<HEAD>'.
        '<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
        '<TITLE> Приложение 2031819 Hi-tech Service </TITLE>'.
        '<LINK href="'.SITE.'/include/globalStyle.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
        '<LINK href="'.SITE.'/css/global.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
        (ADMIN ? '<SCRIPT type="text/javascript" src="http://nyandoma'.(DOMAIN == 'vkmobile' ? '' : '.ru').'/js/errors.js?'.VERSION.'"></SCRIPT>' : '').
        '<SCRIPT type="text/javascript" src="'.SITE.'/include/jquery-1.9.1.min.js"></SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/include/xd_connection.js"></SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/js/highstock.js"></SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/js/vkapi.js?'.VERSION.'"></SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/include/globalScript.js?'.VERSION.'"></SCRIPT>'.
        '<SCRIPT type="text/javascript">'.
            'if(document.domain=="vkmobile")for(var i in VK)if(typeof VK[i]=="function")VK[i]=function(){return false};'.
            'G.domain = "'.DOMAIN.'";'.
            'G.values = "'.VALUES.'";'.
            'G.vku = {'.
                'viewer_id:'.VIEWER_ID.','.
                'first_name:"'.$vku->first_name.'",'.
                'last_name:"'.$vku->last_name.'",'.
                'name:"'.$vku->first_name.' '.$vku->last_name.'",'.
                'ws_id:'.WS_ID.','.
                'country_id:'.$vku->country_id.','.
                'city_id:'.$vku->city_id.
            '};'.
            'G.clients = [];'.
            'G.ws = {devs:['.($WS ? $WS->devs : '').']};'.
        '</SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/js/global.js?'.VERSION.'"></SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/include/G_values.js?'.G_VALUES.'"></SCRIPT>'.
        '<SCRIPT type="text/javascript" src="/include/clients/G_clients_'.WS_ID.'.js?'.VERSION.'"></SCRIPT>'.//todo для удаления
        '<SCRIPT type="text/javascript" src="/include/clients/clients.js?'.VERSION.'"></SCRIPT>'.//todo для удаления
        '<SCRIPT type="text/javascript" src="/include/device/device.js?'.VERSION.'"></SCRIPT>'.//todo для удаления
        '<SCRIPT type="text/javascript" src="/include/foto/foto.js?'.VERSION.'"></SCRIPT>'.//todo для удаления
        '</HEAD>'.
        '<BODY>'.
        '<div id="frameBody">'.
        '<iframe id="frameHidden" name="frameHidden"></iframe>';
}//end of _header()

function _footer() {
    global $html, $sqlQuery, $sqls; //todo временная переменная для отображения списка запросов
    if(ADMIN)
        $html .= '<DIV id="admin">'.
                '<A href="'.URL.'&my_page=superAdmin&pre_page='.@$_GET['my_page'].'&pre_id='.@$_GET['id'].'">Admin</A> :: '.
                '<A href="https://github.com/MihailKornilov/vkmobile/issues" target="_blank">Issues</A> :: '.
                '<A href="http://vkmobile.reformal.ru" target="_blank">Reformal</A> :: '.
                '<A id="script_style">Стили и скрипты ('.VERSION.')</A> :: '.
                '<A id="cache_clear">Очисить кэш</A> :: '.
                'sql '.$sqlQuery.' :: '.
                'php '.round(microtime(true) - TIME, 3).' :: '.
                'js <EM></EM>'.
            '</DIV>'.
            $sqls;
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
    $html .= '<SCRIPT type="text/javascript">'.
                'hashSet({'.implode(',', $gValues).'});'.
                (ADMIN ? '$("#admin EM").html(((new Date().getTime())-G.T)/1000);' : '').
             '</SCRIPT>'.
         '</DIV></BODY></HTML>';
}//end of _footer()

//Получение количества активных напоминаний
function _remindActiveSet() {
    $key = 'vkmobile_remind_active';
    $count = xcache_get($key);
    if(!strlen($count)) {
        $sql = "SELECT COUNT(`id`) AS `count`
                FROM `reminder`
                WHERE `ws_id`=".WS_ID."
                  AND `day`<=DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d')
                  AND `status`=1
                  AND (`private`=0 OR `private`=1 AND `viewer_id_add`=".VIEWER_ID.")";
        $r = mysql_fetch_assoc(query($sql));
        $count = $r['count'];
        xcache_set($key, $count, 7200);
    }
    define('REMIND_ACTIVE', $count > 0 ? ' (<B>'.$count.'</B>)' : '');
}//end of _remindActiveSet()

function _mainLinks() {
    global $html, $sel;
    _remindActiveSet();
    $links = array(
        array(
            'name' => 'Клиенты',
            'page' => 'remClient',
            'show' => 1
        ),
        array(
            'name' => 'Заявки',
            'page' => 'no&p=zayav',
            'show' => 1
        ),
        array(
            'name' => 'Устройства',
            'page' => 'remDevice',
            'show' => 0
        ),
        array(
            'name' => 'Запчасти',
            'page' => 'remZp',
            'show' => 1
        ),
        array(
            'name' => 'Отчёты'.REMIND_ACTIVE,
            'page' => 'no&p=report',
            'show' => VIEWER_ADMIN
        ),
        array(
            'name' => 'Установки',
            'page' => 'remSetup',
            'show' => VIEWER_ADMIN
        )
    );

    $send = '<DIV id="mainLinks">';
    foreach($links as $l)
        if($l['show']) {
            $s = $l['page'] == $sel ? ' sel' : '';
            // todo для удаления
            if(!$s) {
                $page = explode('=', $l['page']);
                $s = isset($page[1]) && $page[1] == @$_GET['p'] ? ' sel' : '';
            }
            $send .= "<A HREF='".URL."&my_page=".$l['page']."' class='la".$s."'>".
                    "<DIV class=l1></DIV>".
                    "<DIV class=l2></DIV>".
                    "<DIV class=l3>".$l['name']."</DIV>".
                "</A>";
        }
    $send .= '</DIV>';
    $html .= $send;
}//end of _mainLinks()

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

function _dopLinks($p, $data, $d=false, $d1=false) {
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
    if(!$o5) $o5 = $o2;
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

function _monthFull($n) {
    $mon = array(
        1 => 'январь',
        2 => 'февраль',
        3 => 'март',
        4 => 'апрель',
        5 => 'май',
        6 => 'июнь',
        7 => 'июль',
        8 => 'август',
        9 => 'сентябрь',
        10 => 'октябрь',
        11 => 'ноябрь',
        12 => 'декабрь'
    );
    return $mon[intval($n)];
}//end of _monthFull

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
    return '<DIV class="vkComment" val="'.$table.'_'.$id.'">'.
        '<DIV class=headBlue><div class="count">'.$count.'</div>Заметки</DIV>'.
        '<DIV class="add">'.
            '<TEXTAREA>Добавить заметку...</TEXTAREA>'.
            '<DIV class="vkButton"><BUTTON>Добавить</BUTTON></DIV>'.
        '</DIV>'.
        $units.
    '</DIV>';
}//end of _vkComment
function _vkCommentUnit($id, $viewer, $txt, $dtime, $childs=array(), $n=0) {
    return '<DIV class="unit" val="'.$id.'">'.
        '<TABLE cellspacing="0" class="tab">'.
            '<TR><TD class="ava">'.$viewer['photo'].
                '<TD class="inf">'.$viewer['link'].
                    ($viewer['id'] == VIEWER_ID || ADMIN ? '<div class="img_del unit_del" title="Удалить заметку"></div>' : '').
                    '<DIV class="ctxt">'.$txt.'</DIV>'.
                    '<DIV class="cdat">'.FullDataTime($dtime, 1).
                        '<SPAN'.($n == 1  && !empty($childs) ? ' class="hide"' : '').'> | '.
                            '<a>'.(empty($childs) ? 'Комментировать' : 'Комментарии ('.count($childs).')').'</a>'.
                        '</SPAN>'.
                    '</DIV>'.
                    '<DIV class="cdop'.(empty($childs) ? ' empty' : '').($n == 1 && !empty($childs) ? '' : ' hide').'">'.
                        implode('', $childs).
                        '<DIV class="cadd">'.
                            '<TEXTAREA>Комментировать...</TEXTAREA>'.
                            '<DIV class="vkButton"><BUTTON>Добавить</BUTTON></DIV>'.
                        '</DIV>'.
                    '</DIV>'.
        '</TABLE></DIV>';
}//end of _vkCommentUnit()
function _vkCommentChild($id, $viewer, $txt, $dtime) {
    return '<DIV class="child" val="'.$id.'">'.
        '<TABLE cellspacing="0" class="tab">'.
            '<TR><TD class="dava">'.$viewer['photo'].
                '<TD class="dinf">'.$viewer['link'].
                    ($viewer['id'] == VIEWER_ID || ADMIN ? '<div class="img_del child_del" title="Удалить комментарий"></div>' : '').
                    '<DIV class="dtxt">'.$txt.'</DIV>'.
                    '<DIV class="ddat">'.FullDataTime($dtime, 1).'</DIV>'.
        '</TABLE></DIV>';
}//end of _vkCommentChild()

function statistic() {
    $sql = "SELECT
                SUM(`summa`) AS `summa`,
                DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `dtime`
            FROM `money`
            WHERE `status`=1
              AND `summa`>0
            GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
            ORDER BY `dtime_add`";
    $q = query($sql);
    $prihod = array();
    while($r = mysql_fetch_assoc($q))
        $prihod[] = array(strtotime($r['dtime']) * 1000, intval($r['summa']));
    $sql = "SELECT
                SUM(`summa`)*-1 AS `summa`,
                DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `dtime`
            FROM `money`
            WHERE `status`=1
              AND `summa`<0
            GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
            ORDER BY `dtime_add`";
    $q = query($sql);
    $rashod = array();
    while($r = mysql_fetch_assoc($q))
        $rashod[] = array(strtotime($r['dtime']) * 1000, intval($r['summa']));

    return '<div id="statistic"></div>'.
        '<SCRIPT type="text/javascript">'.
            'var statPrihod = '.json_encode($prihod).';'.
            'var statRashod = '.json_encode($rashod).';'.
        '</SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/js/statistic.js"></SCRIPT>';
}//end of statistic()

//Понедельник в текущей неделе
function currentMonday() {
    // Номер текущего дня недели
    $time = time();
    $curDay = date("w", $time);
    if($curDay == 0) $curDay = 7;
    // Приведение дня к понедельнику
    $time -= 86400 * ($curDay - 1);
    return strftime('%Y-%m-%d', $time);
}//end of currentMonday()

//Воскресенье в текущей неделе
function currentSunday() {
    $time = time();
    $curDay = date("w", $time);
    if($curDay == 0) $curDay = 7;
    $time += 86400 * (7 - $curDay);
    return strftime('%Y-%m-%d', $time);

}//end of currentMonday()

//todo не сделано очищение кеша
function viewerName($link=false, $id=VIEWER_ID) {
    $key = 'vkmobile_viewer_name_'.$id;
    $name = xcache_get($key);
    if(empty($name)) {
        $sql = "SELECT CONCAT(`first_name`,' ',`last_name`) AS `name` FROM `vk_user` WHERE `viewer_id`=".$id." LIMIT 1";
        $r = mysql_fetch_assoc(query($sql));
        $name = $r['name'];
        xcache_set($key, $name, 86400);
    }
    return $link ? '<A href="http://vk.com/id'.$id.'" target="_blank">'.$name.'</a>' : $name;
}//end of viewerName()

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

function getClientsLink($arr) {
    if(empty($arr))
        return array();
    $id = false;
    if(!is_array($arr)) {
        $id = $arr;
        $arr = array($arr);
    }
    $sql = "SELECT `id`,`fio` FROM `client` WHERE `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['id']] = '<A href="'.URL.'&my_page=remClientInfo&id='.$r['id'].'">'.$r['fio'].'</a>';
    if($id)
        return $send[$id];
    return $send;
}//end of getClientsLink()

//Вывод номеров заявок с возможностью отображения дополнительной информации при наведении
function getZayavNomerLink($arr, $noHint=false) {
    if(empty($arr))
        return array();
    if(!is_array($arr)) {
        $zayav_id = $arr;
        $arr = array($zayav_id);
    }
    $sql = "SELECT `id`,`nomer` FROM `zayavki` WHERE `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['id']] =
            '<A href="'.URL.'&p=zayav&d=info&id='.$r['id'].'"'.(!$noHint ? ' class="zayav_link" val="'.$r['id'].'"' : '').'>'.
                '№'.$r['nomer'].
                '<div class="tooltip empty"></div>'.
            '</a>';
    return isset($zayav_id) ? $send[$zayav_id] : $send;
}//end of getZayavNomerLink()

function get_zp_info($arr) {
    if(empty($arr))
        return array();
    $sql = "SELECT * FROM `zp_catalog` WHERE `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['id']] = '<A href="'.URL.'&my_page=remZp&id='.$r['id'].'">'.
            '<b>'._zpName($r['name_id']).'</b> для '.
            _deviceName($r['base_device_id'], 1).
            _vendorName($r['base_vendor_id']).
            _modelName($r['base_model_id']).
        '</a>';
    return $send;
}//end of get_zp_info()

function zayavCategory($id=false) {
    $arr = array(
        '1' => 'Приём в ремонт',
        '2' => 'Заказ запчастей',
        '3' => 'Консультация'
    );
    return $id ? $arr[$id] : $arr;
}//end of zayavCategory()

function zayav_status($id=false) {
    $arr = array(
        '1' => array(
            'name' => 'Ожидает выполнения',
            'color' => 'E8E8FF'
        ),
        '2' => array(
            'name' => 'Выполнено!',
            'color' => 'CCFFCC'
        ),
        '3' => array(
            'name' => 'Завершить не удалось',
            'color' => 'FFDDDD'
        )
    );
    return $id ? $arr[$id] : $arr;
}//end of zayav_status()

// изменение размера изображения
function _imageResize($x_cur, $y_cur, $x_new, $y_new) {
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
}
function zayav_image_link($zayav_id, $size='small', $x_new=10000, $y_new=10000) {
    $sql = "SELECT * FROM `images` WHERE `status`=1 AND `sort`=0 AND `owner`='zayav".$zayav_id."' LIMIT 1";
    if($r = mysql_fetch_assoc(query($sql))) {
        //$s = _imageResize($r[$size.'_x'], $r[$size.'_y'], $x_new, $y_new);
        //$send = $r['link'].'-'.$size.'.jpg width="'.$s['x'].'" height="'.$s['y'].'"';
        $send = $r['link'].'-'.$size.'.jpg';
    } else {
        $sql = "SELECT `base_model_id` FROM `zayavki` WHERE `id`=".$zayav_id." LIMIT 1";
        $r = mysql_fetch_assoc(query($sql));
        $send = model_image_link($r['base_model_id'], $size, $x_new, $y_new);
    }
    return $send;
}//end of zayav_image_link()
function model_image_link($model_id, $size='small', $x_new=10000, $y_new=10000) {
    $send = '/img/nofoto.gif';
    $sql = "SELECT * FROM `images` WHERE `status`=1 AND `sort`=0 AND `owner`='dev".$model_id."' LIMIT 1";
    if($r = mysql_fetch_assoc(query($sql))) {
        //$s = _imageResize($r[$size.'_x'], $r[$size.'_y'], $x_new, $y_new);
        //$send = $r['link'].'-'.$size.'.jpg width="'.$s['x'].'" height="'.$s['y'].'"';
        $send = $r['link'].'-'.$size.'.jpg';
    }
    return $send;
}//end of model_image_link()

function unescape($str){
    $escape_chars = "0410 0430 0411 0431 0412 0432 0413 0433 0490 0491 0414 0434 0415 0435 0401 0451 0404 0454 0416 0436 0417 0437 0418 0438 0406 0456 0419 0439 041A 043A 041B 043B 041C 043C 041D 043D 041E 043E 041F 043F 0420 0440 0421 0441 0422 0442 0423 0443 0424 0444 0425 0445 0426 0446 0427 0447 0428 0448 0429 0449 042A 044A 042B 044B 042C 044C 042D 044D 042E 044E 042F 044F";
    $russian_chars = "А а Б б В в Г г Ґ ґ Д д Е е Ё ё Є є Ж ж З з И и І і Й й К к Л л М м Н н О о П п Р р С с Т т У у Ф ф Х х Ц ц Ч ч Ш ш Щ щ Ъ ъ Ы ы Ь ь Э э Ю ю Я я";
    $e = explode(" ",$escape_chars);
    $r = explode(" ",$russian_chars);
    $rus_array = explode("%u",$str);
    $new_word = str_replace($e,$r,$rus_array);
    $new_word = str_replace("%20"," ",$new_word);
    return implode("",$new_word);
}

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
    $sql = "SELECT * FROM `zp_catalog` WHERE `id`=".intval($zp_id);
    $zp = mysql_fetch_assoc(query($sql));
    return $zp['compat_id'] ? $zp['compat_id'] : $zp['id'];
}//end of _zpCompatId()
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


// ---===! zayav !===--- Секция заявок

function zayav_add() {
    $sql = "SELECT `id`,`name` FROM `setup_fault` ORDER BY SORT";
    $q = query($sql);
    $fault = '<table cellspacing="0">';
    $k = 0;
    while($r = mysql_fetch_assoc($q))
        $fault .= (++$k%2 ? '<tr>' : '').'<td>'._checkbox('f_'.$r['id'], $r['name']);
    $fault .= '</table>';
    switch(@$_GET['back']) {
        case 'remClientInfo': $back = 'remClientInfo'; break;
        default: $back = 'zayav';

    }
    $client_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    $id = !empty($_GET['id']) ? '&id='.$client_id : '';
    return '<DIV id="zayavAdd">'.
        '<DIV class="headName">Внесение новой заявки</DIV>'.
        '<TABLE cellspacing="8">'.
            '<TR><TD class="label">Клиент:        <TD><INPUT TYPE="hidden" id="client_id" value="'.$client_id.'" />'.
            '<TR><TD class="label">Категория:     <TD><INPUT TYPE="hidden" id="category" value="1" />'.
            '<TR><TD class="label top">Устройство:<TD><TABLE cellspacing="0"><TD id="dev"><TD id="device_image"></TABLE>'.
            '<TR><TD class="label top">Местонахождение устройства<br />после внесения заявки:<TD><INPUT type="hidden" id="place" />'.
            '<TR><TD class="label">IMEI:          <TD><INPUT type="text" id="imei" maxlength="20" />'.
            '<TR><TD class="label">Серийный номер:<TD><INPUT type="text" id="serial" maxlength="30" />'.
            '<TR><TD class="label">Цвет:          <TD><INPUT TYPE="hidden" id="color_id" value="0" />'.
            '<TR><TD class="label top">Неисправности: <TD id="fault">'.$fault.
            '<TR><TD class="label top">Заметка:       <TD><textarea id="comm"></textarea>'.
            '<TR><TD class="label">Добавить напоминание:<TD>'._checkbox('reminder').
        '</TABLE>'.

        '<TABLE cellspacing="8" id="reminder_tab">'.
            '<TR><TD class="label">Содержание: <TD><INPUT TYPE="text" id="reminder_txt" />'.
            '<TR><TD class="label">Дата:       <TD><INPUT TYPE="hidden" id="reminder_day" />'.
        '</TABLE>'.

        '<DIV class="vkButton"><BUTTON>Внести</BUTTON></DIV>'.
        '<DIV class="vkCancel" val="'.$back.$id.'"><BUTTON>Отмена</BUTTON></DIV>'.
    '</DIV>';
}//end of zayav_add()

function zayavFilter($v) {
    if(empty($v['status']) || !preg_match(REGEXP_NUMERIC, $v['status']))
        $v['status'] = 0;
    if(empty($v['device']) || !preg_match(REGEXP_NUMERIC, $v['device']))
        $v['device'] = 0;
    if($v['device'] == 0 || !preg_match(REGEXP_NUMERIC, $v['vendor']))
        $v['vendor'] = 0;
    if($v['device'] == 0 || !preg_match(REGEXP_NUMERIC, $v['model']))
        $v['model'] = 0;
    if(empty($v['devstatus']) || !preg_match(REGEXP_NUMERIC, $v['devstatus']) && $v['devstatus'] != -1)
        $v['devstatus'] = 0;

    $filter = array();
    $filter['find'] = htmlspecialchars(trim(@$v['find']));
    switch(@$v['sort']) {
        case '2': $filter['sort'] = 'zayav_status_dtime'; break;
        default: $filter['sort'] = 'dtime_add';
    }
    $filter['desc'] = intval(@$v['desc']) == 1 ? 'ASC' : 'DESC';
    $filter['status'] = intval($v['status']);
    $filter['device'] = intval($v['device']);
    $filter['vendor'] = intval($v['vendor']);
    $filter['model'] = intval($v['model']);
    $filter['place'] = win1251(urldecode(htmlspecialchars(trim(@$v['place']))));
    $filter['devstatus'] = $v['devstatus'];
    return $filter;
}
function get_zayav_list($page=1, $filter=array()) {
    $limit = 20;
    $cond = "`ws_id`=".WS_ID." AND `zayav_status`>0";

    if(empty($filter['sort']))
        $filter['sort'] = 'dtime_add';
    if(empty($filter['desc']))
        $filter['desc'] = 'DESC';
    if(!empty($filter['find'])) {
        $cond .= " AND `find` LIKE '%".$filter['find']."%'";
        if($page ==1 && preg_match(REGEXP_NUMERIC, $filter['find']))
            $nomer = intval($filter['find']);
    } else {
        if(isset($filter['status']) && $filter['status'] > 0)
            $cond .= " AND `zayav_status`=".$filter['status'];
        if(isset($filter['device']) && $filter['device'] > 0)
            $cond .= " AND `base_device_id`=".$filter['device'];
        if(isset($filter['vendor']) && $filter['vendor'] > 0)
            $cond .= " AND `base_vendor_id`=".$filter['vendor'];
        if(isset($filter['model']) && $filter['model'] > 0)
            $cond .= " AND `base_model_id`=".$filter['model'];
        if(isset($filter['place']) && $filter['place'] != '0') {
            if(preg_match(REGEXP_NUMERIC, $filter['place']))
                $cond .= " AND `device_place`=".$filter['place'];
            elseif($filter['place'] == -1)
                $cond .= " AND `device_place`=0 AND LENGTH(`device_place_other`)=0";
            else
                $cond .= " AND `device_place`=0 AND `device_place_other`='".$filter['place']."'";
        }
        if(isset($filter['devstatus']) && $filter['devstatus'] != 0)
            $cond .= " AND `device_status`=".($filter['devstatus'] > 0 ? $filter['devstatus'] : 0);
    }
    $zayav = array();
    $client = array();
    $images = array();

    $sql = "SELECT COUNT(`id`) AS `all` FROM `zayavki` WHERE ".$cond." LIMIT 1";
    $r = mysql_fetch_assoc(query($sql));
    $send['all'] = $r['all'];
    if(isset($nomer)) {
        $sql = "SELECT * FROM `zayavki` WHERE `nomer`=".$nomer." AND `zayav_status`>0 LIMIT 1";
        if($r = mysql_fetch_assoc(query($sql))) {
            $send['all']++;
            $limit--;
            $r['nomer_find'] = 1;
            $zayav[$r['id']] = $r;
            $client[$r['client_id']] = $r['client_id'];
            $images['zayav'.$r['id']] = '"zayav'.$r['id'].'"';
            $images['dev'.$r['base_model_id']] = '"dev'.$r['base_model_id'].'"';
        }
    }
    if($send['all'] == 0)
        return $send;

    $start = ($page - 1) * $limit;
    $sql = "SELECT *
            FROM `zayavki`
            WHERE ".$cond."
            ORDER BY `".$filter['sort']."` ".$filter['desc']."
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    while($r = mysql_fetch_assoc($q)) {
        if(isset($nomer) && $nomer == $r['nomer'])
            continue;
        $zayav[$r['id']] = $r;
        $client[$r['client_id']] = $r['client_id'];
        $images['zayav'.$r['id']] = '"zayav'.$r['id'].'"';
        $images['dev'.$r['base_model_id']] = '"dev'.$r['base_model_id'].'"';
    }
    $client = getClientsLink($client);
    $status = zayav_status();

    $sql = "SELECT `owner`,`link` FROM `images` WHERE `status`=1 AND `sort`=0 AND `owner` IN (".implode(',', $images).")";
    $q = query($sql);
    $imgLinks = array();
    while($r = mysql_fetch_assoc($q))
        $imgLinks[$r['owner']] = $r['link'].'-small.jpg';
    unset($images);

    $sql = "SELECT
                `table_id`,
                `txt`
            FROM `vk_comment`
            WHERE `table_name`='zayav'
              AND `table_id` IN (".implode(',', array_keys($zayav)).")
              AND `status`=1
            ORDER BY `id` ASC";
    $articles = array();
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $articles[$r['table_id']] = $r['txt'];

    foreach($zayav as $r) {
        $img = '/img/nofoto.gif';
        if(isset($imgLinks['zayav'.$r['id']]))
            $img = $imgLinks['zayav'.$r['id']];
        elseif(isset($imgLinks['dev'.$r['base_model_id']]))
            $img = $imgLinks['dev'.$r['base_model_id']];
        $send['spisok'][$r['id']] = array(
            'status_color' => $status[$r['zayav_status']]['color'],
            'nomer' => $r['nomer'],
            'nomer_find' => isset($r['nomer_find']),
            'category' => zayavCategory($r['category']),
            'device' => _deviceName($r['base_device_id']),
            'vendor' => _vendorName($r['base_vendor_id']),
            'model' => _modelName($r['base_model_id']),
            'client' => $client[$r['client_id']],
            'dtime' => FullData($r['dtime_add'], 1),
            'img' => $img,
            'article' => isset($articles[$r['id']]) ? $articles[$r['id']] : ''
        );
        if(!empty($filter['find'])) {
            $reg = '/('.$filter['find'].')/i';
            if(preg_match($reg, $send['spisok'][$r['id']]['model']))
                $send['spisok'][$r['id']]['model'] = preg_replace($reg, "<em>\\1</em>", $send['spisok'][$r['id']]['model'], 1);
            if(preg_match($reg, $r['imei']))
                $send['spisok'][$r['id']]['imei'] = preg_replace($reg, "<em>\\1</em>", $r['imei'], 1);
            if(preg_match($reg, $r['serial']))
                $send['spisok'][$r['id']]['serial'] = preg_replace($reg, "<em>\\1</em>", $r['serial'], 1);
        }
    }
    if($start + $limit < $send['all'])
        $send['next'] = $page + 1;
    return $send;
}//end of get_zayav_list()
function show_zayav_count($count) {
    return '<a id="filter_break">Сбросить условия поиска</a>'.
        ($count > 0 ?
            'Показан'._end($count, 'а', 'о').' '.$count.' заяв'._end($count, 'ка', 'ки', 'ок')
            :
            'Заявок не найдено');
}//end of show_zayav_count()
function show_zayav_list($data, $values) {
    $device_ids = array();
    $sql = "SELECT DISTINCT(`base_device_id`) AS `id`
            FROM `zayavki`
            WHERE `base_device_id`>0
              AND `zayav_status`>0
              AND `ws_id`=".WS_ID;
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $device_ids[] = $r['id'];

    $vendor_ids = array();
    $sql = "SELECT DISTINCT(`base_vendor_id`) AS `id`
            FROM `zayavki`
            WHERE `base_vendor_id`>0
              AND `zayav_status`>0
              AND `ws_id`=".WS_ID;
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $vendor_ids[] = $r['id'];

    $model_ids = array();
    $sql = "SELECT DISTINCT(`base_model_id`) AS `id`
            FROM `zayavki`
            WHERE `base_model_id`>0
              AND `zayav_status`>0
              AND `ws_id`=".WS_ID;
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $model_ids[] = $r['id'];

    $place_other = array();
    $sql = "SELECT DISTINCT(`device_place_other`) AS `other`
            FROM `zayavki`
            WHERE LENGTH(`device_place_other`)>0
              AND `zayav_status`>0
              AND `ws_id`=".WS_ID;
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $place_other[] = '"'.$r['other'].'"';

    return '<DIV id="zayav">'.
        '<DIV class="result">'.show_zayav_count($data['all']).'</DIV>'.
        '<TABLE cellspacing=0 class="tabLR">'.
            '<TR><TD id="spisok">'.show_zayav_spisok($data).
                '<TD class="right">'.
                    '<DIV id="buttonCreate"><A HREF="'.URL.'&p=zayav&d=add&back=zayav">Новая заявка</A></DIV>'.
                    '<DIV id="find"></DIV>'.
                    '<DIV class="findHead">Порядок</DIV>'.
                    '<INPUT TYPE="hidden" id="sort" value="'.$values['sort'].'">'.
                    _checkbox('desc', 'Обратный порядок', $values['desc']).
                    '<div class="condLost'.(!empty($values['find']) ? ' hide' : '').'">'.
                        '<DIV class="findHead">Статус заявки</DIV><DIV id="status"></DIV>'.
                        '<DIV class="findHead">Устройство</DIV><DIV id="dev"></DIV>'.
                        '<DIV class="findHead">Нахождение устройства</DIV><INPUT TYPE="hidden" id="device_place" value="'.$values['place'].'">'.
                        '<DIV class="findHead">Состояние устройства</DIV><INPUT TYPE="hidden" id="devstatus" value="'.$values['devstatus'].'">'.
                    '</div>'.
        '</TABLE>'.
        '<script type="text/javascript">'.
            'G.device_ids = ['.implode(',', $device_ids).'];'.
            'G.vendor_ids = ['.implode(',', $vendor_ids).'];'.
            'G.model_ids = ['.implode(',', $model_ids).'];'.
            'G.place_other = ['.implode(',', $place_other).'];'.
            'G.zayav_find = "'.unescape($values['find']).'";'.
            'G.zayav_status = '.$values['status'].';'.
            'G.zayav_device = '.$values['device'].';'.
            'G.zayav_vendor = '.$values['vendor'].';'.
            'G.zayav_model = '.$values['model'].';'.
        '</script>'.
    '</DIV>';
}//end of show_zayav_list()
function show_zayav_spisok($data) {
    if(!isset($data['spisok']))
        return '<div class="findEmpty">Заявок не найдено.</div>';
    $send = '';
    foreach($data['spisok'] as $id => $sp) {
        $send .= '<div class="unit" style="background-color:#'.$sp['status_color'].'" val="'.$id.'">'.
            '<TABLE cellspacing="0" width="100%">'.
                '<TR><TD valign=top>'.
                        '<h2'.($sp['nomer_find'] ? ' class="finded"' : '').'>#'.$sp['nomer'].'</h2>'.
                        '<H1>'.$sp['category'].' <A>'.$sp['device'].' <B>'.$sp['vendor'].' '.$sp['model'].'</B></A></H1>'.
                        '<TABLE cellspacing="2">'.
                            '<TR><TD class="label">Клиент:<TD>'.$sp['client'].
                            '<TR><TD class="label">Дата подачи:<TD>'.$sp['dtime'].
                            (isset($sp['imei']) ? '<TR><TD class="label">IMEI:<TD>'.$sp['imei'] : '').
                            (isset($sp['serial']) ? '<TR><TD class="label">Серийный номер:<TD>'.$sp['serial'] : '').
                        '</TABLE>'.
                    '<TD class="image"><IMG src="'.$sp['img'].'" />'.
            '</TABLE>'.
            '<input type="hidden" class="msg" value="'.htmlspecialchars($sp['article']).'">'.
        '</div>';
    }
    if(isset($data['next']))
        $send .= '<div class="ajaxNext" id="zayav_next" val="'.($data['next']).'"><span>Следующие 20 заявок</span></div>';
    return $send;
}//end of show_zayav_spisok()

function zayav_info($zayav_id) {
    $sql = "SELECT * FROM `zayavki` WHERE `zayav_status`>0 AND `id`=".$zayav_id." LIMIT 1";
    if(!$zayav = mysql_fetch_assoc(query($sql)))
        return 'Заявки не существует.';
    $status = zayav_status($zayav['zayav_status']);
    $model = _vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']);
    $sql = "SELECT *
        FROM `accrual`
        WHERE `ws_id`=".WS_ID."
          AND `status`=1
          AND `zayav_id`=".$zayav['id']."
        ORDER BY `dtime_add` ASC";
    $q = query($sql);
    $money = array();
    $accSum = 0;
    while($acc = mysql_fetch_assoc($q)) {
        $money[strtotime($acc['dtime_add'])] = zayav_accrual_unit($acc);
        $accSum += $acc['summa'];
    }

    $sql = "SELECT *
        FROM `money`
        WHERE `ws_id`=".WS_ID."
          AND `status`=1
          AND `summa`>0
          AND `zayav_id`=".$zayav['id']."
        ORDER BY `dtime_add` ASC";
    $q = query($sql);
    $opSum = 0;
    while($op = mysql_fetch_assoc($q)) {
        $money[strtotime($op['dtime_add'])] = zayav_oplata_unit($op);
        $opSum += $op['summa'];
    }
    $dopl = $accSum - $opSum;
    ksort($money);

    $sql = "SELECT *
            FROM `zp_catalog`
            WHERE `base_device_id`=".$zayav['base_device_id']."
              AND `base_vendor_id`=".$zayav['base_vendor_id']."
              AND `base_model_id`=".$zayav['base_model_id'];
    $q = query($sql);
    if(!mysql_num_rows($q))
        $zpSpisok = '<div class="findEmpty">Для '.$model.' запчастей нет.</div>';
    else {
        $zpSpisok = '';
        $zp = array();
        $ids = array();
        while($r = mysql_fetch_assoc($q)) {
            $id = $r['compat_id'] ? $r['compat_id'] : $r['id'];
            $zp[$id] = $r;
            $ids[$r['id']] = $r['id'];
            $ids[$r['compat_id']] = $r['compat_id'];
        }
        unset($ids[0]);
        $ids = implode(',', $ids);
        $sql = "SELECT `zp_catalog_id` AS `id`,`count` FROM `zp_available` WHERE `zp_catalog_id` IN (".$ids.")";
        $q = query($sql);
        while($r = mysql_fetch_assoc($q))
            $zp[$r['id']]['avai'] = $r['count'];
        $sql = "SELECT `zp_catalog_id` AS `id`,`count`
                FROM `zp_zakaz`
                WHERE `zp_catalog_id` IN (".$ids.")
                  AND `zayav_id`=".$zayav_id;
        $q = query($sql);
        while($r = mysql_fetch_assoc($q))
            $zp[$r['id']]['zakaz'] = $r['count'];
        foreach($zp as $r)
            $zpSpisok .= zayav_zp_unit($r, $model);
    }

    return '<script type="text/javascript">'.
        'G.zayavInfo = {'.
            'id:'.$zayav['id'].','.
            'nomer:'.$zayav['nomer'].','.
            'client_id:'.$zayav['client_id'].','.
            'category:'.$zayav['category'].','.
            'device:'.$zayav['base_device_id'].','.
            'vendor:'.$zayav['base_vendor_id'].','.
            'model:'.$zayav['base_model_id'].','.
            'imei:"'.$zayav['imei'].'",'.
            'serial:"'.$zayav['serial'].'",'.
            'color_id:'.$zayav['color_id'].
        '};'.
    '</script>'.
    '<DIV id="zayavInfo">'.
        '<div id="dopLinks">'.
            '<a class="link sel">Информация</a>'.
            '<a class="link edit">Редактирование</a>'.
            '<a class="link acc_add">Начислить</a>'.
            '<a class="link op_add">Принять платёж</a>'.
        '</div>'.
        '<TABLE cellspacing="10" width="100%">'.
            '<TR><TD id="left">'.
                '<DIV class="headName">Заявка №'.$zayav['nomer'].'</DIV>'.
                '<TABLE cellspacing="4" class="tabInfo">'.
                    '<TR><TD class="label">Категория:  <TD>'.zayavCategory($zayav['category']).
                    '<TR><TD class="label">Устройство: <TD>'._deviceName($zayav['base_device_id']).'<a><b>'.$model.'</b></a>'.
                    '<TR><TD class="label">Клиент:     <TD>'.getClientsLink($zayav['client_id']).
                    '<TR><TD class="label">Дата приёма:'.
                        '<TD class="dtime_add" title="Заявку внёс '.viewerName(false, $zayav['viewer_id_add']).'">'.FullDataTime($zayav['dtime_add']).
                    '<TR><TD class="label">Статус:'.
                        '<TD><DIV id="status" style="background-color:#'.$status['color'].'">'.$status['name'].'</DIV>'.
                            '<DIV id="status_dtime">от '.FullDataTime($zayav['zayav_status_dtime'], 1).'</DIV>'.
                    '<TR class="acc_tr'.($accSum > 0 ? '' : ' dn').'"><TD class="label">Начислено: <TD><b class="acc">'.$accSum.'</b> руб.'.
                    '<TR class="op_tr'.($opSum > 0 ? '' : ' dn').'"><TD class="label">Оплачено:    <TD><b class="op">'.$opSum.'</b> руб.'.
                        '<span class="dopl'.($dopl == 0 ? ' dn' : '').'" title="Необходимая доплата'."\n".'Если значение отрицательное, то это переплата">'.($dopl > 0 ? '+' : '').$dopl.'</span>'.
                '</TABLE>'.
                '<DIV class="headBlue">Задания<a class="add remind_add">Добавить задание</a></DIV>'.
                '<DIV id="remind_spisok">'.report_remind_spisok(1, array('zayav'=>$zayav['id'])).'</DIV>'.
                _vkComment('zayav', $zayav['id']).
                '<div class="headBlue mon">Начисления и платежи'.
                    '<a class="add op_add">Принять платёж</a>'.
                    '<em>::</em>'.
                    '<a class="add acc_add">Начислить</a>'.
                '</div>'.
                '<table cellspacing="0" class="tabSpisok mon">'.implode($money).'</table>'.

            '<TD id="right">'.
                '<DIV id="foto"><img src='.zayav_image_link($zayav_id, 'big', 200, 320).' style="max-width:200px"></DIV>'.
                '<DIV id="foto_upload"></DIV>'.
                '<DIV class="headBlue">Информация об устройстве</DIV>'.
                '<DIV class="devContent">'.
                    '<DIV class="devName">'._deviceName($zayav['base_device_id']).'<br />'.'<a>'.$model.'</a>'.
                    '</DIV>'.
                    '<TABLE cellspacing="1" class="devInfo">'.
                        '<tr><th>imei:      <td id="info_imei">'.$zayav['imei'].
                        '<tr><th>serial:    <td id="info_serial">'.$zayav['serial'].
                        '<tr><th>Цвет:      <td id="info_color">'._colorName($zayav['color_id']).
                        '<tr><th>Нахождение:<td><A id="info_place">'.($zayav['device_place'] ? @_devPlace($zayav['device_place']) : $zayav['device_place_other']).'</A>'.
                        '<tr><th>Состояние: <td><A id="info_status">'._devStatus($zayav['device_status']).'</A>'.
                    '</TABLE>'.
                '</dev>'.

                '<DIV class="headBlue">'.
                    '<A class="goZp" href="'.URL.'&my_page=remZp&id=[1,0,'.$zayav['base_device_id'].','.$zayav['base_vendor_id'].','.$zayav['base_model_id'].']">Список запчастей</A>'.//todo изменить ссылку
                    '<A class="zpAdd add">добавить</A>'.
                '</DIV>'.
                '<DIV id="zpSpisok">'.$zpSpisok.'</DIV>'.
        '</TABLE>'.
    '</div>';
}//end of zayav_info()
function zayav_accrual_unit($acc) {
    return '<tr><td class="sum acc" title="Начисление">'.$acc['summa'].'</td>'.
        '<td>'.$acc['prim'].'</td>'.
        '<td class="dtime" title="Начислил '.viewerName(false, isset($acc['viewer_id_add']) ? $acc['viewer_id_add'] : VIEWER_ID).'">'.
            FullDataTime(isset($acc['dtime_add']) ? $acc['dtime_add'] : curTime()).
        '</td>'.
        '<td class="del"><div class="img_del acc_del" title="Удалить начисление" val="'.$acc['id'].'"></div></td>'.
    '</tr>';
}//end of zayav_accrual_unit()
function zayav_oplata_unit($op) {
    return '<tr><td class="sum op" title="Платёж">'.$op['summa'].'</td>'.
        '<td>'.$op['prim'].'</td>'.
        '<td class="dtime" title="Платёж внёс '.viewerName(false, isset($op['viewer_id_add']) ? $op['viewer_id_add'] : VIEWER_ID).'">'.
            FullDataTime(isset($op['dtime_add']) ? $op['dtime_add'] : curTime()).
        '</td>'.
        '<td class="del"><div class="img_del op_del" title="Удалить платёж" val="'.$op['id'].'"></div></td>'.
    '</tr>';
}//end of zayav_oplata_unit()
function zayav_zp_unit($r, $model) {
    return '<div class="unit" val="'.$r['id'].'">'.
        '<a href="'.URL.'&my_page=remZp&id='.$r['id'].'"><b>'._zpName($r['name_id']).'</b> '.$model.'</a>'.
        ($r['name_dop'] ? '<div class="dop">'.$r['name_dop'].'</div>' : '').
        ($r['color_id'] ? '<div class="color">Цвет: '._colorName($r['color_id']).'</div>' : '').
        '<div>'.
            (isset($r['zakaz']) ? '<a class="zakaz_ok">Заказано!</a>' : '<a class="zakaz">Заказать</a>').
            (isset($r['avai']) && $r['avai'] > 0 ? '<b class="avai">Наличие: '.$r['avai'].'</b> <a class="set">Установить</A>' : '').
        '</div>'.
    '</div>';
}//end of zayav_zp_unit()

// ---===! report !===--- Секция отчётов

function history_insert($arr) {
    $sql = "INSERT INTO `history` (
           `ws_id`,
           `type`,
           `value`,
           `value1`,
           `client_id`,
           `zayav_id`,
           `zp_id`,
           `viewer_id_add`
        ) VALUES (
            ".WS_ID.",
            ".$arr['type'].",
            '".(isset($arr['value']) ? $arr['value'] : '')."',
            '".(isset($arr['value1']) ? $arr['value1'] : '')."',
            ".(isset($arr['client_id']) ? $arr['client_id'] : 0).",
            ".(isset($arr['zayav_id']) ? $arr['zayav_id'] : 0).",
            ".(isset($arr['zp_id']) ? $arr['zp_id'] : 0).",
            ".VIEWER_ID."
        )";
    query($sql);
}//end of history_insert()
function history_types($arr) {
    if(!isset($arr['client_link']))
        $arr['client_link'] = '<i>удалённый клиент</i>';
    if(!isset($arr['zayav_link']))
        $arr['zayav_link'] = '<i>удалённая заявка</i>';
    if(!isset($arr['zp_link']))
        $arr['zp_link'] = '<i>удалённая запчасть</i>';
    switch($arr['type']) {
        case 1: return 'Создал новую заявку '.$arr['zayav_link'].' для клиента '.$arr['client_link'].'.';
        case 2: return 'Удалил заявку №'.$arr['value'].'.';
        case 3: return 'Внёс в базу нового клиента '.$arr['client_link'].'.';
        case 4:
            $status = zayav_status($arr['value']);
            return 'Изменил статус заявки '.$arr['zayav_link'].' на <span style="background-color:#'.$status['color'].'">'.$status['name'].'</span>.';
        case 5: return 'Произвёл начисление на сумму <b>'.$arr['value'].'</b> руб. для заявки '.$arr['zayav_link'].'.';
        case 6: return
            'Внёс платёж на сумму <b>'.$arr['value'].'</b> руб. '.
            ($arr['value1'] ? '('.$arr['value1'].')' : '').
            ($arr['zayav_id'] ? ' по заявке '.$arr['zayav_link'] : '');
        case 7: return 'Отредактировал данные заявки '.$arr['zayav_link'].'.';
        case 8:
            return 'Удалил начисление на сумму <b>'.$arr['value'].'</b> руб. '.
                ($arr['value1'] ? '('.$arr['value1'].')' : '').
                ' у заявки '.$arr['zayav_link'].'.';
        case 9:
            return 'Удалил платёж на сумму <b>'.$arr['value'].'</b> руб. '.
                ($arr['value1'] ? '('.$arr['value1'].')' : '').
                ($arr['zayav_id'] ? ' у заявки '.$arr['zayav_link'] : '').
                ($arr['zp_id'] ? ' (Продажа запчасти '.$arr['zp_link'].')' : '').
                '.';
        case 10: return 'Отдерактировал данные клиента '.$arr['client_link'].'.';
        case 11: return 'Произвёл объединение клиентов. Результат: '.$arr['client_link'].'.';
        case 12: return 'Установил значение в кассе: '.$arr['value'].' руб.';
        case 13: return 'Произвёл установку запчасти '.$arr['zp_link'].' по заявке '.$arr['zayav_link'].'.';
        case 14: return 'Продал запчасть '.$arr['zp_link'].' на сумму <b>'.$arr['value'].'</b> руб.';
        case 15: return 'Произвёл списание запчасти '.$arr['zp_link'].'';
        case 16: return 'Произвёл возврат запчасти '.$arr['zp_link'].'';
        case 17: return 'Забраковал запчась '.$arr['zp_link'].'';
        case 18: return 'Внёс наличие запчасти '.$arr['zp_link'].' в количестве '.$arr['value'].' шт.';
        case 19:
            return 'Восстановил платёж на сумму <b>'.$arr['value'].'</b> руб. '.
                ($arr['value1'] ? '('.$arr['value1'].')' : '').
                ($arr['zayav_id'] ? ' у заявки '.$arr['zayav_link'] : '').
                ($arr['zp_id'] ? ' (Продажа запчасти '.$arr['zp_link'].')' : '').
                '.';
        case 20:
            return 'Создал новое задание'.
                ($arr['zayav_id'] ? ' для заявки '.$arr['zayav_link'] : '').
                ($arr['client_id'] ? ' для клиента '.$arr['client_link'] : '').
                '.';
        case 21: return 'Внёс расход на сумму <b>'.$arr['value'].'</b> руб.';
        case 22: return 'Удалил расход на сумму <b>'.$arr['value'].'</b> руб.';
        case 23: return 'Изменил данные расхода на сумму <b>'.$arr['value'].'</b> руб.';
        case 24: return 'Установил начальное значение в кассе = <b>'.$arr['value'].'</b> руб.';
        case 25: return 'Удалил запись в кассе на сумму <b>'.$arr['value'].'</b> руб. ('.$arr['value1'].')';
        case 26: return 'Восстановил запись в кассе на сумму <b>'.$arr['value'].'</b> руб. ('.$arr['value1'].')';
        case 27:
            return 'Восстановил начисление на сумму <b>'.$arr['value'].'</b> руб. '.
                ($arr['value1'] ? '('.$arr['value1'].')' : '').
                ' у заявки '.$arr['zayav_link'].'.';

        default: return $arr['type'];
    }
}//end of history_types()
function history_types_group($action) {
    switch($action) {
        case 1: return '3,10,11';
        case 2: return '1,2,4,5,6,7,8,9,13';
        case 3: return '13,14,15,16,17,18';
        case 4: return '6,9,12,19';
    }
    return 0;
}//end of history_types_group()
function report_history_right() {
    $sql = "SELECT
                DISTINCT `viewer_id_add` AS `id`
            FROM `history`
            WHERE `ws_id`=".WS_ID;
    $viewer = array();
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $viewer[$r['id']] = $r['id'];
    $viewer = _viewersInfo($viewer);
    $workers = array();
    foreach($viewer as $id => $w)
        $workers[] = '{uid:'.$id.',title:"'.$w['name'].'"}';
    return '<script type="text/javascript">var workers = ['.implode(',', $workers).'];</script>'.
        '<div class="report_history_rl">'.
            '<div class="findHead">Сотрудник</div>'.
            '<input type="hidden" id="report_history_worker" value="0">'.
            '<div class="findHead">Действие</div>'.
            '<input type="hidden" id="report_history_action" value="0">'.
        '</div>';
}//end of report_history_right()
function report_history() {
    return '<div id="report_history">'.report_history_spisok().'</div>';
}//end of report_history()
function report_history_spisok($worker=0, $action=0, $page=1) {
    $limit = 30;
    $cond = "`ws_id`=".WS_ID.($worker > 0 ? ' AND `viewer_id_add`='.$worker : '').
        ($action > 0 ? ' AND `type` IN ('.history_types_group($action).')' : '');
    $sql = "SELECT
                COUNT(`id`) AS `all`
            FROM `history`
            WHERE ".$cond;
    $r = mysql_fetch_assoc(query($sql));
    if($r['all'] == 0)
        return 'Истории по указанным условиям нет.';
    $all = $r['all'];
    $start = ($page - 1) * $limit;

    $sql = "SELECT *
            FROM `history`
            WHERE ".$cond."
            ORDER BY `id` DESC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $history = array();
    $viewer = array();
    $client = array();
    $zayav = array();
    $zp = array();
    while($r = mysql_fetch_assoc($q)) {
        $viewer[$r['viewer_id_add']] = $r['viewer_id_add'];
        if($r['client_id'] > 0)
            $client[$r['client_id']] = $r['client_id'];
        if($r['zayav_id'] > 0)
            $zayav[$r['zayav_id']] = $r['zayav_id'];
        if($r['zp_id'] > 0)
            $zp[$r['zp_id']] = $r['zp_id'];
        $history[] = $r;
    }
    $viewer = _viewersInfo($viewer);
    $client = getClientsLink($client);
    $zayav = getZayavNomerLink($zayav);
    $zp = get_zp_info($zp);
    $send = '';
    foreach($history as $r) {
        if($r['client_id'] > 0 && isset($client[$r['client_id']]))
            $r['client_link'] = $client[$r['client_id']];
        if($r['zayav_id'] > 0 && isset($zayav[$r['zayav_id']]))
            $r['zayav_link'] = $zayav[$r['zayav_id']];
        if($r['zp_id'] > 0 && isset($zp[$r['zp_id']]))
            $r['zp_link'] = $zp[$r['zp_id']];
        $send .= '<div class="head">'.FullDataTime($r['dtime_add']).$viewer[$r['viewer_id_add']]['link'].'</div>'.
                 '<div class="txt">'.history_types($r).'</div>';
    }
    if($start + $limit < $all)
        $send .= '<div class="ajaxNext" id="report_history_next" val="'.($page + 1).'"><span>Далее...</span></div>';
    return $send;
}//end of report_history_spisok()


function report_remind() {
    $send = '<div id="report_remind">'.
        '<div class="info">'.
            '<A class="opening">Показать информацию</A>'.
            '<div class="text">'.
                '<B>Задания</B> - они же напоминания '.
                'необходимы для отслеживания и учёта действий над заявками, обещаний, данных клиентам, '.
                'организации периодических звонков должниками, постановки задач и тп.<BR><BR>'.

                '<B>Жёлтым</B> цветом помечаются задания, которые требуют решения в ближайший день, то есть сегодня. '.
                'Их количество всегда отображается в скобках напротив вкладки "Отчёты" и раздела "Задания" в отчётах.<BR>'.
                'Синим - задания, ожидающие выполнения более одного дня.<BR>'.
                'Зелёные - готовые, серые - отменены, и <B>красные</B> - просроченные задания.<BR><BR>'.

                '<B>Очень важно</B> при внесении нового задания более подробно указывать его содержание. '.
                'Это означает, к примеру, такой текст напоминания к заявке как "<I>Позвонить</I>" ни о чём не говорит. '.
                'Лучше писать "<I>Позвонить и сообщить результат диагностики</I>".<BR><BR>'.

                'Все дальнейшие действия над заданиями обязательно нужно отмечать в программе. '.
                'Требуется всегда указывать <B>причину</B> переноса задания на другой день или причину его отмены. '.
                'Комментарий к выполненному заданию не обязателен. <BR><BR>'.

                'При установке галочки <B>Личное</B> задание будет видно только его автору.<BR><BR>'.

                'По ссылке "История" выводится хронологический список всех действий над заданием.<BR><BR>'.
            '</div>'.
            '<A class="closing">Скрыть</A>'.
        '</div>'.
        '<div id="remind_spisok">'.report_remind_spisok().'</div>'.
    '</div>';
    return $send;
}//end of report_remind()
function report_remind_right() {
    return '<DIV class=findHead>Категории заданий</DIV>'.
        '<INPUT type="hidden" id="remind_status" value="1">'.
        _checkbox('remind_private', 'Личное');
}//end of report_remind_right()
function report_remind_spisok($page=1, $filter=array()) {
    $limit = 20;
    $cond = " `ws_id`=".WS_ID." AND `status`=".(!empty($filter['status']) ? intval($filter['status']) : 1);
    if(!empty($filter['private']))
        $cond .= " AND `private`=1";
    if(!empty($filter['zayav']))
        $cond .= " AND `zayav_id`=".intval($filter['zayav']);

    $start = ($page - 1) * $limit;
    $sql = "SELECT *
            FROM `reminder`
            WHERE".$cond."
            ORDER BY `day` ASC,`id` DESC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $all = mysql_num_rows($q);
    if(!$all)
        return 'Заданий не найдено.';
    $remind = array();
    $viewer = array();
    $client = array();
    $zayav = array();
    while($r = mysql_fetch_assoc($q)) {
        $viewer[$r['viewer_id_add']] = $r['viewer_id_add'];
        if($r['client_id'] > 0)
            $client[$r['client_id']] = $r['client_id'];
        if($r['zayav_id'] > 0)
            $zayav[$r['zayav_id']] = $r['zayav_id'];
        $remind[] = $r;
    }
    //$viewer = _viewersInfo($viewer);
    $client = getClientsLink($client);
    $zayav = getZayavNomerLink($zayav);

    $send = '';
    $today = strtotime(strftime("%Y-%m-%d", time()));
    foreach($remind as $r) {
        $day_leave = (strtotime($r['day']) - $today) / 3600 / 24;
        $leave = '';
        if($day_leave < 0)
            $leave = 'просрочен'._end($day_leave * -1, ' ', 'о ').($day_leave * -1)._end($day_leave * -1, ' день', ' дня', ' дней');
        elseif($day_leave > 2)
            $leave = 'остал'._end($day_leave, 'ся ', 'ось ').$day_leave._end($day_leave, ' день', ' дня', ' дней');
        else
            switch($day_leave) {
                case 0: $leave = 'сегодня'; break;
                case 1: $leave = 'завтра'; break;
                case 2: $leave = 'послезавтра'; break;
            }

        if($r['status'] == 0) $color = 'grey';
        elseif($r['status'] == 2) $color = 'green';
        elseif($day_leave > 0) $color = 'blue';
        elseif($day_leave < 0) $color = 'redd';
        else $color = 'yellow';
        // состояние задачи
        switch($r['status']) {
            case 2: $rem_cond = "<EM>Выполнено.</EM>"; break;
            case 0: $rem_cond = "<EM>Отменено.</EM>"; break;
            default:
                $rem_cond = '<EM>Выполнить '.($day_leave == 0 ? '' : 'до ').'</EM>'.
                    ($day_leave >= 0 && $day_leave < 3 ? $leave : FullData($r['day'], 1)).
                    ($day_leave > 2 || $day_leave < 0 ? '<SPAN>, '.$leave.'</SPAN>' : '');

        }
        $send .= '<div class="remind_unit '.$color.'">'.
            '<div class="txt">'.
                ($r['private'] ? '<u>Личное.</u> ' : '').
                ($r['client_id'] ? 'Клиент '.$client[$r['client_id']].': ' : '').
                (isset($zayav[$r['zayav_id']]) && empty($filter['zayav']) ? 'Заявка '.$zayav[$r['zayav_id']].': ' : '').
                '<b>'.$r['txt'].'</b>'.
            '</div>'.
            '<div class="day">'.
                '<div class="action">'.
                    ($r['status'] == 1 ? '<A class="edit" val="'.$r['id'].'">Действие</A> :: ' : '').
                    '<a class="hist_a">История</a>'.
                '</div>'.
                $rem_cond.
                '<div class="hist">'.$r['history'].'</div>'.
            '</div>'.
        '</div>';
    }
    if($start + $limit < $all)
        $send .= '<div class="ajaxNext" id="report_remind_next" val="'.($page + 1).'"><span>Показать ещё задания...</span></div>';

    return $send;
}//end of report_remind_spisok()


//Условия поиска справа для отчётов
function report_prihod_right() {
    return '<div class="report_prihod_rl">'.
        '<div class="findHead">Период</div>'.
        '<div class="cal"><EM class="label">от:</EM><INPUT type="hidden" id="report_prihod_day_begin" value="'.currentMonday().'"></div>'.
        '<div class="cal"><EM class="label">до:</EM><INPUT type="hidden" id="report_prihod_day_end" value="'.currentSunday().'"></div>'.
        (ADMIN ? _checkbox('prihodShowDel', 'Показывать удалённые платежи') : '').
        '</div>';
}//end of report_prihod_right()
function report_prihod() {
    return '<div id="report_prihod">'.report_prihod_spisok(currentMonday(), currentSunday(), 0).'</div>';
}//end of report_prihod()
function report_prihod_spisok($day_begin, $day_end, $del_show=0, $page=1) {
    $limit = 30;
    $cond = "`ws_id`=".WS_ID."
        AND `summa`>0
        AND `dtime_add`>='".$day_begin." 00:00:00'
        AND `dtime_add`<='".$day_end." 23:59:59'
        ".($del_show && ADMIN ? '' : ' AND `status`=1');
    $sql = "SELECT
                COUNT(`id`) AS `all`,
                SUM(`summa`) AS `sum`
            FROM `money`
            WHERE ".$cond;
    $r = mysql_fetch_assoc(query($sql));
    if($r['all'] == 0)
        return 'Поступления за указанный период отсутствуют.';
    $all = $r['all'];
    $start = ($page - 1) * $limit;

    $send = '';
    if($page == 1)
        $send = '<div class="summa">'.
                '<a class="summa_add">Внести произвольную сумму</a>'.
                'Показан'._end($all, '', 'о').' <b>'.$all.'</b> платеж'._end($all, '', 'а', 'ей').' на сумму <b>'.$r['sum'].'</b> руб.'.
            '</div>'.
            '<TABLE class="tabSpisok">'.
                '<TR><TH class="sum">Сумма'.
                    '<TH>Описание'.
                    '<TH class="data">Дата'.
                    '<TH class="del">';

    $sql = "SELECT *
            FROM `money`
            WHERE ".$cond."
            ORDER BY `dtime_add` ASC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $viewer = array();
    $money = array();
    $zayav = array();
    $zp = array();
    while($r = mysql_fetch_assoc($q)) {
        $viewer[$r['viewer_id_add']] = $r['viewer_id_add'];
        $viewer[$r['viewer_id_del']] = $r['viewer_id_del'];
        if($r['zayav_id'] > 0)
            $zayav[$r['zayav_id']] = $r['zayav_id'];
        if($r['zp_id'] > 0)
            $zp[$r['zp_id']] = $r['zp_id'];
        $money[] = $r;
    }
    $viewer = _viewersInfo($viewer);
    $zayav = getZayavNomerLink($zayav);
    $zp = get_zp_info($zp);
    foreach($money as $r) {
        $about = $r['prim'];
        if($r['zayav_id'] > 0)
            $about = 'Заявка '.$zayav[$r['zayav_id']];
        if($r['zp_id'] > 0)
            $about = 'Продажа запчасти '.$zp[$r['zp_id']];
        $dtimeTitle = 'Внёс: '.$viewer[$r['viewer_id_add']]['name'];
        if($r['status'] == 0)
            $dtimeTitle .= "\n".'Удалил: '.$viewer[$r['viewer_id_del']]['name'].
                "\n".FullDataTime($r['dtime_del']);
        $send .= '<tr'.($r['status'] == 0 ? ' class="deleted"' : '').'>'.
            '<TD class="sum"><B>'.$r['summa'].'</B>'.
            '<TD>'.$about.
            '<TD class="dtime" title="'.$dtimeTitle.'">'.FullDataTime($r['dtime_add']).
            '<TD class="edit">'.($r['status'] == 1 ?
                '<div class="img_del" val="'.$r['id'].'" title="Удалить платёж"></div>' :
                '<div class="img_rest" val="'.$r['id'].'" title="Восстановить платёж"></div>');
    }
    if($start + $limit < $all)
        $send .= '<tr class="ajaxNext" id="report_prihod_next" val="'.($page + 1).'"><td colspan="4"><span>Показать ещё платежи...</span></td></tr>';
    if($page == 1) $send .= '</TABLE>';
    return $send;
}//end of report_prihod_spisok()


function report_rashod_right() {
    return '<div class="findHead">Категория</div>'.
        '<input type="hidden" id="rashod_category">'.
        '<div class="findHead">Сотрудник</div>'.
        '<input type="hidden" id="rashod_worker">'.
        '<input type="hidden" id="rashod_year">'.
        '<input type="hidden" id="rashod_monthSum" value="'.intval(strftime('%m', time())).'">'.
        '<SCRIPT type="text/javascript">var monthSum = ['.report_rashod_monthSum().'];</SCRIPT>';
}//end of report_rashod_right()
function report_rashod_monthSum($year=false, $category=0, $worker=0) {
    if(!$year) $year = strftime('%Y', time());
    $sql = "SELECT
                DISTINCT(DATE_FORMAT(`dtime_add`,'%m')) AS `month`,
                SUM(`summa`) AS `sum`
            FROM `money`
            WHERE `ws_id`=".WS_ID."
              AND `status`=1
              AND `summa`<0
              AND `dtime_add` LIKE '".$year."-%'
              ".($worker ? " AND `worker_id`=".$worker : '')."
              ".($category ? " AND `rashod_category`=".$category : '')."
            GROUP BY DATE_FORMAT(`dtime_add`,'%m')
            ORDER BY `dtime_add` ASC";
    $q = query($sql);
    $res = array();
    while($r = mysql_fetch_assoc($q))
        $res[intval($r['month'])] = abs($r['sum']);
    $send = array();
    for($n = 1; $n <= 12; $n++)
        $send[] = isset($res[$n]) ? $res[$n] : 0;
    return implode(',', $send);
}//end of report_rashod_monthSum()
function report_rashod() {
    $sql = "SELECT
                `viewer_id`,
                `first_name`,
                `last_name`
            FROM `vk_user`
            WHERE `ws_id`=".WS_ID;
    $q = query($sql);
    $viewers = array();
    while($r = mysql_fetch_assoc($q))
        $viewers[] = '{uid:'.$r['viewer_id'].',title:"'.$r['first_name'].' '.$r['last_name'].'"}';

    $sql = "SELECT `id`,`name` FROM `setup_rashod_category` ORDER BY `name` ASC";
    $q = query($sql);
    $cat = array();
    while($r = mysql_fetch_assoc($q))
        $cat[] = '{uid:'.$r['id'].',title:"'.$r['name'].'"}';

    return '<SCRIPT type="text/javascript">'.
                'var rashodViewers = ['.implode(',', $viewers).'];'.
                'var rashodCaregory = ['.implode(',', $cat).'];'.
            '</SCRIPT>'.
        '<div id="report_rashod">'.
            '<div class="headName">Список расходов мастерской<a id="add">Внести новый расход</a></div>'.
            '<div id="spisok">'.report_rashod_spisok().'</div>'.
        '</div>';
}//end of report_rashod()
function report_rashod_spisok($page=1, $month=false, $category=0, $worker=0) {
    if(!$month) $month = strftime('%Y-%m', time());
    $limit = 30;
    $cond = "`ws_id`=".WS_ID."
        AND `status`=1
        AND `summa`<0
        AND `dtime_add` LIKE '".$month."-%'
        ".($worker ? " AND `worker_id`=".$worker : '')."
        ".($category ? ' AND `rashod_category`='.$category : '');
    $sql = "SELECT
                COUNT(`id`) AS `all`,
                SUM(`summa`) AS `sum`
            FROM `money`
            WHERE ".$cond;
    $r = mysql_fetch_assoc(query($sql));
    if($r['all'] == 0)
        return 'Данные отсутствуют.';
    $all = $r['all'];
    $start = ($page - 1) * $limit;

    $rashodCat = array();
    $sql = "SELECT `id`,`name` FROM `setup_rashod_category`";
    $q = query($sql);
    while($c = mysql_fetch_assoc($q))
        $rashodCat[$c['id']] = $c['name'];

    $send = '';
    if($page == 1) {
        $ex = explode('-', $month);
        $send = '<div class="summa">'.
                'Показан'._end($all, 'а', 'о').' <b>'.$all.'</b> запис'._end($all, 'ь', 'и', 'ей').
                ' на сумму <b>'.abs($r['sum']).'</b> руб.'.
                ' за '._monthFull($ex[1]).' '.$ex[0].' г.'.
            '</div>'.
            '<TABLE class="tabSpisok">'.
                '<TR><TH class="sum">Сумма'.
                    '<TH>Описание'.
                    '<TH class="data">Дата'.
                    '<TH class="edit">';
    }
    $sql = "SELECT *
            FROM `money`
            WHERE ".$cond."
            ORDER BY `dtime_add` ASC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $viewer = array();
    $rashod = array();
    while($r = mysql_fetch_assoc($q)) {
        $viewer[$r['viewer_id_add']] = $r['viewer_id_add'];
        $viewer[$r['viewer_id_del']] = $r['viewer_id_del'];
        $viewer[$r['worker_id']] = $r['worker_id'];
        $rashod[] = $r;
    }
    $viewer = _viewersInfo($viewer);
    foreach($rashod as $r) {
        $dtimeTitle = 'Внёс: '.$viewer[$r['viewer_id_add']]['name'];
        if($r['status'] == 0)
            $dtimeTitle .= "\n".'Удалил: '.$viewer[$r['viewer_id_del']]['name'].
                "\n".FullDataTime($r['dtime_del']);
        $send .= '<tr'.($r['status'] == 0 ? ' class="deleted"' : '').'>'.
            '<TD class="sum"><B>'.abs($r['summa']).'</B>'.
            '<TD>'.($r['rashod_category'] ? '<em>'.$rashodCat[$r['rashod_category']].($r['prim'] || $r['worker_id'] ? ':' : '').'</em>' : '').
                   ($r['worker_id'] ? $viewer[$r['worker_id']]['link'].($r['prim'] ? ', ' : '') : '').
                   $r['prim'].
            '<TD class="dtime" title="'.$dtimeTitle.'">'.FullDataTime($r['dtime_add']).
            '<TD class="edit">'.($r['status'] == 1 ?
                '<div class="img_edit" val="'.$r['id'].'" title="Редактировать"></div>'.
                '<div class="img_del" val="'.$r['id'].'" title="Удалить"></div>'
                :
                '<div class="img_rest" val="'.$r['id'].'" title="Восстановить"></div>');
    }
    if($start + $limit < $all)
        $send .= '<tr class="ajaxNext" id="report_rashod_next" val="'.($page + 1).'"><td colspan="4"><span>Показать далее...</span></td></tr>';
    if($page == 1) $send .= '</TABLE>';
    return $send;
}//end of report_rashod_spisok()


function kassa_sum() {
    $sql = "SELECT SUM(`sum`) AS `sum` FROM `kassa` WHERE `ws_id`=".WS_ID." AND `status`=1 LIMIT 1";
    $r = mysql_fetch_assoc(query($sql));
    $kassa_sum = $r['sum'];
    $sql = "SELECT SUM(`summa`) AS `sum` FROM `money` WHERE `ws_id`=".WS_ID." AND `status`=1 AND `kassa`=1 LIMIT 1";
    $r = mysql_fetch_assoc(query($sql));
    return KASSA_START + $kassa_sum + $r['sum'];
}//end of kassa_sum()
function report_kassa() {
    if(KASSA_START == -1)
        $send = '<DIV class="set_info">Установите значение, равное текущей сумме денег, находящейся сейчас в мастерской. '.
                'От этого значения будет вестись дальнейший учёт средств, поступающих, либо забирающихся из кассы.<BR>'.
                '<B>Внимание!</B> Данное действие можно произвести только один раз.'.
            '</DIV>'.
            '<TABLE class="set_tab"><tr>'.
                '<td>Сумма: <INPUT type=text id="set_summa" maxlength=8> руб.</td>'.
                '<td><DIV class="vkButton" id="set_go"><BUTTON>Установить</BUTTON></DIV></td>'.
            '</tr></TABLE>';
    else
        $send = '<DIV class="in">В кассе: <B id="kassa_summa">'.kassa_sum().'</B> руб. '.
                    '<div class="actions"><A>Внести в кассу</A> :: <A>Взять из кассы</A></div>'.
                '</DIV>'.
                '<DIV id="spisok">'.report_kassa_spisok().'</DIV>';
    return '<DIV id="report_kassa">'.$send.'</DIV>';
}//end of report_kassa()
function report_kassa_right() {
    return KASSA_START == -1 ? '' : _checkbox('kassaShowDel', 'Показывать удалённые записи');
}//end of report_kassa_right()
function report_kassa_spisok($page=1, $del_show=0) {
    $limit = 30;
    $cond = "`ws_id`=".WS_ID."
         ".($del_show ? '' : ' AND `status`=1');
    $sql = "SELECT COUNT(`id`) AS `all`
            FROM `kassa`
            WHERE ".$cond;
    $r = mysql_fetch_assoc(query($sql));
    if($r['all'] == 0)
        return 'Действий с кассой нет.';
    $all = $r['all'];
    $start = ($page - 1) * $limit;

    $send = '';
    if($page == 1)
        $send = '<div class="all">'.'Показан'._end($all, '', 'о').' <b>'.$all.'</b> запис'._end($all, 'ь', 'и', 'ей').'.</div>'.
            '<TABLE class="tabSpisok">'.
                '<TR><TH class="sum">Сумма'.
                    '<TH>Описание'.
                    '<TH class="data">Дата'.
                    '<TH>';

        $sql = "SELECT *
                FROM `kassa`
                WHERE ".$cond."
                ORDER BY `dtime_add` ASC
                LIMIT ".$start.",".$limit;
        $q = query($sql);
        $viewer = array();
        $money = array();
        while($r = mysql_fetch_assoc($q)) {
            $viewer[$r['viewer_id_add']] = $r['viewer_id_add'];
        $money[] = $r;
    }
    $viewer = _viewersInfo($viewer);
    foreach($money as $r) {
        $send .= '<tr'.($r['status'] == 0 ? ' class="deleted"' : '').'>'.
            '<TD class="sum"><B>'.$r['sum'].'</B>'.
            '<TD>'.$r['txt'].
            '<TD class="dtime" title="Внёс: '.$viewer[$r['viewer_id_add']]['name'].'">'.FullDataTime($r['dtime_add']).
            '<TD class="edit">'.($r['status'] == 1 ?
                '<div class="img_del" val="'.$r['id'].'" title="Удалить"></div>' :
                '<div class="img_rest" val="'.$r['id'].'" title="Восстановить"></div>');
    }
    if($start + $limit < $all)
        $send .= '<tr class="ajaxNext" id="report_kassa_next" val="'.($page + 1).'"><td colspan="4"><span>Показать ещё платежи...</span></td></tr>';
    if($page == 1) $send .= '</TABLE>';
    return $send;
}//end of report_kassa_spisok()
