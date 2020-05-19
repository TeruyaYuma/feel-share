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
    <header class="l-header header header--fix isHeaderColor" id="header">
        <h1><a href="./index.php" class="header__title">FEEL_SHARE</a></h1>

        <nav class="nav-menu">
            <ul class="nav-menu__menu">
                <li class="nav-menu__list-item"><a href="./index.php">ホーム</a></li>
                <?php
                    if(empty($_SESSION['user_id'])){
                ?>
                    <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">登録</a></li>
                    <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">ログイン</a></li>
                <?php
                    } else {
                ?>
                    <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">ログアウト</a></li>
                    <li class="nav-menu__list-item"><a href="./myPage.php" class="nav-menu__list-link">マイページ</a></li>
                    <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link btn btn--header">アップロード</a></li>
                <?php
                    }
                ?>
            </ul>
        </nav>

    </header>
<!-- header -->

<main id="main">
    <div class="l-form modal modal--backgroundImg">
        <div class="container container--s">

            <form method="POST" class="form mt100">

                <p><?php if(!empty($msg)) echo $msg; ?></p>
 
                <h2 class="form__title">お問合せ</h2>

                <input type="text" name="email" class="input input--form" placeholder="email" value="<?php if(!empty($_POST['email'])) echo sanitize($_POST['email']);?>">

                <input type="text" name="subject" class="input input--form" placeholder="件名" value="<?php if(!empty($_POST['subject'])) echo sanitize($_POST['subject']);?>">

                <textarea name="comment" class="txtarea txtarea--contact" placeholder="内容"><?php if(!empty($_POST['comment'])) echo sanitize($_POST['comment']);?></textarea>

                <input type="submit" class="btn btn--form" value="送信">

            </form>

        </div>
    </div>
</main>
<!-- main -->
<footer id="footer" class="l-footer js-footer">
    Copryright&copy; U
</footer>
<!-- footer -->
<script src="../dist/js/bundle.js"></script>
</body>
</html>