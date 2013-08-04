<?php
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
        '</HEAD>'.
        '<BODY>'.
        '<div id="frameBody">'.
        '<iframe id="frameHidden" name="frameHidden"></iframe>';
}//end of _header()

function _footer() {
    global $html, $sqlQuery;
    if(ADMIN)
        $html .= '<DIV id="admin">'.
                '<A href="'.URL.'&my_page=superAdmin&pre_page='.$_GET['my_page'].'&pre_id='.$_GET['id'].'">Admin</A> :: '.
                '<A href="https://github.com/MihailKornilov/vkmobile/issues" target="_blank">Issues</A> :: '.
                '<A id=script_style>Стили и скрипты ('.VERSION.')</A> :: '.
                'sql '.$sqlQuery.' :: '.
                'php '.round(microtime(true) - TIME, 3).' :: '.
                'js <EM></EM>'.
            '</DIV>'.
            '<SCRIPT type="text/javascript">'.
                '$("#script_style").click(function(){$.getJSON("/superadmin/AjaxScriptStyleUp.php?"+G.values,function(){location.reload()})});'.
                '$("#admin EM:first").html(((new Date().getTime())-G.T)/1000);'.
            '</SCRIPT>';
    $html .= '</DIV></BODY></HTML>';
}//end of _footer()

function _mainLinks() {
    global $html, $sel, $vku;
    $sql = "SELECT COUNT(`id`) AS `count`
            FROM `reminder`
            WHERE `ws_id`=".$vku->ws_id."
              AND `day`<=DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d')
              AND `status`=1
              AND (`private`=0 OR `private`=1 AND `viewer_id_add`=".VIEWER_ID.")";
    $r = mysql_fetch_assoc(query($sql));
    define('REMIND_ACTIVE', $r['count'] > 0 ? ' (<B>'.$r['count'].'</B>)' : '');

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
            'show' => $vku->admin
        ),
        array(
            'name' => 'Установки',
            'page' => 'remSetup',
            'show' => $vku->admin
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

function viewerName($link=false, $id=VIEWER_ID) {
    $sql = "SELECT CONCAT(`first_name`,' ',`last_name`) AS `name` FROM `vk_user` WHERE `viewer_id`=".$id." LIMIT 1";
    $r = mysql_fetch_assoc(query($sql));
    return $link ? '<A href="http://vk.com/id'.$id.'" target="_blank">'.$r['name'].'</a>' : $r['name'];
}

function get_viewers_info($arr) {
    $sql = "SELECT * FROM `vk_user` WHERE `viewer_id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['viewer_id']] = array(
            'name' => $r['first_name'].' '.$r['last_name'],
            'link' => '<a href="http://vk.com/id'.$r['viewer_id'].'" target="_blank">'.$r['first_name'].' '.$r['last_name'].'</a>'
        );
    return $send;
}//end of get_viewers_info()

function get_clients_info($arr) {
    if(empty($arr))
        return array();
    $sql = "SELECT id,fio FROM `client` WHERE `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['id']] = '<A href="'.URL.'&my_page=remClientInfo&id='.$r['id'].'">'.$r['fio'].'</a>';
    return $send;
}//end of get_clients_info()

//Вывод номеров заявок с возможностью отображения дополнительной информации при наведении
function get_zayav_info($arr) {
    if(empty($arr))
        return array();
    $sql = "SELECT id,nomer FROM `zayavki` WHERE `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['id']] =
            '<A href="'.URL.'&my_page=remZayavkiInfo&id='.$r['id'].'" class="zayav_link" val="'.$r['id'].'">'.
                '№'.$r['nomer'].
                '<div class="tooltip empty"></div>'.
            '</a>';
    return $send;
}//end of get_zayav_info()

function get_zp_info($arr) {
    if(empty($arr))
        return array();

    $sql = "SELECT * FROM `setup_zp_name`";
    $q = query($sql);
    $zpName = array();
    while($r = mysql_fetch_assoc($q))
        $zpName[$r['id']] = $r['name'];

    $sql = "SELECT * FROM `base_device`";
    $q = query($sql);
    $device = array();
    while($r = mysql_fetch_assoc($q))
        $device[$r['id']] = $r['name_rod'];

    $sql = "SELECT * FROM `base_vendor`";
    $q = query($sql);
    $vendor = array();
    while($r = mysql_fetch_assoc($q))
        $vendor[$r['id']] = $r['name'];

    $sql = "SELECT * FROM `zp_catalog` WHERE `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $zpModel = array();
    $zpSpisok = array();
    while($r = mysql_fetch_assoc($q)) {
        $zpModel[] = $r['base_model_id'];
        $zpSpisok[$r['id']] = $r;
    }

    $sql = "SELECT * FROM `base_model` WHERE `id` IN (".implode(',', $zpModel).")";
    $q = query($sql);
    $model = array();
    while($r = mysql_fetch_assoc($q))
        $model[$r['id']] = $r['name'];

    $send = array();
    foreach($zpSpisok as $r)
        $send[$r['id']] = '<A href="'.URL.'&my_page=remZp&id='.$r['id'].'">'.
            '<b>'.$zpName[$r['name_id']].'</b> для '.
            $device[$r['base_device_id']].' '.
            $vendor[$r['base_vendor_id']].' '.
            $model[$r['base_model_id']].
        '</a>';
    return $send;
}//end of get_zp_info()

