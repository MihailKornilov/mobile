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
    return '<a href="'.URL.'&p='.$_COOKIE['pre_p'].$d.$d1.$id.'">Назад</a> » ';
}//end of sa_cookie_back()

function sa_index() {
    $userCount = query_value("SELECT COUNT(`viewer_id`) FROM `vk_user`");
    $wsCount = query_value("SELECT COUNT(`id`) FROM `workshop`");
    return '<DIV class="path">'.sa_cookie_back().'Администрирование</DIV>'.
    '<DIV class="sa-index">'.
        '<DIV><B>Мастерские и сотрудники:</B></DIV>'.
        '<A href="'.URL.'&p=sa&d=vkuser">Пользователи ('.$userCount.')</A><BR>'.
        '<A href="'.URL.'&p=sa&d=ws">Мастерские ('.$wsCount.')</A><BR>'.
        '<BR>'.
        '<DIV><B>Устройства и запчасти:</B></DIV>'.
        '<A href="'.URL.'&p=sa&d=fault">Виды неисправностей</A><BR>'.
        '<BR>'.
        '<A href="'.URL.'&p=sa&d=device">Устройства</A><BR>'.
        '<A href="'.URL.'&p=sa&d=dev-spec">Характеристики устройств для информации</A><BR>'.
        '<A href="'.URL.'&p=sa&d=dev-status">Статусы устройств в заявках</A><BR>'.
        '<A href="'.URL.'&p=sa&d=dev-place">Местонахождения устройств в заявках</A><BR>'.
        '<BR>'.
        '<A href="'.URL.'&p=sa&d=color">Цвета для устройств и запчастей</A><BR>'.
        '<BR>'.
        '<A href="'.URL.'&p=sa&d=zp-name">Наименования запчастей</A><BR>'.
    '</DIV>';
}//end of sa_index()