<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 28/06/2018
 * Time: 16:08
 */

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Les membres</title>
    <link rel="stylesheet" type="text/css" href="../../css/css.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>
<?php include("header.html"); ?>
<div class="bodyIndex">
    <h1 class="pageTitle">Détails du joueur</h1>
    <br><br>
    <table id="tableIndex" class="tableIndex">
        <thead>
        <tr class="rowIndex">
            <th class="headIndex">Rang</th>
            <th class="headIndex">Tag</th>
            <th class="headIndex">Nom</th>
            <th class="headIndex">Role</th>
            <th class="headIndex">Trophée</th>
            <th class="headIndex">Arène</th>
            <th class="headIndex">Donations</th>
            <th class="headIndex">Donations reçues</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="../../res/loader.gif"/>
</div>
</body>
</html>
