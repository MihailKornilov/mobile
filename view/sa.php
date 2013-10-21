<?php
function sa_cookie_back() {
    if(!empty($_GET['pre_p'])) {
        $_COOKIE['pre_p'] = $_GET['pre_p'];
        $_COOKIE['pre_d'] = empty($_GET['pre_d']) ? '' : $_GET['pre_d'];
        $_COOKIE['pre_d1'] = empty($_GET['pre_d1']) ? '' : $_GET['pre_d1'];
        $_COOKIE['pre_id'] = empty($_GET['pre_id']) ? '' : $_GET['pre_id'];
        setcookie('pre_p', $_COOKIE['pre_p'], time() + 2592000, '/');
        setcookie('pre_d', $_COOKIE['pre_d'], time() + 2592000, '/');
        setcookie('pre_d1', $_COOKIE['pre_d1'], time() + 2592000, '/');
        setcookie('pre_id', $_COOKIE['pre_id'], time() + 2592000, '/');
    }
    $d = empty($_COOKIE['d']) ? '' :'&d='.$_COOKIE['d'];
    $d1 = empty($_COOKIE['d1']) ? '' :'&d1='.$_COOKIE['d1'];
    $id = empty($_COOKIE['id']) ? '' :'&id='.$_COOKIE['id'];
    return '<a href="'.URL.'&p='.$_COOKIE['pre_p'].$d.$d1.$id.'">�����</a> � ';
}//end of sa_cookie_back()

function sa_index() {
    $userCount = query_value("SELECT COUNT(`viewer_id`) FROM `vk_user`");
    $wsCount = query_value("SELECT COUNT(`id`) FROM `workshop`");
    return '<div class="path">'.sa_cookie_back().'�����������������</div>'.
    '<div class="sa-index">'.
        '<div><B>���������� � ����������:</B></div>'.
        '<A href="'.URL.'&p=sa&d=vkuser">������������ ('.$userCount.')</A><BR>'.
        '<A href="'.URL.'&p=sa&d=ws">���������� ('.$wsCount.')</A><BR>'.
        '<BR>'.
        '<div><B>���������� � ��������:</B></div>'.
        '<A href="'.URL.'&p=sa&d=fault">���� ��������������</A><BR>'.
        '<BR>'.
        '<A href="'.URL.'&p=sa&d=device">����������</A><BR>'.
        '<A href="'.URL.'&p=sa&d=dev-spec">�������������� ��������� ��� ����������</A><BR>'.
        '<A href="'.URL.'&p=sa&d=dev-status">������� ��������� � �������</A><BR>'.
        '<A href="'.URL.'&p=sa&d=dev-place">��������������� ��������� � �������</A><BR>'.
        '<BR>'.
        '<A href="'.URL.'&p=sa&d=color">����� ��� ��������� � ���������</A><BR>'.
        '<BR>'.
        '<A href="'.URL.'&p=sa&d=zp-name">������������ ���������</A><BR>'.
    '</div>';
}//end of sa_index()

function sa_ws() {
    $wsSpisok =
        '<tr><th>id'.
            '<th>������������'.
            '<th>�����'.
            '<th>����';
    $sql = "SELECT * FROM `workshop` ORDER BY `id`";
    $q = query($sql);
    $count = mysql_num_rows($q);
    while($r = mysql_fetch_assoc($q))
        $wsSpisok .=
            '<tr><td class="id">'.$r['id'].
                '<td class="name'.(!$r['status'] ? ' del' : '').'">'.
                    '<a href="'.URL.'&p=sa&d=ws&id='.$r['id'].'">'.$r['org_name'].'</a>'.
                    '<div class="city">'.$r['city_name'].($r['country_id'] != 1 ? ', '.$r['country_name'] : '').'</div>'.
                '<td>'._viewerName($r['admin_id'], true).
                '<td class="dtime">'.FullDataTime($r['dtime_add']);

    return '<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">�����������������</a> � ����������</div>'.
    '<div class="sa-ws">'.
        '<div class="count">����� <b>'.$count.'</b> ��������'._end($count, '��', '��').'.</div>'.
        '<table class="_spisok">'.$wsSpisok.'</table>'.
    '</div>';
}//end of sa_ws()
function sa_ws_info($id) {
    $sql = "SELECT * FROM `workshop` WHERE `id`=".$id;
    if(!$ws = mysql_fetch_assoc(query($sql)))
        return sa_ws();

    $tables = array(
        'client' => '�������',
        'zayavki' => '������',
        'accrual' => '����������',
        'money' => '������',
        'zp_avai' => '������� ���������',
        'zp_move' => '�������� ���������',
        'zp_zakaz' => '����� ���������',
        'history' => '������� ��������',
        'reminder' => '�������'
    );
    $counts = '';
    foreach ($tables as $tab => $about) {
        $c = query_value("select count(id) from ".$tab." where ws_id=".$ws['id']);
        if($c)
            $counts .= '<tr><td class="tb">'.$tab.':<td class="c">'.$c.'<td>'.$about;
    }

    if($ws['status']) {
        $workers = '';
        $sql = "SELECT * FROM `vk_user` WHERE `ws_id`=".$ws['id']." AND `viewer_id`!=".$ws['admin_id'];
        $q = query($sql);
        while($r = mysql_fetch_assoc($q))
            $workers = _viewerName($r['viewer_id'], true).'<br />';
    }

    return
    '<div class="path">'.
        sa_cookie_back().
        '<a href="'.URL.'&p=sa">�����������������</a> � '.
        '<a href="'.URL.'&p=sa&d=ws">����������</a> � '.
        $ws['org_name'].
    '</div>'.
    '<div class="sa-ws-info">'.
        '<div class="headName">���������� � ����������</div>'.
        '<table class="tab">'.
            '<tr><td class="label">������������:<td><b>'.$ws['org_name'].'</b>'.
            '<tr><td class="label">�����:<td>'.$ws['city_name'].', '.$ws['country_name'].
            '<tr><td class="label">�������������:<td>'._viewerName($ws['admin_id'], true).
            '<tr><td class="label">���� ��������:<td>'.FullDataTime($ws['dtime_add']).
            (!$ws['status'] ? '<tr><td class="label">���� ��������:<td>'.FullDataTime($ws['dtime_del']) : '').
            ($ws['status'] && $workers ? '<tr><td class="label top">����������:<td>'.$workers : '').
        '</table>'.
        '<div class="headName">������ � ����</div>'.
        '<table class="counts">'.$counts.'</table>'.
    '</div>';
}//end of sa_ws_info()