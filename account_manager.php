<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 03/08/18
 * Time: 10:22
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['accountId']) || empty($_SESSION['accountId']))
    header('Location: login.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron - Gestion du compte</title>
    <?php include("head.php"); ?>
    <script>
        function changePassword() {
            let oldPass = $('#oldPass').val();
            let newPass = $('#newPass').val();

            if (!oldPass.trim() || !newPass.trim())
                return;

            $.ajax({
                type: "POST",
                url: "query/accounts/update_password.php",
                data: {
                    old: oldPass,
                    new: newPass
                },
                success: function (data) {
                    let form = $('#passwordChangeForm');
                    if (data === 'false') {
                        form.hide();
                        $('#passwordChangeFailed').show();
                    } else {
                        form.hide();
                        $('#passwordChangeSuccess').show();
                    }
                }
            });
        }
    </script>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <h1 class="whiteShadow">Gestion du compte</h1><br>
    <h3 class="whiteShadow">Changer de mot de passe</h3>
    <div>
        <div id="passwordChangeForm">
            <label class="whiteShadow" for="oldPass">
                <input type="password" id="oldPass">
            </label><br>
            <label class="whiteShadow" for="newPass">
                <input type="password" id="newPass">
            </label><br>
            <button name="btn-change-password" onclick="changePassword()">Envoyer</button>
        </div>
        <div id="passwordChangeSuccess">
            <span class="whiteShadow">Votre mot de passe a bien été modifié</span>
        </div>
        <div id="passwordChangeFailed">
            <span class="whiteShadow">Une erreur est survenue lors de la modification de votre mot de passe, veuillez reessayer plus tard</span>
        </div>
    </div>
    <br>
    <br>
    <!--                <li class="dropdown-li"><a href="index.php?reset">Changer de TAG</a></li>-->
</div>
<?php include("footer.html"); ?>
</body>
</html>