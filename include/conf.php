<?php
/** ������ ���������� ����������: **/
$T = getTime();    // ��������� ���������� ������� � �������������. ����� �������� �������: getTime($T)
$AUTH = null;     // ����������� ������������ � ����������
$mysql = null;       // ��������� mysql
$VK = null;           // ����� ��� �������� �� ���� ������
$vku = null;         // ������ �����e�� ������������ �� VK
$WS = null;         // ������ ����������
$PATH_FILES = null; //����������������� ������ ��� �������
$VALUES = null;  // 
$URL = null;        // ������� url ��� ������
$SA = null;           // ���������� ��������������������
define(SECRET, "RjnCrjnbyfRjczr"); // ��������� ���� � ���������� ���������� ���������

if (!isset($_GET['viewer_id'])) { $_GET['viewer_id'] = 0; } // id ������������
if (!isset($_GET['api_id'])) { $_GET['api_id'] = 0; }            // id ����������
if (!isset($_GET['auth_key'])) { $_GET['auth_key'] = ''; }  // ��� ��� �����������
if (!isset($_GET['sid'])) { $_GET['sid'] = ''; }                       // ����� ������� ������

if (!isset($_GET['start'])) { $_GET['start'] = ''; }                  // ��������� ���� � ����������
if (!isset($_GET['my_page'])) { $_GET['my_page'] = ''; }   // ��� ������� ��������
if (!isset($_GET['id'])) { $_GET['id'] = ''; }                          // �������������� ���������� � $_GET['my_page']
if (!isset($_GET['hash'])) { $_GET['hash'] = ''; }                // �������� ����������, ������� �������� � ������ ����� #

$SA[982006] = 1; // �������� ������
$SA[2170788] = 1; // �������� �������




switch($_SERVER["SERVER_NAME"]) {

// ��������� ���� � ���������
case 'vkmobile':
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  $mysql = array(
    'host' => '127.0.0.1',
    'user' => 'root',
    'pass' => '4909099',
    'database' => 'vk_mobile',
    'names' => 'cp1251'
  );
  $PATH_FILES = "c:/www/vkmobile/files/";
  $_GET['viewer_id'] = 982006;
  $_GET['api_id'] = 2031819;
  $AUTH = 1;
  break;

// ���� �� ��������
case 'mobile.nyandoma.ru':
  if($_GET['auth_key'] == md5($_GET['api_id']."_".$_GET['viewer_id']."_".SECRET)) { $AUTH = 1; }
  $mysql = array(
    'host' => 'a6460.mysql.mchost.ru',
    'user' => 'a6460_vk_mobile',
    'pass' => '4909099',
    'database' => 'a6460_vk_mobile',
    'names' => 'cp1251'
  );
  $PATH_FILES = "/home/httpd/vhosts/nyandoma.ru/subdomains/mobile/httpdocs/files/";
  break;

default: echo "�������� ����: ".$_SERVER["SERVER_NAME"]; exit(); break;
}


header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"'); // �������� ������ ����� � ie ����� �����
require_once('class_MysqlDB.php');


$VK = new MysqlDB($mysql['host'],$mysql['user'],$mysql['pass'],$mysql['database'],$mysql['names']);

$vku = $VK->QueryObjectOne("select * from vk_user where viewer_id='".$_GET['viewer_id']."' limit 1");
if (!isset($vku->viewer_id)) {
  $_GET['start'] = 1;  // �������� ������������, ���� �� ����������
} else {
  if ($vku->ws_id > 0) { $WS = $VK->QueryObjectOne("select * from workshop where status=1 and id=".$vku->ws_id); }
}




$VALUES = "viewer_id=".$_GET['viewer_id'];
$VALUES .= "&api_id=".$_GET['api_id'];
$VALUES .= "&auth_key=".$_GET['auth_key'];
$VALUES .= "&sid=".$_GET['sid'];
$URL = "http://".$_SERVER["SERVER_NAME"]."/index.php?".$VALUES;














/* ��������� ������� ������� */
function setClientBalans($client_id) {
  global $VK;
  $rashod = 0;
  $prihod = $VK->QRow("select sum(summa) from money where status=1 and client_id=".$client_id." and summa>0");
  $acc = $VK->QRow("select sum(summa) from accrual where status=1 and client_id=".$client_id);
  $balans = $prihod - $acc - $rashod;
  $VK->Query("update client set balans=".$balans." where id=".$client_id);
  return $balans;
}




