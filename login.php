<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 03/08/18
 * Time: 10:58
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Connexion</title>
    <?php include("head.php"); ?>
    <script>
        $(document).ready(function () {
            $('#search').keydown(function (e) {
                if (e.keyCode === 32) {
                    return false;
                }
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
        });

        function login() {
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
                    console.log(data);
                    if (data === 'false') {
                        $('#loginFailed').show();
                    } else {
                        let date = new Date();
                        date.setTime(+date + (365 * 86400000));
                        document.cookie = "playerTag=" + search + ";expires=" + date.toUTCString();
                        window.location.replace("player.php?tag=".concat(search));
                    }
                }
            });
        }
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Connexion au compte</h1><br>
    <div>
        <div id="loginFailed">
            <span class="whiteShadow">Les informations sont incorrectes</span><br>
        </div>
        <span class="whiteShadow">Entrez votre tag ou votre nom de joueur :</span><br><br>
        <label for="search" class="whiteShadow">Votre tag :</label>
        <input type="text" id="search">
        <br>
        <label for="password" class="whiteShadow">Mot de passe :</label>
        <input type="password" id="password">
        <br>
        <button name="btn-login" onclick="login()">Valider</button>
    </div>
    <br><br>
</div>
<?php include("footer.html"); ?>
</body>
</html>