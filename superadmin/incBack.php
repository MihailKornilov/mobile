<?php
if(!isset($SA[$_GET['viewer_id']])) header("Location:".$URL); // вернуть пользователя на главную страницу, если нет прав

if (!isset($_GET['pre_id'])) { $_GET['pre_id'] = ''; }
if (!isset($_COOKIE['pre_page'])) { $_COOKIE['pre_page'] = ''; }
if (!isset($_COOKIE['pre_id'])) { $_COOKIE['pre_id'] = ''; }
if (isset($_GET['pre_page'])) {
  setcookie('pre_page', $_GET['pre_page'], time() + 2592000, '/');
  setcookie('pre_id', $_GET['pre_id'], time() + 2592000, '/');
  $_COOKIE['pre_page'] = $_GET['pre_page'];
  $_COOKIE['pre_id'] = $_GET['pre_id'];
}
echo "<A HREF='".$URL."&my_page=".$_COOKIE['pre_page']."&id=".$_COOKIE['pre_id']."'>Назад</A> » ";
?>
