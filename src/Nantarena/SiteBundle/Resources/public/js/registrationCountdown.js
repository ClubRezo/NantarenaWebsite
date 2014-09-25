$(function(){
    // On récupère l'élément 'note' dans lequel on va inscrire des informations
    var tournamentProgress = $('#tournamentProgress');
    var tournamentCountdown = $('#tournamentCountdown');
    // Création de l'objet 'date' (année / mois / jour)
    // Attention les mois commencent à 0 !
    var ts = new Date(2014, 09, 15);

    if((new Date()) > ts){
        // L'évènement est passé, on affiche les inscriptions et on cache le compteur
        tournamentCountdown.css('display', 'none');
    }
    else {

        // On cache la progression des inscriptions et on forme le compteur
        tournamentProgress.css('display', 'none');
        $('#countdown').countdown({
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
