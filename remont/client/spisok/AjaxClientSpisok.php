<?php
function clEnd($count)
  {
  $ost=$count%10;
  $ost10=$count/10%10;

  if($ost10==1) return 'ов';
  else
    switch($ost)
      {
      case '1': return '';
      case '2': return 'а';
      case '3': return 'а';
      case '4': return 'а';
      default: return 'ов';
      }
  }

$char = array(
'q' => 'й',
'w' => 'ц',
'e' => 'у',
'r' => 'к',
't' => 'е',
'y' => 'н',
'u' => 'г',
'i' => 'ш',
'o' => 'щ',
'p' => 'з',
'[' => 'х',
']' => 'ъ',
'a' => 'ф',
's' => 'ы',
'd' => 'в',
'f' => 'а',
'g' => 'п',
'h' => 'р',
'j' => 'о',
'k' => 'л',
'l' => 'д',
';' => 'ж',
"'" => 'э',
'z' => 'я',
'x' => 'ч',
'c' => 'с',
'v' => 'м',
'b' => 'и',
'n' => 'т',
'm' => 'ь',
',' => 'б',
'.' => 'ю'
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
if($_GET['input'] or $_GET['dolg']==1) $fCount = "Найден".($cCount%10==1?'':'о')." "; else $fCount="В базе ";
$send->result = utf8($fCount.$cCount." клиент".clEnd($cCount).($_GET['dolg'] == 1 ? ".<EM>(Общая сумма долга = ".$dolg." руб.)</EM>":''));
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



