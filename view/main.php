<?php
function _vkUserUpdate($uid=VIEWER_ID) {//���������� ������������ �� ��������
    require_once(DOCUMENT_ROOT.'/include/vkapi.class.php');
    $VKAPI = new vkapi($_GET['api_id'], SECRET);
    $res = $VKAPI->api('users.get',array('uids' => $uid, 'fields' => 'photo,sex,country,city'));
    $u = $res['response'][0];
    $u['first_name'] = win1251($u['first_name']);
    $u['last_name'] = win1251($u['last_name']);
    $u['country_id'] = isset($u['country']) ? $u['country'] : 0;
    $u['city_id'] = isset($u['city']) ? $u['city'] : 0;
    $u['menu_left_set'] = 0;

    // ��������� �� ����������
    $app = $VKAPI->api('isAppUser', array('uid'=>$uid));
    $u['app_setup'] = $app['response'];

    // �������� �� � ����� ����
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
        if(isset($_GET['start'])) {// �������������� ��������� ���������� ��������
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
        '<title> ���������� 2031819 Hi-tech Service </title>'.
        '<link href="'.SITE.'/css/global.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
        (SA ? '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/errors.js?'.VERSION.'"></script>' : '').
        '<script type="text/javascript" src="'.SITE.'/js/jquery-1.9.1.min.js"></script>'.
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
        '<script type="text/javascript" src="'.SITE.'/js/G_values.js?'.G_VALUES.'"></script>'.
        '</head>'.
        '<body>'.
        '<div id="frameBody">'.
        '<iframe id="frameHidden" name="frameHidden"></iframe>';
}//end of _header()

function _footer() {
    global $html, $sqlQuery, $sqls;
    if(SA)
        $html .= '<div id="admin">'.
                '<a href="'.URL.'&my_page=superAdmin&pre_page='.@$_GET['my_page'].'&pre_id='.@$_GET['id'].'">Admin</a> :: '.
                //'<a href="https://github.com/MihailKornilov/vkmobile/issues" target="_blank">Issues</a> :: '.
                '<a href="http://vkmobile.reformal.ru" target="_blank">Reformal</a> :: '.
                '<a class="debug_toggle'.(DEBUG ? ' on' : '').'">�'.(DEBUG ? '�' : '').'������� Debug</a> :: '.
                '<a id="cache_clear">������� ��� ('.VERSION.')</a> :: '.
                'sql '.$sqlQuery.' :: '.
                'php '.round(microtime(true) - TIME, 3).' :: '.
                'js <EM></EM>'.
            '</div>'
            .(DEBUG ? $sqls : '');
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

function _remindActiveSet() { //��������� ���������� �������� �����������
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
    define('REMIND_ACTIVE', $count > 0 ? ' (<b>'.$count.'</b>)' : '');
}//end of _remindActiveSet()

function _mainLinks() {
    global $html;
    _remindActiveSet();
    $links = array(
        array(
            'name' => '�������',
            'page' => 'client',
            'show' => 1
        ),
        array(
            'name' => '������',
            'page' => 'zayav',
            'show' => 1
        ),
        array(
            'name' => '��������',
            'page' => 'zp',
            'show' => 1
        ),
        array(
            'name' => '������'.REMIND_ACTIVE,
            'page' => 'report',
            'show' => VIEWER_ADMIN
        ),
        array(
            'name' => '���������',
            'page' => 'setup',
            'show' => VIEWER_ADMIN
        )
    );

    $send = '<div id="mainLinks">';
    foreach($links as $l)
        if($l['show'])
            $send .= '<a href="'.URL.'&p='.$l['page'].'"'.($l['page'] == $_GET['p'] ? 'class="sel"' : '').'>'.$l['name'].'</a>';
    $send .= '</div>';
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
function _dopLinks($p, $data, $d=false, $d1=false) {//�������������� ���� �� ����� ����
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

function GvaluesCreate() {//����������� ����� G_values.js
    $save = 'function SpisokToAss(s){var a=[];for(var n=0;n<s.length;a[s[n].uid]=s[n].title,n++);return a}'.
        'G.status_spisok='.query_selJson("SELECT `id`,`name` FROM `setup_zayavki_status` ORDER BY id").';G.status_ass=SpisokToAss(G.status_spisok);'.
        'G.status_color_ass='.query_ptpJson("SELECT `id`,`bg` FROM setup_zayavki_status").';'.
        'G.color_spisok='.query_selJson("SELECT `id`,`name` FROM setup_color_name ORDER BY name").';G.color_ass=SpisokToAss(G.color_spisok);'.
        'G.fault_spisok='.query_selJson("SELECT `id`,`name` FROM setup_fault ORDER BY sort").';G.fault_ass=SpisokToAss(G.fault_spisok);'.
        'G.zp_name_spisok='.query_selJson("SELECT `id`,`name` FROM setup_zp_name ORDER BY name").';G.zp_name_ass=SpisokToAss(G.zp_name_spisok);'.
        'G.device_status_spisok='.query_selJson("SELECT `id`,`name` FROM setup_device_status ORDER BY sort").';G.device_status_spisok.unshift({uid:0, title:"�� ��������"});G.device_status_ass=SpisokToAss(G.device_status_spisok);'.
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
        1 => '������',
        2 => '�������',
        3 => '�����',
        4 => '������',
        5 => '���',
        6 => '����',
        7 => '����',
        8 => '�������',
        9 => '��������',
        10 => '�������',
        11 => '������',
        12 => '�������'
    );
    return $mon[intval($n)];
}//end of _monthFull
function _monthCut($n) {
    $mon = array(
        1 => '���',
        2 => '���',
        3 => '���',
        4 => '���',
        5 => '���',
        6 => '���',
        7 => '���',
        8 => '���',
        9 => '���',
        10 => '���',
        11 => '���',
        12 => '���'
    );
    return $mon[intval($n)];
}//end of _monthCut
function FullData($value, $noyear=false) {//14 ������ 2010
    $d = explode('-', $value);
    return
        abs($d[2]).' '.
        _monthFull($d[1]).
        (!$noyear || date('Y') != $d[0] ? ' '.$d[0] : '');
}//end of FullData()
function FullDataTime($value, $cut=false) {//14 ������ 2010 � 12:45
    $arr = explode(' ',$value);
    $d = explode('-',$arr[0]);
    $t = explode(':',$arr[1]);
    return
        abs($d[2]).' '.
        ($cut ? _monthCut($d[1]) : _monthFull($d[1])).
        (date('Y') == $d[0] ? '' : ' '.$d[0]).
        ' � '.$t[0].':'.$t[1];
}//end of FullDataTime()


function _vkComment($table, $id=0) {
    $sql = "SELECT *
            FROM `vk_comment`
            WHERE `status`=1
              AND `table_name`='".$table."'
              AND `table_id`=".intval($id)."
            ORDER BY `dtime_add` ASC";
    $count = '������� ���';
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
        $count = '����� '.$count.' �����'._end($count, '��', '��','��');
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
        '<div class=headBlue><div class="count">'.$count.'</div>�������</div>'.
        '<div class="add">'.
            '<textarea>�������� �������...</textarea>'.
            '<div class="vkButton"><button>��������</button></div>'.
        '</div>'.
        $units.
    '</div>';
}//end of _vkComment
function _vkCommentUnit($id, $viewer, $txt, $dtime, $childs=array(), $n=0) {
    return '<div class="cunit" val="'.$id.'">'.
        '<table class="t">'.
            '<tr><td class="ava">'.$viewer['photo'].
                '<td class="i">'.$viewer['link'].
                    ($viewer['id'] == VIEWER_ID || VIEWER_ADMIN ? '<div class="img_del unit_del" title="������� �������"></div>' : '').
                    '<div class="ctxt">'.$txt.'</div>'.
                    '<div class="cdat">'.FullDataTime($dtime, 1).
                        '<SPAN'.($n == 1  && !empty($childs) ? ' class="hide"' : '').'> | '.
                            '<a>'.(empty($childs) ? '��������������' : '����������� ('.count($childs).')').'</a>'.
                        '</SPAN>'.
                    '</div>'.
                    '<div class="cdop'.(empty($childs) ? ' empty' : '').($n == 1 && !empty($childs) ? '' : ' hide').'">'.
                        implode('', $childs).
                        '<div class="cadd">'.
                            '<textarea>��������������...</textarea>'.
                            '<div class="vkButton"><button>��������</button></div>'.
                        '</div>'.
                    '</div>'.
        '</table></div>';
}//end of _vkCommentUnit()
function _vkCommentChild($id, $viewer, $txt, $dtime) {
    return '<div class="child" val="'.$id.'">'.
        '<table class="t">'.
            '<tr><td class="dava">'.$viewer['photo'].
                '<td class="di">'.$viewer['link'].
                    ($viewer['id'] == VIEWER_ID || VIEWER_ADMIN ? '<div class="img_del child_del" title="������� �����������"></div>' : '').
                    '<div class="dtxt">'.$txt.'</div>'.
                    '<div class="ddat">'.FullDataTime($dtime, 1).'</div>'.
        '</table></div>';
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
        '<script type="text/javascript">'.
            'var statPrihod = '.json_encode($prihod).';'.
            'var statRashod = '.json_encode($rashod).';'.
        '</script>'.
        '<script type="text/javascript" src="'.SITE.'/js/statistic.js"></script>';
}//end of statistic()

function _curMonday() { //����������� � ������� ������
    // ����� �������� ��� ������
    $time = time();
    $curDay = date("w", $time);
    if($curDay == 0) $curDay = 7;
    // ���������� ��� � ������������
    $time -= 86400 * ($curDay - 1);
    return strftime('%Y-%m-%d', $time);
}//end of _curMonday()
function _curSunday() { //����������� � ������� ������
    $time = time();
    $curDay = date("w", $time);
    if($curDay == 0) $curDay = 7;
    $time += 86400 * (7 - $curDay);
    return strftime('%Y-%m-%d', $time);

}//end of _curSunday()

function _engRusChar($word) { //������� �������� ��������� � ����������� �� �������
    $char = array(
        'q' => '�',
        'w' => '�',
        'e' => '�',
        'r' => '�',
        't' => '�',
        'y' => '�',
        'u' => '�',
        'i' => '�',
        'o' => '�',
        'p' => '�',
        '[' => '�',
        ']' => '�',
        'a' => '�',
        's' => '�',
        'd' => '�',
        'f' => '�',
        'g' => '�',
        'h' => '�',
        'j' => '�',
        'k' => '�',
        'l' => '�',
        ';' => '�',
        "'" => '�',
        'z' => '�',
        'x' => '�',
        'c' => '�',
        'v' => '�',
        'b' => '�',
        'n' => '�',
        'm' => '�',
        ',' => '�',
        '.' => '�'
    );
    $send = '';
    for($n = 0; $n < strlen($word); $n++)
        if(isset($char[$word[$n]]))
            $send .= $char[$word[$n]];
    return $send;
}

function _viewerName($id=VIEWER_ID, $link=false) { //todo �� ������� �������� ����
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

function _clientsLink($arr) {
    if(empty($arr))
        return array();
    $id = false;
    if(!is_array($arr)) {
        $id = $arr;
        $arr = array($arr);
    }
    $sql = "SELECT `id`,`fio` FROM `client` WHERE `ws_id`=".WS_ID." AND `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['id']] = '<a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>';
    if($id)
        return $send[$id];
    return $send;
}//end of _clientsLink()

function _zpLink($arr) {
    if(empty($arr))
        return array();
    $sql = "SELECT * FROM `zp_catalog` WHERE `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['id']] = '<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'">'.
            '<b>'._zpName($r['name_id']).'</b> ��� '.
            _deviceName($r['base_device_id'], 1).
            _vendorName($r['base_vendor_id']).
            _modelName($r['base_model_id']).
        '</a>';
    return $send;
}//end of _zpLink()

function _zayavStatus($id=false) {
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
}//end of _zayavStatus()
function _zayavNomerLink($arr, $noHint=false) { //����� ������� ������ � ������������ ����������� �������������� ���������� ��� ���������
    if(empty($arr))
        return true;
    if(!is_array($arr)) {
        $zayav_id = $arr;
        $arr = array($zayav_id);
    }
    if(isset($zayav_id) && defined('ZAYAV_NOMER_'.$zayav_id))
        return constant('ZAYAV_NOMER_'.$zayav_id);
    foreach($arr as $n => $id)
        if(defined('ZAYAV_NOMER_'.$id))
            unset($arr[$n]);
    if(empty($arr))
        return true;
    $sql = "SELECT `id`,`nomer` FROM `zayavki` WHERE `ws_id`=".WS_ID." AND `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        define('ZAYAV_NOMER_'.$r['id'],
            '<a href="'.URL.'&p=zayav&d=info&id='.$r['id'].'"'.(!$noHint ? ' class="zayav_link" val="'.$r['id'].'"' : '').'>'.
                '�'.$r['nomer'].
                '<div class="tooltip empty"></div>'.
            '</a>');
    foreach($arr as $id)
        if(!defined('ZAYAV_NOMER_'.$id))
            define('ZAYAV_NOMER_'.$id, false);
    return isset($zayav_id) ? constant('ZAYAV_NOMER_'.$zayav_id) : true;
}//end of _zayavNomerLink()
function _zayavDeviveBaseIds($type='device', $client_id=0) { //������ id ���������, �������������� � �������, ������� ������������ � �������
    if($type == 'vendor' && !ZAYAV_BASE_DEVICE || $type == 'model' && !ZAYAV_BASE_VENDOR)
        return '';
    $key = 'vkmobile_zayav_base_'.$type.WS_ID;
    if(!$client_id)
        $cache = xcache_get($key);
    if(empty($cache)) {
        $ids = array();
        $sql = "SELECT DISTINCT `base_".$type."_id` AS `id`
            FROM `zayavki`
            WHERE `base_".$type."_id`>0
              AND `zayav_status`>0
              ".($client_id ? " AND `client_id`=".$client_id : '')."
              AND `ws_id`=".WS_ID;
        $q = query($sql);
        while($r = mysql_fetch_assoc($q))
            $ids[] = $r['id'];
        $cache = implode(',', $ids);
        if(!$client_id)
            xcache_set($key, $cache, 86400);
    }
    define('ZAYAV_BASE_'.strtoupper($type), !empty($cache));
    if(!ZAYAV_BASE_DEVICE)
        define('ZAYAV_BASE_VENDOR', false);
    return $cache;
}//end of _zayavDeviveBaseIds()

function _imageResize($x_cur, $y_cur, $x_new, $y_new) { // ��������� ������� �����������
    $x = $x_new;
    $y = $y_new;
    // ���� ������ ������ ��� ����� ������
    if ($x_cur >= $y_cur) {
        if ($x > $x_cur) { $x = $x_cur; } // ���� ����� ������ ������, ��� ��������, �� X ������� ��������
        $y = round($y_cur / $x_cur * $x);
        if ($y > $y_new) { // ���� ����� ������ � ����� �������� ������ ��������, �� ������������� �� Y
            $y = $y_new;
            $x = round($x_cur / $y_cur * $y);
        }
    }

    // ���� ������ ������ ������
    if ($y_cur > $x_cur) {
        if ($y > $y_cur) { $y = $y_cur; } // ���� ����� ������ ������, ��� ��������, �� Y ������� ��������
        $x = round($x_cur / $y_cur * $y);
        if ($x > $x_new) { // ���� ����� ������ � ����� �������� ������ ��������, �� ������������� �� X
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

function unescape($str){
    $escape_chars = '0410 0430 0411 0431 0412 0432 0413 0433 0490 0491 0414 0434 0415 0435 0401 0451 0404 0454 '.
                    '0416 0436 0417 0437 0418 0438 0406 0456 0419 0439 041A 043A 041B 043B 041C 043C 041D 043D '.
                    '041E 043E 041F 043F 0420 0440 0421 0441 0422 0442 0423 0443 0424 0444 0425 0445 0426 0446 '.
                    '0427 0447 0428 0448 0429 0449 042A 044A 042B 044B 042C 044C 042D 044D 042E 044E 042F 044F';
    $russian_chars = '� � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � � �';
    $e = explode(' ', $escape_chars);
    $r = explode(' ', $russian_chars);
    $rus_array = explode('%u', $str);
    $new_word = str_replace($e, $r, $rus_array);
    $new_word = str_replace('%20', ' ', $new_word);
    return implode($new_word);
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
function _zpAvaiSet($zp_id) { // ���������� ���������� ������� ��������
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
        define('DEV_STATUS_0', '�� ��������');
        define('DEV_STATUS_LOADED', true);
    }
    return constant('DEV_STATUS_'.$status_id);
}//end of _devStatus()


// ---===! ws_create !===--- ������ �������� ����������

function ws_create_info() {
    return
    '<div class="ws-create-info">'.
        '<div class="txt">'.
            '<h3>����� ���������� � ���������� Hi-Tech Service!</h3>'.
            '������ ���������� �������� ���������� ��� ����� ������� ��������� ���������, '.
            '���, ���������, ����������� � ������ ���������������� ���������� � ������� �������.<br />'.
            '<br />'.
            '<U>��� ������ ��������� �����:</U><br />'.
            '- ����� ���������� ���� (�������, �������� ���������� � ��������, ������� ����� ���������� � ������);<br />'.
            '- ����� ���� ���������, �������� � ������;<br />'.
            '- ��������� ������ �� ����������� ������;<br />'.
            '- ��������� ������� � ����� ���� �������� �������;<br />'.
            '- ��������, �������� ���������� � ���������.<br />'.
            '<br />'.
            '��� ����, ����� ������ ������������ �����������, ���������� ������� ���� ����������.'.
        '</div>'.
		'<div class="vkButton"><button onclick="location.href=\''.URL.'&p=wscreate&d=step1\'">���������� � �������� ����������</button></div>'.
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
            '��� ������ ���������� ������� �������� ����� ���������� � �����, � ������� �� ����������.<br />'.
            '����������� � ��������� ��������� ����� ����� �������� ��� �������� �������.'.
        '</div>'.
        '<div class="headName">�������� ����������</div>'.
        '<TABLE class="tab">'.
            '<TR><TD class="label">�������� �����������:<TD><INPUT type="text" id="org_name" maxlength="100">'.
            '<TR><TD class="label">������:<TD><INPUT type="hidden" id="countries" value="'.VIEWER_COUNTRY_ID.'">'.
            '<TR><TD class="label">�����:<TD><INPUT type="hidden" id="cities" value="0">'.
            '<TR><TD class="label">������� �������������:<TD><b>'.VIEWER_NAME.'</b>'.
            '<TR><TD class="label top">��������� ���������,<br />�������� �������<br />�� �����������:<TD id="devs">'.$checkDevs.
        '</TABLE>'.

        '<div class="vkButton"><button>������</button></div>'.
        '<div class="vkCancel"><button>������</button></div>'.
        '<script type="text/javascript" src="'.SITE.'/js/ws_create_step1.js?'.VERSION.'"></script>'.
    '</div>';
}//end of ws_create_step1()






// ---===! client !===--- ������ ��������

function clientFilter($v) {
    if(!preg_match(REGEXP_WORDFIND, win1251($v['fast'])))
        $v['fast'] = '';
    if(!preg_match(REGEXP_BOOL, $v['dolg']))
        $v['dolg'] = 0;
    if(!preg_match(REGEXP_BOOL, $v['active']))
        $v['active'] = 0;
    $filter = array(
        'fast' => htmlspecialchars(trim($v['fast'])),
        'dolg' => intval($v['dolg']),
        'active' => intval($v['active'])
    );
    return $filter;
}//end of clientFilter()
function client_data($page=1, $filter=array()) {
    $cond = "`ws_id`=".WS_ID;
    $reg = '';
    $regEngRus = '';
    if(!empty($filter['fast'])) {
        $fast = win1251($filter['fast']);
        $engRus = _engRusChar($fast);
        $cond .= " AND (`fio` LIKE '%".$fast."%'
                     OR `telefon` LIKE '%".$fast."%'
                     ".($engRus ?
                           "OR `fio` LIKE '%".$engRus."%'
                            OR `telefon` LIKE '%".$engRus."%'"
                        : '')."
                     )";
        $reg = '/('.$fast.')/i';
        if($engRus)
            $regEngRus = '/('.$engRus.')/i';
    } else {
        if(isset($filter['dolg']) && $filter['dolg'] == 1)
            $cond .= " AND `balans`<0";
        if(isset($filter['active']) && $filter['active'] == 1) {
            $sql = "SELECT DISTINCT `client_id`
                FROM `zayavki`
                WHERE `ws_id`=".WS_ID."
                  AND `zayav_status`=1";
            $q = query($sql);
            $ids = array();
            while($r = mysql_fetch_assoc($q))
                $ids[] = $r['client_id'];
            $cond .= " AND `id` IN (".(empty($ids) ? 0 : implode(',', $ids)).")";
        }
    }
    $send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `client` WHERE ".$cond." LIMIT 1");
    if($send['all'] == 0) {
        $send['spisok'] = '<div class="_empty">�������� �� �������.</div>';
        return $send;
    }
    $limit = 20;
    $start = ($page - 1) * $limit;
    $spisok = array();
    $sql = "SELECT *
            FROM `client`
            WHERE ".$cond."
            ORDER BY `id` DESC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    while($r = mysql_fetch_assoc($q)) {
        if(!empty($filter['fast'])) {
            if(preg_match($reg, $r['fio']))
                $r['fio'] = preg_replace($reg, '<em>\\1</em>', $r['fio'], 1);
            if(preg_match($reg, $r['telefon']))
                $r['telefon'] = preg_replace($reg, '<em>\\1</em>', $r['telefon'], 1);
            if($regEngRus && preg_match($regEngRus, $r['fio']))
                $r['fio'] = preg_replace($regEngRus, '<em>\\1</em>', $r['fio'], 1);
            if($regEngRus && preg_match($regEngRus, $r['telefon']))
                $r['telefon'] = preg_replace($regEngRus, '<em>\\1</em>', $r['telefon'], 1);
        }
        $spisok[$r['id']] = $r;
    }

    $sql = "SELECT
                `client_id` AS `id`,
                COUNT(`id`) AS `count`
            FROM `zayavki`
            WHERE `ws_id`=".WS_ID."
              AND `zayav_status`>0
              AND `client_id` IN (".implode(',', array_keys($spisok)).")
            GROUP BY `client_id`";
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $spisok[$r['id']]['zayav_count'] = $r['count'];
    $send['spisok'] = '';
    foreach($spisok as $r)
        $send['spisok'] .= '<div class="unit">'.
            ($r['balans'] ? '<div class="balans">������: <b style=color:#'.($r['balans'] < 0 ? 'A00' : '090').'>'.$r['balans'].'</b></div>' : '').
            '<table>'.
               '<tr><td class="label">���:<td><a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>'.
                ($r['telefon'] ? '<tr><td class="label">�������:<td>'.$r['telefon'] : '').
                (isset($r['zayav_count']) ? '<tr><td class="label">������:<td>'.$r['zayav_count'] : '').
            '</table>'.
         '</div>';
    if($start + $limit < $send['all']) {
        $c = $send['all'] - $start - $limit;
        $c = $c > $limit ? $limit : $c;
        $send['spisok'] .= '<div class="ajaxNext" val="'.($page + 1).'"><span>�������� ��� '.$c.' ������'._end($c, '�', '�', '��').'</span></div>';
    }
    return $send;
}//end of client_data()
function client_list($data) {
    return '<div id="client">'.
        '<div id="find"></div>'.
        '<div class="result">'.client_count($data['all']).'</div>'.
        '<table class="tabLR">'.
            '<tr><td class="left">'.$data['spisok'].
                '<td class="right">'.
                    '<div id="buttonCreate"><a>����� ������</a></div>'.
                    '<div class="filter">'.
                       _checkbox('dolg', '��������').
                       _checkbox('active', '� ��������� ��������').
                    '</div>'.
          '</table>'.
        '</div>';
}//end of client_list()
function client_count($count, $dolg=0) {
    if($dolg)
        $dolg = abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE `balans`<0 LIMIT 1"));
    return ($count > 0 ?
            '������'._end($count, ' ', '� ').$count.' ������'._end($count, '', '�', '��').
            ($dolg ? '<em>(����� ����� ����� = '.$dolg.' ���.)</em>' : '')
            :
            '�������� �� �������');
}//end of client_count()

function client_info($client_id) {
    $sql = "SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND `id`=".$client_id;
    if(!$client = mysql_fetch_assoc(query($sql)))
        return '������� �� ����������';

    $zayavData = zayav_data(1, array('client'=>$client_id), 10);
    $commCount = query_value("SELECT COUNT(`id`)
                              FROM `vk_comment`
                              WHERE `status`=1
                                AND `parent_id`=0
                                AND `table_name`='client'
                                AND `table_id`=".$client_id);

    $moneyCount = query_value("SELECT COUNT(`id`)
                               FROM `money`
                               WHERE `ws_id`=".WS_ID."
                                 AND `status`=1
                                 AND `client_id`=".$client_id);
    $money = '<div class="_empty">�������� ���.</div>';
    if($moneyCount) {
        $money = '<table class="_spisok _money">'.
            '<tr><th class="sum">�����'.
            '<th>��������'.
            '<th class="data">����';
        $sql = "SELECT *
                FROM `money`
                WHERE `ws_id`=".WS_ID."
                  AND `status`=1
                  AND `client_id`=".$client_id;
        $q = query($sql);
        $moneyArr = array();
        $zayav_ids = array();
        while($r = mysql_fetch_assoc($q)) {
            $moneyArr[] = $r;
            if($r['zayav_id'])
                $zayav_ids[$r['zayav_id']] = $r['zayav_id'];
        }
        _zayavNomerLink($zayav_ids);
        foreach($moneyArr as $r) {
            $about = '';
            if($r['zayav_id'] > 0)
                $about .= '������ '._zayavNomerLink($r['zayav_id']).'. ';
            if($r['zp_id'] > 0)
                $about = '������� �������� '.$r['zp_id'].'. ';
            $about .= $r['prim'];
            $money .= '<tr><td class="sum"><b>'.$r['summa'].'</b>'.
                          '<td>'.$about.
                          '<td class="dtime" title="���: '._viewerName($r['viewer_id_add']).'">'.FullDataTime($r['dtime_add']);
        }
        $money .= '</table>';
    }

    $remindData = remind_data(1, array('client'=>$client_id));

    return '<script type="text/javascript">'.
        'G.clientInfo = {'.
            'id:'.$client_id.','.
            'fio:"'.$client['fio'].'"'.
        '};'.
        'G.device_ids = ['._zayavDeviveBaseIds('device', $client_id).'];'.
        'G.vendor_ids = ['._zayavDeviveBaseIds('vendor', $client_id).'];'.
        'G.model_ids = ['._zayavDeviveBaseIds('model', $client_id).'];'.
        '</script>'.
        '<div id="clientInfo">'.
            '<table class="tabLR">'.
                '<tr><td class="left">'.
                    '<div class="fio">'.$client['fio'].'</div>'.
                    '<div class="cinf">'.
                        '<table style="border-spacing:2px">'.
                            '<tr><td class="label">�������:  <td class="telefon">'.$client['telefon'].'</TD>'.
                            '<tr><td class="label">������:   <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.$client['balans'].'</b>'.
                        '</table>'.
                        '<div class="dtime">������� ��� '._viewerName($client['viewer_id_add']).' '.FullData($client['dtime_add'], 1).'</div>'.
                    '</div>'.
                    '<div id="dopLinks">'.
                        '<a class="link sel" val="zayav">������'.($zayavData['all'] ? ' ('.$zayavData['all'].')' : '').'</a>'.
                        '<a class="link" val="money">�������'.($moneyCount ? ' ('.$moneyCount.')' : '').'</a>'.
                        '<a class="link" val="remind">�������'.(!empty($remindData) ? ' ('.$remindData['all'].')' : '').'</a>'.
                        '<a class="link" val="comm">�������'.($commCount ? ' ('.$commCount.')' : '').'</a>'.
                    '</div>'.
                    '<div id="zayav_spisok">'.zayav_spisok($zayavData).'</div>'.
                    '<div id="money_spisok">'.$money.'</div>'.
                    '<div id="remind_spisok">'.(!empty($remindData) ? report_remind_spisok($remindData) : '<div class="_empty">������� ���.</div>').'</div>'.
                    '<div id="comments">'._vkComment('client', $client_id).'</div>'.
                '<td class="right">'.
                    '<div class="rightLinks">'.
                        '<a class="sel">����������</a>'.
                        '<a class="cedit">�������������</a>'.
                        '<a href="'.URL.'&p=zayav&d=add&back=client&id='.$client_id.'">����� ������</a>'.
                        '<a class="remind_add">����� �������</a>'.
                    '</div>'.
                    '<div id="zayav_filter">'.
                        '<div id="zayav_result">'.zayav_count($zayavData['all'], 0).'</div>'.
                        '<div class="findHead">������ ������</div><div id="zayav_status"></div>'.
                        '<div class="findHead">����������</div><div id="dev"></div>'.
                    '</div>'.
            '</table>'.
        '</div>';
}//end of client_info()
function clientBalansUpdate($client_id) {//���������� ������� �������
    $prihod = query_value("SELECT SUM(`summa`) FROM `money` WHERE `status`=1 AND `client_id`=".$client_id." AND `summa`>0");
    $acc = query_value("SELECT SUM(`summa`) FROM `accrual` WHERE `status`=1 AND `client_id`=".$client_id);
    $balans = $prihod - $acc;
    query("UPDATE `client` SET `balans`=".$balans." WHERE `id`=".$client_id);
    return $balans;
}//end of clientBalansUpdate()





// ---===! zayav !===--- ������ ������

function zayav_add() {
    $sql = "SELECT `id`,`name` FROM `setup_fault` ORDER BY SORT";
    $q = query($sql);
    $fault = '<table>';
    $k = 0;
    while($r = mysql_fetch_assoc($q))
        $fault .= (++$k%2 ? '<tr>' : '').'<td>'._checkbox('f_'.$r['id'], $r['name']);
    $fault .= '</table>';

    $client_id = empty($_GET['id']) ? 0 : intval($_GET['id']);

    switch(@$_GET['back']) {
        case 'client': $back = 'client'.($client_id > 0 ? '&d=info&id='.$client_id : ''); break;
        default: $back = 'zayav';
    }
    return '<div id="zayavAdd">'.
        '<div class="headName">�������� ����� ������</div>'.
        '<table style="border-spacing:8px">'.
            '<tr><td class="label">������:        <td><INPUT TYPE="hidden" id="client_id" value="'.$client_id.'" />'.
            '<tr><td class="label top">����������:<td><table><td id="dev"><td id="device_image"></table>'.
            '<tr><td class="label top">��������������� ����������<br />����� �������� ������:<td><INPUT type="hidden" id="place" />'.
            '<tr><td class="label">IMEI:          <td><INPUT type="text" id="imei" maxlength="20" />'.
            '<tr><td class="label">�������� �����:<td><INPUT type="text" id="serial" maxlength="30" />'.
            '<tr><td class="label">����:          <td><INPUT TYPE="hidden" id="color_id" value="0" />'.
            '<tr><td class="label top">�������������: <td id="fault">'.$fault.
            '<tr><td class="label top">�������:       <td><textarea id="comm"></textarea>'.
            '<tr><td class="label">�������� �����������:<td>'._checkbox('reminder').
        '</table>'.

        '<table id="reminder_tab">'.
            '<tr><td class="label">����������: <td><INPUT TYPE="text" id="reminder_txt" />'.
            '<tr><td class="label">����:       <td><INPUT TYPE="hidden" id="reminder_day" />'.
        '</table>'.

        '<div class="vkButton"><button>������</button></div>'.
        '<div class="vkCancel" val="'.$back.'"><button>������</button></div>'.
    '</div>';
}//end of zayav_add()

function zayavFilter($v) {
    if(empty($v['status']) || !preg_match(REGEXP_NUMERIC, $v['status']))
        $v['status'] = 0;
    if(empty($v['zpzakaz']) || !preg_match(REGEXP_NUMERIC, $v['zpzakaz']))
        $v['zpzakaz'] = 0;
    if(empty($v['device']) || !preg_match(REGEXP_NUMERIC, $v['device']))
        $v['device'] = 0;
    if($v['device'] == 0 || !preg_match(REGEXP_NUMERIC, $v['vendor']))
        $v['vendor'] = 0;
    if($v['device'] == 0 || !preg_match(REGEXP_NUMERIC, $v['model']))
        $v['model'] = 0;
    if(empty($v['devstatus']) || !preg_match(REGEXP_NUMERIC, $v['devstatus']) && $v['devstatus'] != -1)
        $v['devstatus'] = 0;
    if(empty($v['client']) || !preg_match(REGEXP_NUMERIC, $v['client']))
        $v['client'] = 0;

    $filter = array();
    $filter['find'] = htmlspecialchars(trim(@$v['find']));
    switch(@$v['sort']) {
        case '2': $filter['sort'] = 'zayav_status_dtime'; break;
        default: $filter['sort'] = 'dtime_add';
    }
    $filter['desc'] = intval(@$v['desc']) == 1 ? 'ASC' : 'DESC';
    $filter['status'] = intval($v['status']);
    $filter['zpzakaz'] = intval($v['zpzakaz']);
    $filter['device'] = intval($v['device']);
    $filter['vendor'] = intval($v['vendor']);
    $filter['model'] = intval($v['model']);
    if(isset($v['place']))
        $filter['place'] = win1251(urldecode(htmlspecialchars(trim($v['place']))));
    $filter['devstatus'] = $v['devstatus'];
    if($v['client'] > 0)
        $filter['client'] = intval($v['client']);
    return $filter;
}//end of zayavFilter()
function zayav_data($page=1, $filter=array(), $limit=20) {
    $cond = "`ws_id`=".WS_ID." AND `zayav_status`>0";

    if(empty($filter['sort']))
        $filter['sort'] = 'dtime_add';
    if(empty($filter['desc']))
        $filter['desc'] = 'DESC';
    if(!empty($filter['find'])) {
        $cond .= " AND `find` LIKE '%".$filter['find']."%'";
        if($page ==1 && preg_match(REGEXP_NUMERIC, $filter['find']))
            $nomer = intval($filter['find']);
        $reg = '/('.$filter['find'].')/i';
    } else {
        if(isset($filter['status']) && $filter['status'] > 0)
            $cond .= " AND `zayav_status`=".$filter['status'];
        if(isset($filter['zpzakaz']) && $filter['zpzakaz'] > 0) {
            $sql = "SELECT `zayav_id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID;
            $q = query($sql);
            $ids[0] = 0;
            while($r = mysql_fetch_assoc($q))
                $ids[$r['zayav_id']] = $r['zayav_id'];
            $cond .= " AND `id` ".($filter['zpzakaz'] == 2 ? 'NOT' : '')." IN (".implode(',', $ids).")";
        }
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
        if(isset($filter['client']) && $filter['client'] > 0)
            $cond .= " AND `client_id`=".$filter['client'];
    }
    $zayav = array();
    $client = array();
    $images = array();

    $send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayavki` WHERE ".$cond." LIMIT 1");

    if(isset($nomer)) {
        $sql = "SELECT * FROM `zayavki` WHERE `nomer`=".$nomer." AND `zayav_status`>0 LIMIT 1";
        if($r = mysql_fetch_assoc(query($sql))) {
            $send['all']++;
            $limit--;
            $r['nomer_find'] = 1;
            $zayav[$r['id']] = $r;
            $client[$r['client_id']] = $r['client_id'];
            $images['zayav'.$r['id']] = '"zayav'.$r['id'].'"';
            if($r['base_model_id'] > 0)
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
        if($r['base_model_id'] > 0)
            $images['dev'.$r['base_model_id']] = '"dev'.$r['base_model_id'].'"';
    }

    if(empty($filter['client']))
        $client = _clientsLink($client);
    $status = _zayavStatus();

    $sql = "SELECT `owner`,`link` FROM `images` WHERE `status`=1 AND `sort`=0 AND `owner` IN (".implode(',', $images).")";
    $q = query($sql);
    $imgLinks = array();
    while($r = mysql_fetch_assoc($q))
        $imgLinks[$r['owner']] = $r['link'].'-small.jpg';
    unset($images);

    $sql = "SELECT `zayav_id`,`zp_id` FROM `zp_zakaz` WHERE `zayav_id` IN (".implode(',', array_keys($zayav)).")";
    $q = query($sql);
    $zp = array();
    $zpZakaz = array();
    while($r = mysql_fetch_assoc($q)) {
        $zp[$r['zp_id']] = $r['zp_id'];
        $zpZakaz[$r['zayav_id']][] = $r['zp_id'];
    }
    if(!empty($zp)) {
        $sql = "SELECT `id`,`name_id` FROM `zp_catalog` WHERE `id` IN (".implode(',', $zp).")";
        $q = query($sql);
        while($r = mysql_fetch_assoc($q))
            $zp[$r['id']] = $r['name_id'];
        foreach($zpZakaz as $id => $zz)
            foreach($zz as $i => $zpId)
                $zpZakaz[$id][$i] = _zpName($zp[$zpId]);
    }

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

    foreach($zayav as $id => $r) {
        $img = '/img/nofoto-small.gif';
        if(isset($imgLinks['zayav'.$id]))
            $img = $imgLinks['zayav'.$id];
        elseif(isset($imgLinks['dev'.$r['base_model_id']]))
            $img = $imgLinks['dev'.$r['base_model_id']];
        $unit = array(
            'status_color' => $status[$r['zayav_status']]['color'],
            'nomer' => $r['nomer'],
            'nomer_find' => isset($r['nomer_find']),
            'device' => _deviceName($r['base_device_id']),
            'vendor' => _vendorName($r['base_vendor_id']),
            'model' => _modelName($r['base_model_id']),
            'dtime' => FullData($r['dtime_add'], 1),
            'img' => $img,
            'article' => isset($articles[$id]) ? $articles[$id] : ''
        );
        if(empty($filter['client']))
            $unit['client'] = $client[$r['client_id']];
        if(!empty($filter['find'])) {
            if(preg_match($reg, $unit['model']))
                $unit['model'] = preg_replace($reg, "<em>\\1</em>", $unit['model'], 1);
            if(preg_match($reg, $r['imei']))
                $unit['imei'] = preg_replace($reg, "<em>\\1</em>", $r['imei'], 1);
            if(preg_match($reg, $r['serial']))
                $unit['serial'] = preg_replace($reg, "<em>\\1</em>", $r['serial'], 1);
        }
        if(isset($zpZakaz[$id]))
            $unit['zakaz'] = implode(', ', $zpZakaz[$id]);
        $send['spisok'][$id] = $unit;
    }
    $send['limit'] = $limit;
    if($start + $limit < $send['all'])
        $send['next'] = $page + 1;
    return $send;
}//end of zayav_data()
function zayav_count($count, $filter_break_show=true) {
    return
        ($filter_break_show ? '<a id="filter_break">�������� ������� ������</a>' : '').
        ($count > 0 ?
            '�������'._end($count, '�', '�').' '.$count.' ����'._end($count, '��', '��', '��')
            :
            '������ �� �������');
}//end of zayav_count()
function zayav_list($data, $values) {
    $place_other = array();
    $sql = "SELECT DISTINCT `device_place_other` AS `other`
            FROM `zayavki`
            WHERE LENGTH(`device_place_other`)>0
              AND `zayav_status`>0
              AND `ws_id`=".WS_ID;
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $place_other[] = '"'.$r['other'].'"';

    return '<div id="zayav">'.
        '<div class="result">'.zayav_count($data['all']).'</div>'.
        '<table class="tabLR">'.
            '<tr><td id="spisok">'.zayav_spisok($data).
                '<td class="right">'.
                    '<div id="buttonCreate"><a HREF="'.URL.'&p=zayav&d=add&back=zayav">����� ������</a></div>'.
                    '<div id="find"></div>'.
                    '<div class="findHead">�������</div>'.
                    '<INPUT TYPE="hidden" id="sort" value="'.$values['sort'].'">'.
                    _checkbox('desc', '�������� �������', $values['desc']).
                    '<div class="condLost'.(!empty($values['find']) ? ' hide' : '').'">'.
                        '<div class="findHead">������ ������</div><div id="status"></div>'.
                        '<div class="findHead">�������� ��������</div><INPUT type="hidden" id="zpzakaz" value="'.$values['zpzakaz'].'" />'.
                        '<div class="findHead">����������</div><div id="dev"></div>'.
                        '<div class="findHead">���������� ����������</div><INPUT TYPE="hidden" id="device_place" value="'.$values['place'].'">'.
                        '<div class="findHead">��������� ����������</div><INPUT TYPE="hidden" id="devstatus" value="'.$values['devstatus'].'">'.
                    '</div>'.
        '</table>'.
        '<script type="text/javascript">'.
            'G.device_ids = ['._zayavDeviveBaseIds().'];'.
            'G.vendor_ids = ['._zayavDeviveBaseIds('vendor').'];'.
            'G.model_ids = ['._zayavDeviveBaseIds('model').'];'.
            'G.place_other = ['.implode(',', $place_other).'];'.
            'G.zayav_find = "'.unescape($values['find']).'";'.
            'G.zayav_status = '.$values['status'].';'.
            'G.zayav_device = '.$values['device'].';'.
            'G.zayav_vendor = '.$values['vendor'].';'.
            'G.zayav_model = '.$values['model'].';'.
        '</script>'.
    '</div>';
}//end of zayav_list()
function zayav_spisok($data) {
    if(!isset($data['spisok']))
        return '<div class="_empty">������ �� �������.</div>';
    $send = '';
    foreach($data['spisok'] as $id => $sp) {
        $send .= '<div class="zayav_unit" style="background-color:#'.$sp['status_color'].'" val="'.$id.'">'.
            '<table width="100%">'.
                '<tr><td valign=top>'.
                        '<h2'.($sp['nomer_find'] ? ' class="finded"' : '').'>#'.$sp['nomer'].'</h2>'.
                        '<a class="name">'.$sp['device'].' <b>'.$sp['vendor'].' '.$sp['model'].'</b></a>'.
                        '<table style="border-spacing:2px">'.
                            (isset($sp['client']) ? '<tr><td class="label">������:<td>'.$sp['client'] : '').
                            '<tr><td class="label">���� ������:<td>'.$sp['dtime'].
                            (isset($sp['imei']) ? '<tr><td class="label">IMEI:<td>'.$sp['imei'] : '').
                            (isset($sp['serial']) ? '<tr><td class="label">�������� �����:<td>'.$sp['serial'] : '').
                            (isset($sp['zakaz']) ? '<tr><td class="label">�������� �/�:<td class="zz">'.$sp['zakaz'] : '').
                        '</table>'.
                    '<td class="image"><IMG src="'.$sp['img'].'" />'.
            '</table>'.
            '<input type="hidden" class="msg" value="'.htmlspecialchars($sp['article']).'">'.
        '</div>';
    }
    if(isset($data['next']))
        $send .= '<div class="ajaxNext" val="'.($data['next']).'"><span>��������� '.$data['limit'].' ������</span></div>';
    return $send;
}//end of zayav_spisok()

function zayav_info($zayav_id) {
    $sql = "SELECT * FROM `zayavki` WHERE `zayav_status`>0 AND `id`=".$zayav_id." LIMIT 1";
    if(!$zayav = mysql_fetch_assoc(query($sql)))
        return '������ �� ����������.';
    $status = _zayavStatus($zayav['zayav_status']);
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
        $zpSpisok = '<div class="_empty">��� '.$model.' ��������� ���.</div>';
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
        _zpImg($ids, 'small', 80, 80, 'fotoView');
        $ids = implode(',', $ids);
        $sql = "SELECT `zp_id` AS `id`,`count` FROM `zp_avai` WHERE `zp_id` IN (".$ids.")";
        $q = query($sql);
        while($r = mysql_fetch_assoc($q))
            $zp[$r['id']]['avai'] = $r['count'];
        $sql = "SELECT `zp_id` AS `id`,`count`
                FROM `zp_zakaz`
                WHERE `zp_id` IN (".$ids.")
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
            'device:'.$zayav['base_device_id'].','.
            'vendor:'.$zayav['base_vendor_id'].','.
            'model:'.$zayav['base_model_id'].','.
            'z_status:'.$zayav['zayav_status'].','.
            'dev_status:'.$zayav['device_status'].','.
            'dev_place:'.$zayav['device_place'].','.
            'place_other:"'.$zayav['device_place_other'].'",'.
            'imei:"'.$zayav['imei'].'",'.
            'serial:"'.$zayav['serial'].'",'.
            'color_id:'.$zayav['color_id'].
        '};'.
    '</script>'.
    '<div id="zayavInfo">'.
        '<div id="dopLinks">'.
            '<a class="delete'.(!empty($money) ?  ' dn': '').'">������� ������</a>'.
            '<a class="link sel">����������</a>'.
            '<a class="link zedit">��������������</a>'.
            '<a class="link acc_add">���������</a>'.
            '<a class="link op_add">������� �����</a>'.
        '</div>'.
        '<table class="itab">'.
            '<tr><td id="left">'.
                '<div class="headName">������ �'.$zayav['nomer'].'</div>'.
                '<table class="tabInfo">'.
                    '<tr><td class="label">����������: <td>'._deviceName($zayav['base_device_id']).'<a><b>'.$model.'</b></a>'.
                    '<tr><td class="label">������:     <td>'._clientsLink($zayav['client_id']).
                    '<tr><td class="label">���� �����:'.
                        '<td class="dtime_add" title="������ ��� '._viewerName($zayav['viewer_id_add']).'">'.FullDataTime($zayav['dtime_add']).
                    '<tr><td class="label">������:'.
                        '<td><div id="status" style="background-color:#'.$status['color'].'" class="status_place">'.$status['name'].'</div>'.
                            '<div id="status_dtime">�� '.FullDataTime($zayav['zayav_status_dtime'], 1).'</div>'.
                    '<tr class="acc_tr'.($accSum > 0 ? '' : ' dn').'"><td class="label">���������: <td><b class="acc">'.$accSum.'</b> ���.'.
                    '<tr class="op_tr'.($opSum > 0 ? '' : ' dn').'"><td class="label">��������:    <td><b class="op">'.$opSum.'</b> ���.'.
                        '<span class="dopl'.($dopl == 0 ? ' dn' : '').'" title="����������� �������'."\n".'���� �������� �������������, �� ��� ���������">'.($dopl > 0 ? '+' : '').$dopl.'</span>'.
                '</table>'.
                '<div class="headBlue">�������<a class="add remind_add">�������� �������</a></div>'.
                '<div id="remind_spisok">'.report_remind_spisok(remind_data(1, array('zayav'=>$zayav['id']))).'</div>'.
                _vkComment('zayav', $zayav['id']).
                '<div class="headBlue mon">���������� � �������'.
                    '<a class="add op_add">������� �����</a>'.
                    '<em>::</em>'.
                    '<a class="add acc_add">���������</a>'.
                '</div>'.
                '<table class="_spisok _money">'.implode($money).'</table>'.

            '<td id="right">'.
                '<div id="foto">'._zayavImg($zayav_id, 'big', 200, 320, 'fotoView').'</div>'.
                '<div class="fotoUpload">�������� �����������</div>'.
                '<div class="headBlue">���������� �� ����������</div>'.
                '<div class="devContent">'.
                    '<div class="devName">'._deviceName($zayav['base_device_id']).'<br />'.'<a>'.$model.'</a></div>'.
                    '<table class="devInfo">'.
                        ($zayav['imei'] ? '<tr><th>imei:      <td>'.$zayav['imei'] : '').
                        ($zayav['serial'] ? '<tr><th>serial:  <td>'.$zayav['serial'] : '').
                        ($zayav['color_id'] ? '<tr><th>����:  <td>'._colorName($zayav['color_id']) : '').
                        '<tr><th>����������:<td><a class="dev_place status_place">'.($zayav['device_place'] ? @_devPlace($zayav['device_place']) : $zayav['device_place_other']).'</a>'.
                        '<tr><th>���������: <td><a class="dev_status status_place">'._devStatus($zayav['device_status']).'</a>'.
                    '</table>'.
                '</dev>'.

                '<div class="headBlue">'.
                    '<a class="goZp" href="'.URL.'&p=zp&device='.$zayav['base_device_id'].'&vendor='.$zayav['base_vendor_id'].'&model='.$zayav['base_model_id'].'">������ ���������</a>'.
                    '<a class="zpAdd add">��������</a>'.
                '</div>'.
                '<div id="zpSpisok">'.$zpSpisok.'</div>'.
        '</table>'.
    '</div>';
}//end of zayav_info()
function zayav_accrual_unit($acc) {
    return '<tr><td class="sum acc" title="����������">'.$acc['summa'].'</td>'.
        '<td>'.$acc['prim'].'</td>'.
        '<td class="dtime" title="�������� '._viewerName(isset($acc['viewer_id_add']) ? $acc['viewer_id_add'] : VIEWER_ID).'">'.
            FullDataTime(isset($acc['dtime_add']) ? $acc['dtime_add'] : curTime()).
        '</td>'.
        '<td class="del"><div class="img_del acc_del" title="������� ����������" val="'.$acc['id'].'"></div></td>'.
    '</tr>';
}//end of zayav_accrual_unit()
function zayav_oplata_unit($op) {
    return '<tr><td class="sum op" title="�����">'.$op['summa'].'</td>'.
        '<td>'.$op['prim'].'</td>'.
        '<td class="dtime" title="����� ��� '._viewerName(isset($op['viewer_id_add']) ? $op['viewer_id_add'] : VIEWER_ID).'">'.
            FullDataTime(isset($op['dtime_add']) ? $op['dtime_add'] : curTime()).
        '</td>'.
        '<td class="del"><div class="img_del op_del" title="������� �����" val="'.$op['id'].'"></div></td>'.
    '</tr>';
}//end of zayav_oplata_unit()
function zayav_zp_unit($r, $model) {
    return '<div class="unit" val="'.$r['id'].'">'.
        '<div class="image"><div>'._zpImg($r['id']).'</div></div>'.
        ($r['bu'] ? '<span class="bu">�/�</span>' : '').
        '<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'"><b>'._zpName($r['name_id']).'</b> '.$model.'</a>'.
        ($r['version'] ? '<div class="version">'.$r['version'].'</div>' : '').
        ($r['color_id'] ? '<div class="color">����: '._colorName($r['color_id']).'</div>' : '').
        '<div>'.
            (isset($r['zakaz']) ? '<a class="zakaz_ok">��������!</a>' : '<a class="zakaz">��������</a>').
            (isset($r['avai']) && $r['avai'] > 0 ? '<b class="avai">�������: '.$r['avai'].'</b> <a class="set">����������</a>' : '').
        '</div>'.
    '</div>';
}//end of zayav_zp_unit()







// ---===! zp !===--- ������ ���������

function zpAddQuery($zp) {//�������� ����� �������� �� ������ � �� ������ ���������
    if(!isset($zp['compat_id']))
        $zp['compat_id'] = 0;
    $sql = "INSERT INTO `zp_catalog` (
                `name_id`,
                `base_device_id`,
                `base_vendor_id`,
                `base_model_id`,
                `bu`,
                `version`,
                `color_id`,
                `compat_id`,
                `viewer_id_add`,
                `find`
            ) VALUES (
                ".$zp['name_id'].",
                ".$zp['device_id'].",
                ".$zp['vendor_id'].",
                ".$zp['model_id'].",
                ".$zp['bu'].",
                '".$zp['version']."',
                ".$zp['color_id'].",
                ".$zp['compat_id'].",
                ".VIEWER_ID.",
                '".win1251(_modelName($zp['model_id']))." ".$zp['version']."'
            )";
    query($sql);
    return mysql_insert_id();
}//end of zpAddQuery()

function zpFilter($v) {
    if(empty($v['menu']) || !preg_match(REGEXP_NUMERIC, $v['menu']))
        $v['menu'] = 0;
    if(empty($v['name']) || !preg_match(REGEXP_NUMERIC, $v['name']))
        $v['name'] = 0;
    if(empty($v['device']) || !preg_match(REGEXP_NUMERIC, $v['device']))
        $v['device'] = 0;
    if(empty($v['vendor']) || !preg_match(REGEXP_NUMERIC, $v['vendor']))
        $v['vendor'] = 0;
    if(empty($v['model']) || !preg_match(REGEXP_NUMERIC, $v['model']))
        $v['model'] = 0;
    if(empty($v['bu']) || !preg_match(REGEXP_BOOL, $v['bu']))
        $v['bu'] = 0;

    $filter = array();
    $filter['find'] = htmlspecialchars(trim(@$v['find']));
    $filter['menu'] = intval($v['menu']);
    $filter['name'] = intval($v['name']);
    $filter['device'] = intval($v['device']);
    $filter['vendor'] = intval($v['vendor']);
    $filter['model'] = intval($v['model']);
    $filter['bu'] = intval($v['bu']);
    return $filter;
}//end of zpFilter()
function zp_data($page=1, $filter=array(), $limit=20) {
    $cond = "`id`";
    if(empty($filter['find']) && (!isset($filter['model']) || $filter['model'] == 0))
        $cond .= " AND (`compat_id`=0 OR `compat_id`=`id`)";
    if(!empty($filter['find'])) {
        $cond .= " AND `find` LIKE '%".$filter['find']."%'";
        $reg = '/('.$filter['find'].')/i';
    }
    if(isset($filter['menu']))
        switch($filter['menu']) {
            case '1':
                $sql = "SELECT `zp_id` AS `id` FROM `zp_avai` WHERE `ws_id`=".WS_ID;
                $q = query($sql);
                $ids = '0';
                while($r = mysql_fetch_assoc($q))
                    $ids .= ','.$r['id'];
                $cond .= " AND `id` IN (".$ids.")";
                break;
            case '2':
                $sql = "SELECT `zp_id` AS `id` FROM `zp_avai` WHERE `ws_id`=".WS_ID;
                $q = query($sql);
                $ids = '0';
                while($r = mysql_fetch_assoc($q))
                    $ids .= ','.$r['id'];
                $cond .= " AND `id` NOT IN (".$ids.")";
                break;
            case '3':
                $sql = "SELECT `zp_id` AS `id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." GROUP BY `zp_id`";
                $q = query($sql);
                $ids = '0';
                while($r = mysql_fetch_assoc($q))
                    $ids .= ','.$r['id'];
                $cond .= " AND `id` IN (".$ids.")";
                break;
        }
    if(isset($filter['name']) && $filter['name'] > 0)
        $cond .= " AND `name_id`=".$filter['name'];
    if(isset($filter['device']) && $filter['device'] > 0)
        $cond .= " AND `base_device_id`=".$filter['device'];
    if(isset($filter['vendor']) && $filter['vendor'] > 0)
        $cond .= " AND `base_vendor_id`=".$filter['vendor'];
    if(isset($filter['model']) && $filter['model'] > 0)
        $cond .= " AND `base_model_id`=".$filter['model'];
    if(isset($filter['bu']) && $filter['bu'] == 1)
        $cond .= " AND `bu`=1";

    $send['filter'] = $filter;
    $send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zp_catalog` WHERE ".$cond." LIMIT 1");
    if($send['all'] == 0) {
        $send['spisok'] = '<div class="_empty">��������� �� �������.</div>';
        return $send;
    }
    $start = ($page - 1) * $limit;
    $spisok = array();
    $sql = "SELECT *
            FROM `zp_catalog`
            WHERE ".$cond."
            ORDER BY `id` DESC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $ids = array();
    while($r = mysql_fetch_assoc($q)) {
        $r['model'] = _modelName($r['base_model_id']);
        if(!empty($filter['find'])) {
            if(preg_match($reg, $r['model']))
                $r['model'] = preg_replace($reg, "<em>\\1</em>", $r['model'], 1);
            if(preg_match($reg, $r['version']))
                $r['version'] = preg_replace($reg, "<em>\\1</em>", $r['version'], 1);
        }
        $r['zp_id'] = $r['compat_id'] > 0 ? $r['compat_id'] : $r['id'];
        $ids[$r['zp_id']] = $r['zp_id'];
        $spisok[$r['id']] = $r;
    }

    _getImg('zp', $ids);

    $sql = "SELECT
                `zp_id`,
                `count`
            FROM `zp_avai`
            WHERE `ws_id`=".WS_ID."
              AND `zp_id` IN (".implode(',', $ids).")";
    $q = query($sql);
    $avai = array();
    while($r = mysql_fetch_assoc($q))
        $avai[$r['zp_id']] = $r['count'];

    $sql = "SELECT
                `zp_id`,
                SUM(`count`) AS `count`
            FROM `zp_zakaz`
            WHERE `ws_id`=".WS_ID."
              AND `zp_id` IN (".implode(',', $ids).")
            GROUP BY `zp_id`";
    $q = query($sql);
    $zakaz = array();
    while($r = mysql_fetch_assoc($q))
        $zakaz[$r['zp_id']] = $r['count'];

    $sql = "SELECT
                `zp_id`,
                `zayav_id`
            FROM `zp_zakaz`
            WHERE `ws_id`=".WS_ID."
              AND `zp_id` IN (".implode(',', $ids).")
              AND `zayav_id`>0";
    $q = query($sql);
    $zakazZayav = array();
    $zayav = array();
    while($r = mysql_fetch_assoc($q)) {
        $zakazZayav[$r['zp_id']][] = $r['zayav_id'];
        $zayav[$r['zayav_id']] = $r['zayav_id'];
    }
    _zayavNomerLink($zayav);
    foreach($zakazZayav as $id => $zz)
        foreach($zz as $i => $zayav_id)
            $zakazZayav[$id][$i] = _zayavNomerLink($zayav_id);

    $send['spisok'] = '';
    foreach($spisok as $id => $r) {
        $zakazCount = isset($zakaz[$r['zp_id']]) ? $zakaz[$r['zp_id']] : 0;
        $zakazEdit = '<span class="zzedit">���: <tt>�</tt><b>'.$zakazCount.'</b><tt>+</tt></span>';
        $send['spisok'] .= '<div class="unit" val="'.$id.'">'.
            '<table>'.
                '<tr><td class="img">'._zpImg($r['zp_id']).
                    '<td class="cont">'.
                        ($r['bu'] ? '<span class="bu">�/�</span>' : '').
                        '<a href="'.URL.'&p=zp&d=info&id='.$id.'" class="name">'.
                            _zpName($r['name_id']).
                            ' <b>'._vendorName($r['base_vendor_id']).$r['model'].'</b>'.
                        '</a>'.
                        ($r['version'] ? '<div class="version">'.$r['version'].'</div>' : '').
                        '<div class="for">��� '._deviceName($r['base_device_id'], 1).'</div>'.
                        ($r['color_id'] ? '<div class="color"><span>����:</span> '._colorName($r['color_id']).'</div>' : '').
                        //($r['compat_id'] == $id ? '<b>�������</b>' : '').
                        //($r['compat_id'] > 0 && $r['compat_id'] != $id ? '<b>�������������</b>' : '').
                        (isset($zakazZayav[$id]) ? '<div class="zz">�������� ��� ����'.(count($zakazZayav[$id]) > 1 ? '��' : '��').' '.implode(', ', $zakazZayav[$id]).'</div>' : '').
                    '<td class="action">'.
                        (isset($avai[$r['zp_id']]) ? '<a class="avai avai_add">� �������: <b>'.$avai[$r['zp_id']].'</b></a>' : '<a class="hid avai_add">������ �������</a>').
                        '<a class="zpzakaz'.($zakazCount ? '' : ' hid').'">�����<span class="cnt">'.($zakazCount ? '���: <b>'.$zakazCount.'</b>' : '���').'</span>'.$zakazEdit.'</a>'.
            '</table>'.
        '</div>';
    }
    if($start + $limit < $send['all']) {
        $c = $send['all'] - $start - $limit;
        $c = $c > $limit ? $limit : $c;
        $send['spisok'] .= '<div class="ajaxNext" val="'.($page + 1).'"><span>�������� ��� '.$c.' �������'._end($c, '�', '�', '��').'</span></div>';
    }
    return $send;
}//end of zp_data()
function zp_list($data) {
    $filter = $data['filter'];
    return '<div id="zp">'.
        '<div class="result">'.zp_count($data).'</div>'.
        '<table class="tabLR">'.
            '<tr><td class="left">'.$data['spisok'].
                '<td class="right">'.
                    '<div id="find"></div>'.
                    '<div id="menu"></div>'.
                    '<div class="findHead">������������</div><INPUT type="hidden" id="zp_name" value="'.$filter['name'].'" />'.
                    '<div class="findHead">����������</div><div id="dev"></div>'.
                    _checkbox('bu', '�/�', $filter['bu']).
        '</table>'.
        '<script type="text/javascript">'.
            'G.zp_find = "'.$filter['find'].'";'.
            'G.zp_menu = "'.$filter['menu'].'";'.
            'G.zp_device = '.$filter['device'].';'.
            'G.zp_vendor = '.$filter['vendor'].';'.
            'G.zp_model = '.$filter['model'].';'.
        '</script>'.
    '</div>';
}//end of zp_list()
function zp_count($data) {
    $all = $data['all'];
    return ($all > 0 ?
        '�������'._end($all, '� ', '� ').$all.' �������'._end($all, '�', '�', '��').
        (!$data['filter']['menu'] ? '<a class="add">������ ����� �������� � �������</a>' : '')
        :
        '��������� �� �������');
}//end of zp_count()

function zp_info($zp_id) {
    $sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$zp_id;
    if(!$zp = mysql_fetch_assoc(query($sql)))
        return '�������� �� ����������';

    $compat_id = $zp['compat_id'] ? $zp['compat_id'] : $zp_id;
    if($zp_id != $compat_id) {
        $sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$compat_id;
        $compat = mysql_fetch_assoc(query($sql));
        $zp['color_id'] = $compat['color_id'];
        $zp['bu'] = $compat['bu'];
    }

    $avai = query_value("SELECT `count` FROM `zp_avai` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$compat_id);

    $zakazCount = query_value("SELECT IFNULL(SUM(`count`),0) FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$compat_id);
    $zakazEdit = '<span class="zzedit">���: <tt>�</tt><b>'.$zakazCount.'</b><tt>+</tt></span>';

    _zpImg($compat_id, 'big', 160, 280, 'fotoView');

    $compatSpisok = zp_compat_spisok($zp_id, $compat_id);
    $compatCount = count($compatSpisok);

    return
    '<script type="text/javascript">'.
        'G.zpInfo = {'.
            'id:'.$zp_id.','.
            'compat_id:'.$compat_id.','.
            'name_id:'.$zp['name_id'].','.
            'device:'.$zp['base_device_id'].','.
            'vendor:'.$zp['base_vendor_id'].','.
            'model:'.$zp['base_model_id'].','.
            'version:"'.$zp['version'].'",'.
            'color_id:'.$zp['color_id'].','.
            ($zp['color_id'] ? 'color_name:"'._colorName($zp['color_id']).'",' : '').
            'bu:'.$zp['bu'].','.
            'name:"'._zpName($zp['name_id']).' <b>'._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).'</b>",'.
            'for:"��� '._deviceName($zp['base_device_id'], 1).'",'.
            'count:'.($avai ? $avai : 0).','.
            'img:"'.addslashes(_zpImg($compat_id)).'"'.
        '};'.
    '</script>'.
    '<div id="zpInfo">'.
        '<table class="ztab">'.
            '<tr><td class="left">'.
                    '<div class="name">'.
                        ($zp['bu'] ? '<span>�/�</span>' : '').
                        _zpName($zp['name_id']).
                        '<em>'.$zp['version'].'</em>'.
                    '</div>'.
                    '<div class="for">'.
                        '��� '._deviceName($zp['base_device_id'], 1).
                        ' <a>'._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).'</a>'.
                    '</div>'.
                    '<table class="prop">'.
                        ($zp['color_id'] ? '<tr><td class="label">����:<td>'._colorName($zp['color_id']) : '').
                        //'<tr><td class="label">id:<td>'.$zp['id'].
                        //'<tr><td class="label">compat_id:<td>'.$zp['compat_id'].
                    '</table>'.
                    '<div class="avai'.($avai ? '' : ' no').'">'.($avai ? '� ������� '.$avai.' ��.' : '��� � �������.').'</div>'.
                    '<div class="added">��������� � ������� '.FullData($zp['dtime_add'], 1).'</div>'.
                    '<div class="headBlue">��������</div>'.
                    '<div class="move">'.zp_move($compat_id).'</div>'.
                '<td class="right">'.
                    '<div id="foto">'._zpImg($compat_id).'</div>'.
                    '<div class="rightLinks">'.
                        '<a class="fotoUpload">�������� �����������</a>'.
                        '<a class="edit">�������������</a>'.
                        '<a class="avai_add">������ �������</a>'.
                        '<a class="zpzakaz unit'.($zakazCount ? '' : ' hid').'" val="'.$zp_id.'">'.
                            '�����<span class="cnt">'.($zakazCount ? '���: <b>'.$zakazCount.'</b>' : '���').'</span>'.
                            $zakazEdit.
                        '</a>'.
                        '<a class="set"> - ���������</a>'.
                        '<a class="sale"> - �������</a>'.
                        '<a class="defect"> - ����</a>'.
                        '<a class="return"> - �������</a>'.
                        '<a class="writeoff"> - ��������</a>'.
                    '</div>'.
                    '<div class="headBlue">�������������<a class="add compat_add">��������</a></div>'.
                    '<div class="compatCount">'.zp_compat_count($compatCount).'</div>'.
                    '<div class="compatSpisok">'.($compatCount ? implode($compatSpisok) : '').'</div>'.
        '</table>'.
    '</div>';
}//end of zp_info()
function zp_move($zp_id, $page=1) {
    $all = query_value("SELECT COUNT(`id`) FROM `zp_move` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id);
    if(!$all)
        return '<div class="unit">�������� �������� ���.</div>';

    $limit = 10;
    $start = ($page - 1) * $limit;
    $sql = "SELECT *
            FROM `zp_move`
            WHERE `ws_id`=".WS_ID."
              AND `zp_id`=".$zp_id."
            ORDER BY `id` DESC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $spisok = array();
    $viewer = array();
    $zayav = array();
    $client = array();
    while($r = mysql_fetch_assoc($q)) {
        $spisok[] = $r;
        $viewer[$r['viewer_id_add']] = $r['viewer_id_add'];
        if($r['zayav_id'] > 0)
            $zayav[$r['zayav_id']] = $r['zayav_id'];
        if($r['client_id'] > 0)
            $client[$r['client_id']] = $r['client_id'];
    }
    $viewer = _viewersInfo($viewer);
    _zayavNomerLink($zayav);
    $client = _clientsLink($client);
    $move = '';
    $type = array(
        '' => '������',
        'set' => '���������',
        'sale' => '�������',
        'defect' => '����',
        'return' => '�������',
        'writeoff' => '��������'
    );
    foreach($spisok as $n => $r) {
        $cena = round($r['cena'], 2);
        $summa = round($r['summa'], 2);
        $count = abs($r['count']);
        $move .= '<div class="unit">'.
            '<div>'.
                (!$n && $page == 1 ? '<div class="img_del" val="'.$r['id'].'"></div>' : '').
                $type[$r['type']].' <b>'.$count.'</b> ��. '.
                ($summa ? '�� ����� '.$summa.' ���.'.($count > 1 ? ' <span class="cenaed">('.$cena.' ���./��.)</span> ' : '') : '').
                ($r['zayav_id'] ? '�� ������ '._zayavNomerLink($r['zayav_id']).'.' : '').
                ($r['client_id'] ? '������� '.$client[$r['client_id']].'.' : '').
            '</div>'.
            ($r['prim'] ? '<div class="prim">'.$r['prim'].'</div>' : '').
            '<div class="dtime" title="��� '.$viewer[$r['viewer_id_add']]['name'].'">'.FullDataTime($r['dtime_add']).'</div>'.
        '</div>';
    }
    if($start + $limit < $all) {
        $c = $all - $start - $limit;
        $c = $c > $limit ? $limit : $c;
        $move .= '<div class="ajaxNext" val="'.($page + 1).'"><span>�������� ��� '.$c.' �����'._end($c, '�', '�', '��').'</span></div>';
    }
    return $move;
}//end of zp_move()
function zp_compat_spisok($zp_id, $compat_id=false) {
    if(!$compat_id)
        $compat_id = _zpCompatId($zp_id);
    $sql = "SELECT * FROM `zp_catalog` WHERE `id`!=".$zp_id." AND `compat_id`=".$compat_id;
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q)) {
        $key = explode(' ', _modelName($r['base_model_id']));
        $send[$key[0]] = '<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'">'.
            '<div class="img_del" val="'.$r['id'].'" title="��������� �������������"></div>'.
            _vendorName($r['base_vendor_id'])._modelName($r['base_model_id']).
        '</a>';
    }
    ksort($send);
    return $send;
}//end of zp_compat_spisok()
function zp_compat_count($c) {
    return $c ? $c.' ���������'._end($c, '�', '�', '') : '�������������� ���';
}






// ---===! report !===--- ������ �������

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
        case 3: return '��� � ���� ������ ������� '.$arr['client_link'].'.';
        case 4:
            $status = _zayavStatus($arr['value']);
            return '������� ������ ������ '.$arr['zayav_link'].' �� <span style="background-color:#'.$status['color'].'">'.$status['name'].'</span>.';
        case 5: return '������� ���������� �� ����� <b>'.$arr['value'].'</b> ���. ��� ������ '.$arr['zayav_link'].'.';
        case 6: return
            '��� ����� �� ����� <b>'.$arr['value'].'</b> ���. '.
            ($arr['value1'] ? '('.$arr['value1'].')' : '').
            ($arr['zayav_id'] ? ' �� ������ '.$arr['zayav_link'] : '');
        case 7: return '�������������� ������ ������ '.$arr['zayav_link'].'.';
        case 8:
            return '������ ���������� �� ����� <b>'.$arr['value'].'</b> ���. '.
                ($arr['value1'] ? '('.$arr['value1'].')' : '').
                ' � ������ '.$arr['zayav_link'].'.';
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
        case 18: return '��� ������� �������� '.$arr['zp_link'].' � ���������� '.$arr['value'].' ��.';
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
        case 21: return '��� ������ �� ����� <b>'.$arr['value'].'</b> ���.';
        case 22: return '������ ������ �� ����� <b>'.$arr['value'].'</b> ���.';
        case 23: return '������� ������ ������� �� ����� <b>'.$arr['value'].'</b> ���.';
        case 24: return '��������� ��������� �������� � ����� = <b>'.$arr['value'].'</b> ���.';
        case 25: return '������ ������ � ����� �� ����� <b>'.$arr['value'].'</b> ���. ('.$arr['value1'].')';
        case 26: return '����������� ������ � ����� �� ����� <b>'.$arr['value'].'</b> ���. ('.$arr['value1'].')';
        case 27:
            return '����������� ���������� �� ����� <b>'.$arr['value'].'</b> ���. '.
                ($arr['value1'] ? '('.$arr['value1'].')' : '').
                ' � ������ '.$arr['zayav_link'].'.';

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
            '<div class="findHead">���������</div>'.
            '<input type="hidden" id="report_history_worker" value="0">'.
            '<div class="findHead">��������</div>'.
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
    $viewer = _viewersInfo($viewer);
    $client = _clientsLink($client);
    _zayavNomerLink($zayav);
    $zp = _zpLink($zp);
    $send = '';
    foreach($history as $r) {
        if($r['client_id'] > 0 && isset($client[$r['client_id']]))
            $r['client_link'] = $client[$r['client_id']];
        if($r['zayav_id'] && _zayavNomerLink($r['zayav_id']))
            $r['zayav_link'] = _zayavNomerLink($r['zayav_id']);
        if($r['zp_id'] && isset($zp[$r['zp_id']]))
            $r['zp_link'] = $zp[$r['zp_id']];
        $send .= '<div class="head">'.FullDataTime($r['dtime_add']).$viewer[$r['viewer_id_add']]['link'].'</div>'.
                 '<div class="txt">'.history_types($r).'</div>';
    }
    if($start + $limit < $all)
        $send .= '<div class="ajaxNext" id="report_history_next" val="'.($page + 1).'"><span>�����...</span></div>';
    return $send;
}//end of report_history_spisok()

function report_remind() {
    $data = remind_data();
    $send = '<div id="report_remind">'.
        '<div class="info">'.
            '<a class="opening">�������� ����������</a>'.
            '<div class="text">'.
                '<b>�������</b> - ��� �� ����������� '.
                '���������� ��� ������������ � ����� �������� ��� ��������, ��������, ������ ��������, '.
                '����������� ������������� ������� ����������, ���������� ����� � ��.<BR><BR>'.

                '<b>Ƹ����</b> ������ ���������� �������, ������� ������� ������� � ��������� ����, �� ���� �������. '.
                '�� ���������� ������ ������������ � ������� �������� ������� "������" � ������� "�������" � �������.<BR>'.
                '����� - �������, ��������� ���������� ����� ������ ���.<BR>'.
                '������ - �������, ����� - ��������, � <b>�������</b> - ������������ �������.<BR><BR>'.

                '<b>����� �����</b> ��� �������� ������ ������� ����� �������� ��������� ��� ����������. '.
                '��� ��������, � �������, ����� ����� ����������� � ������ ��� "<I>���������</I>" �� � ��� �� �������. '.
                '����� ������ "<I>��������� � �������� ��������� �����������</I>".<BR><BR>'.

                '��� ���������� �������� ��� ��������� ����������� ����� �������� � ���������. '.
                '��������� ������ ��������� <b>�������</b> �������� ������� �� ������ ���� ��� ������� ��� ������. '.
                '����������� � ������������ ������� �� ����������. <BR><BR>'.

                '��� ��������� ������� <b>������</b> ������� ����� ����� ������ ��� ������.<BR><BR>'.

                '�� ������ "�������" ��������� ��������������� ������ ���� �������� ��� ��������.<BR><BR>'.
            '</div>'.
            '<a class="closing">������</a>'.
        '</div>'.
        '<div id="remind_spisok">'.(!empty($data) ? report_remind_spisok($data) : '<div class="_empty">������� ���.</div>').'</div>'.
    '</div>';
    return $send;
}//end of report_remind()
function report_remind_right() {
    return '<div class=findHead>��������� �������</div>'.
        '<INPUT type="hidden" id="remind_status" value="1">'.
        _checkbox('remind_private', '������');
}//end of report_remind_right()
function remind_data($page=1, $filter=array()) {
    $cond = "`ws_id`=".WS_ID." AND `status`=".(isset($filter['status']) ? intval($filter['status']) : 1);
    if(!empty($filter['private']))
        $cond .= " AND `private`=1";
    if(!empty($filter['zayav']))
        $cond .= " AND `zayav_id`=".intval($filter['zayav']);
    if(!empty($filter['client'])) {
        $client_id = intval($filter['client']);
        $cond .= " AND `client_id`=".$client_id;
        $sql = "SELECT `id` FROM `zayavki` WHERE `ws_id`=".WS_ID." AND `zayav_status`>0 AND `client_id`=".$client_id;
        $q = query($sql);
        $zayav_ids = array();
        while($r = mysql_fetch_assoc($q))
            $zayav_ids[] = $r['id'];
        if(!empty($zayav_ids))
            $cond .= " OR `ws_id`=".WS_ID." AND `status`=1 AND `zayav_id` IN (".implode(',', $zayav_ids).")";
    }
    $send['all'] = query_value("SELECT COUNT(`id`) FROM `reminder` WHERE ".$cond);
    if(!$send['all'])
        return array();

    $limit = 20;
    $start = ($page - 1) * $limit;
    $sql = "SELECT *
            FROM `reminder`
            WHERE ".$cond."
            ORDER BY `day` ASC,`id` DESC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $client = array();
    $zayav = array();
    while($r = mysql_fetch_assoc($q)) {
        if($r['client_id'] > 0)
            $client[$r['client_id']] = $r['client_id'];
        if($r['zayav_id'])
            $zayav[$r['zayav_id']] = $r['zayav_id'];
        $send['spisok'][] = $r;
    }
    $send['client'] = _clientsLink($client);
    _zayavNomerLink($zayav);
    if($start + $limit < $send['all'])
        $send['page'] = ++$page;
    $send['filter'] = $filter;
    return $send;
}//end of remind_data()
function report_remind_spisok($data) {
    if(empty($data['spisok']))
        return '';
    $send = '';
    $today = strtotime(strftime("%Y-%m-%d", time()));
    foreach($data['spisok'] as $r) {
        $day_leave = (strtotime($r['day']) - $today) / 3600 / 24;
        $leave = '';
        if($day_leave < 0)
            $leave = '���������'._end($day_leave * -1, ' ', '� ').($day_leave * -1)._end($day_leave * -1, ' ����', ' ���', ' ����');
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
        switch($r['status']) {
            case 2: $rem_cond = "<EM>���������.</EM>"; break;
            case 0: $rem_cond = "<EM>��������.</EM>"; break;
            default:
                $rem_cond = '<EM>��������� '.($day_leave == 0 ? '' : '�� ').'</EM>'.
                    ($day_leave >= 0 && $day_leave < 3 ? $leave : FullData($r['day'], 1)).
                    ($day_leave > 2 || $day_leave < 0 ? '<SPAN>, '.$leave.'</SPAN>' : '');

        }
        $send .= '<div class="remind_unit '.$color.'">'.
            '<div class="txt">'.
                ($r['private'] ? '<u>������.</u> ' : '').
                ($r['client_id'] && empty($data['filter']['client']) ? '������ '.$data['client'][$r['client_id']].': ' : '').
                ($r['zayav_id'] && empty($data['filter']['zayav']) ? '������ '._zayavNomerLink($r['zayav_id']).': ' : '').
                '<b>'.$r['txt'].'</b>'.
            '</div>'.
            '<div class="day">'.
                '<div class="action">'.
                    ($r['status'] == 1 ? '<a class="edit" val="'.$r['id'].'">��������</a> :: ' : '').
                    '<a class="hist_a">�������</a>'.
                '</div>'.
                $rem_cond.
                '<div class="hist">'.$r['history'].'</div>'.
            '</div>'.
        '</div>';
    }
    if(isset($data['page']))
        $send .= '<div class="ajaxNext" val="'.$data['page'].'"><span>�������� ��� �������...</span></div>';
    return $send;
}//end of report_remind_spisok()

function report_prihod_right() { //������� ������ ������ ��� �������
    return '<div class="report_prihod_rl">'.
        '<div class="findHead">������</div>'.
        '<div class="cal"><EM class="label">��:</EM><INPUT type="hidden" id="report_prihod_day_begin" value="'._curMonday().'"></div>'.
        '<div class="cal"><EM class="label">��:</EM><INPUT type="hidden" id="report_prihod_day_end" value="'._curSunday().'"></div>'.
        (VIEWER_ADMIN ? _checkbox('prihodShowDel', '���������� �������� �������') : '').
        '</div>';
}//end of report_prihod_right()
function report_prihod() {
    return '<div id="report_prihod">'.report_prihod_spisok(_curMonday(), _curSunday(), 0).'</div>';
}//end of report_prihod()
function report_prihod_spisok($day_begin, $day_end, $del_show=0, $page=1) {
    $limit = 30;
    $cond = "`ws_id`=".WS_ID."
        AND `summa`>0
        AND `dtime_add`>='".$day_begin." 00:00:00'
        AND `dtime_add`<='".$day_end." 23:59:59'
        ".($del_show && VIEWER_ADMIN ? '' : ' AND `status`=1');
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

    $send = '';
    if($page == 1)
        $send = '<div class="summa">'.
                '<a class="summa_add">������ ������������ �����</a>'.
                '�������'._end($all, '', '�').' <b>'.$all.'</b> ������'._end($all, '', '�', '��').' �� ����� <b>'.$r['sum'].'</b> ���.'.
            '</div>'.
            '<table class="_spisok">'.
                '<tr><th class="sum">�����'.
                    '<th>��������'.
                    '<th class="data">����'.
                    '<th class="del">';

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
        if($r['zayav_id'])
            $zayav[$r['zayav_id']] = $r['zayav_id'];
        if($r['zp_id'] > 0)
            $zp[$r['zp_id']] = $r['zp_id'];
        $money[] = $r;
    }
    $viewer = _viewersInfo($viewer);
    _zayavNomerLink($zayav);
    $zp = _zpLink($zp);
    foreach($money as $r) {
        $about = $r['prim'];
        if($r['zayav_id'])
            $about = '������ '._zayavNomerLink($r['zayav_id']);
        if($r['zp_id'] > 0)
            $about = '������� �������� '.$zp[$r['zp_id']];
        $dtimeTitle = '���: '.$viewer[$r['viewer_id_add']]['name'];
        if($r['status'] == 0)
            $dtimeTitle .= "\n".'������: '.$viewer[$r['viewer_id_del']]['name'].
                "\n".FullDataTime($r['dtime_del']);
        $send .= '<tr'.($r['status'] == 0 ? ' class="deleted"' : '').'>'.
            '<td class="sum"><b>'.$r['summa'].'</b>'.
            '<td>'.$about.
            '<td class="dtime" title="'.$dtimeTitle.'">'.FullDataTime($r['dtime_add']).
            '<td class="edit">'.($r['status'] == 1 ?
                '<div class="img_del" val="'.$r['id'].'" title="������� �����"></div>' :
                '<div class="img_rest" val="'.$r['id'].'" title="������������ �����"></div>');
    }
    if($start + $limit < $all)
        $send .= '<tr class="ajaxNext" id="report_prihod_next" val="'.($page + 1).'"><td colspan="4"><span>�������� ��� �������...</span></td></tr>';
    if($page == 1) $send .= '</table>';
    return $send;
}//end of report_prihod_spisok()

function report_rashod_right() {
    return '<div class="findHead">���������</div>'.
        '<input type="hidden" id="rashod_category">'.
        '<div class="findHead">���������</div>'.
        '<input type="hidden" id="rashod_worker">'.
        '<input type="hidden" id="rashod_year">'.
        '<input type="hidden" id="rashod_monthSum" value="'.intval(strftime('%m', time())).'">'.
        '<script type="text/javascript">var monthSum = ['.report_rashod_monthSum().'];</script>';
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

    return '<script type="text/javascript">'.
                'var rashodViewers = ['.implode(',', $viewers).'];'.
                'var rashodCaregory = ['.implode(',', $cat).'];'.
            '</script>'.
        '<div id="report_rashod">'.
            '<div class="headName">������ �������� ����������<a id="add">������ ����� ������</a></div>'.
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
        return '������ �����������.';
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
                '�������'._end($all, '�', '�').' <b>'.$all.'</b> �����'._end($all, '�', '�', '��').
                ' �� ����� <b>'.abs($r['sum']).'</b> ���.'.
                ' �� '._monthFull($ex[1]).' '.$ex[0].' �.'.
            '</div>'.
            '<table class="_spisok">'.
                '<tr><th class="sum">�����'.
                    '<th>��������'.
                    '<th class="data">����'.
                    '<th class="edit">';
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
        $dtimeTitle = '���: '.$viewer[$r['viewer_id_add']]['name'];
        if($r['status'] == 0)
            $dtimeTitle .= "\n".'������: '.$viewer[$r['viewer_id_del']]['name'].
                "\n".FullDataTime($r['dtime_del']);
        $send .= '<tr'.($r['status'] == 0 ? ' class="deleted"' : '').'>'.
            '<td class="sum"><b>'.abs($r['summa']).'</b>'.
            '<td>'.($r['rashod_category'] ? '<em>'.$rashodCat[$r['rashod_category']].($r['prim'] || $r['worker_id'] ? ':' : '').'</em>' : '').
                   ($r['worker_id'] ? $viewer[$r['worker_id']]['link'].($r['prim'] ? ', ' : '') : '').
                   $r['prim'].
            '<td class="dtime" title="'.$dtimeTitle.'">'.FullDataTime($r['dtime_add']).
            '<td class="edit">'.($r['status'] == 1 ?
                '<div class="img_edit" val="'.$r['id'].'" title="�������������"></div>'.
                '<div class="img_del" val="'.$r['id'].'" title="�������"></div>'
                :
                '<div class="img_rest" val="'.$r['id'].'" title="������������"></div>');
    }
    if($start + $limit < $all)
        $send .= '<tr class="ajaxNext" id="report_rashod_next" val="'.($page + 1).'"><td colspan="4"><span>�������� �����...</span></td></tr>';
    if($page == 1) $send .= '</table>';
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
        $send = '<div class="set_info">���������� ��������, ������ ������� ����� �����, ����������� ������ � ����������. '.
                '�� ����� �������� ����� ������� ���������� ���� �������, �����������, ���� ������������ �� �����.<BR>'.
                '<b>��������!</b> ������ �������� ����� ���������� ������ ���� ���.'.
            '</div>'.
            '<table class="set_tab"><tr>'.
                '<td>�����: <INPUT type=text id="set_summa" maxlength=8> ���.</td>'.
                '<td><div class="vkButton" id="set_go"><button>����������</button></div></td>'.
            '</tr></table>';
    else
        $send = '<div class="in">� �����: <b id="kassa_summa">'.kassa_sum().'</b> ���. '.
                    '<div class="actions"><a>������ � �����</a> :: <a>����� �� �����</a></div>'.
                '</div>'.
                '<div id="spisok">'.report_kassa_spisok().'</div>';
    return '<div id="report_kassa">'.$send.'</div>';
}//end of report_kassa()
function report_kassa_right() {
    return KASSA_START == -1 ? '' : _checkbox('kassaShowDel', '���������� �������� ������');
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
        return '�������� � ������ ���.';
    $all = $r['all'];
    $start = ($page - 1) * $limit;

    $send = '';
    if($page == 1)
        $send = '<div class="all">'.'�������'._end($all, '', '�').' <b>'.$all.'</b> �����'._end($all, '�', '�', '��').'.</div>'.
            '<table class="_spisok">'.
                '<tr><th class="sum">�����'.
                    '<th>��������'.
                    '<th class="data">����'.
                    '<th>';

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
            '<td class="sum"><b>'.$r['sum'].'</b>'.
            '<td>'.$r['txt'].
            '<td class="dtime" title="���: '.$viewer[$r['viewer_id_add']]['name'].'">'.FullDataTime($r['dtime_add']).
            '<td class="edit">'.($r['status'] == 1 ?
                '<div class="img_del" val="'.$r['id'].'" title="�������"></div>' :
                '<div class="img_rest" val="'.$r['id'].'" title="������������"></div>');
    }
    if($start + $limit < $all)
        $send .= '<tr class="ajaxNext" id="report_kassa_next" val="'.($page + 1).'"><td colspan="4"><span>�������� ��� �������...</span></td></tr>';
    if($page == 1) $send .= '</table>';
    return $send;
}//end of report_kassa_spisok()



// ---===! setup !===--- ������ ���������

function setup_main() {
    $sql = "SELECT * FROM `workshop` WHERE `id`=".WS_ID." LIMIT 1";
    if(!$ws = mysql_fetch_assoc(query($sql))) {
        _cacheClear();
        header('Location:'.URL);
    }

    $ex = explode(',', $ws['devs']);
    $devs = array();
    foreach($ex as $d)
        $devs[$d] = $d;

    $sql = "SELECT `id`,`name_mn` FROM `base_device` ORDER BY `sort`";
    $q = query($sql);
    $checkDevs = '';
    while($r = mysql_fetch_assoc($q))
        $checkDevs .= _checkbox($r['id'], $r['name_mn'], isset($devs[$r['id']]) ? 1 : 0);

    return
    '<script type="text/javascript">'.
        'G.org_name = "'.$ws['org_name'].'";'.
    '</script>'.
    '<DIV id="setup_main">'.
	    '<DIV class="headName" id="headName">���������� � ����������</DIV>'.
	    '<TABLE class="tab">'.
            '<TR><TD class="label">�������� �����������:<TD><INPUT type="text" id="org_name" maxlength="100" value="'.$ws['org_name'].'">'.
            '<TR><TD class="label">�����:<TD>'.$ws['city_name'].', '.$ws['country_name'].
            '<TR><TD class="label">������� �������������:<TD><B>'._viewerName($ws['admin_id']).'</B>'.
        '</TABLE>'.

        '<DIV class="headName">��������� ������������� ���������</DIV>'.
        '<DIV id="devs">'.$checkDevs.'</DIV>'.

        '<DIV class="headName">�������� ����������</DIV>'.
        '<div class="del_inf">����������, � ����� ��� ������ ��������� ��� ����������� ��������������.</div>'.
        '<DIV class="vkButton" id="ws_del"><BUTTON>������� ����������</BUTTON></DIV>'.
    '</DIV>';
}//end of setup_main()

function setup_workers() {
    return
    '<DIV id="setup_workers">'.
        '<DIV class="headName">���������� ����������<a class="add">�������� ������ ����������</a></DIV>'.
        '<DIV id="spisok">'.setup_workers_spisok().'</DIV>'.
    '</DIV>';
}//end of setup_workers()
function setup_workers_spisok() {
    $sql = "SELECT * FROM `vk_user` WHERE `ws_id`=".WS_ID." ORDER BY `dtime_add`";
    $q = query($sql);
    $send = '';
    while($r = mysql_fetch_assoc($q)) {
        $send .=
        '<table class="unit" val="'.$r['viewer_id'].'">'.
            '<tr><td class="photo"><img src="'.$r['photo'].'">'.
                '<td>'.
                    (WS_ADMIN != $r['viewer_id'] ? '<div class="img_del"></div>' : '').
                    '<a class="name">'.$r['first_name'].' '.$r['last_name'].'</a>'.
                    '<div class="adm">'.
                        ($r['admin'] ?
                            '�������������'.(WS_ADMIN != $r['viewer_id'] ? ' <a class="adm_cancel">��������</a>' : '')
                            : '<a class="adm_set">��������� ���������������</a>').
                    '</div>'.
        '</table>';
    }
    return $send;
}//end of setup_workers()