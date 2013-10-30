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
    $d = empty($_COOKIE['pre_d']) ? '' :'&d='.$_COOKIE['pre_d'];
    $d1 = empty($_COOKIE['pre_d1']) ? '' :'&d1='.$_COOKIE['pre_d1'];
    $id = empty($_COOKIE['pre_id']) ? '' :'&id='.$_COOKIE['pre_id'];
    return '<a href="'.URL.'&p='.$_COOKIE['pre_p'].$d.$d1.$id.'">Назад</a> » ';
}//end of sa_cookie_back()

function sa_index() {
    $userCount = query_value("SELECT COUNT(`viewer_id`) FROM `vk_user`");
    $wsCount = query_value("SELECT COUNT(`id`) FROM `workshop`");
    return '<div class="path">'.sa_cookie_back().'Администрирование</div>'.
    '<div class="sa-index">'.
        '<div><B>Мастерские и сотрудники:</B></div>'.
        //'<A href="'.URL.'&p=sa&d=vkuser">Пользователи ('.$userCount.')</A><BR>'.
        '<A href="'.URL.'&p=sa&d=ws">Мастерские ('.$wsCount.')</A><BR>'.
        '<BR>'.
        '<div><B>Устройства и запчасти:</B></div>'.
        '<A href="'.URL.'&p=sa&d=equip">Комплектация устройств</A><BR>'.
        //'<A href="'.URL.'&p=sa&d=fault">Виды неисправностей</A><BR>'.
        '<BR>'.
        //'<A href="'.URL.'&p=sa&d=device">Устройства</A><BR>'.
        //'<A href="'.URL.'&p=sa&d=dev-spec">Характеристики устройств для информации</A><BR>'.
        //'<A href="'.URL.'&p=sa&d=dev-status">Статусы устройств в заявках</A><BR>'.
        //'<A href="'.URL.'&p=sa&d=dev-place">Местонахождения устройств в заявках</A><BR>'.
        '<BR>'.
        //'<A href="'.URL.'&p=sa&d=color">Цвета для устройств и запчастей</A><BR>'.
        '<BR>'.
        //'<A href="'.URL.'&p=sa&d=zp-name">Наименования запчастей</A><BR>'.
    '</div>';
}//end of sa_index()

