<?php

?>
<!DOCTYPE html>
<html lang="ja">

<?php
    $title = 'メインページ';
    require('head.php');
?>

<style>
.main{
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translateY(-50%);
    transform: translateX(-50%);
}
.fa-heart{
  font-size: 30px;
  display: block;
}
.far{
  color: #a8a8a8;
}
.fas{
  color: #e7a0c5;
}
.fav{
    z-index: 2;
    position: absolute;  
    background: #fff;
    transition: all .25s;
}
.fav2{
    z-index: 1;
    position: absolute;
}
.fav.is-active{
    animation: favPushAnimation .2s ease-out;
}
.fav2.is-active{
    animation: favMoveAnimation 10s ease-out;
}



@keyframes favPushAnimation{
  0%{
    transform: scale(.7);
  }
  100%{
    transform: scale(1);
  }
}
@keyframes favMoveAnimation{
  0%{
    z-index: 3;
    transform: translateY(0) translateX(-3px);
  }
  10%{ transform: translateY(-5px) translateX(3px); }
  20%{ transform: translateY(-10px); }
  30%{ transform: translateY(-15px); }
  40%{ transform: translateY(-20px) translateX(-3px); }
  50%{ transform: translateY(-25px); }
  60%{ transform: translateY(-30px); }
  70%{ transform: translateY(-35px) translateX(3px); }
  80%{ transform: translateY(-40px); }
  90%{ transform: translateY(-45px); }
  100%{
    transform: translateY(-50px) translateX(-3px);
    opacity: 0;
  }
}
/* @keyframes favMoveAnimation{
  0%{
    font-size: 1px;
  }
  90%{
    font-size: 16px;
    transform: translateY(-100px);
  }
  100%{
    z-index: 3;
    transform: translateY(inherit);
  }
} */
.rotate{
  width: 100px;
  height: 100px;
  background: black;
  position: absolute;
  top: 40%;
  left: 40%;
  transform: translateY(-50%);
  transform: translateX(-50%);
}
</style>
<body>
<div class="main">
<i class="far fa-heart fav is-active"></i>
<i class="fas fa-heart fav2 is-active"></i>
</div>
<div class="rotate"></div>
<script src="../dist/js/bundle.js"></script>
</body>
</html>