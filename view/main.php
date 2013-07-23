<?php
function _header() {
    global $html, $vku, $WS;
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.
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
                'ws_id:'.$vku->ws_id.','.
                'country_id:'.$vku->country_id.','.
                'city_id:'.$vku->city_id.
            '};'.
            'G.clients = [];'.
            'G.ws = {devs:['.($WS ? $WS->devs : '').']};'.
        '</SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/js/global.js?'.VERSION.'"></SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/include/G_values.js?'.G_VALUES.'"></SCRIPT>'.
        '</HEAD>'.
        '<BODY>'.
        '<DIV id="frameBody">';
}//end of _header()

function _footer() {
    global $html, $sqlQuery;
    if(ADMIN)
        $html .= '<DIV id=admin>'.
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
              AND `day`<DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 DAY)
              AND `status`=1
              AND (`private`=0 OR `private`=1 AND `viewer_id_add`=".$vku->viewer_id.")";
    $r = mysql_fetch_assoc(query($sql));
    $remindActive = $r['count'] > 0 ? ' (<B>'.$r['count'].'</B>)' : '';

    $page = array('remClient', 'remZayavki', 'remDevice',  'remZp',    'no&p=report',          'remSetup');
    $name = array('Клиенты',   'Заявки',     'Устройства', 'Запчасти', 'Отчёты'.$remindActive, 'Установки');
    $show = array(1,           1,             0,           1,           $vku->admin,           $vku->admin);

    $links = "<DIV id=mainLinks>";
    for ($n = 0; $n < count($page); $n++)
        if ($show[$n] > 0)
            $links .= "<A HREF='".URL."&my_page=".$page[$n]."' class='la".($page[$n] == $sel ? ' sel' : '')."'>".
                "<DIV class=l1></DIV>".
                "<DIV class=l2></DIV>".
                "<DIV class=l3>".$name[$n]."</DIV>".
                "</A>";
    $links .= "</DIV>";
    $html .= $links;
}//end of _mainLinks()

function _rightLinks($p, $data, $d='') {
    $page = false;
    foreach($data as $link) {
        if($d == $link['d']) {
            $page = true;
            break;
        }
    }
    $send =  '<div class="rightLink">';
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

function statistic() {
    $sql = "SELECT
                SUM(`summa`) AS `summa`,
                DATE_FORMAT(`dtime_add`, '%Y-%m-01') AS `dtime`
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
                DATE_FORMAT(`dtime_add`, '%Y-%m-01') AS `dtime`
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
}

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

function report_prihod() {
    return '<div id="report_prihod">'.report_prihod_spisok(currentMonday(), currentSunday()).'</div>';
}

function report_prihod_spisok($day_begin, $day_end, $page=1) {
    $limit = 20;
    $sql = "SELECT
                COUNT(`id`) AS `all`,
                SUM(`summa`) AS `sum`
            FROM `money`
            WHERE `status`=1
              AND `summa`>0
              AND `dtime_add`>='".$day_begin." 00:00:00'
              AND `dtime_add`<='".$day_end." 23:59:59'";
    $r = mysql_fetch_assoc(query($sql));
    if($r['all'] == 0)
        return 'Поступления за указанный период отсутствуют.';
    $all = $r['all'];
    $start = ($page - 1) * $limit;

    $spisok = '';
    if($page == 1)
        $spisok = '<div class="summa">'.
            '<a class="summa_add">Внести произвольную сумму</a>'.
            'Всего <b>'.$all.'</b> платежей на сумму <b>'.$r['sum'].'</b> руб.'.
        '</div>'.
        '<TABLE class="tabSpisok">'.
            '<TR><TH class="sum">Сумма'.
                '<TH>Описание'.
                '<TH class="data">Дата';

    $sql = "SELECT *
            FROM `money`
            WHERE `status`=1
              AND `summa`>0
              AND `dtime_add`>='".$day_begin." 00:00:00'
              AND `dtime_add`<='".$day_end." 23:59:59'
            ORDER BY `dtime_add` ASC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    while($r = mysql_fetch_assoc($q)) {
        $about = $r['prim'];
        if($r['zayav_id'] > 0)
            $about = 'Заявка <A href="'.URL.'&my_page=remZayavkiInfo&id='.$r['zayav_id'].'">№'.$r['zayav_id'].'</A>';
        if($r['zp_id'] > 0) {
            $about = 'Продажа запчасти '.
                '<A href="'.URL.'&my_page=remZp&id='.$r['zp_id'].'">'.
                    $r['zp_id'].
                '</A>';
        }
        $spisok .= '<tr>'.
            '<TD class="sum"><B>'.$r['summa'].'</B>'.
            '<TD>'.$about.
            '<TD class="dtime">'.FullDataTime($r['dtime_add']);
    }
    if($start + $limit < $all)
        $spisok .= '<tr class="ajaxNext" id="report_prihod_next" val="'.($page + 1).'"><td colspan="3"><span>Показать ещё платежи...</span></td></tr>';
    if($page == 1) $spisok .= '</TABLE>';
//    if($start + $limit < $all)
//        $spisok .= '<div class="ajaxNext" id="report_prihod_next" val="'.($page + 1).'"><span>Показать ещё платежи...</span></div>';
    return $spisok;
}//end of report_prihod()

//Условия поиска справа
function report_prihod_right() {
    return '<div class="report_prihod_rl">'.
        '<DIV class="findHead">Период</DIV>'.
        '<div class="cal"><EM class="label">от:</EM><INPUT type="hidden" id="report_prihod_day_begin" value="'.currentMonday().'"></div>'.
        '<div class="cal"><EM class="label">до:</EM><INPUT type="hidden" id="report_prihod_day_end" value="'.currentSunday().'"></div>'.
    '</div>';
}



