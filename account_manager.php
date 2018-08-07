<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 03/08/18
 * Time: 10:22
 */

if (session_status() == PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['accountId']) || empty($_SESSION['accountId']))
    header('Location: https://ironmanager.fr/login.php');

$accountId = $_SESSION['accountId'];

include(__DIR__ . "/tools/database.php");
$playerTag = getPlayerInfoByAccountId($db, $accountId);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron - Gestion du compte</title>
    <?php include("head.php"); ?>
    <script>
        let dates = [];
        let pauses = [];
        $(document).ready(function () {
            $.ajax({
                type: 'GET',
                url: '/query/ajax/get_player_pauses.php',
                success(data) {
                    if (data === 'false')
                        return;

                    dates = dates.concat(JSON.parse(data));
                    $("#datepicker").datepicker({
                        dateFormat: "@",
                        onSelect: function (dateText, inst) {
                            addOrRemoveDate(dateText);
                        },
                        beforeShowDay: function (date) {
                            let gotDate = $.inArray($.datepicker.formatDate($(this).datepicker('option', 'dateFormat'), date), dates);
                            if (gotDate >= 0) {
                                return [true, "ui-state-highlight"];
                            }
                            return [true, ""];
                        }
                    });
                }
            });
        });

        function addOrRemoveDate(date) {
            let index = $.inArray(date, dates);
            if (index >= 0)
                dates.splice(index, 1);
            else {
                if ($.inArray(date, dates) < 0)
                    dates.push(date);
            }
        }


        function selectDates() {
            $.ajax({
                type: "POST",
                url: "/query/ajax/insert_player_pause.php",
                data: {
                    dates: dates
                },
                success() {
                    window.location.reload();
                }
            })
        }

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
                    new: newPass,
                    tag: $('#playerTag').val()
                },
                success: function (data) {
                    $('#passwordChangeForm').hide();
                    if (data === 'false') {
                        $('#passwordChangeFailed').show();
                        $('#passwordChangeSuccess').hide();
                    } else {
                        $('#passwordChangeSuccess').show();
                        $('#passwordChangeFailed').hide();
                    }
                }
            });
        }
    </script>
</head>
<body>
<?php include("header.php"); ?>
<input type="hidden" id="playerTag" value="<?php print $playerTag; ?>"/>
<div class="container">
    <h1 class="whiteShadow">Gestion du compte</h1><br>
    <div class="row">
        <div class="col-md-7">
            <h3 class="whiteShadow">Changer de mot de passe</h3><br>
            <div>
                <div id="passwordChangeSuccess">
                    <span class="whiteShadow">Votre mot de passe a bien été modifié</span><br><br>
                </div>
                <div id="passwordChangeFailed">
                    <span class="whiteShadow error-message">Une erreur est survenue lors de la modification de votre mot de passe, veuillez réessayer plus tard</span><br><br>
                </div>
                <div id="passwordChangeForm">
                    <div class="form-group">
                        <label class="whiteShadow" for="oldPass">Ancien mot de passe :</label>
                        <input type="password" id="oldPass" class="pull-right">
                    </div>
                    <div class="form-group">
                        <label class="whiteShadow" for="newPass">Nouveau mot de passe :</label>
                        <input type="password" id="newPass" class="pull-right">
                    </div>
                    <button name="btn-change-password" onclick="changePassword()" class="btn btn-success pull-right">
                        Envoyer
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-md-offset-2">
            <h3 class="whiteShadow">Absences</h3><br>
            <div>
                <div class="form-group">
                    <label class="whiteShadow" for="datepicker">Choisir des dates :</label>
                    <!--                    TODO refaire le design du calendrier -->
                    <div id="datepicker"></div>
                </div>
                <button name="btn-date" onclick="selectDates()" class="btn btn-success">Envoyer</button>
            </div>
        </div>
    </div>
    <br>
    <br>
</div>
<?php include("footer.html"); ?>
</body>
</html>