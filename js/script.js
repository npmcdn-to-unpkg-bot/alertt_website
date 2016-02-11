$(function() {
    $('#fullpage').fullpage();
    var BV = new $.BigVideo({container: $('#page')});
    BV.init();
    BV.show('video/alertt.mp4',{ambient:true});

    //$('#next-btn').on('click', function(e) {
    //    $('#page').addClass('animated slideOutUp').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
    //        $('#next-btn').css('display','none');
    //        $('#body').css('display','block');
    //    });
    //});

    setInterval(function(){
        $('#next-btn').addClass('animated bounce').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
            $(this).removeClass('animated bounce');
        });
    },5000);

    $("#submit-button").click(function() {
        var email = $("#email").val();
        $.post('http://localhost:63342/alertt_website/emails.php', {email: email});
    });

    $('#next-btn').click(function(){
        $.fn.fullpage.moveSectionDown();
    });

});