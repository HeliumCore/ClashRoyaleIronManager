<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 14:56
 */

include("../tools/api_conf.php");
include("../tools/database.php");
include ("../query/update_clan.php");

/*Data to get
-> name
-> tag
-> rank
-> previousRank
-> role
-> expLevel
-> trophies
-> donations
-> donationsDelta
-> arena
-> donationsPercent
*/

/*$getPlayer = "
SELECT players.tag, players.name, players.rank, players.trophies, role.name, players.exp_level, arena.name, players.donations, players.donations_received, players.donations_delta, players.donations_ratio
FROM players
INNER JOIN role ON role.id = players.role_id
INNER JOIN arena ON arena.id = players.arena
WHERE players.in_clan = 1
";*/

$tag = [];
$name = [];
$rank = [];
$trophies = [];
$clanRank = [];
$level = [];
$arena = [];
$donations = [];
$donationsReceived = [];
$donationsDelta = [];
$donationsRatio = [];
$counter = 0;

$getPlayer = "
SELECT players.tag, players.name as playerName, players.rank, players.trophies, role.name as playerRole, players.exp_level, players.arena, players.donations, players.donations_received, players.donations_delta, players.donations_ratio
FROM players
INNER JOIN role ON role.id = players.role_id
WHERE players.in_clan = 1
";

$query = sprintf($getPlayer);
$getPlayerRequest = $db->prepare($query);
$getPlayerRequest->execute();

foreach ($getPlayerRequest as $player) {
    $tag[$counter] = $player['tag'];
    $name[$counter] = utf8_decode($player['playerName']);
    $rank[$counter] = $player['rank'];
    $trophies[$counter] = $player['trophies'];
    $clanRank[$counter] = utf8_decode($player['playerRole']);
    $level[$counter] = $player['exp_level'];
    $arena[$counter] = utf8_decode($player['arena']);
    $donations[$counter] = $player['donations'];
    $donationsReceived[$counter] = $player['donations_received'];
    $donationsDelta[$counter] = $player['donations_delta'];
    $donationsRatio[$counter] = $player['donations_ratio'];
    $counter++;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Les membres</title>
    <link rel="stylesheet" type="text/css" href="../../css/css.css">
</head>
<body>
<?php include("header.html"); ?>
<div class="bodyIndex">
    <h1>Liste des joueurs</h1><br>

    <?php
    $playerNumber = 0;
    foreach ($name as $playerName) {
        echo($playerName . " (" . $tag[$playerNumber] . "), ". $clanRank[$playerNumber] . " - Rang : " . $rank[$playerNumber] . " (" . $trophies[$playerNumber] . " trophées). <br>");
        //$level[$playerNumber] = $player['exp_level'];
        //$arena[$playerNumber] = $player['arena'];
        //$donations[$playerNumber] = $player['donations'];
        //$donationsReceived[$playerNumber] = $player['donations_received'];
        //$donationsDelta[$playerNumber] = $player['donations_delta'];
        //$donationsRatio[$playerNumber] = $player['donations_ratio'];
        $playerNumber++;
    }
    ?>
</div>
</body>
</html>

