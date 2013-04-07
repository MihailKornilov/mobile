<?php
header("Content-type: text/html; charset=windows-1251");
header("Cache-Control: no-store, no-cache,must-revalidate"); 
header("Expires: ".date('r'));
require_once('conf.php');
if (!$AUTH) {
  echo "Ошибка авторизации.";
  exit();
}
?>
