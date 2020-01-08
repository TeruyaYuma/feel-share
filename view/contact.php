<?php
//postfix導入済
//ファイアウォール、TCP/25のOutbound通信を許可済
/*
mailコマンドがなかったので [mailix] install
[rpm -qa | grep mail] で「mailx-12.4-6.el6.x86_64」mailixパッケージの確認
yum install mailxで [mailix] install
*/
//アクセス制限によりgmailに届かない???
//以降はこちらの記事参照 http://kkv.hatenablog.com/entry/2015/06/12/001436
/*
Gmailの認証をクリアするために [cyrus-sasl-plain] と [cyrus-sasl-md5] をインストール
yum -y install cyrus-sasl-plain cyrus-sasl-md5
*/

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　お問い合わせ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if(!empty($_POST)){
    
    $from = $_POST['email'];
    $subject = $_POST['subject'];
    $comment = $_POST['comment'];

    if(!empty($from) && !empty($subject) && !empty($comment)){
   
        
        mb_language("Japanese"); 
        mb_internal_encoding("UTF-8"); 
         
        
        $to = 'ky19860120@icloud.com';
         
        
        $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
         
        
        if ($result) {
          unset($_POST);
          $msg = 'メールが送信されました。';
        } else {
          $msg = 'メールの送信に失敗しました。';
        }
         
      }else{
         
        $msg = '全て入力必須です。';
      }
       
}
?>

<!DOCTYPE html>
<html lang="ja">

<?php
$title ='お問い合わせ';
require('head.php');
?>

<body>
<head></head>

<main>
<p><?php if(!empty($msg)) echo $msg; ?></p>
 
 <h1>お問合せ</h1>
 <form method="post">

   <input type="text" name="email" placeholder="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email'];?>">

   <input type="text" name="subject" placeholder="件名" value="<?php if(!empty($_POST['subject'])) echo $_POST['subject'];?>">

   <textarea name="comment" placeholder="内容"><?php if(!empty($_POST['comment'])) echo $_POST['comment'];?></textarea>

   <input type="submit" value="送信">

 </form>

</main>
</body>
</html>