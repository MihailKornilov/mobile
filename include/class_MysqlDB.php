<?php
// name  : Класс Mysql
// version  : 2013-02-17 22:28
// author  : Mikhail V Kornilov (mihan_k@mail.ru)

class MysqlDB {
  var $host;
  var $user;
  var $pass;
  var $database;
  var $names;

  var $conn;

  function MysqlDB($alt_host = 'localhost',$alt_user = '',$alt_pass='',$alt_database='',$alt_names='latin1') {
    $this->host = $alt_host;
    $this->user = $alt_user;
    $this->pass = $alt_pass;
    $this->database = $alt_database;
    $this->names = $alt_names;
    $this->conn=mysql_connect($this->host,$this->user,$this->pass,1);
    mysql_select_db($this->database,$this->conn);
    mysql_query("set NAMES `".$this->names."`",$this->conn);
  }

  function Query($sql) {
    mysql_query($sql,$this->conn) or die($sql);
    return mysql_insert_id();
  }

  function QRow($sql) {
    $result=mysql_query($sql,$this->conn) or die($sql);
    $rez=mysql_fetch_row($result);
    return $rez[0];
  }

  function QueryRowOne($sql) {
    $result=mysql_query($sql,$this->conn) or die($sql);
    return mysql_fetch_row($result);
  }

  // создание одномерного массива (берётся только первая колонка в запросе) 2012.12.12
  function QArray($sql) {
    $result = mysql_query($sql, $this->conn) or die($sql);
    $arr = array();
    while($item = mysql_fetch_row($result)) { array_push($arr, $item[0]); }
    return $arr;
  }

  // поправка для возврата пустого массива 2013-01-21
  function QueryRowArray($sql) {
    $result = mysql_query($sql,$this->conn) or die($sql);
    $send = array();
    while($temp = mysql_fetch_row($result)) {
      array_push($send, $temp);
    }
    return $send;
  }

  function QueryPtPArray($sql) {
    $result = mysql_query($sql,$this->conn) or die($sql);
    $send = array();
    while($sp = mysql_fetch_row($result)) {
      $send[$sp[0]] = $sp[1];
    }
    return $send;
  }

  function QueryObjectOne($sql) {
    $result = mysql_query($sql,$this->conn) or die($sql);
    return mysql_fetch_object($result);
  }
  
  // замена вставки на array_push 2013.01.07
  function QueryObjectArray($sql) {
    $result = mysql_query($sql,$this->conn) or die($sql);
    $arr = array();
    while($temp = mysql_fetch_object($result)) {
      array_push($arr, $temp);
    }
    return $arr;
  }
  
  function QNumRows($sql) {
    $result=mysql_query($sql,$this->conn) or die($sql);
    return mysql_num_rows($result);
  }
  
  // ассоциативный массив
  function ptpJson($q) {
    $res = mysql_query($q, $this->conn) or die($q);
    $send = array();
    while($sp = mysql_fetch_row($res)) {
      array_push($send, $sp[0].":".(preg_match("|^[-\d]+$|", $sp[1]) ? $sp[1] : "\"".$sp[1]."\""));
    }
    return "{".implode(',',$send)."}";
  }

  // последовательный массив из чисел: "select id from table"
  function idsJson($q) {
    $res = mysql_query($q, $this->conn) or die($q);
    $send = array();
    while($sp = mysql_fetch_row($res)) {
      array_push($send, preg_match("|^[\d]+$|", $sp[0]) ? $sp[0] : "\"".$sp[0]."\"");
    }
    return "[".implode(',',$send)."]";
  }

  // последовательный список из чисел: "select id from table"
  function ids($q) {
    $res = mysql_query($q, $this->conn) or die($q);
    $send = array();
    while($sp = mysql_fetch_row($res)) {
      array_push($send, $sp[0]);
    }
    return implode(',',$send);
  }

/*
  function ptpJson($q) {
    $res = mysql_query($q, $this->conn) or die($q);
    while($sp = mysql_fetch_row($res)) {
      $send[$sp[0]] = iconv("WINDOWS-1251","UTF-8",$sp[1]);
    }
    return json_encode($send);
  }
*/

  // массив для выпадающего списка 2013-01-22
  function vkSelJson($q) {
    $send = array();
    $res = mysql_query($q, $this->conn) or die($q);
    while($sp = mysql_fetch_row($res)) {
      array_push($send, "{uid:".$sp[0].",title:\"".$sp[1]."\"}");
    }
    return "[".implode(',',$send)."]";
  }
/*
  function vkSelJson($q) {
    $send = array();
    $res = mysql_query($q, $this->conn) or die($q);
    while($sp = mysql_fetch_row($res)) {
      array_push($send, array(
        'uid' => $sp[0],
        'title' => iconv("WINDOWS-1251","UTF-8",$sp[1])
      ));
    }
    return json_encode($send);
  }
*/
}
?>

