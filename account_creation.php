<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 02/08/18
 * Time: 17:09
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron</title>
    <?php include("head.php"); ?>
    <script>
        $(document).ready(function () {
            $('#search').keydown(function (e) {
                if (e.keyCode === 32) {
                    return false;
                }
            });

            $.ajax({
                url: "query/ajax_get_players.php",
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

        function launchSearch() {
            let search = $('#search').val();
            let password = $('#password').val();

            if (!search.trim() || !password.trim())
                return;

            if (search.charAt(0) === '#')
                search = search.substr(1);

            $.ajax({
                url: "query/ajax_check_player_tag.php?tag=".concat(search),
                success: function (data) {
                    if (data === 'false')
                        return;

                    console.log("createAccount");
                    createAccount(search, password);
                }
            });
        }

        function createAccount(search, password) {
            $.ajax({
                type: 'POST',
                url: "query/create_account.php",
                data: {
                    tag: search,
                    password: password
                },
                success: function(data) {
                    console.log(data);
                    if (data === 'false')
                        return;

                    window.location.replace("player.php?tag=".concat(search));
                },
                error: function (xhr, status, error) {
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                }
            })
        }
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Création d'un compte</h1><br>
    <div>
        <span class="whiteShadow">Entrez votre tag ou votre nom de joueur :</span><br><br>
        <label for="search" class="whiteShadow">Votre tag :</label>
        <input type="text" id="search">
        <br>
        <label for="password" class="whiteShadow">Mot de passe :</label>
        <input type="password" id="password">
        <br>
        <button name="btn-create-account" onclick="launchSearch()">Valider</button>
    </div>
    <br><br>
</div>
<?php include("footer.html"); ?>
</body>
</html>