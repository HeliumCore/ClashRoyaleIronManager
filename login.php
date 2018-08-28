<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 02/08/18
 * Time: 17:09
 */
include(__DIR__ . "/tools/database.php");

$bForceHttpsLogin = defined('FORCE_HTTPS_LOGIN')?FORCE_HTTPS_LOGIN:true;
if ($bForceHttpsLogin && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")) {
    header('Location: https://ironmanager.fr/login');
    exit();
}

if (session_status() == PHP_SESSION_NONE)
    session_start();

if (!empty($_SESSION['accountId'])) {
    $playerTag  = getPlayerTagByAccountId($db, $_SESSION['accountId'])['tag'];
    header('Location: /player/' . $playerTag);
}

if (!empty($_COOKIE['remember'])) {
    $playerTag = $_COOKIE['remember'];
    $accountId = getAccountInfos($db, $_COOKIE['remember'])['id'];
    $date = new DateTime();
    $time = $date->getTimestamp();
    setLastVisit($db, $accountId, $time);
    $_SESSION['accountId'] = $accountId;
    header("Location: /player/" . $playerTag);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron - Connexion</title>
    <?php include("head.php"); ?>
    <script type="text/javascript" src="/js/login.js"></script>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <h1 class="whiteShadow">Connexion</h1><br>
            <div id="loginFailed">
                <span class="whiteShadow error-message">Les informations sont incorrectes</span><br><br>
            </div>
            <div id="registerFailed">
                <span class="whiteShadow error-message">Une erreur est survenue, veuillez réessayer plus tard</span><br><br>
            </div>
            <div id="playerNotInClan">
                <span class="whiteShadow error-message">Le tag est incorrect ou ce joueur n'est pas dans le clan</span><br><br>
            </div>
            <div>
                <span class="whiteShadow">Entrez votre tag ou votre nom de joueur :</span><br><br>
                <div class="form-group">
                    <label for="search" class="whiteShadow">Votre tag :</label>
                    <input type="text" id="search" class="pull-right" maxlength="10">
                </div>
                <br>
                <div class="form-group">
                    <label for="password" class="whiteShadow">Mot de passe :</label>
                    <input type="password" id="password" class="pull-right">
                </div>
                <br>
                <button id="btn-login" class="btn btn-success pull-right" type="submit">Se connecter</button>
            </div>
        </div>
    </div>

    <br><br>
</div>
<div id="loaderDiv">
    <img id="loaderImg" src="/images/loader.gif"/>
</div>
<?php include("footer.html"); ?>
</body>
</html>