function zayav_category($id=false) {
    $arr = array(
        '1' => 'Приём в ремонт',
        '2' => 'Заказ запчастей',
        '3' => 'Консультация'
    );
    return $id ? $arr[$id] : $arr;
}//end of zayav_category()

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

function zayav_image_link($zayav_id) {
    $sql = "SELECT `link` FROM `images` WHERE `status`=1 AND `sort`=0 AND `owner`='zayav".$zayav_id."' LIMIT 1";
    if($r = mysql_fetch_assoc(query($sql)))
        $send = $r['link'].'-small.jpg';
    else {
        $sql = "SELECT `base_model_id` FROM `zayavki` WHERE `id`=".$zayav_id." LIMIT 1";
        $r = mysql_fetch_assoc(query($sql));
        $send = model_image_link($r['base_model_id']);
    }
    return $send;
}//end of zayav_image_link()

function model_image_link($model_id) {
    $send = '/img/nofoto.gif';
    $sql = "SELECT `link` FROM `images` WHERE `status`=1 AND `sort`=0 AND `owner`='dev".$model_id."' LIMIT 1";
    if($r = mysql_fetch_assoc(query($sql)))
        $send = $r['link'].'-small.jpg';
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
        case 'remZayavki': $back = 'remZayavki'; break;
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
    if(empty($v['dev_status']) || !preg_match(REGEXP_NUMERIC, $v['dev_status']) && $v['dev_status'] != -1)
        $v['dev_status'] = 0;

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
    $filter['dev_status'] = $v['dev_status'];
    return $filter;
}
function get_zayav_list($page=1, $filter=array()) {
    $cond = "`ws_id`=".WS_ID." AND `zayav_status`>0";
    if(!empty($filter['find'])) {
        $cond .= " AND `find` LIKE '%".$filter['find']."%'";
        if(preg_match(REGEXP_NUMERIC, $filter['find'])) {
            $nomer = intval($filter['find']);
            $cond .= " OR `nomer`=".$nomer." AND `zayav_status`>0";
        }
    }
    if(empty($filter['sort']))
        $filter['sort'] = 'dtime_add';
    if(empty($filter['desc']))
        $filter['desc'] = 'DESC';
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
    if(isset($filter['dev_status']) && $filter['dev_status'] != 0)
        $cond .= " AND `device_status`=".($filter['dev_status'] > 0 ? $filter['dev_status'] : 0);

    $sql = "SELECT COUNT(`id`) AS `all` FROM `zayavki` WHERE ".$cond." LIMIT 1";
    $r = mysql_fetch_assoc(query($sql));
    $send['all'] = $r['all'];
    if($send['all'] == 0)
        return $send;

    $limit = 20;
    $start = ($page - 1) * $limit;
    $sql = "SELECT *
            FROM `zayavki`
            WHERE ".$cond."
            ORDER BY `".$filter['sort']."` ".$filter['desc']."
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $zayav = array();
    $client = array();
    $device = array();
    $vendor = array();
    $model = array();
    $images = array();
    while($r = mysql_fetch_assoc($q)) {
        $zayav[$r['id']] = $r;
        if(isset($nomer) && $r['nomer'] == $nomer)
            $nomer_id = $r['id'];
        $client[$r['client_id']] = $r['client_id'];
        $device[$r['base_device_id']] = $r['base_device_id'];
        $vendor[$r['base_vendor_id']] = $r['base_vendor_id'];
        $model[$r['base_model_id']] = $r['base_model_id'];
        $images['zayav'.$r['id']] = '"zayav'.$r['id'].'"';
        $images['dev'.$r['base_model_id']] = '"dev'.$r['base_model_id'].'"';
    }
    if(isset($nomer_id)) {
        $z = $zayav[$nomer_id];
        unset($zayav[$nomer_id]);
        $z['nomer_find'] = 1;
        array_unshift($zayav, $z);
        unset($z);
    }
    $client = get_clients_info($client);
    $status = zayav_status();

    $sql = "SELECT `owner`,`link` FROM `images` WHERE `status`=1 AND `sort`=0 AND `owner` IN (".implode(',', $images).")";
    $q = query($sql);
    $imgLinks = array();
    while($r = mysql_fetch_assoc($q))
        $imgLinks[$r['owner']] = $r['link'].'-small.jpg';
    unset($images);

    $sql = "SELECT `id`,`name` FROM `base_device` WHERE `id` IN (".implode(',', $device).")";
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $device[$r['id']] = $r['name'];

    $sql = "SELECT `id`,`name` FROM `base_vendor` WHERE `id` IN (".implode(',', $vendor).")";
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $vendor[$r['id']] = $r['name'];

    $sql = "SELECT `id`,`name` FROM `base_model` WHERE `id` IN (".implode(',', $model).")";
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $model[$r['id']] = $r['name'];

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
            'category' => zayav_category($r['category']),
            'device' => $device[$r['base_device_id']],
            'vendor' => $vendor[$r['base_vendor_id']] ? $vendor[$r['base_vendor_id']] : '',
            'model' => $model[$r['base_model_id']] ? $model[$r['base_model_id']] : '',
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
                    '<DIV id="buttonCreate"><A HREF="'.URL.'&p=zayav&d=add&back=remZayavki">Новая заявка</A></DIV>'. //todo заменить back
                    '<DIV id="find"></DIV>'.
                    '<DIV class="findHead">Порядок</DIV>'.
                    '<INPUT TYPE="hidden" id="sort" value="'.$values['sort'].'">'.
                    _checkbox('desc', 'Обратный порядок', $values['desc']).
                    '<DIV class="findHead">Статус заявки</DIV><DIV id="status"></DIV>'.
                    '<DIV class="findHead">Устройство</DIV><DIV id="dev"></DIV>'.
                    '<DIV class="findHead">Нахождение устройства</DIV><INPUT TYPE="hidden" id="device_place" value="'.$values['place'].'">'.
                    '<DIV class="findHead">Состояние устройства</DIV><INPUT TYPE="hidden" id="dev_status" value="'.$values['dev_status'].'">'.
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
            '<input type="hidden" class="msg" value="'.$sp['article'].'">'.
        '</div>';
    }
    if(isset($data['next']))
        $send .= '<div class="ajaxNext" id="zayav_next" val="'.($data['next']).'"><span>Следующие 20 заявок</span></div>';
    return $send;
}//end of show_zayav_spisok()




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
        case 8: return 'Удалил начисление на сумму '.$arr['zayav_id'].' руб. у заявки '.$arr['zayav_link'].'.';
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
    $viewer = get_viewers_info($viewer);
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
    $viewer = get_viewers_info($viewer);
    $client = get_clients_info($client);
    $zayav = get_zayav_info($zayav);
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
        '<div id="spisok">'.report_remind_spisok().'</div>'.
    '</div>';
    return $send;
}//end of report_remind()
function report_remind_right() {
    return '<DIV class=findHead>Категории заданий</DIV>'.
        '<INPUT type="hidden" id="remind_status" value="1">'.
        _checkbox('remind_private', 'Личное');
}//end of report_remind_right()
function report_remind_spisok($page=1, $status=1, $private=0) {
    $limit = 20;
    $cond = " `ws_id`=".WS_ID." AND `status`=".$status;
    if($private)
        $cond .= " AND `private`=1";
    $sql = "SELECT
                COUNT(`id`) AS `all`
            FROM `reminder`
            WHERE".$cond;
    $r = mysql_fetch_assoc(query($sql));
    if($r['all'] == 0)
        return 'Заданий не найдено.';
    $all = $r['all'];
    $start = ($page - 1) * $limit;

    $sql = "SELECT *
            FROM `reminder`
            WHERE".$cond."
            ORDER BY `day` ASC,`id` DESC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
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
    //$viewer = get_viewers_info($viewer);
    $client = get_clients_info($client);
    $zayav = get_zayav_info($zayav);

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
        $send .= '<div class="unit '.$color.'">'.
            '<div class="txt">'.
                ($r['private'] ? '<u>Личное.</u> ' : '').
                ($r['client_id'] ? 'Клиент '.$client[$r['client_id']].': ' : '').
                ($r['zayav_id'] ? 'Заявка '.$zayav[$r['zayav_id']].': ' : '').
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
    $viewer = get_viewers_info($viewer);
    $zayav = get_zayav_info($zayav);
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
    $viewer = get_viewers_info($viewer);
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
    $viewer = get_viewers_info($viewer);
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
