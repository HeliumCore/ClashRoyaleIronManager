<?php
/**
 * Created by PhpStorm.
 * User: LMironne
 * Date: 28/06/2018
 * Time: 18:47
 */

include("../tools/api_conf.php");
include("../tools/database.php");

$apiResult = file_get_contents("https://api.royaleapi.com/constants", true, $context);
$data = json_decode($apiResult, true);

$insertPattern = "
INSERT INTO arena
VALUES('', %d, \"%s\", \"%s\", %d)
";

foreach ($data['arenas'] as $arena) {
    $insertQuery = utf8_decode(sprintf(
        $insertPattern, $arena['arena'], $arena['subtitle'], $arena['title'], $arena['trophy_limit']
    ));
    execute_query($db, $insertQuery);
}