function sa_ws() {
    $wsSpisok =
        '<tr><th>id'.
            '<th>Наименование'.
            '<th>Админ'.
            '<th>Дата создания';
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

    return '<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">Администрирование</a> » Мастерские</div>'.
    '<div class="sa-ws">'.
        '<div class="count">Всего <b>'.$count.'</b> мастерск'._end($count, 'ая', 'их').'.</div>'.
        '<table class="_spisok">'.$wsSpisok.'</table>'.
    '</div>';
}//end of sa_ws()
function sa_ws_tables() {//Таблицы, которые задействуются в мастерских
    return array(
        'client' => 'Клиенты',
        'zayavki' => 'Заявки',
        'accrual' => 'Начисления',
        'money' => 'Оплаты',
        'zp_avai' => 'Наличие запчастей',
        'zp_move' => 'Движения запчастей',
        'zp_zakaz' => 'Заказ запчастей',
        'history' => 'История действий',
        'reminder' => 'Задания'
    );
}//end of sa_ws_tables()
function sa_ws_info($id) {
    $sql = "SELECT * FROM `workshop` WHERE `id`=".$id;
    if(!$ws = mysql_fetch_assoc(query($sql)))
        return sa_ws();

    $counts = '';
    foreach(sa_ws_tables() as $tab => $about) {
        $c = query_value("select count(id) from ".$tab." where ws_id=".$ws['id']);
        if($c)
            $counts .= '<tr><td class="tb">'.$tab.':<td class="c">'.$c.'<td>'.$about;
    }

    $workers = '';
    if($ws['status']) {
        $sql = "SELECT * FROM `vk_user` WHERE `ws_id`=".$ws['id']." AND `viewer_id`!=".$ws['admin_id'];
        $q = query($sql);
        while($r = mysql_fetch_assoc($q))
            $workers = _viewerName($r['viewer_id'], true).'<br />';
    }

    return
    '<div class="path">'.
        sa_cookie_back().
        '<a href="'.URL.'&p=sa">Администрирование</a> » '.
        '<a href="'.URL.'&p=sa&d=ws">Мастерские</a> » '.
        $ws['org_name'].
    '</div>'.
    '<div class="sa-ws-info">'.
        '<div class="headName">Информация о мастерской</div>'.
        '<table class="tab">'.
            '<tr><td class="label">Наименование:<td><b>'.$ws['org_name'].'</b>'.
            '<tr><td class="label">Город:<td>'.$ws['city_name'].', '.$ws['country_name'].
            '<tr><td class="label">Дата создания:<td>'.FullDataTime($ws['dtime_add']).
            '<tr><td class="label">Статус:<td><div class="status'.($ws['status'] ? '' : ' off').'">'.($ws['status'] ? '' : 'не ').'активна</div>'.
            (!$ws['status'] ? '<tr><td class="label">Дата удаления:<td>'.FullDataTime($ws['dtime_del']) : '').
            '<tr><td class="label">Администратор:<td>'._viewerName($ws['admin_id'], true).
            ($ws['status'] && $workers ? '<tr><td class="label top">Сотрудники:<td>'.$workers : '').
        '</table>'.
        '<div class="headName">Действия</div>'.
        '<div class="vkButton ws_status_change" val="'.$ws['id'].'"><button>'.($ws['status'] ? 'Деактивировать' : 'Восстановить').' мастерскую</button></div>'.
        '<br />'.
        ($ws['status'] ? '<div class="vkButton ws_enter" val="'.$ws['admin_id'].'"><button>Выполнить вход в эту мастерскую</button></div><br />' : '').
        '<div class="vkCancel ws_del" val="'.$ws['id'].'"><button style="color:red">Физическое удаление мастерской</button></div>'.
        '<div class="headName">Записи в базе</div>'.
        '<table class="counts">'.$counts.'</table>'.
    '</div>';
}//end of sa_ws_info()

function sa_equip() {
    $sql = "SELECT `id`,`name` FROM `base_device` ORDER BY `sort`";
    $q = query($sql);
    $dev = '';
    while($r = mysql_fetch_assoc($q))
        $dev .= '<a'.($r['id'] == 1 ? ' class="sel"' : '').'>'.$r['name'].'</a>';
    return '<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">Администрирование</a> » Комплектация устройств</div>'.
    '<div class="sa-equip">'.
        '<div class="headName">Комплектация устройств<a class="add">Добавить новое наименование</add></div>'.
        '<table class="etab">'.
            '<tr><td><div class="rightLinks">'.$dev.'</dev>'.
                '<td id="eq-spisok">'.sa_equip_spisok().
        '</table>'.
    '</div>';
}//end of sa_equip()
function sa_equip_spisok() {
    $sql = "SELECT * FROM `setup_device_equip` ORDER BY `sort`";
    $spisok = '';
    $q = query($sql);
    if(mysql_num_rows($q)) {
        $spisok = '<table class="_spisok">'.
            '<tr><th class="use">'.
                '<th class="name">Наименование'.
                '<th class="set">Настройки'.
        '</table>';
        while($r = mysql_fetch_assoc($q))
            $spisok .= '<table class="_spisok">'.
                '<tr><td class="use">'._checkbox('c'.$r['id']).
                '<td class="name">'.($r['title'] ? '<span title="'.$r['title'].'">'.$r['name'].'</span>' : $r['name']).
                    '<td class="set"><div class="img_edit"></div><div class="img_del"></div>'.
            '</table>';
    }
    return $spisok ? $spisok : 'Вариантов комплектаций нет';
}