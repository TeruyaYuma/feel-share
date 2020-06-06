import $ from 'jquery';

$(function(){

    //scrollMessageBox
    var $scrollAuto = $('.js-scroll-auto');
    if($scrollAuto.length > 0){
        $scrollAuto.scrollTop($scrollAuto[0].scrollHeight);
    }

    //tab
    $('.js-tab li').click(function(){

        var index = $('.js-tab li').index(this);
        
        $('.js-tab li').removeClass('isActive');
        $(this).addClass('isActive');

        $('.js-tab-content').removeClass('isNone').eq(index).addClass('isNone');
        
    });

    //table幅
    var $table = $('table'),
    $bodyCells = $table.find('tbody tr:first').children(),
    colWidth;

    colWidth = $bodyCells.map(function() {
    return $(this).width();
    }).get();

    $table.find('thead tr').children().each(function(i, v) {
    $(v).width(colWidth[i]);
    });
    
    //footer固定
    var $ftr = $('.js-footer');

    if($ftr.length > 0){
        if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
        $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;' });
        }
    }
    $('.js-click-add').on('click', function(){
        $(this).toggleClass('addClass');
    });

    // header固定
    var $win = $(window),
        $header = $('.js-header');

        if($header.length){
            var navPos = $header.offset().top;
        }
        
        $win.on('scroll', function(){
            var value = $(this).scrollTop();
            if(value > navPos){
                $header.addClass('isHeaderColor');
            } else {
                $header.removeClass('isHeaderColor');
            }
        });
    
    //tggleSpMenu
    var $jsToggleMenu = $('.js-toggle-sp-menu');
    var $jsToggleMenuTarget = $('.js-toggle-sp-menu-target');

    $jsToggleMenu.on('click', function(){
        $(this).toggleClass('active');
        $jsToggleMenuTarget.toggleClass('active');
    });
    $jsToggleMenuTarget.on('click', function(){
        $(this).removeClass('active');
        $jsToggleMenu.removeClass('active');
    });

    // toggleメッセージ
    var $jsShowMsg = $('.js-show-msg');
    var msg = $jsShowMsg.text();

    if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
        $jsShowMsg.slideToggle('slow');
        setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
    }
    //画像プレビュー
    var $dropArea = $('.js-drop-area');
    var $file = $('.js-file');
    var $areaContainer = $('.js-prev');

    $dropArea.on('dragover', function(e){
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', '3px #ccc dashed');
    });
    $dropArea.on('dragleave', function(e){
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', '3px #ccc solid');
    });

    if( $('.prev-img').attr('src') !== '' ){

        var getAttr = $('.prev-img').attr('src'),
            appendAttr = '../dist/' + getAttr;

            $('.prev-img').attr('src', appendAttr);
            $areaContainer.css('display', 'block');
    } else{

        $file.on('change', function(e){
            $dropArea.css('border', 'none');

            var imgFile = this.files[0],
                $prevArea = $('.prev-img'),
                fileReader = new FileReader();

            fileReader.onload = function(event){

                    $prevArea.attr('src', event.target.result).show();
                    $areaContainer.css('display', 'block');
            };

            fileReader.readAsDataURL(imgFile);
        });
    }
    //タグ生成//
    $(document).on('click', '.js-click-append',function(){

        var tagsLength = $('.js-input-tag').length;
            console.log(tagsLength);
            
        var tagList = '<input name="tags[]" type="text" class="input input--tag js-input-tag">';
            console.log(tagList);
            $('.js-tags').parent().append(tagList);
        
            if(tagsLength >= 2){
                $('.js-click-append').prop('disabled', true).attr('title', 'これ以上増やすことができません');
    
            }

        
    });
    
    //ajax画像取得//
    var $jsAjax = $('.js-ajaxImg');

    $jsAjax.on('click', function(e){

        e.stopPropagation();
        e.preventDefault();
    
    // メインページのモーダルをdisplay:blockに
    $('.js-modal').addClass('isOpen');
        
        $.ajax({
            type: 'GET',
            url: 'ajaxImg.php',
            dataType: 'json',
            data: { id: $(this).data('id') }

        }).then(function(data,status){
            console.log('ステータス：' + status);
            if(data){
                console.log(data);
                console.log(data.img_id);
                var imgSrc = '../dist/' + data.name;
                var href = './profile.php?u_id=' + data.user_id;

                if(data.tags.length !== 0){
                    var $tagArea = $('.js-tags-area');

                    $tagArea.empty();
                    
                    var tag = data.tags.length;
                    console.log(tag);

                    for(var $i = 0; $i < data.tags.length; $i++){
                        $tagArea.append('<span class="tag">' + data.tags[$i].name + ' </span>');
                    }

                } else {
                    $tagArea.empty();
                }

                if(data.pic === null || data.pic === ''){
                    var avatarSrc = '../dist/img/no-image.jpeg';
                } else {
                    var avatarSrc = '../dist/' + data.pic;
                }

                $('.js-firstName').text(data.first_name);
                $('.js-lastName').text(data.last_name);
                $('.js-img').attr('src', imgSrc);
                $('.js-avatar').attr('src', avatarSrc);
                $('.js-link').attr('href', href);
                $('.js-comment').text(data.comment);
                $('.js-good-cnt').text(data.likeCount);

            }

        }, function ( msg ){
            console.log(msg);
            });
        });
    
    // メインページのモーダルをdisplay:noneに
    $('.js-close').on('click',function(){
        $('.js-modal').removeClass('isOpen');
    });

    //いいね//
    var $ajaxLike = $('.js-heart');

    $ajaxLike.on('click', function(e){

        e.stopPropagation();
        e.preventDefault();

        var imgId = $(this).parent().data('id');
        
        var $this = $(this);
        $.ajax({
            type: "POST",
            url: "ajaxLike.php",
            data: {id : imgId}

        }).then(function( data ){
            console.log(data);
            console.log('AjaxSuccess');

            if(data !== 'false'){
                $this.toggleClass('active');
            } else {
                window.location.href = './login.php';
            }

        }, function( msg ){
            console.log('AjaxFails');
        });
    });
    // sp用 もっと見るボタン
    var moreNum = 4;
    $('.image .js-slideDown:nth-child(n + ' + (moreNum + 1) + ')').addClass('isHidden');

    $('.js-more').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        
        $('.image .image__item-s.isHidden').slice(0, moreNum).removeClass('isHidden');

        if ($('.image .image__item-s.isHidden').length == 0) {
        $('.js-more').fadeOut();
        }        
    });

    // フェイドイメージ
    $(function() {
         
        var imgBox = $('.js-crossFade');
        var fadeSpeed = 6000;
        var switchDelay = 8000;
         
        imgBox.find('li').hide();
        imgBox.find('li:first').stop().fadeIn(fadeSpeed);
     
        setInterval(function(){
            imgBox.find('li:first-child').fadeOut(fadeSpeed)
            .next('li').fadeIn(fadeSpeed)
            .end().appendTo(imgBox);
        },switchDelay);
         
    });

    // アイコンアニメーション
    $('.js-click-animation').on('click', function(){
        var $this = $(this);

        $this.toggleClass('far');
        $this.toggleClass('fas');
        $this.toggleClass('is-active');

        if(!$this.parent().hasClass('active')){
            $('.js-click-animation2').addClass('is-active');   
        }
    });

    
    var hasActive = function(){
        if( $('.js-heart').hasClass('active') ){
            var activeIcn = $('.js-heart.active').children('.js-click-animation');
    
            activeIcn.removeClass('far');
            activeIcn.addClass('fas');
            activeIcn.addClass('is-active');
        }
    }
    hasActive();
    
});



