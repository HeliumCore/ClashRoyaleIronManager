<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 01/08/18
 * Time: 15:46
 */

include(__DIR__ . "/tools/database.php");
include_once(__DIR__ . "/check_login.php");

// TODO si pas de cookie -> redirect vers la page de choix du joueur - voir si on oblige pas a avoir un compte
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Iron - Tournoi</title>
    <?php include("head.php"); ?>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <h1 class="whiteShadow">Tournoi</h1>

</div>
<?php include("footer.html"); ?>
</body>
</html>
