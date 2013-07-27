<?php
function _header() {
    global $html, $vku, $WS;
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.
        '<HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.
        '<HEAD>'.
        '<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
        '<TITLE> ���������� 2031819 Hi-tech Service </TITLE>'.
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
                'ws_id:'.$vku->ws_id.','.
                'country_id:'.$vku->country_id.','.
                'city_id:'.$vku->city_id.
            '};'.
            'G.clients = [];'.
            'G.ws = {devs:['.($WS ? $WS->devs : '').']};'.
        '</SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/js/global.js?'.VERSION.'"></SCRIPT>'.
        '<SCRIPT type="text/javascript" src="'.SITE.'/include/G_values.js?'.G_VALUES.'"></SCRIPT>'.
        '<SCRIPT type="text/javascript" src="/include/clients/G_clients_'.WS_ID.'.js?'.VERSION.'"></SCRIPT>'.//todo ��� ��������
        '<SCRIPT type="text/javascript" src="/include/clients/clients.js?'.VERSION.'"></SCRIPT>'.//todo ��� ��������
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
                '<A id=script_style>����� � ������� ('.VERSION.')</A> :: '.
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
              AND `day`=DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d')
              AND `status`=1
              AND (`private`=0 OR `private`=1 AND `viewer_id_add`=".VIEWER_ID.")";
    $r = mysql_fetch_assoc(query($sql));
    define('REMIND_ACTIVE', $r['count'] > 0 ? ' (<B>'.$r['count'].'</B>)' : '');

    $links = array(
        array(
            'name' => '�������',
            'page' => 'remClient',
            'show' => 1
        ),
        array(
            'name' => '������',
            'page' => 'remZayavki',
            'show' => 1
        ),
        array(
            'name' => '����������',
            'page' => 'remDevice',
            'show' => 0
        ),
        array(
            'name' => '��������',
            'page' => 'remZp',
            'show' => 1
        ),
        array(
            'name' => '������'.REMIND_ACTIVE,
            'page' => 'no&p=report',
            'show' => $vku->admin
        ),
        array(
            'name' => '���������',
            'page' => 'remSetup',
            'show' => $vku->admin
        )
    );

    $send = '<DIV id="mainLinks">';
    foreach($links as $l)
        if($l['show']) {
            $s = $l['page'] == $sel ? ' sel' : '';
            // todo ��� ��������
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

function _checkbox($id, $txt, $value=0) {
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
}//end of statistic()

//����������� � ������� ������
function currentMonday() {
    // ����� �������� ��� ������
    $time = time();
    $curDay = date("w", $time);
    if($curDay == 0) $curDay = 7;
    // ���������� ��� � ������������
    $time -= 86400 * ($curDay - 1);
    return strftime('%Y-%m-%d', $time);
}//end of currentMonday()

//����������� � ������� ������
function currentSunday() {
    $time = time();
    $curDay = date("w", $time);
    if($curDay == 0) $curDay = 7;
    $time += 86400 * (7 - $curDay);
    return strftime('%Y-%m-%d', $time);

}//end of currentMonday()

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

function get_zayav_info($arr) {
    if(empty($arr))
        return array();
    $sql = "SELECT id,nomer FROM `zayavki` WHERE `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['id']] = '<A href="'.URL.'&my_page=remZayavkiInfo&id='.$r['id'].'">�'.$r['nomer'].'</a>';
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
            '<b>'.$zpName[$r['name_id']].'</b> ��� '.
            $device[$r['base_device_id']].' '.
            $vendor[$r['base_vendor_id']].' '.
            $model[$r['base_model_id']].
        '</a>';
    return $send;
}//end of get_zp_info()

function get_zayav_status($id=false) {
    $arr = array(
        '1' => array(
            'name' => '������� ����������',
            'color' => 'E8E8FF'
        ),
        '2' => array(
            'name' => '���������!',
            'color' => 'CCFFCC'
        ),
        '3' => array(
            'name' => '��������� �� �������',
            'color' => 'FFDDDD'
        )
    );
    return $id ? $arr[$id] : $arr;
}//end of get_zayav_status()

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
        $arr['client_link'] = '<i>�������� ������</i>';
    if(!isset($arr['zayav_link']))
        $arr['zayav_link'] = '<i>�������� ������</i>';
    if(!isset($arr['zp_link']))
        $arr['zp_link'] = '<i>�������� ��������</i>';
    switch($arr['type']) {
        case 1: return '������ ����� ������ '.$arr['zayav_link'].' ��� ������� '.$arr['client_link'].'.';
        case 2: return '������ ������ �'.$arr['value'].'.';
        case 3: return '���� � ���� ������ ������� '.$arr['client_link'].'.';
        case 4:
            $status = get_zayav_status($arr['value']);
            return '������� ������ ������ '.$arr['zayav_link'].' �� <span style="background-color:#'.$status['color'].'">'.$status['name'].'</span>.';
        case 5: return '������� ���������� �� ����� <b>'.$arr['value'].'</b> ���. ��� ������ '.$arr['zayav_link'].'.';
        case 6: return
            '���� ����� �� ����� <b>'.$arr['value'].'</b> ���. '.
            ($arr['value1'] ? '('.$arr['value1'].')' : '').
            ($arr['zayav_id'] ? ' �� ������ '.$arr['zayav_link'] : '');
        case 7: return '�������������� ������ ������ '.$arr['zayav_link'].'.';
        case 8: return '������ ���������� �� ����� '.$arr['zayav_id'].' ���. � ������ '.$arr['zayav_link'].'.';
        case 9:
            return '������ ����� �� ����� <b>'.$arr['value'].'</b> ���. '.
                ($arr['value1'] ? '('.$arr['value1'].')' : '').
                ($arr['zayav_id'] ? ' � ������ '.$arr['zayav_link'] : '').
                ($arr['zp_id'] ? ' (������� �������� '.$arr['zp_link'].')' : '').
                '.';
        case 10: return '�������������� ������ ������� '.$arr['client_link'].'.';
        case 11: return '������� ����������� ��������. ���������: '.$arr['client_link'].'.';
        case 12: return '��������� �������� � �����: '.$arr['value'].' ���.';
        case 13: return '������� ��������� �������� '.$arr['zp_link'].' �� ������ '.$arr['zayav_link'].'.';
        case 14: return '������ �������� '.$arr['zp_link'].' �� ����� <b>'.$arr['value'].'</b> ���.';
        case 15: return '������� �������� �������� '.$arr['zp_link'].'';
        case 16: return '������� ������� �������� '.$arr['zp_link'].'';
        case 17: return '���������� ������� '.$arr['zp_link'].'';
        case 18: return '���� ������� �������� '.$arr['zp_link'].' � ���������� '.$arr['value'].' ��.';
        case 19:
            return '����������� ����� �� ����� <b>'.$arr['value'].'</b> ���. '.
                ($arr['value1'] ? '('.$arr['value1'].')' : '').
                ($arr['zayav_id'] ? ' � ������ '.$arr['zayav_link'] : '').
                ($arr['zp_id'] ? ' (������� �������� '.$arr['zp_link'].')' : '').
                '.';
        case 20:
            return '������ ����� �������'.
                ($arr['zayav_id'] ? ' ��� ������ '.$arr['zayav_link'] : '').
                ($arr['client_id'] ? ' ��� ������� '.$arr['client_link'] : '').
                '.';
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
            '<div class="findHead">���������</div>'.
            '<input type="hidden" id="report_history_worker" value="0">'.
            '<div class="findHead">��������</div>'.
            '<input type="hidden" id="report_history_action" value="0">'.
        '</div>';
}//end of report_history_right()

function report_history($worker=0, $action=0, $page=1) {
    $limit = 30;
    $cond = "`ws_id`=".WS_ID.($worker > 0 ? ' AND `viewer_id_add`='.$worker : '').
        ($action > 0 ? ' AND `type` IN ('.history_types_group($action).')' : '');
    $sql = "SELECT
                COUNT(`id`) AS `all`
            FROM `history`
            WHERE ".$cond;
    $r = mysql_fetch_assoc(query($sql));
    if($r['all'] == 0)
        return '������� �� ��������� �������� ���.';
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
    if($page == 1)
        $send = '<TABLE cellspacing="8" id="report_history">';
    foreach($history as $r) {
        if($r['client_id'] > 0 && isset($client[$r['client_id']]))
            $r['client_link'] = $client[$r['client_id']];
        if($r['zayav_id'] > 0 && isset($zayav[$r['zayav_id']]))
            $r['zayav_link'] = $zayav[$r['zayav_id']];
        if($r['zp_id'] > 0 && isset($zp[$r['zp_id']]))
            $r['zp_link'] = $zp[$r['zp_id']];
        $send .= '<tr><td class="dtime">'.FullDataTime($r['dtime_add']).'</td>'.
                     '<td>'.$viewer[$r['viewer_id_add']]['link'].' '.history_types($r).
                '</tr>';
    }
    if($start + $limit < $all)
        $send .= '<tr class="ajaxNext" id="report_history_next" val="'.($page + 1).'"><td colspan="2"><span>�����...</span></td></tr>';
    if($page == 1) $send .= '</TABLE>';
    return $send;
}//end of report_history()



function report_remind() {
    $send = '<div id="report_remind">'.
        '<div class="info">'.
            '<A class="opening">�������� ����������</A>'.
            '<div class="text">'.
                '<B>�������</B> - ��� �� ����������� '.
                '���������� ��� ������������ � ����� �������� ��� ��������, ��������, ������ ��������, '.
                '����������� ������������� ������� ����������, ���������� ����� � ��.<BR><BR>'.

                '<B>Ƹ����</B> ������ ���������� �������, ������� ������� ������� � ��������� ����, �� ���� �������. '.
                '�� ���������� ������ ������������ � ������� �������� ������� "������" � ������� "�������" � �������.<BR>'.
                '����� - �������, ��������� ���������� ����� ������ ���.<BR>'.
                '������ - �������, ����� - ��������, � <B>�������</B> - ������������ �������.<BR><BR>'.

                '<B>����� �����</B> ��� �������� ������ ������� ����� �������� ��������� ��� ����������. '.
                '��� ��������, � �������, ����� ����� ����������� � ������ ��� "<I>���������</I>" �� � ��� �� �������. '.
                '����� ������ "<I>��������� � �������� ��������� �����������</I>".<BR><BR>'.

                '��� ���������� �������� ��� ��������� ����������� ����� �������� � ���������. '.
                '��������� ������ ��������� <B>�������</B> �������� ������� �� ������ ���� ��� ������� ��� ������. '.
                '����������� � ������������ ������� �� ����������. <BR><BR>'.

                '��� ��������� ������� <B>������</B> ������� ����� ����� ������ ��� ������.<BR><BR>'.

                '�� ������ "�������" ��������� ��������������� ������ ���� �������� ��� ��������.<BR><BR>'.
            '</div>'.
            '<A class="closing">������</A>'.
        '</div>'.
        '<div id="spisok">'.report_remind_spisok().'</div>'.
    '</div>';
    return $send;
}//end of report_remind()

function report_remind_spisok($page=1) {
    $limit = 20;
    $sql = "SELECT
                COUNT(`id`) AS `all`
            FROM `reminder`
            WHERE `ws_id`=".WS_ID."
              AND `status`=1";
    $r = mysql_fetch_assoc(query($sql));
    if($r['all'] == 0)
        return '������� �� �������.';
    $all = $r['all'];
    $start = ($page - 1) * $limit;

    $sql = "SELECT *
            FROM `reminder`
            WHERE `ws_id`=".WS_ID."
              AND `status`=1
            ORDER BY `day` ASC
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
            $leave = '���������� '.($day_leave * -1) + _end($day_leave * -1, ' ����', ' ���', ' ����');
        elseif($day_leave > 2)
            $leave = '�����'._end($day_leave, '�� ', '��� ').$day_leave._end($day_leave, ' ����', ' ���', ' ����');
        else
            switch($day_leave) {
                case 0: $leave = '�������'; break;
                case 1: $leave = '������'; break;
                case 2: $leave = '�����������'; break;
            }

        if($r['status'] == 0) $color = 'grey';
        elseif($r['status'] == 2) $color = 'green';
        elseif($day_leave > 0) $color = 'blue';
        elseif($day_leave < 0) $color = 'redd';
        else $color = 'yellow';
        // ��������� ������
        $rem_cond = '';
        switch($r['status']) {
            case 2: $rem_cond = "<EM>���������.</EM>"; break;
            case 0: $rem_cond = "<EM>��������.</EM>"; break;
            default:
                $rem_cond = '<EM>��������� '.($day_leave == 0 ? '' : '�� ').'</EM>'.
                    ($day_leave >= 0 && $day_leave < 3 ? $leave : FullData($r['day'], 1)).
                    ($day_leave > 2 || $day_leave < 0 ? '<SPAN>, '.$leave.'</SPAN>' : '');

        }
        $send .= '<div class="unit '.$color.'">'.
            '<div class="txt">'.
                ($r['private'] ? '<u>������.</u> ' : '').
                ($r['client_id'] ? '������ '.$client[$r['client_id']].': ' : '').
                ($r['zayav_id'] ? '������ '.$zayav[$r['zayav_id']].': ' : '').
                '<b>'.$r['txt'].'</b>'.
            '</div>'.
            '<div class="day">'.
                '<div class="action">'.($r['status'] == 1 ? '<A>��������</A> :: ' : '').'<A class="hist_a">�������</A></DIV>'.
                $rem_cond.
                '<div class="hist">'.$r['history'].'</div>'.
            '</div>'.
        '</div>';
    }
    if($start + $limit < $all)
        $send .= '<div class="ajaxNext" id="report_remind_next" val="'.($page + 1).'"><span>�������� ��� �������...</span></div>';

    return $send;
}//end of report_remind_spisok()









//������� ������ ������ ��� �������
function report_prihod_right() {
    return '<div class="report_prihod_rl">'.
        '<div class="findHead">������</div>'.
        '<div class="cal"><EM class="label">��:</EM><INPUT type="hidden" id="report_prihod_day_begin" value="'.currentMonday().'"></div>'.
        '<div class="cal"><EM class="label">��:</EM><INPUT type="hidden" id="report_prihod_day_end" value="'.currentSunday().'"></div>'.
        (ADMIN ? _checkbox('prihodShowDel', '���������� �������� �������') : '').
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
        return '����������� �� ��������� ������ �����������.';
    $all = $r['all'];
    $start = ($page - 1) * $limit;

    $spisok = '';
    if($page == 1)
        $spisok = '<div class="summa">'.
            '<a class="summa_add">������ ������������ �����</a>'.
            '�������� <b>'.$all.'</b> ������'._end($all, '', '�', '��').' �� ����� <b>'.$r['sum'].'</b> ���.'.
            '</div>'.
            '<TABLE class="tabSpisok">'.
            '<TR><TH class="sum">�����'.
            '<TH>��������'.
            '<TH class="data">����'.
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
//        if($r['client_id'] > 0)
//            $client[$r['client_id']] = $r['client_id'];
        if($r['zayav_id'] > 0)
            $zayav[$r['zayav_id']] = $r['zayav_id'];
        if($r['zp_id'] > 0)
            $zp[$r['zp_id']] = $r['zp_id'];
        $money[] = $r;
    }
    $viewer = get_viewers_info($viewer);
    //$client = get_clients_info($client);
    $zayav = get_zayav_info($zayav);
    $zp = get_zp_info($zp);
    foreach($money as $r) {
        $about = $r['prim'];
        if($r['zayav_id'] > 0)
            $about = '������ '.$zayav[$r['zayav_id']];
        if($r['zp_id'] > 0)
            $about = '������� �������� '.$zp[$r['zp_id']];
        $dtimeTitle = '����: '.$viewer[$r['viewer_id_add']]['name'];
        if($r['status'] == 0)
            $dtimeTitle .= "\n".'������: '.$viewer[$r['viewer_id_del']]['name'].
                "\n".FullDataTime($r['dtime_del']);
        $spisok .= '<tr'.($r['status'] == 0 ? ' class="deleted"' : '').'>'.
            '<TD class="sum"><B>'.$r['summa'].'</B>'.
            '<TD>'.$about.
            '<TD class="dtime" title="'.$dtimeTitle.'">'.FullDataTime($r['dtime_add']).
            ($r['status'] == 1 ?
                '<TD class="del" title="������� �����"><div class="img_del" val="'.$r['id'].'">' :
                '<TD class="rest" title="������������ �����"><div class="img_rest" val="'.$r['id'].'">');
    }
    if($start + $limit < $all)
        $spisok .= '<tr class="ajaxNext" id="report_prihod_next" val="'.($page + 1).'"><td colspan="4"><span>�������� ��� �������...</span></td></tr>';
    if($page == 1) $spisok .= '</TABLE>';
    return $spisok;
}//end of report_prihod_spisok()

