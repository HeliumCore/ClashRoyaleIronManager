<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 14:56
 */

include("../tools/api_conf.php");

$result = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC", true, $context);
$data = json_decode($result, true);

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
    foreach ($data["members"] as $player) {
        echo $player['rank'] . " " . ucfirst($player['name']) . " (" . $player['tag'] . ")" . "<br>";
    }
    ?>
</div>
</body>
</html>

