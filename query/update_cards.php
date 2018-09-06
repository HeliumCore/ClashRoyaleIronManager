<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 01/07/2018
 * Time: 15:49
 */

require(__DIR__ . "/../tools/api.class.php");
require(__DIR__ . "/../tools/database.php");
require(__DIR__ . "/../models/card.class.php");

ClashRoyaleApi::create();
$card = new Card();
$card->updateAllCards();