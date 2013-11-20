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
        '<A href="'.URL.'&p=sa&d=device">Устройства / Производители / Модели</A><BR>'.
        '<A href="'.URL.'&p=sa&d=equip">Комплектация устройств</A><BR>'.
        //'<A href="'.URL.'&p=sa&d=fault">Виды неисправностей</A><BR>'.
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
                '<td>'._viewer($r['admin_id'], 'link').
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
            $workers .= _viewer($r['viewer_id'], 'link').'<br />';
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
            '<tr><td class="label">Администратор:<td>'._viewer($ws['admin_id'], 'link').
            ($ws['status'] && $workers ? '<tr><td class="label top">Сотрудники:<td>'.$workers : '').
        '</table>'.
        '<div class="headName">Действия</div>'.
        '<div class="vkButton ws_status_change" val="'.$ws['id'].'"><button>'.($ws['status'] ? 'Деактивировать' : 'Восстановить').' мастерскую</button></div>'.
        '<br />'.
        ($ws['status'] && $ws['id'] != WS_ID ? '<div class="vkButton ws_enter" val="'.$ws['admin_id'].'"><button>Выполнить вход в эту мастерскую</button></div><br />' : '').
        '<div class="vkCancel ws_del" val="'.$ws['id'].'"><button style="color:red">Физическое удаление мастерской</button></div>'.
        '<div class="headName">Записи в базе</div>'.
        '<table class="counts">'.$counts.'</table>'.
        '<div class="headName">Счётчики</div>'.
        '<div class="vkButton ws_client_balans" val="'.$ws['id'].'"><button>Обновить балансы клиентов</button></div>'.
        '<br />'.
        '<div class="vkButton ws_zayav_balans" val="'.$ws['id'].'"><button>Обновить суммы начислений и платежей заявок</button></div>'.
    '</div>';
}//end of sa_ws_info()

