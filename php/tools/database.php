<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 11/06/2018
 * Time: 14:53
 */

$hostname = 'sql7.freemysqlhosting.net:3306';
$username = 'sql7244954';
$password = 'KX8acGLpt8';
$dbname = 'sql7244954';

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    echo $e->getMessage();
}

function fetch_query($db, $query)
{
    return execute_query($db, $query)->fetch();
}

function fetch_all_query($db, $query)
{
    return execute_query($db, $query)->fetchAll();
}

function execute_query($db, $query)
{
    $transaction = $db->prepare($query);
    $transaction->execute();
    return $transaction;
}