/* �������������� ������ ��� �������� � ���� */
function textFormat($txt) {
  $txt = str_replace("'","&#039;",$txt);
  $txt = str_replace("<","&lt;",$txt);
  $txt = str_replace(">","&gt;",$txt);
  return str_replace("\n","<BR>",trim($txt));
}





function win1251($txt) { return iconv("UTF-8","WINDOWS-1251",$txt); }
function utf8($txt) { return iconv("WINDOWS-1251","UTF-8",$txt); }
function curTime() { return strftime("%Y-%m-%d %H:%M:%S",time()); }







function endZayavki($count) {
  $ost = $count % 10;
  $ost10 = $count / 10 % 10;

  if($ost10==1) return '��';
  else
    switch($ost) {
    case '1': return '��';
    case '2': return '��';
    case '3': return '��';
    case '4': return '��';
    default: return '��';
  }
}

$MonthFull = array(
  1 => '������',
  2 => '�������',
  3 => '�����',
  4 => '������',
  5 => '���',
  6 => '����',
  7 => '����',
  8 => '�������',
  9 => '��������',
  10 => '�������',
  11 => '������',
  12 => '�������',
  '01' => '������',
  '02' => '�������',
  '03' => '�����',
  '04' => '������',
  '05' => '���',
  '06' => '����',
  '07' => '����',
  '08' => '�������',
  '09' => '��������'
);

$MonthCut = array(
  1=>'���',
  2=>'���',
  3=>'���',
  4=>'���',
  5=>'���',
  6=>'���',
  7=>'���',
  8=>'���',
  9=>'����',
  10=>'���',
  11=>'���',
  12=>'���',
  '01'=>'���',
  '02'=>'���',
  '03'=>'���',
  '04'=>'���',
  '05'=>'���',
  '06'=>'���',
  '07'=>'���',
  '08'=>'���',
  '09'=>'���'
);


function FullData($value, $noyear = 0) {
  // 14 ������ 2010
  global $MonthFull;
  $d = explode("-",$value);
  $year = '';
  if ($noyear == 0 or date('Y') != $d[0]) { $year = " ".$d[0]; }
  return abs($d[2])." ".$MonthFull[abs($d[1])].$year;
}

function FullDataTime($value, $cut = 0) {
  // 14 ������ 2010 � 12:45
  global $MonthFull,$MonthCut;
  $arr = explode(" ",$value);
  $d = explode("-",$arr[0]);
  $t = explode(":",$arr[1]);
  return abs($d[2])." ".($cut==0?$MonthFull[$d[1]]:$MonthCut[$d[1]]).(date('Y')==$d[0]?'':' '.$d[0])." � ".$t[0].":".$t[1];
}




// ������� ����� � �������������
function getTime($start = 0) {
  $arr = explode(' ', microtime());
  return round($arr[1] + $arr[0] - $start, 3);
}


// �������� �� �����, ����� ������������ 0
function number($val) { return preg_match("|^[\d]+$|", $val) ? $val : 0; }


