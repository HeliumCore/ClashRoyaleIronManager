<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 01/08/2018
 * Time: 20:06
 */

$logout = explode("/", substr($_SERVER['REQUEST_URI'], 1))[1];

if ($logout == "logout") {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    setcookie('remember', null, -1, '/');
    session_unset();
    session_destroy();
}
header('Location: https://ironmanager.fr/login');