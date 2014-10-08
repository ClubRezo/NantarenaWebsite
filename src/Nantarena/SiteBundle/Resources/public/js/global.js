$(function() {

    /**
     * Affichage/Masquage du menu mobile
     */
    $('nav#header li a.menu').on('click', function() {
        $('nav#menu').slideToggle();
    });

    /**
     * Affichage/Masquage du formulaire de login
     */
    $("nav#menu .login").click(function() {
        $("nav#menu .loginbox").slideToggle(100);
        $("nav#menu .login").toggleClass("active");
        $("nav#menu .loginbox .username").focus();

        var handler = function(e) {
            if (e.keyCode == 27) {
                $("nav#menu .loginbox").slideUp(100);
                $("nav#menu .login").removeClass("active");
                $(document).off("keydown", handler);
            }
        };

        $(document).on("keydown", handler);
    })

    /**
     * Mise en route des effets bootstrap
     */
    $('.carousel').carousel();
    $('.collapse').collapse();
    $('abbr, .ttip').tooltip();

    $('.ppover').popover().on('click', function(e) {e.preventDefault(); return true;});

    /**
     * Flèche scroll-to-top
     */
    $(function() {
        $("#scrolltotop").hide();

        $(function () {
            $(window).scroll(function () {
                if ($(this).scrollTop() > 100) {
                    $('#scrolltotop').fadeIn();
                } else {
                    $('#scrolltotop').fadeOut();
                }
            });

            $('#scrolltotop a').click(function () {
                $('body,html').animate({
                    scrollTop: 0
                }, 800);
                return false;
            });
        });
    });

    datetime_init();
});

function datetime_init() {
    $('.datetimepicker').datetimepicker({
        language:  'fr',
        weekStart: 1,
        todayBtn:  1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        forceParse: 0,
        format: 'dd/mm/yyyy hh:ii',
        pickerPosition: 'bottom-left'
    });
    $('.datepicker').datetimepicker({
        language:  'fr',
        weekStart: 1,
        todayBtn:  1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2,
        forceParse: 0,
        format: 'dd/mm/yyyy',
        pickerPosition: 'bottom-left'
    });
    $('.timepicker').datetimepicker({
        language:  'fr',
        weekStart: 1,
        todayBtn:  0,
        autoclose: 1,
        todayHighlight: 1,
        startView: 1,
        minView: 0,
        maxView: 1,
        forceParse: 0,
        format: 'hh:ii',
        pickerPosition: 'bottom-left'
    });
    $('.birthdaypicker').datetimepicker({
        language:  'fr',
        startDate: '01/01/1940',
        endDate: '31/12/2010',
        weekStart: 1,
        autoclose: 1,
        startView: 4,
        minView: 2,
        forceParse: 0,
        initialDate: '01/01/1990',
        format: 'dd/mm/yyyy',
        pickerPosition: 'bottom-left'
    });
}





