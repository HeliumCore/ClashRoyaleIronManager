<?php
/**
 * Created by PhpStorm.
 * User: miron
 * Date: 29/06/2018
 * Time: 00:33
 */


function getWarState()
{
    include("../tools/api_conf.php");
    $apiResult = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC/war", true, $context);
    return json_decode($apiResult, true)['state'];
}