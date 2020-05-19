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
define('MSG10', '古いパスワードが違います');
define('MSG11', '古いパスワードと同じです');

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
      $err_msg[$key] = $max.'文字以内で入力してください';
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
//     ログイン認証
//====================================
function isLogin(){

  if( !empty($_SESSION['login_date']) ){
    debug('ログイン済みユーザーです');

    if( ($_SESSION['login_limit'] + $_SESSION['login_date']) < time() ){
      debug('セッション有効期限が切れてます');

      session_destroy();
      return false;
    }else{
      debug('セッション有効期限内です');
      return true;
    }

  }else{
    debug('未ログインユーザーです');
    return false;
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

    $sql ='SELECT id, first_name, last_name, email, pic, twitter_id FROM users WHERE id = :u_id AND delete_flg = 0';
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
function getImgList($currentMinNum = 1, $category, $sort, $span = 9) {
  debug('images全件取得');
  try{
    
    $dbh = dbConnect();
    ////件数用sql////
    $sql = 'SELECT id FROM images';
    if(!empty($category)) $sql .= ' WHERE id IN
      (SELECT it.img_id FROM tags AS t RIGHT JOIN image_tag AS it ON t.id = it.tag_id WHERE name LIKE :category)';
    if(!empty($sort)){
      switch($sort){
        case 0:
          $sql .= ' ORDER BY id ASC';
        case 1:
          $sql .= ' ORDER BY id DESC';
      }
    }

    $data = array(':category' => '%'.$category.'%');

    $stmt = querypost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レーコード
    $rst['total_page'] = ceil($rst['total'] / $span); //総ページ
    if(!$stmt){
      return false;
    }

    ////ページング用のSQL分作成////
    $sql = 'SELECT * FROM images';
    if(!empty($category)) $sql .= ' WHERE id IN
      (SELECT it.img_id FROM tags AS t RIGHT JOIN image_tag AS it ON t.id = it.tag_id WHERE name LIKE :category)';
    if(!empty($sort)){
      switch($sort){
        case 0:
          $sql .= ' ORDER BY id ASC';
        case 1:
          $sql .= ' ORDER BY id DESC';
      }
    }

    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array(':category' => '%'.$category.'%');
    debug('SQL:'. $sql);

    $stmt = querypost($dbh, $sql, $data);

    if($stmt) {
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
    }

  } catch (Exeption $e) {
    error_log('エラーが発生しました：'. $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
//ランダムイメージ取得//
function getRandomImage($limitNum = 6){
  debug('ランダムイメージを取得します。');

  try{

    $dbh = dbConnect();

    $sql = 'SELECT id FROM images';
    $data = array();

    $stmt = queryPost($dbh, $sql, $data);
    $rstCount = $stmt->rowCount();
    debug('レコードカウント：'.$rstCount);

    if($rstCount){

      $count = $rstCount - $limitNum;
      debug('オフセットベース：'.$count);

      if($count > 0){
        $offsetNum = mt_rand(0, $count);
      } else {
        $offsetNum = 0;
      }
      debug('ランダムオフセット：'.$offsetNum);

      $sql = 'SELECT id, name FROM images LIMIT '.$limitNum.' OFFSET '.$offsetNum;
      $data = array();

      $stmt = queryPost($dbh, $sql, $data);
      $rst = $stmt->fetchAll();

      if($rst){
        return $rst;
      } else {
        return false;
      }

    } else {    
      return false;
    }

  } catch(Exeption $e){
    error_log('エラーが発生しました:'. $e->getMessage());
  }
}
//ユーザー別イメージ全取得//
function getUserImages($u_id){
  debug('$u_id'.$u_id);
  
  try{

    $dbh = dbConnect();

    $sql = 'SELECT id, name FROM images WHERE user_id = :id';
    $data = array(':id' => $u_id);

    $stmt = queryPost($dbh, $sql, $data);

    $rst = $stmt->fetchAll();

    return $rst;
    
  } catch(Exeption $e) {
    error_log('エラーが発生しました。'. $e->getMessage());
    $err_msg['common'];
  }
}
//ユーザー別イメージ取得//
function getProduct($u_id, $i_id){
  debug('ユーザーID：'. $u_id);
  debug('画像ID：'. $i_id);

  try{

    $dbh = dbConnect();

    $sql = 'SELECT * FROM images WHERE user_id = :u_id AND id = :i_id';
    $data = array(':u_id' => $u_id, ':i_id' => $i_id);

    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetch(PDO::FETCH_ASSOC);

    return $rst;

  } catch (Exeption $e) {
    error_log('エラー発生：'. $e->getMessage());
  }
}
function getProductTags($i_id){
  debug('$i_id'.$i_id);

  try{

    $dbh = dbConnect();

    $sql = 'SELECT t.id, t.name FROM image_tag AS i RIGHT JOIN tags AS t ON i.tag_id = t.id WHERE i.img_id = :i_id';
    $data = array(':i_id' => $i_id);

    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();

    return $rst;

  } catch (Exeption $e) {
    error_log('エラー発生：'. $e->getMessage());
  }
}
//getMsgAndBoard//
function getMsgAndBoard($b_id){
  debug('msg情報取得');
  debug('$b_id:'.$b_id);

  try{
  debug('ボード、メッセージを取得します');

    $dbh = dbConnect();

    $sql = 'SELECT m.id AS m_id, m.user_id, board_id, comment, m.send_date, b.id, b.to_user, b.from_user, b.create_date FROM msg AS m RIGHT JOIN boards AS b ON b.id = m.board_id WHERE b.id = :b_id';
    $data = array(':b_id' => $b_id);

    $stmt = queryPost($dbh, $sql, $data);
    debug('クエリ結果：'.print_r($stmt,true));

    if($stmt){

      return $stmt->fetchAll();

    } else {

      return false;

    }

  } catch (Exeption $e) {
    error_log('エラーが発生しました:'. $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
//getMyBoardAndMsg//
function getMyBoardAndMsg($u_id){
  debug('myBoardAndMsgを取得します');
  debug('ユーザーID：'.$u_id);

  try{

    $dbh = dbConnect();

    $sql = 'SELECT * FROM boards WHERE to_user = :u_id OR from_user = :u_id';
    $data = array(':u_id' => $u_id);

    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    debug('$rst:'.print_r($rst,true));

    if(!empty($rst)){
      foreach($rst as $key => $val) {

        $dealUserIds[] = $val['to_user'];
        $dealUserIds[] = $val['from_user'];
        
        if( ($myIdKey = array_search($_SESSION['user_id'], $dealUserIds)) !== false ){
          unset($dealUserIds[$myIdKey]);
        } 
        $partnerUserId = array_shift($dealUserIds);
  
        //チャット相手の情報取得//
        if(!empty($partnerUserId)){       
          $sql = 'SELECT first_name AS p_first, last_name AS p_last FROM users WHERE id = :u_id';
          $data = array(':u_id' => $partnerUserId);

          $stmt = queryPost($dbh, $sql, $data);
          $partnerInfo = $stmt->fetch(PDO::FETCH_ASSOC);

          $rst[$key] = array_merge($rst[$key], $partnerInfo);
        }
        //msgテーブル取得//
        $sql = 'SELECT * FROM msg WHERE board_id = :b_id';
        $data = array(':b_id' => $val['id']);

        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
      
      return $rst;

    } else {
      return false;
    }

  } catch(Expetion $e) {
    error_log('エラーが発生しました');
  }
}
//isLike//
function isLike($u_id, $i_id){
  debug('isLike確認');
  debug('$u_id'.$u_id);
  debug('$i_id'.$i_id);
  if( isset($u_id) && isset($i_id) ) {
    debug('チェックします');
    $dbh = dbConnect();

    $sql = 'SELECT * FROM good WHERE user_id = :u_id AND image_id = :i_id';
    $data = array(':u_id' => $u_id, ':i_id' => $i_id);

    $stmt = queryPost($dbh, $sql, $data);

    if($stmt->rowCount()) {
      debug('true');
      return true;
    } else {
      debug('false');
      return false;
    }

  } 
}
//idを元にお気に入りの画像全取得//
function getMyLike($u_id){
  debug('お気に入りを取得します');

  try{

    $dbh = dbConnect();

    $sql = 'SELECT * FROM good AS g RIGHT JOIN images AS i ON g.image_id = i.id WHERE g.user_id = :u_id';
    $data = array(':u_id' => $u_id);

    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();

    if($rst){

      return $rst;
    } else {

      return false;
    }

  } catch(Exeption $e){
    error_log('エラーが発生しました:'. $e->getMessage());
  }
}
//====================================
//     メール
//====================================
//メール送信//
function sendMail($from, $to, $subject, $comment) {
  if(!empty($to) && !empty($subject) && !empty($comment)) {

    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    $result = mb_send_mail($to, $subject, $comment, 'From:'.$from);

    if($result) {
      debug('メールを送信しました');
    } else {
      debug('送信を失敗しました');
    }

  }
}


//====================================
//     画像
//====================================
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

      chmod($path,0644);

      $path = substr($path,7);

      debug('正常にアップロードされました');
      return $path;

    } catch (RuntimeExeption $e) {
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}
//画像表示//
function showImg($path) {
  if(empty($path)) {
    return 'img/no-image.jpeg';
  } else {
    return $path;
  }
}

//====================================
//     その他
//====================================
//カスタムフィルター//
function costumFilter($val){
  return !(is_null($val) || $val === '');
}
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
//ランダム認証キー//
function makeRandKey($length = 8) {

  $chars = '123456789abcderfhijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $key = '';

  for($i = 0; $i < $length; ++$i) {

    $key .= $chars[mt_rand(0, 61)];

  }
  return $key;
}
//ページネーション//
function pagination( $currentPageNum, $totalPageNum, $pageColNum = 5) {
  if($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
    debug('パターン１');
    $minPageNum  = $currentPageNum -4;
    $maxPageNum  = $currentPageNum;
  } elseif ($currentPageNum == ($totalPageNum - 1) && $totalPageNum >= $pageColNum){
    debug('パターン2');
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  } elseif ($currentPageNum == 2 && $totalPageNum >= $pageColNum){
    debug('パターン3');
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  } elseif ($currentPageNum == 1 && $totalPageNum >= $pageColNum){
    debug('パターン4');
    $minPageNum = $currentPageNum;
    $maxPageNum = $currentPageNum + 4;
  } elseif ($totalPageNum < $pageColNum){
    debug('パターン5');
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  } else {
    debug('パターン6');
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }
   

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';

      if($currentPageNum != 1) {
        echo '<li class="list-item"><a href="?p=1">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i){ echo 'active'; }
        echo '"><a href="?p='.$i.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.'">&gt;</a></li>';
      }

    echo '</ul>';
  echo '</div>';
}


//test//
// if(!empty($_POST)){
//   $pic = '/image';
//   $tags = ['海','川','水'];
//   $comment = '綺麗な海や川私に映った景色';

//   $dbh = connect;
  
//   if(!empty($tags)){
//     for($i = 0; $i < count($tags); $i++){
//       $sql = 'SELECT id,name FROM tags WHERE name = :name';
//       $data = array(':name' => $tags[$i]);

//       $stmt = queryPost($dbh, $sql, $data);

//       if($stmt){
//         $rst[] = $stmt->fetch(PDO::FETCH_ASSOC);
//       }
//     }

//     if(!empty($rst)){
        
//     }
//   }
  
// }