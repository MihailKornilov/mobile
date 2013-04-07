<?php
function clEnd($count)
  {
  $ost=$count%10;
  $ost10=$count/10%10;

  if($ost10==1) return '��';
  else
    switch($ost)
      {
      case '1': return '';
      case '2': return '�';
      case '3': return '�';
      case '4': return '�';
      default: return '��';
      }
  }

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

setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251');
require_once('../../../include/AjaxHeader.php');

$find="where ws_id=".$vku->ws_id;
$inputChar = '';
if ($_GET['input']) {
  $input = win1251($_GET['input']);
  for ($n = 0; $n < strlen($input); $n++) {
    if (isset($char[$input[$n]])) {
      $inputChar .= $char[$input[$n]];
    }
  }
  $find.=" and (fio LIKE '%".$input."%'".(strlen($inputChar) > 0? " or fio LIKE '%".$inputChar."%'":"")." or telefon LIKE '%".$input."%')";
}

$dolg = 0;
if($_GET['dolg'] == 1) {
  $find .= " and balans<0";
  $dolg = $VK->QRow("select sum(balans)*-1 from client ".$find);
}

$cCount = $VK->QRow("select count(id) from client ".$find);
if($_GET['input'] or $_GET['dolg']==1) $fCount = "������".($cCount%10==1?'':'�')." "; else $fCount="� ���� ";
$send->result = utf8($fCount.$cCount." ������".clEnd($cCount).($_GET['dolg'] == 1 ? ".<EM>(����� ����� ����� = ".$dolg." ���.)</EM>":''));
$send->page = 0;

$send->spisok = array();
$spisok = $VK->QueryObjectArray("select * from client ".$find." order by id desc limit ".(($_GET['page']-1)*20).",20");
if (count($spisok) > 0) {
  foreach($spisok as $sp) {
    if($_GET['input']) {
      $sp->fio = preg_replace("/(".$input.")/i","<EM>\\1</EM>",$sp->fio);
      if(strlen($inputChar) > 0) { $sp->fio = preg_replace("/(".$inputChar.")/i","<EM>\\1</EM>",$sp->fio); }
      $sp->telefon = preg_replace("/(".$input.")/i","<EM>\\1</EM>",$sp->telefon);
    }
    array_push($send->spisok, array(
      'id' => $sp->id,
      'fio' => utf8($sp->fio),
      'telefon' => utf8($sp->telefon),
      'zayav_count' => $sp->zayav_count,
      'balans' => $sp->balans
    ));
  }
  if (count($spisok) == 20) {
    if ($VK->QNumRows("select id from client ".$find." order by id desc limit ".($_GET['page']*20).",20") > 0) {
      $send->page = $_GET['page'] + 1;
    }
  }
}

echo json_encode($send);
?>



