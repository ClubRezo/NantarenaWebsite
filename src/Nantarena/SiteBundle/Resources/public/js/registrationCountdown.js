$(function(){
    $('div.countdown').each(function() {
        var deadline = $(this).text();
        var ts = new Date(deadline);

        $(this).text('');

        if (ts > new Date()) {
            $(this).countdown({
                timestamp: ts
            });
        }
    });
});
