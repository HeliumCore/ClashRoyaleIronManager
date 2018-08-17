<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 07/08/18
 * Time: 17:02
 */

include(__DIR__ . "/tools/database.php");

if (session_status() == PHP_SESSION_NONE)
    session_start();

if (isset($_SESSION['accountId']) && !empty($_SESSION['accountId'])) {
    $accountId = intval($_SESSION['accountId']);
    $isAdmin = isAccountAdmin($db, $accountId);
}

// Si l'utilisateur n'est pas admin, on redirige
if (!$isAdmin)
    header("Location: https://ironmanager.fr/login");

$pauses = getAllPauses($db);

//TODO ajouter/calculer les status de stats de guerre pour les afficher ici directement, avoir une meilleure liste

// TODO cacher les dates de pauses passées
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Iron - Gestion du clan</title>
    <?php include("head.php"); ?>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <h1 class="whiteShadow">Gestion du clan</h1><br>
    <div class="row">
        <div class="col-md-6">
            <h3 class="whiteShadow">Absences</h3><br>
            <?php if (!$pauses): ?>
                <span class="whiteShadow">Il n'y a aucune absence programée</span>
            <?php else: ?>
                <div>
                    <?php foreach ($pauses as $playerPause): ?>
                        <div>
                            <span class="whiteShadow"><?php print $playerPause['name']; ?> :</span><br>
                            <ul>
                                <?php
                                $playerPauses = explode(',', $playerPause['pauses']);
                                sort($playerPauses);
                                $firstDay = $playerPauses[0];
                                $previousDay = $playerPauses[0];
                                if (sizeof($playerPauses) > 1):
                                    for ($i = 1; $i <= sizeof($playerPauses) - 1; $i++):
                                        if ($playerPauses[$i] - $previousDay == 86400000):
                                            $previousDay = $playerPauses[$i];
                                        endif;
                                    endfor;
                                    $lastDay = $playerPauses[sizeof($playerPauses) - 1];
                                    print '<li class="whiteShadow">Du ' . date('d/m/Y', ($firstDay / 1000)) . ' au ' . date('d/m/Y', ($lastDay / 1000)) . '</li>';
                                endif; ?>
                            </ul>
                        </div>
                        <br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>