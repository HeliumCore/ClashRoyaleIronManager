<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 11/06/2018
 * Time: 14:53
 */

$DBowner = "ironmanauedata";
$DBpw = "Whlilenhe1610";

try {
    $db = new PDO("mysql:dbname=ironmanauedata;host=ironmanauedata.mysql.db", $DBowner, $DBpw);
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