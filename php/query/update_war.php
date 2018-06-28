<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 28/06/18
 * Time: 10:08
 */

include("update_clan.php");

$apiResult = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC/war", true, $context);
$data = json_decode($apiResult, true);

foreach ($data['participants'] as $player) {
    
}