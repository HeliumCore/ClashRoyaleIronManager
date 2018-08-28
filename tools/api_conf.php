<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 27/06/2018
 * Time: 17:11
 */

$opts = [
    "http" => [
        "header" => "auth:" . API_KEY
    ]
];

$api = stream_context_create($opts);

function getClanFromApi($api)
{
    $query = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC", true, $api);
    return json_decode($query, true);
}

function getWarFromApi($api)
{
    $query = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC/war", true, $api);
    return json_decode($query, true);
}

function getWarLogFromApi($api)
{
    $query = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC/warlog", true, $api);
    return json_decode($query, true);
}

function getPlayerFromApi($api, $tag)
{
    $url = "https://api.royaleapi.com/player/" . $tag;
    $result = file_get_contents($url, true, $api);
    return json_decode($result, true);
}

function getPlayerChestsFromApi($api, $tag)
{
    $url = "https://api.royaleapi.com/player/" . $tag . "/chests";
    $result = file_get_contents($url, true, $api);
    return json_decode($result, true);
}

function getWarStateFromApi($api)
{
    return getWarFromApi($api)['state'];
}

function getConstantsFromApi($api)
{
    $apiResult = file_get_contents("https://api.royaleapi.com/constants", true, $api);
    return json_decode($apiResult, true);
}

function getWarBattlesFromApi($api)
{
    $query = file_get_contents("https://api.royaleapi.com/clan/9RGPL8PC/battles?type=war", true, $api);
    return json_decode($query, true);
}

function isApiRunning($api) {
    $apiResult = getClanFromApi($api);
    return ($apiResult['error'] != true && $apiResult != null);
}