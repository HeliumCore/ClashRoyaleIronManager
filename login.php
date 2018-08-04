<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 02/08/18
 * Time: 17:09
 */

if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: https://ironmanager.fr/login');
    exit();
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId']))
    header('Location: https://ironmanager.fr/index.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron - Connexion</title>
    <?php include("head.php"); ?>
    <script>
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
                            return false;
                        }
                    });
                }
            });

            $('#btn-register').click(function () {
                launchSearch('register');
            });

            $('#btn-login').click(function () {
                launchSearch('login');
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

        function launchSearch(state) {
            let search = $('#search').val();
            let password = $('#password').val();

            if (!search.trim() || !password.trim())
                return;

            if (search.charAt(0) === '#')
                search = search.substr(1);

            if (state === 'register') {
                $.ajax({
                    url: "query/accounts/ajax_check_player_tag.php?tag=".concat(search),
                    success: function (data) {
                        if (data === 'false')
                            return;

                        createAccount(search, password);
                    }
                });
            } else if (state === 'login') {
                loginAccount(search, password);
            }
        }

        function loginAccount(search, password) {
            $.ajax({
                type: 'POST',
                url: "query/accounts/validate_account.php",
                data: {
                    tag: search,
                    password: password
                },
                success: function (data) {
                    if (data === 'false') {
                        $('#playerExists').hide();
                        $('#loginFailed').show();
                        $('#registerFailed').hide();
                    } else {
                        $('#loaderDiv').show();
                        let date = new Date();
                        date.setTime(+date + (365 * 86400000));
                        document.cookie = "playerTag=" + search + ";expires=" + date.toUTCString();
                        window.location.replace("player/".concat(search));
                    }
                }
            })
        }

        function createAccount(search, password) {
            $.ajax({
                type: 'POST',
                url: "query/accounts/create_account.php",
                data: {
                    tag: search,
                    password: password
                },
                success: function (data) {
                    if (data === 'true') {
                        let date = new Date();
                        date.setTime(+date + (365 * 86400000));
                        document.cookie = "playerTag=" + search + ";expires=" + date.toUTCString();
                        window.location.replace("player/".concat(search));
                    } else if (data === 'exists') {
                        $('#playerExists').show();
                        $('#loginFailed').hide();
                        $('#registerFailed').hide();
                    } else {
                        $('#playerExists').hide();
                        $('#loginFailed').hide();
                        $('#registerFailed').show();
                    }
                }
            })
        }
    </script>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <h1 class="whiteShadow">Connexion</h1><br>
            <div id="loginFailed">
                <span class="whiteShadow error-message">Les informations sont incorrectes</span><br><br>
            </div>
            <div id="registerFailed">
                <span class="whiteShadow error-message">Une erreur est survenue, veuillez réessayer plus tard</span><br><br>
            </div>
            <div id="playerExists">
                <span class="whiteShadow error-message">Un compte existe déjà pour ce joueur</span><br><br>
            </div>
            <div>
                <span class="whiteShadow">Entrez votre tag ou votre nom de joueur :</span><br><br>
                <div class="form-group">
                    <label for="search" class="whiteShadow">Votre tag :</label>
                    <input type="text" id="search" class="pull-right" maxlength="10">
                </div>
                <br>
                <div class="form-group">
                    <label for="password" class="whiteShadow">Mot de passe :</label>
                    <input type="password" id="password" class="pull-right">
                </div>
                <br><br>
                <button id="btn-register" class="btn btn-warning">S'enregistrer</button>
                <button id="btn-login" class="btn btn-success pull-right">Se connecter</button>
            </div>
        </div>
        <div class="col-md-3">

        </div>
    </div>

    <br><br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="/images/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>