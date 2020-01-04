import $ from 'jquery';

$(function(){
    
    $('.js-click-add').on('click', function(){
        $(this).toggleClass('addClass');
    });
});