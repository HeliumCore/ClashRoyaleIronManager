<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 06/09/18
 * Time: 10:39
 */

function getPlayerFromNewApi($api2, $tag) {
}

function getPlayerChestsFromNewApi($tag) {
    $curl = curl_init();
    $url = "https://api.clashroyale.com/v1/players/%23" . $tag . "/upcomingchests";
    $authorization = "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImtpZCI6IjI4YTMxOGY3LTAwMDAtYTFlYi03ZmExLTJjNzQzM2M2Y2NhNSJ9.eyJpc3MiOiJzdXBlcmNlbGwiLCJhdWQiOiJzdXBlcmNlbGw6Z2FtZWFwaSIsImp0aSI6IjdhMDM0NjMyLTg0ZTktNDVmYS1hMzgzLTJjNDM5MWMyMTc5OCIsImlhdCI6MTUzNjIyNTQyMiwic3ViIjoiZGV2ZWxvcGVyL2Y5YmY3NTVmLTJiNWUtYzEzZi05NWQxLTBmMzQyNDlmZjc3ZSIsInNjb3BlcyI6WyJyb3lhbGUiXSwibGltaXRzIjpbeyJ0aWVyIjoiZGV2ZWxvcGVyL3NpbHZlciIsInR5cGUiOiJ0aHJvdHRsaW5nIn0seyJjaWRycyI6WyI4Ny45OC4xNTQuMTQ2IiwiMTg1LjExNy4zNy45OCIsIjkxLjEzNC4yNDguMjExIl0sInR5cGUiOiJjbGllbnQifV19.o_SsQBtUlsDTV5xaBlQss_K6FyK9yE5IIIVKaAMEtzcDlztpjHoiqX9hobB0CunMi8yYNQWqzv9M78dButF2SQ";
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result, true);
}

function getClanFromNewApi($api2) {
    $base = "https://api.clashroyale.com/v1/";
    $hashtag = "%23";
    $query = file_get_contents($base . "clans/" . $hashtag . "9RGPL8PC", true, $api2);
    return json_decode($query, true);
}

function getClanMembersFromNewApi($api2) {
    $base = "https://api.clashroyale.com/v1/";
    $hashtag = "%23";
    $query = file_get_contents($base . "clans/" . $hashtag . "9RGPL8PC/members", true, $api2);
    return json_decode($query, true);
}

function getClanWarlogFromNewApi($api2) {
    $base = "https://api.clashroyale.com/v1/";
    $hashtag = "%23";
    $query = file_get_contents($base . "clans/" . $hashtag . "9RGPL8PC/warlog", true, $api2);
    return json_decode($query, true);
}

function getClanWarFromNewApi($api2) {
    $base = "https://api.clashroyale.com/v1/";
    $hashtag = "%23";
    $query = file_get_contents($base . "clans/" . $hashtag . "9RGPL8PC/currentwar", true, $api2);
    return json_decode($query, true);
}

function getCardsFromNewApi($api2) {
    $base = "https://api.clashroyale.com/v1/";
    $hashtag = "%23";
    $query = file_get_contents($base . "cards", true, $api2);
    return json_decode($query, true);
}