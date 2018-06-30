<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 01/07/2018
 * Time: 01:01
 */
include("../tools/api_conf.php");
include("../tools/database.php");

if (isset($_GET['tag']) && !empty($_GET['tag'])) $getPlayerTag = $_GET['tag'];
else return;

$updatePattern = "
UPDATE players
SET players.max_trophies = %d
WHERE players.tag = \"%s\" 
";
$apiQuery = "https://api.royaleapi.com/player/" . $getPlayerTag;
$playerApiResult = json_decode(file_get_contents($apiQuery, true, $context), true);
$maxTrophies = $playerApiResult['stats']['maxTrophies'];
execute_query($db, utf8_decode(sprintf($updatePattern, $maxTrophies, $getPlayerTag)));
