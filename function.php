<?php
//====================================
//      ログ設定
//====================================
// ini_set('log_errors', 'on');
// ini_set('error_log', 'php_error.log');

$debug_flg = true;

function debug($str) {
  global $debug_flg;
  if(!empty($debug_flg)) {
    error_log('デバッグ：'.$str);
  }
}

function debugLogStart() {
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理');
  debug('セッションID'.session_id());
  debug('セッション変数の中身'.print_r($_SESSION,true));
  debug('現在日時'.time());
  if(!empty($_SESSION['login_time']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ' .($_SESSION['login_time'] + $_SESSION['login_limit']) );
  }
}
//====================================
//      セッション準備・有効期限設定
//====================================
session_save_path('/var/tmp');

ini_set('session.gc_maxlifetime', 60*60*24*30);
ini_set('session.cookie_lifetime', 60*60*24*30);

session_start();

session_regenerate_id();

//====================================
//     グローバル変数
//====================================
$err_msg = array();

//====================================
//     メッセージ定数
//====================================
define('MSG01','入力必須です');
define('MSG02','email形式で入力してください');
define('MSG03','パスワード(再入力)が合っていません');
define('MSG04','半角英数字で入力してください');
define('MSG05','６文字以上で入力してください');
define('MSG06','256文字以内で入力してください');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください');
define('MSG08','そのEmailは既に登録されています');
//====================================
//     データベース接続・SQL
//====================================
//DB接続//
function dbConnect() {
    $dsn = 'mysql:dbname=feel_share;host=localhost;charset=utf8';
    $name = 'root';
    $password = 'Yuma@19860120';
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );

    $dbh = new PDO($dsn, $name, $password, $options);
    return $dbh;
}
//SQL実行//
function querypost($dbh, $sql, $data) {

    $stmt = $dbh->prepare($sql);

    if(!$stmt->execute($data)) {
      debug('クエリ失敗しました。');
      debug('失敗したSQL：'.print_r($stmt,true));
      $err_msg['common'] = MSG07;
      return 0;
    }
    return $stmt;
}

//====================================
//     バリデーション関数
//====================================
//空値チェック//
function validRequire($str, $key){
    if($str === '') {
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}
//Email形式チェック//
function validEmail($str, $key){
    if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
      global $err_msg;
      $err_msg[$key] = MSG02;
    }
}
//Email重複チェック//
function validEmailDup($email){
    global $err_msg;
    
    try {
      
      $dbh = dbConnect();
      
      $sql = 'SELECT count(*) FROM users WHERE eamil = :email AND delete_flg = 0';
      $data = array(':email' => $email);
      
      $stmt = queryPost($dbh, $sql, $data);
      
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      debug('クエリ結果：'.print_r($result,true));
      //array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
      if(!empty(array_shift($result))){
        $err_msg['email'] = MSG08;
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
//バリデーション関数（半角チェック）
function validHalf($str, $key){
    if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
      global $err_msg;
      $err_msg[$key] = MSG04;
    }
  }
//最小文字数チェック//
function validMinLen($str, $key, $min = 6){
    if(mb_strlen($str) < $min){
      global $err_msg;
      $err_msg[$key] = MSG05;
    }
  }
  //最大文字数チェック//
  function validMaxLen($str, $key, $max = 256){
    if(mb_strlen($str) > $max){
      global $err_msg;
      $err_msg[$key] = MSG06;
    }
  }
  //password同値チェック//
  function validMatch($str1, $str2, $key){
    if($str1 !== $str2){
      global $err_msg;
      $err_msg[$key] = MSG03;
    }
  }
  //エラーメッセージ表示//
  function getErrMsg($key){
    global $err_msg;
    if(!empty($err_msg[$key])){
      return $err_msg[$key];
    }
  }