$(document).ready(function () {
    $('#playersTable').on('click', 'tbody td', function () {
        $("body").css("cursor", "wait");
        window.location = $(this).closest('tr').find('.linkToPlayer').attr('href');
    });

    $('#numberOfParticipant').html($('#hd_numberOfParticipants').val());
    $('#numberOfMissing').html($('#hd_numberOfMissing').val());
    $('#numberOfCollectionPlayed').html($('#hd_numberOfCollectionPlayed').val());
    $('#numberOfCollectionWon').html($('#hd_numberOfCollectionWon').val());
    $('#numberOfCardsEarned').html("&nbsp;".concat($('#hd_numberOfCardsEarned').val()));

    $('.clan-rank').each(function() {
        let pos = $(this).data('pos');
        if (pos === 1) {
            $(this).addClass("first-place");
        } else if (pos === 2) {
            $(this).addClass("second-place");
        } else if (pos === 3) {
            $(this).addClass("third-place");
        } else {
            $(this).addClass("last-place");
        }
    });

    // TODO refaire le tri et la recherche par joueur

});