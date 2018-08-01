<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 01/08/2018
 * Time: 20:06
 */

if (isset($_COOKIE["playerTag"]) && !empty($_COOKIE["playerTag"]))
    header('Location: player.php?tag='.$_COOKIE["playerTag"]);
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

            if (!search.trim())
                return;

            if (search.charAt(0) === '#')
                search = search.substr(1);

            $.ajax({
                url: "query/ajax_check_player_tag.php?tag=".concat(search),
                success: function (data) {
                    if (data === 'false')
                        return;

                    let date = new Date();
                    date.setTime(+date + (365 * 86400000));
                    document.cookie = "playerTag=" + search + ";expires=" + date.toUTCString();
                    window.location.replace("player.php?tag=".concat(search));
                }
            });
        }
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Recherche de joueur</h1><br>
    <div>
        <span class="whiteShadow">Entrez le tag, le nom ou le grade du joueur pour accéder à la page personnalisée correspondante.</span><br><br>
        <input type="text" placeholder="Tag" id="search">
        <button name="btLaunchSearch" onclick="launchSearch()">Valider</button>
        <br><br>
        <span class="whiteShadow"><a href="index.php">Ou cliquez ici pour accéder au clan.<a></a></span>
    </div>

    <br>
</div>
<?php include("footer.html"); ?>
</body>
</html>