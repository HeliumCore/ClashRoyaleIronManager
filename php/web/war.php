<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 16:41
 */

include("../tools/api_conf.php");

$result = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC/war", true, $context);
$data = json_decode($result, true);

var_dump($data);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guerre en cours</title>
    <link rel="stylesheet" type="text/css" href="../../css/css.css">
</head>
<body>
<?php include("header.html"); ?>
<div class="bodyIndex">
    <h1>Liste des joueurs</h1><br>

    <?php /*
    foreach ($data as $player) {
        echo ucfirst($player["name"]) . "(".$player["tag"].") - Cartes gagnées : " . $player["cardsEarned"] . "(".$player["wins"]."/".$player["battlesPlayed"].").<br>";
    } */
    ?>
    <br>
    <button onclick="location.href = 'war.php'">Guerre</button>
</div>
</body>
</html>

