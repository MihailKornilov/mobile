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
getValue(1,'��� �������');
getValue(2,'���');
getValue(3,'������� &#40;�x�x�&#41;');
getValue(7,'��������');
getValue(8,'���������');
getValue(9,'�������� �������');
getValue(10,'����������');
getValue(4,'��� ������');
getValue(5,'���������');
getValue(6,'������ �����������');
getValue(11,'��� �������');
getValue(12,'����������');
getValue(13,'������ ������������');
getValue(14,'�����');
getValue(15,'��������');
getValue(16,'����');
getValue(17,'Java-����������');
getValue(18,'������ ��� ���������');
getValue(28,'������� A2DP');
getValue(19,'����������');
getValue(20,'������ � ��������');
getValue(21,'�����');
getValue(22,'������������� � �����������');
getValue(23,'��� ������������');
getValue(24,'������� ������������');
getValue(31,'����� ���������');
getValue(32,'����� ��������');
getValue(25,'������� ����� &#40;���������� �������&#41;');
getValue(26,'���� ������');
getValue(27,'����������');
getValue(29,'�������� ������ � ��������');
getValue(30,'����������');

$send[0]->link = "http://market.yandex.ru/model.xml?modelid=".$_POST['yaid'];
$VK->Query("update base_model set link_yandex='".$send[0]->link."' where id=".$_POST['model_id']);

echo json_encode($send);
?>



