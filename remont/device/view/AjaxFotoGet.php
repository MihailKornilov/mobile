<?php
require_once('../../../include/AjaxHeader.php');

ini_set('max_execution_time',120);

$host="market.yandex.ru";

$fp = fsockopen($host, 80, $errno, $errstr, 30);
if (!$fp) {
  $send[0]->error = "$errstr ($errno)<br />\n";
} else {
  $out = "GET model.xml?modelid=".$_GET['yaid']." HTTP/1.1\r\n";
  $out .= "Host: ".$host."\r\n";
  $out .= "Connection: Close\r\n\r\n";

  fwrite($fp, $out);
  $file = fopen($PATH_FILES."sockets.txt", "w");
  for ($n = 1; $n < 12; $n++) { fgets($fp, 128); }
  while (!feof($fp)) { fwrite($file, fgets($fp, 128)); }
  fclose($fp);
}

$fp = fopen($PATH_FILES."sockets.txt", "r");
while($stroka = fgets($fp,10000)) {
  if($stroka = strstr($stroka,"b-model-pictures__big")) {
    $stroka = explode("b-model-prices",$stroka);
    $html = $stroka[0];
    break;
  }
}

$arr = explode('href="http://mdata',$html);
for ($n = 1; $n < count($arr); $n++) {
  $str = explode('"',$arr[$n]);
  $send[$n-1]->link = "http://mdata".$str[0];
}


echo json_encode($send);
?>



