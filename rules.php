<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 01/08/18
 * Time: 14:41
 */

include(__DIR__ . "/check_login.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Iron - Réglement</title>
    <?php include("head.php"); ?>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <h1 class="whiteShadow">Réglement</h1>
    <br>
    <hr/>
    <h3 class="whiteShadow">Promotions</h3>
    <p class="whiteShadow">Pour devenir Ainé, il faut participer à au moins 3 guerres.</p>
    <br>
    <p class="whiteShadow">Pour devenir Chef Adjoint, il faut avoir recruté au moins 5 joueurs qui sont devenus
        Ainés.</p>
    <br>
    <hr/>
    <h3 class="whiteShadow">Rétrogradations et Exlusions</h3>
    <br>
    <p class="whiteShadow">Les conditions de rétrogradation dépendent de la participation du joueur à la guerre.<br><br>Voici
        les conditions :</p><br>
    <li class="list-unstyled">
        <ul class="whiteShadow">- Il ne participe pas à 10 guerres consécutives ;</ul>
        <ul class="whiteShadow">- Il ne participe que partiellement aux collections (5 matchs injoués) ;</ul>
        <ul class="whiteShadow">- Il ne participe pas à 2 matchs de guerre dans la saison ;</ul>
        <ul class="whiteShadow">- Il ne participe pas à 4 matchs de guerre au total.</ul>
    </li>
    <br>
    <p class="whiteShadow">Si un joueur Ainé remplit l'une de ces conditions, il sera rétrogradé au rang de Membre. <br>Si
        un membre remplit l'une de ces conditions, il sera exclu du clan.</p>
    <br>
    <hr/>
    <br>
</div>
<?php include("footer.html"); ?>
</body>
</html>