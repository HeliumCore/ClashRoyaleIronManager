<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 28/06/2018
 * Time: 23:44
 */

function getPlayerChestsByTag($tag) {
    include("../tools/api_conf.php");
    $url = sprintf("https://api.royaleapi.com/player/%s/chests", $tag);
    $apiResult = file_get_contents($url, true, $context);
    return json_decode($apiResult, true);
}