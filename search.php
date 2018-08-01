<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 01/08/2018
 * Time: 17:13
 */

include(__DIR__ . "/tools/database.php");
$lastUpdated = getLastUpdated($db, "index");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron</title>
    <?php include("head.php"); ?>
    <script>
        $(document).ready(function () {
            $('#tableIndex').on('click', 'tbody td', function () {
                $("body").css("cursor", "wait");
                window.location = $(this).closest('tr').find('.linkToPlayer').attr('href');
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

            $("#valueSearched").keydown(function (e) {
                if (e.keyCode === 32) {
                    return false;
                }
            });
        });

        function launchSearch() {
            var valueSearched = document.getElementById('valueSearched').value;

            if (valueSearched !== "") {
                if (valueSearched.charAt(0) === '#') {
                    valueSearched = valueSearched.substring(1);
                }

                ajax.({
                    url: "query/ajax_check_player.php?tag="+valueSearched,
                    success: function () {
                        var date = new Date();
                        date.setTime(+ date + (365 * 86400000));
                        document.cookie = "playerTag=" + valueSearched + ";expires=" + date.toUTCString();
                        window.location.replace("player.php?tag=" + valueSearched);
                    }
                });
            }
        }
    </script>
</head>
<body>
<?php include("header.html"); ?>
<div class="container">
    <h1 class="whiteShadow">Recherche de joueur</h1><br>
    <div>
        <span class="whiteShadow">Entrez le tag, le nom ou le grade du joueur pour accéder à la page personnalisée correspondante.</span><br>
        <input type="text" placeholder="Tag" id="valueSearched">
        <button name="btLaunchSearch" onclick="launchSearch()">Valider</button><br><br>
        <span class="whiteShadow"><a href="index.php">Ou cliquez ici pour accéder au clan.<a></a></span>
    </div>

    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="images/loader.gif"/>
</div>
<div class="row text-center">
    <?php if ($lastUpdated['updated'] != null):
        $time = strtotime($lastUpdated['updated']);
        ?>
        <span class="whiteShadow">Dernière mise à jour le : <b><?php echo '' . date('d/m/Y', $time) ?></b> à <b><?php echo '' . date('H:i', $time) ?></span>
    <?php else: ?>
        <span class="whiteShadow">Nécessite une mise à jour</span>
    <?php endif; ?>
</div>
<?php include("footer.html"); ?>
</body>
</html>