function sa_device() {
    return '<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">Администрирование</a> » Устройства</div>'.
    '<script type="text/javascript">var devEquip = \''.devEquipCheck().'\';</script>'.
    '<div class="sa-device">'.
        '<div class="headName">Список устройств<a class="add">Добавить новое наименование</a></div>'.
        '<div class="spisok">'.sa_device_spisok().'</div>'.
    '</div>';
}//end of sa_device()
function sa_device_spisok() {
    $sql = "SELECT
                `bd`.`id` AS `id`,
                `bd`.`name` AS `name`,
                COUNT(`bv`.`id`) AS `vendor_count`
            FROM `base_device` AS `bd`
                LEFT JOIN `base_vendor` AS `bv`
                ON `bd`.`id`=`bv`.`device_id`
            GROUP BY `bd`.`id`
            ORDER BY `bd`.`sort`";
    $q = query($sql);
    if(!mysql_num_rows($q))
        return 'Устройств нет.';
    $devs = array();
    while($r = mysql_fetch_assoc($q))
        $devs[$r['id']] = $r;

    $sql = "SELECT
                `bd`.`id` AS `id`,
                COUNT(`bm`.`id`) AS `count`
            FROM `base_device` AS `bd`,
                 `base_model` AS `bm`
            WHERE `bd`.`id`=`bm`.`device_id`
            GROUP BY `bd`.`id`";
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $devs[$r['id']]['model_count'] = $r['count'];

    $sql = "SELECT
                `bd`.`id` AS `id`,
                COUNT(`z`.`id`) AS `count`
            FROM `base_device` AS `bd`,`zayavki` AS `z`
            WHERE `bd`.`id`=`z`.`base_device_id` AND `z`.`zayav_status`>0
            GROUP BY `bd`.`id`";
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $devs[$r['id']]['zayav_count'] = $r['count'];

    $spisok =
        '<table class="_spisok">'.
            '<tr><th class="name">Наименование устройства'.
                '<th class="ven">Кол-во<BR>производителей'.
                '<th class="mod">Кол-во<BR>моделей'.
                '<th class="zayav">Кол-во<BR>заявок'.
                '<th class="edit">'.
        '</table>'.
        '<dl class="_sort" val="base_device">';
    foreach($devs as $id => $r)
        $spisok .= '<dd val="'.$id.'">'.
            '<table class="_spisok">'.
                '<tr><td class="name"><a href="'.URL.'&p=sa&d=vendor&id='.$id.'">'.$r['name'].'</a>'.
                    '<td class="ven">'.($r['vendor_count'] ? $r['vendor_count'] : '').
                    '<td class="mod">'.(isset($r['model_count']) ? $r['model_count'] : '').
                    '<td class="zayav">'.(isset($r['zayav_count']) ? $r['zayav_count'] : '').
                    '<td class="edit">'.
                        '<div class="img_edit"></div>'.
                        ($r['vendor_count'] || isset($r['model_count'])  || isset($r['zayav_count']) ? '' : '<div class="img_del"></div>').
            '</table>';
    $spisok .= '</dl>';
    return $spisok;
}//end of sa_device_spisok()
function sa_vendor() {
    if(empty($_GET['id']) || !preg_match(REGEXP_NUMERIC, $_GET['id']))
        return 'Ошибка id. <a href="'.URL.'&p=sa&d=device">Назад</a>.';
    $device_id = intval($_GET['id']);
    $sql = "SELECT * FROM `base_device` WHERE `id`=".$device_id;
    if(!$dev = mysql_fetch_assoc(query($sql)))
        return 'Устройства id = '.$device_id.' не существует. <a href="'.URL.'&p=sa&d=device">Назад</a>.';
    return
    '<script type="text/javascript">var DEVICE_ID='.$device_id.';</script>'.
    '<div class="path">'.
        sa_cookie_back().
        '<a href="'.URL.'&p=sa">Администрирование</a> » '.
        '<a href="'.URL.'&p=sa&d=device">Устройства</a> » '.
        $dev['name'].
    '</div>'.
    '<div class="sa-vendor">'.
        '<div class="headName">Список производителей для "'.$dev['name'].'"<a class="add">Добавить</a></div>'.
        '<div class="spisok">'.sa_vendor_spisok($device_id).'</div>'.
    '</div>';
}//end of sa_vendor()
function sa_vendor_spisok($device_id) {
    $sql = "SELECT
                `bv`.`id`,
                `bv`.`name`,
                `bv`.`bold`,
                COUNT(`bm`.`id`) AS `model_count`
            FROM `base_vendor` AS `bv`
                 LEFT JOIN `base_model` AS `bm`
                 ON `bv`.`id`=`bm`.`vendor_id`
            WHERE `bv`.`device_id`=".$device_id."
            GROUP BY `bv`.`id`
            ORDER BY `bv`.`sort`";
    $q = query($sql);
    if(!mysql_num_rows($q))
        return 'Производителей нет.';

    $vens = array();
    while($r = mysql_fetch_assoc($q))
        $vens[$r['id']] = $r;

    $sql = "SELECT
                `v`.`id` AS `id`,
                COUNT(`z`.`id`) AS `count`
            FROM `base_vendor` AS `v`,
                 `zayavki` AS `z`
            WHERE `v`.`device_id`=".$device_id."
              AND `v`.`id`=`z`.`base_vendor_id`
              AND `z`.`zayav_status`>0
            GROUP BY `v`.`id`";
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $vens[$r['id']]['zayav_count'] = $r['count'];

    $spisok =
    '<table class="_spisok">'.
        '<tr><th class="name">Наименование устройства'.
            '<th class="mod">Кол-во<BR>моделей'.
            '<th class="zayav">Кол-во<BR>заявок'.
            '<th class="edit">'.
    '</table>'.
    '<dl class="_sort" val="base_vendor">';
    foreach($vens as $id => $r)
        $spisok .= '<dd val="'.$id.'">'.
            '<table class="_spisok">'.
                '<tr><td class="name'.($r['bold'] ? ' b' : '').'"><a href="'.URL.'&p=sa&d=model&vendor_id='.$id.'">'.$r['name'].'</a>'.
                    '<td class="mod">'.($r['model_count'] ? $r['model_count'] : '').
                    '<td class="zayav">'.(isset($r['zayav_count']) ? $r['zayav_count'] : '').
                    '<td class="edit">'.
                        '<div class="img_edit"></div>'.
                        ($r['model_count']  || isset($r['zayav_count']) ? '' : '<div class="img_del"></div>').
            '</table>';
    $spisok .= '</dl>';
    return $spisok;
}//end of sa_vendor_spisok()
function sa_model() {
    if(empty($_GET['vendor_id']) || !preg_match(REGEXP_NUMERIC, $_GET['vendor_id']))
        return 'Ошибка vendor_id. <a href="'.URL.'&p=sa&d=device">Назад</a>.';
    $vendor_id = intval($_GET['vendor_id']);
    $sql = "SELECT * FROM `base_vendor` WHERE `id`=".$vendor_id;
    if(!$ven = mysql_fetch_assoc(query($sql)))
        return 'Произодителя id = '.$vendor_id.' не существует. <a href="'.URL.'&p=sa&d=device">Назад</a>.';
    return
    '<script type="text/javascript">var VENDOR_ID='.$vendor_id.';</script>'.
    '<div class="path">'.
        sa_cookie_back().
        '<a href="'.URL.'&p=sa">Администрирование</a> » '.
        '<a href="'.URL.'&p=sa&d=device">Устройства</a> » '.
        '<a href="'.URL.'&p=sa&d=vendor&id='.$ven['device_id'].'">'._deviceName($ven['device_id']).'</a> » '.
        $ven['name'].
    '</div>'.
    '<div class="sa-model">'.
        '<div class="headName">Список моделей для "'._deviceName($ven['device_id']).$ven['name'].'"<a class="add">Добавить</a></div>'.
        '<div id="find"></div>'.
        '<table class="_spisok">'.
            '<tr><th>'.
                '<th class="name">Наименование модели'.
                '<th class="zayav">Кол-во<BR>заявок'.
                '<th class="edit">'.
            sa_model_spisok($vendor_id).
        '</table>'.
    '</div>';
}//end of sa_model()
function sa_model_spisok($vendor_id, $page=1, $find='') {
    $limit = 15;
    $all = query_value("SELECT COUNT(`id`) FROM `base_model` WHERE `vendor_id`=".$vendor_id.($find ? " AND `name` LIKE '%".$find."%'" : ''));
    if(!$all)
        return 'Моделей нет.';

    $start = ($page - 1) * $limit;
    $sql = "SELECT
                `m`.`id`,
                `m`.`name`,
                COUNT(`z`.`id`) AS `zayav_count`
            FROM `base_model` AS `m`
                 LEFT JOIN `zayavki` AS `z`
                 ON `m`.`id`=`z`.`base_model_id`
            WHERE `m`.`vendor_id`=".$vendor_id.
                ($find ? " AND `m`.`name` LIKE '%".$find."%'" : '')."
            GROUP BY `m`.`id`
            ORDER BY `m`.`name`
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $mods = array();
    while($r = mysql_fetch_assoc($q))
        $mods[$r['id']] = $r;

    _modelImg(array_keys($mods), 'small', 40, 40, 'fotoView');

    $send = '';
    foreach($mods as $id => $r)
        $send .= '<tr class="tr" val="'.$id.'"><td class="img">'._modelImg($id).
                       '<td class="name"><a href="'.URL.'&p=sa&d=modelInfo&id='.$id.'">'._vendorName($vendor_id).'<b>'.$r['name'].'</b></a>'.
                       '<td class="zayav">'.($r['zayav_count'] ? $r['zayav_count'] : '').
                       '<td class="edit">'.
                           '<div class="img_edit"></div>'.
                           ($r['zayav_count'] ? '' : '<div class="img_del"></div>');
    if($start + $limit < $all) {
        $c = $all - $start - $limit;
        $c = $c > $limit ? $limit : $c;
        $send .= '<tr class="tr"><td colspan="4" class="next">'.
            '<div class="ajaxNext" val="'.($page + 1).'"><span>Показать ещё '.$c.' модел'._end($c, 'ь', 'и', 'ей').'</span></div>';
    }
    return $send;
}//end of sa_model_spisok()


