<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 01/08/2018
 * Time: 20:06
 */
$logout = explode("/", substr($_SERVER['REQUEST_URI'], 1))[1];
if ($logout == "logout") {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    setcookie('remember', '', 1);
    session_unset();
    session_destroy();
}

include(__DIR__ . "/check_login.php");

if (isset($_COOKIE["playerTag"]) && !empty($_COOKIE["playerTag"]))
    header('Location: https://ironmanager.fr/player/' . $_COOKIE["playerTag"]);
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

        function launchSearch() {
            let search = $('#search').val();

            if (!search.trim())
                return;

            if (search.charAt(0) === '#')
                search = search.substr(1);

            $.ajax({
                url: "query/accounts/ajax_check_player_tag.php?tag=".concat(search),
                success: function (data) {
                    if (data === 'false')
                        return;

                    let date = new Date();
                    date.setTime(+date + (365 * 86400000));
                    document.cookie = "playerTag=" + search + ";expires=" + date.toUTCString();
                    window.location.replace("https://ironmanager.fr/player/".concat(search));
                }
            });
        }
    </script>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <h1 class="whiteShadow">Recherche de joueur</h1><br>
    <div class="row">
        <div class="col-md-5">
            <span class="whiteShadow">Entrez le tag ou le nom du joueur pour personnaliser votre page d'accueil.</span>
            <br><br>
            <div>
                <div class="form-group">
                    <label for="search" class="whiteShadow">Votre tag :</label>
                    <input type="text" id="search" class="pull-right">
                </div>
                <button name="btLaunchSearch" onclick="launchSearch()" class="btn btn-success pull-right">Valider</button>
            </div>
        </div>
        <div class="col-md-4 col-md-offset-2">
            <span class="whiteShadow"><a href="https://ironmanager.fr/clan">Cliquez ici pour accéder au clan.</a></span>
            <br><br>
            <span class="whiteShadow"><a href="https://ironmanager.fr/login">Cliquez ici pour vous connecter à votre compte</a></span>
        </div>
    </div>
    <br>
    <br>
</div>
<?php include("footer.html"); ?>
</body>
</html>