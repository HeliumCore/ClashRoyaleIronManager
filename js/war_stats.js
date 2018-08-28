$(document).ready(function () {
    $('.js-player-table').each(function () {
        $(this).on('click', 'tbody td', function () {
            $("body").css("cursor", "wait");
            window.location = $(this).closest('tr').find('.linkToPlayer').attr('href');
        });
    });

    $('#tx_search').on("keyup paste", function () {
        let value = $(this).val().toLowerCase();
        const playerLine = $('.playerTr');
        if (value.length === 0) {
            playerLine.show();
            return;
        }

        playerLine.each(function () {
            if ($(this).next().val().toLowerCase().indexOf(value) < 0)
                $(this).hide();
            else
                $(this).show();
        });
    });
});