function sa_equip() {
    $sql = "SELECT `id`,`name` FROM `base_device` ORDER BY `sort`";
    $q = query($sql);
    $default_id = 1;
    $dev = '';
    while($r = mysql_fetch_assoc($q))
        $dev .= '<a'.($r['id'] == $default_id ? ' class="sel"' : '').' val="'.$r['id'].'">'.$r['name'].'</a>';
    return '<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">Администрирование</a> » Комплектация устройств</div>'.
    '<div class="sa-equip">'.
        '<div class="headName">Комплектация устройств<a class="add">Добавить новое наименование</a></div>'.
        '<table class="etab">'.
            '<tr><td><div class="rightLink">'.$dev.'</dev>'.
                '<td id="eq-spisok">'.sa_equip_spisok($default_id).
        '</table>'.
    '</div>';
}//end of sa_equip()
function sa_equip_spisok($device_id) {
    $equip = query_value("SELECT `equip` FROM `base_device` WHERE `id`=".$device_id);
    $arr = explode(',', $equip);
    $equip = array();
    foreach($arr as $id)
        $equip[$id] = 1;

    $spisok = '';
    if(!empty($equip)) {
        $spisok =
            '<table class="_spisok">'.
                '<tr><th class="use">'.
                    '<th class="name">Наименование'.
                    '<th class="set">Настройки'.
            '</table>'.
            '<dl class="_sort" val="setup_device_equip">';
        foreach(equipCache() as $id => $r)
            $spisok .= '<dd val="'.$id.'">'.
                '<table class="_spisok">'.
                    '<tr><td class="use">'._check('c_'.$id, '', isset($equip[$id]) ? 1 : 0).
                    '<td class="name">'.($r['title'] ? '<span title="'.$r['title'].'">'.$r['name'].'</span>' : $r['name']).
                        '<td class="set"><div class="img_edit"></div><div class="img_del"></div>'.
                '</table>';
        $spisok .= '</dl>';
    }
    return '<div class="eq-head">Используемые комплектации для <b>'._deviceName($device_id).'</b>:</div>'.
        ($spisok ? $spisok : 'Вариантов комплектаций нет');
}