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
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ' .($_SESSION['login_date'] + $_SESSION['login_limit']) );
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
define('MSG01', '入力必須です');
define('MSG02', 'email形式で入力してください');
define('MSG03', 'パスワード(再入力)が合っていません');
define('MSG04', '半角英数字で入力してください');
define('MSG05', '６文字以上で入力してください');
define('MSG06', '256文字以内で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスかパスワードが間違っています');

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
      
      $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
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

//====================================
//     データベース関数
//====================================
//プロフィール取得//
function getUserData($u_id) {
  global $err_msg;

  try {

    $dbh = dbConnect();

    $sql ='SELECT first_name, last_name, email, pic, twitter_id FROM users WHERE id = :u_id AND delete_flg = 0';
    $data =array(':u_id' => $u_id);

    $stmt = querypost($dbh, $sql, $data);

    if(!empty($stmt)) {

      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      debug('クエリ結果：'. print_r($result, true));

      return $result;

    } else {
      return false;
    }

  } catch (Exeption $e) {
    error_log('エラーが発生しました：'. $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
//画像全件取得//
function getImg() {
  debug('images全件取得');
  try{
    
    $dbh = dbConnect();

    $sql = 'SELECT id, name, user_id FROM images';
    $data = array();

    $stmt = querypost($dbh, $sql, $data);

    if(!$stmt){
      return false;
    }

    return $stmt->fetchAll();

  } catch (Exeption $e) {
    error_log('エラーが発生しました：'. $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
//====================================
//     その他
//====================================
//サニタイズ//
function sanitize($str) {
  return htmlspecialchars($str, ENT_QUOTES);
}
//フォーム入力保持//
function getFormData($str, $flg = false) {
  debug('getFormData開始');
  if($flg) {
    $method = $_GET;
  }else {
    $method = $_POST;
  }
  debug('通過');
  
  global $dbFormData;

  if(!empty($dbFormData)) {
    debug('$dbFormData');
    if(!empty($err_msg[$str])) {
      //エラーがあった時
      if(isset($method[$str])) {
        return sanitize($method[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
      //エラーが無かった時
    } else {
      debug('エラー無し');
      if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }

    }
    //ユーザー情報自体が無かった時
  } else {

    if(isset($method[$str])) {
      return sanitize($method[$str]);
    }

  }
}
//画像アップロード//
function uploadImg($file, $key) {
  debug('アップロード関数開始');
  if(isset($file['error']) && is_int($file['error'])) {

    try {

      switch($file['error']) {
        case UPLOAD_ERR_OK:
        break;
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeExeption('ファイルが選択されてません');
        case UPLOAD_ERR_INI_SIZE:
          throw new RuntimeExeption('ファイルの最大サイズ超過');
        case UPLOAD_ERR_FROM_SIZE:
          throw new RuntimeExeption('ファイルが最大サイズ超過');
        default:
          throw new RuntimeExeption('その他のエラーが発生しました');
      }
      debug('エラー無し');
      $type = @exif_imagetype($file['tmp_name']);
      debug('$type:'.print_r($type, true));
      if(!in_array($type, [IMAGETYPE_PNG,IMAGETYPE_JPEG,IMAGETYPE_GIF], true)) {
        throw new RuntimeExeption('画像の形式が違います');
      }

      $path = '../src/img/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      debug('$path:'.print_r($path,true));
      if(!move_uploaded_file($file['tmp_name'],$path)) {
        throw new RuntimeExeption('ファイル保存時にエラーが発生しました');
      }

      $path = substr($path,7);

      chmod($path,0644);

      debug('正常にアップロードされました');
      return $path;

    } catch (RuntimeExeption $e) {
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}