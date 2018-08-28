$(document).ready(function () {
    $('#search').keydown(function (e) {
        if (e.keyCode === 32) {
            return false;
        }
        limitText(this, 10);
    });

    $.ajax({
        url: "query/accounts/ajax_get_players.php",
        success(data) {
            let availableTags = JSON.parse(data);
            $('#search').autocomplete({
                source: availableTags,
                select: function (event, ui) {
                    let tag = ui.item.label.substr(0, 9).trim();
                    $('#search').val(tag);
                    $.ajax({
                        type: "POST",
                        url: "query/accounts/ajax_check_player_tag.php",
                        data: {
                            tag: tag
                        },
                        success(data) {
                            let btn = $('#btn-login');
                            if (data === 'false') {
                                btn.html("Créer un compte");
                                btn.addClass("btn-warning");
                                btn.removeClass("btn-success");
                            } else {
                                btn.html("Se connecter");
                                btn.addClass("btn-success");
                                btn.removeClass("btn-warning");
                            }
                        }
                    });
                    return false;
                }
            });
        }
    });

    $('#btn-login').click(function () {
        launchSearch();
    });

    $("#password").keyup(function(event) {
        if (event.keyCode === 13) {
            $("#btn-login").click();
        }
    });
});

function limitText(field, maxChar) {
    let ref = $(field);
    let val = ref.val();
    if (val.length >= maxChar) {
        ref.val(function () {
            return val.substr(0, maxChar);
        });
    }
}

function launchSearch() {
    let search = $('#search').val();
    let password = $('#password').val();

    if (!search.trim() || !password.trim())
        return;

    if (search.charAt(0) === '#')
        search = search.substr(1);

    $.ajax({
        type: "POST",
        url: "query/accounts/validate_account.php",
        data: {
            tag: search,
            password: password
        },
        success: function (data) {
            if (data === 'wrongTag') {
                $('#playerNotInClan').show();
                $('#loginFailed').hide();
                $('#registerFailed').hide();
            } else if (data === 'wrongPass') {
                $('#playerNotInClan').hide();
                $('#loginFailed').show();
                $('#registerFailed').hide();
            } else if (data === 'registerFailed') {
                $('#playerNotInClan').hide();
                $('#loginFailed').hide();
                $('#registerFailed').show();
            } else if (data === 'loginOk') {
                window.location.replace("player/".concat(search));
            } else if (data === 'registerOk') {
                window.location.replace("account_manager");
            }
        }
    });
}