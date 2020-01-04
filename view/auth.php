<?php
if(!empty($_SESSION['login_date'])) {

    debug('ログイン済みユーザー');

    if( $_SESSION['login_date'] + $_SESSION['login_limit'] < time() ) {
        debug('ログイン有効期限オーバー');

        session_destroy();
        header("Location:login.php");

    } else {
        debug('ログイン有効期限内');

        $_SESSION['login_date'] = time();

        if(basename($_SERVER['PHP_SELF']) === 'login.php') {
            header("Location:index.php");
        }
    }

} else {
    debug('未ログインユーザー');

    if(basename($_SERVER['PHP_SELF']) !== 'login.php') {
        header("Location:login.php");
    }
    
}