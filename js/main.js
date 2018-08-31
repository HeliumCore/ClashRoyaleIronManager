$(document).on('click','.navbar-collapse.in',function(e) {
    if( $(e.target).is('a') ) {
        $(this).collapse('hide');
    }
});

function update() {
    $('.hideOnUpdate').hide();
    let source = window.location.pathname.substr(1);
    source = source.split("/")[0];
    let url;

    switch (source) {
        case 'war_decks':
            url = "/query/update_war_decks.php";
            break;

        case 'player':
            url = "/query/update_player.php?tag=".concat($('#playerTagHidden').val());
            break;

        case 'war':
            url = "/query/update_war.php";
            break;

        case 'war_stats':
            url = "/query/update_war_stats.php";
            break;

        case 'clan':
            url = "/query/update_clan.php";
            break;

        default:
            url = "";
            $('#navbar').collapse('hide');
            break;
    }

    if (url !== "") {
        $.ajax({
            url: url,
            beforeSend: function () {
                $('#loaderDiv').show();
            },
            success: function () {
                window.location.reload(true);
            }
        });
    }
}