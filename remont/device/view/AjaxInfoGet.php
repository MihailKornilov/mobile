<?php
function getValue($id,$word) {
  global $html,$send,$num;
  $pL=$word.'</span></th><td class="b-properties__value">';
  if(preg_match("|".$pL."(.*?)</td></tr>|i",$html,$arr)) {
    $val = str_replace("&#40;","(",$arr[1]);
    $val = str_replace("&#41;",")",$val);
    $send[$num]->value = iconv("WINDOWS-1251","UTF-8",$val);
    $send[$num]->id = $id;
    $num++;
  }
}

require_once('../../../include/AjaxHeader.php');

ini_set('max_execution_time',120);

$host="market.yandex.ru";

$fp = fsockopen($host, 80, $errno, $errstr, 30);
if (!$fp) {
  $send[0]->error = "$errstr ($errno)<br />\n";
} else {
  $out = "GET model-spec.xml?modelid=".$_POST['yaid']." HTTP/1.1\r\n";
  $out .= "Host: ".$host."\r\n";
  $out .= "Connection: Close\r\n\r\n";

  fwrite($fp, $out);
  $file = fopen(PATH_FILES."sockets.txt", "w");
  for($n=1;$n<12;$n++) fgets($fp, 128);
  while (!feof($fp))  fwrite($file, fgets($fp, 128));
  fclose($fp);
}

$fp=fopen(PATH_FILES."sockets.txt", "r");
while($stroka = fgets($fp,10000)) {
  $stroka = trim($stroka);
  if($stroka == '<table class="l-page l-page_layout_72-20"><tr><td class="l-page__gap"><i></i></td><td class="l-page__left">') $add=1;
  if($stroka == '</td><td class="l-page__gap-right"><i></i></td><td class="l-page__right">') break;
  if($add) $html.=iconv("UTF-8","WINDOWS-1251",$stroka);
}

$html = str_replace("(","&#40;",$html);
$html = str_replace(")","&#41;",$html);

$num=0;
$send=array();
getValue(1,'Тип корпуса');
getValue(2,'Вес');
getValue(3,'Размеры &#40;ШxВxТ&#41;');
getValue(7,'Стандарт');
getValue(8,'Платформа');
getValue(9,'Материал корпуса');
getValue(10,'Управление');
getValue(4,'Тип экрана');
getValue(5,'Диагональ');
getValue(6,'Размер изображения');
getValue(11,'Тип мелодий');
getValue(12,'Фотокамера');
getValue(13,'Запись видеороликов');
getValue(14,'Аудио');
getValue(15,'Диктофон');
getValue(16,'Игры');
getValue(17,'Java-приложения');
getValue(18,'Разъем для наушников');
getValue(28,'Профиль A2DP');
getValue(19,'Интерфейсы');
getValue(20,'Доступ в интернет');
getValue(21,'Модем');
getValue(22,'Синхронизация с компьютером');
getValue(23,'Тип аккумулятора');
getValue(24,'Емкость аккумулятора');
getValue(31,'Время разговора');
getValue(32,'Время ожидания');
getValue(25,'Громкая связь &#40;встроенный динамик&#41;');
getValue(26,'карт памяти');
getValue(27,'Автодозвон');
getValue(29,'Записная книжка в аппарате');
getValue(30,'Органайзер');

$send[0]->link = "http://market.yandex.ru/model.xml?modelid=".$_POST['yaid'];
$VK->Query("update base_model set link_yandex='".$send[0]->link."' where id=".$_POST['model_id']);

echo json_encode($send);
?>



