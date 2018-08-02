<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 02/08/18
 * Time: 14:55
 */

include(__DIR__ . "/database.php");

function generate_hash($password, $cost = 11)
{
    $salt = substr(base64_encode(openssl_random_pseudo_bytes(17)), 0, 22);
    $salt = str_replace("+", ".", $salt);
    $param = '$' . implode('$', array("2y", str_pad($cost, 2, "0", STR_PAD_LEFT), $salt));
    return crypt($password, $param);
}

function validate_pw($password, $hash)
{
    return crypt($password, $hash) == $hash;
}