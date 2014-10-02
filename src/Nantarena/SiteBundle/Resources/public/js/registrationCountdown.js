$(function(){
    var tournamentProgress = $('#tournamentProgress');
    var tournamentCountdown = $('#tournamentCountdown');

    var ts = new Date(2014, 09, 15);

    if((new Date()) > ts){
        tournamentCountdown.css('display', 'none');
    }
    else {

        tournamentProgress.css('display', 'none');
        $('div.countdown').countdown({
            timestamp	: ts,
            callback	: function(days, hours, minutes, seconds){
                var message = "";
                message += "<center>";
                message += "<p>Les inscriptions sont actuellement fermées.</p><p>Ouverture des inscriptions dans...</p>";
                message += days + " jour" + ( days == 1 ? '':'s' ) + ", ";
                message += hours + " heure" + ( hours==1 ? '':'s' ) + ", ";
                message += minutes + " minute" + ( minutes==1 ? '':'s' ) + " et ";
                message += seconds + " seconde" + ( seconds==1 ? '':'s' );
                message += "</center>";


                $('#tournamentCountdown').html(message);
            }
        });

    }

});
