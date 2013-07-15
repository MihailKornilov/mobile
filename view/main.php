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
        '<SCRIPT type="text/javascript" src="'.SITE.'/include/G_values.js?'.G_VALUES.'"></SCRIPT>'.
        '<SCRIPT type="text/javascript">'.
            'if(document.domain=="vkmobile")for(var i in VK)if(typeof VK[i]=="function")VK[i]=function(){return false};'.
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
        '</HEAD>'.
        '<BODY>'.
        '<DIV id="frameBody">';
}//end of _header()

function _footer() {
    global $html, $sqlQuery;
    if(ADMIN)
        $html .= '<DIV id=admin>'.
                '<A href="'.URL.'&my_page=superAdmin&pre_page='.$_GET['my_page'].'&pre_id='.$_GET['id'].'">Admin</A> :: '.
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

function _dopLinks($p, $data, $d='') {
    $page = false;
    foreach($data as $link)
        if($d == $link['d']) {
            $page = true;
            break;
        }
    $links = '<div id="dopLinks">';
    foreach($data as $link) {
        if($page)
            $sel = $d == $link['d'] ?  ' sel' : '';
        else
            $sel = isset($link['sel']) ? ' sel' : '';
        $links .= '<a href="'.URL.'&p='.$p.'&d='.$link['d'].'" class="link'.$sel.'">'.$link['name'].'</a>';
    }
    $links .= '</div>';
    return $links;
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

function report_prihod() {
    $sql = "SELECT *
            FROM `money`
            WHERE `status`=1 AND `summa`>0
            ORDER BY `dtime_add` DESC
            LIMIT 20";
    $q = query($sql);
    $spisok = 'Поступления отсутствуют.';
    if(mysql_num_rows($q)) {
        $spisok = '<TABLE cellpadding=0 cellspacing=0 class=tabSpisok>'.
            '<TR><TH class=sum>Сумма'.
                '<TH class=about>Описание'.
                '<TH class=data>Дата';
        while($r = mysql_fetch_assoc($q)) {
            $about = $r['prim'];
            if($r['zayav_id'] > 0)
                $about = 'Заявка <A href="'.URL.'&my_page=remZayavkiInfo&id='.$r['zayav_id'].'">№'.$r['zayav_id'].'</A>';
            if($r['zp_id'] > 0) {
                $about = 'Продажа запчасти '.
                    '<A href="'.URL.'&my_page=remZp&id='.$r['zp_id'].'>'.
                        $r['zp_id'].
                    '</A>';
            }
            $spisok .= '<tr>'.
                '<TD class="sum"><B>'.$r['summa'].'</B>'.
                '<TD class="about">'.$about.
                '<TD class="dtime">'.FullDataTime($r['dtime_add']);
        }
        $spisok .= '</TABLE>';
    }
    return '<div id="report_prihod">'.$spisok.'</div>';
}//end of report_prihod()