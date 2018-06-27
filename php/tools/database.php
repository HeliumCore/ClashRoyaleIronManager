<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 11/06/2018
 * Time: 14:53
 */

$hostname = 'localhost';
$username = 'root';
$password = '';
$dbname = 'clashroyale';

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    echo $e->getMessage();
}