//GvaluesCreate();
// ����������� ����� G_values.js
function GvaluesCreate() {
  global $VK, $PATH_FILES;

  $save = "function SpisokToAss(s) { var a = []; for (var n = 0; n < s.length; a[s[n].uid] = s[n].title, n++); return a; }";

  $save .= "G.status_spisok = ".$VK->vkSelJson("select id,name from setup_zayavki_status order by id").";G.status_ass = SpisokToAss(G.status_spisok);";
  $save .= "G.status_color_ass = ".$VK->ptpJson("select id,bg from setup_zayavki_status").";";
  $save .= "G.color_spisok = ".$VK->vkSelJson("select id,name from setup_color_name order by name").";G.color_ass = SpisokToAss(G.color_spisok);";
  $save .= "G.fault_spisok = ".$VK->vkSelJson("select id,name from setup_fault order by sort").";G.fault_ass = SpisokToAss(G.fault_spisok);";
  $save .= "G.zp_name_spisok = ".$VK->vkSelJson("select id,name from setup_zp_name order by name").";G.zp_name_ass = SpisokToAss(G.zp_name_spisok);";
  $save .= "G.category_spisok = ".$VK->vkSelJson("select id,name from setup_zayavki_category order by id").";G.category_ass = SpisokToAss(G.category_spisok);";
  $save .= "G.device_status_spisok = ".$VK->vkSelJson("select id,name from setup_device_status order by sort").";G.device_status_spisok.unshift({uid:0, title:'�� ��������'});G.device_status_ass = SpisokToAss(G.device_status_spisok);";
  $save .= "G.device_place_spisok = ".$VK->vkSelJson("select id,name from setup_device_place order by sort").";G.device_place_ass = SpisokToAss(G.device_place_spisok);";

  $save .= "G.device_spisok = ".$VK->vkSelJson("select id,name from base_device order by sort").";G.device_ass = SpisokToAss(G.device_spisok);";
  $save .= "G.device_rod_spisok = ".$VK->vkSelJson("select id,name_rod from base_device order by sort").";G.device_rod_ass = SpisokToAss(G.device_rod_spisok);";
  $save .= "G.device_mn_spisok = ".$VK->vkSelJson("select id,name_mn from base_device order by sort").";G.device_mn_ass = SpisokToAss(G.device_mn_spisok);";

  $spisok = $VK->QueryObjectArray("select id,name,device_id,bold from base_vendor order by device_id,sort");
  $vendor = array();
  if (count($spisok) > 0) {
    foreach ($spisok as $sp) {
      if (!isset($vendor[$sp->device_id])) { $vendor[$sp->device_id] = array(); }
      array_push($vendor[$sp->device_id], "{uid:".$sp->id.",title:\"".$sp->name."\"".($sp->bold == 1 ? ",content:\"<B>".$sp->name."</B>\"" : '')."}");
    }
    $v = array();
    foreach ($vendor as $n => $sp) { array_push($v, $n.":[".implode(',',$vendor[$n])."]"); }
    $vendor = $v;
  }
  $save .= "G.vendor_spisok = {".implode(',',$vendor)."};";
  $save .= "G.vendor_ass = []; G.vendor_ass[0] = ''; for (var k in G.vendor_spisok) { for (var n = 0; n < G.vendor_spisok[k].length; n++) { var sp = G.vendor_spisok[k][n]; G.vendor_ass[sp.uid] = sp.title; } }";

  $spisok = $VK->QueryObjectArray("select id,name,vendor_id from base_model order by vendor_id,name");
  $model = array();
  if (count($spisok) > 0) {
    foreach ($spisok as $sp) {
      if (!isset($model[$sp->vendor_id])) { $model[$sp->vendor_id] = array(); }
      array_push($model[$sp->vendor_id], "{uid:".$sp->id.",title:\"".$sp->name."\"}");
    }
    $v = array();
    foreach ($model as $n => $sp) { array_push($v, $n.":[".implode(',',$model[$n])."]"); }
    $model = $v;
  }
  $save .= "G.model_spisok = {".implode(',',$model)."};";
  $save .= "G.model_ass = []; G.model_ass[0] = ''; for (var k in G.model_spisok) { for (var n = 0; n < G.model_spisok[k].length; n++) { var sp = G.model_spisok[k][n]; G.model_ass[sp.uid] = sp.title; } }";

  $fp = fopen($PATH_FILES."../include/G_values.js","w+");
  fwrite($fp, $save);
  fclose($fp);

  $VK->Query("update setup_global set g_values=g_values+1");
}








//GclientsCreate();
// ����������� ������ � ���������: ���� G_clients.js
function GclientsCreate() {
  global $VK, $PATH_FILES, $vku;

  $spisok = $VK->QueryObjectArray("select id,fio,telefon,zayav_count,balans from client where ws_id=".$vku->ws_id." order by id desc");
  $clients = array();
  if (count($spisok) > 0) {
    foreach ($spisok as $n =>$sp) {
      $push = "id:".$sp->id;
      $push .= ",fio:\"".$sp->fio."\"";
      if ($sp->telefon) { $push .= ",telefon:\"".$sp->telefon."\""; }
      if ($sp->zayav_count > 0) { $push .= ",count:".$sp->zayav_count; }
      array_push($clients, "{".$push."}");
    }
  }
  $save = "G.clients = [".implode(',',$clients)."];";

  $fp = fopen($PATH_FILES."../include/clients/G_clients_".$vku->ws_id.".js","w+");
  fwrite($fp, $save);
  fclose($fp);

  $VK->Query("update workshop set g_clients=g_clients+1 where id=".$vku->ws_id);
}
?>
