$(document).ready(function () {
    $('.apiError').hide();

    let playerTag = $('#playerTagHidden').val();

    $.ajax({
        type: "GET",
        url: "/query/ajax/get_player_chests.php",
        data: {
            tag: playerTag
        },
        success(data) {
            if (data !== 'false') {
                $('#chestsDiv').fadeOut(function () {
                    $(this).html(data);
                    $(this).fadeIn();
                });
            }
        }
    });

    let playerId = $('#hd_playerId').val();
    $.ajax({
        type: "GET",
        url: "/query/ajax/get_player_cards_levels.php",
        data: {
            playerId: playerId
        },
        success(data) {
            if (data !== 'false') {
                let json = JSON.parse(data);
                $('.card-level-span').each(function () {
                    let key = $(this).data('card');
                    let level = "";
                    let rarity = "";
                    for (let i = 0; i < 8; i++) {
                        if (json[i]['card_key'] === key) {
                            level = json[i]['level'];
                            rarity = json[i]['rarity'];
                        }
                    }
                    if (level !== "") {
                        $(this).html("Niveau ".concat(level));
                        $(this).parent('.card-level').fadeIn();
                    }
                    if (rarity !== "") {
                        let shadow = $(this).parent().parent().children(".card-shadow");
                        switch (rarity) {
                            case "Rare":
                                shadow.addClass("rare-shadow");
                                break;
                            case "Epic":
                                shadow.addClass("epic-shadow");
                                break;
                            case "Legendary":
                                // shadow.addClass("legendary-card-shadow");
                                break;
                            default:
                                break;
                        }
                    }
                });
            }
        }
    });
});