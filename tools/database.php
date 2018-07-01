<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 11/06/2018
 * Time: 14:53
 */
require_once('conf.php');
try {
    $db = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASS);
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

function updatePlayerWar($db, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId)
{
    $updatePattern = "
UPDATE player_war 
SET cards_earned = %d, battle_played = %d, battle_won = %d 
WHERE player_id = %d 
AND war_id = %d
";

    execute_query($db, sprintf($updatePattern, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId));
}

function insertPlayerWar($db, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId)
{
    $insertPattern = "
INSERT INTO player_war (cards_earned, battle_played, battle_won, player_id, war_id)
VALUE (%d, %d, %d, %d, %d)
";

    execute_query($db, sprintf($insertPattern, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId));
}


function getPlayerWar($db, $playerId, $warId)
{
    $getPattern = "
SELECT cards_earned, collection_played, collection_won, battle_played, battle_won
FROM player_war
WHERE player_id = %d
AND war_id = %d
";

    return fetch_query($db, utf8_decode(sprintf($getPattern, $playerId, $warId)));
}

function getWarId($db, $war, $currentWar, $created)
{
    $insertWarPattern = "
INSERT INTO war
VALUES ('', %d, %d)
";

    $updateCurrentWarPattern = "
UPDATE war
SET created = %d,
past_war = 1
WHERE id = %d
";

    if (!is_array($war)) {
        if (!is_array($currentWar)) {
            execute_query($db, sprintf($insertWarPattern, $created, 1));
            return $db->lastInsertId();
        } else {
            execute_query($db, sprintf($updateCurrentWarPattern, $created, $currentWar['id']));
            return getWarID($db, $war, $currentWar, $created);
        }
    } else {
        return $war['id'];
    }
}

function getWar($db, $created)
{
    $getWarPattern = "
SELECT id
FROM war
WHERE created = %d
";
    return fetch_query($db, sprintf($getWarPattern, $created));
}

function getCurrentWar($db)
{
    $getCurrentWarQuery = "
SELECT id
FROM war
WHERE past_war = 0
LIMIT 1
";
    return fetch_query($db, $getCurrentWarQuery);
}

function getAllPlayersInClan($db)
{
    $getAllPlayersQuery = "
SELECT players.id, players.tag
FROM players
WHERE in_clan > 0
";

    return fetch_all_query($db, $getAllPlayersQuery);
}

function getPlayerByTag($db, $tag)
{
    $getPattern = "
SELECT players.tag
FROM players
WHERE players.in_clan = 1
AND players.tag = \"%s\"
";

    return fetch_query($db, sprintf($getPattern, utf8_decode($tag)));
}

function updatePlayer($db, $name, $rank, $trophies, $role, $expLevel, $arenaId, $donations, $donationsReceived,
                      $donationsDelta, $donationsPercent, $tag)
{
    $updatePattern = "
UPDATE players
SET players.name = \"%s\",
players.rank = %d,
players.trophies = %d,
players.role_id = %d,
players.exp_level = %d,
players.arena = %d,
players.donations = %d,
players.donations_received = %d,
players.donations_delta = %d,
players.donations_ratio= %f
WHERE players.tag = \"%s\"
";
    $query = utf8_decode(sprintf($updatePattern, $name, $rank, $trophies, getRoleIdByMachineName($db, $role), $expLevel,
        $arenaId, $donations, $donationsReceived, $donationsDelta, $donationsPercent, $tag));
    execute_query($db, $query);
}

function insertPlayer($db, $name, $tag, $rank, $trophies, $role, $expLevel, $arenaId, $donations, $donationsReceived,
                      $donationsDelta, $donationsPercent)
{
    $insertPattern = "
INSERT INTO players (players.name, tag, rank, trophies, role_id, exp_level, in_clan, arena, donations, 
donations_received, donations_delta, donations_ratio)
VALUES (\"%s\", \"%s\", %d, %d, %d, %d, %d, %d, %d, %d, %d, %f)
";
    $query = utf8_decode(sprintf($insertPattern, $name, $tag, $rank, $trophies, getRoleIdByMachineName($db, $role),
        $expLevel, 1, $arenaId, $donations, $donationsReceived, $donationsDelta, $donationsPercent));
    execute_query($db, $query);
}

function getRoleIdByMachineName($db, $machineName)
{
    $query = "
SELECT id 
FROM role 
WHERE machine_name 
LIKE \"%s\"
";
    $result = fetch_query($db, utf8_decode(sprintf($query, $machineName)));
    return $result['id'];
}

function updateMaxTrophies($db, $maxTrophies, $tag)
{
// MAX TROPHIES
    $updatePattern = "
UPDATE players
SET players.max_trophies = %d
WHERE players.tag = \"%s\" 
";
    execute_query($db, utf8_decode(sprintf($updatePattern, $maxTrophies, $tag)));
}

function insertDeck($db, $playerId, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8)
{
    $pattern = "
INSERT INTO decks (player_id, card_1, card_2, card_3, card_4, card_5, card_6, card_7, card_8, decks.current)
VALUES(%d, %d, %d, %d, %d, %d, %d, %d, %d, 1)
";
    execute_query($db, sprintf($pattern, $playerId, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8));
}

function disableAllDeck($db, $playerId)
{
    $pattern = "
UPDATE decks
SET decks.current = 0
WHERE decks.player_id = %d
";
    execute_query($db, sprintf($pattern, $playerId));
}

function enableOldDeck($db, $deckId)
{
    $pattern = "
UPDATE decks
SET decks.current = 1
WHERE decks.id = %d
";
    execute_query($db, sprintf($pattern, $deckId));
}

function getCurrentDeck($db, $deck)
{
    $getCardIdPattern = "
SELECT cards.id
FROM cards
WHERE cr_id = %d
";
    $currentDeck = [];
    foreach ($deck as $card) {
        $cardId = fetch_query($db, sprintf($getCardIdPattern, $card['id']))['id'];
        array_push($currentDeck, $cardId);
    }
    return $currentDeck;
}

function getExistingDeck($db, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8, $playerId) {
    $pattern = "
SELECT decks.id
FROM decks
WHERE card_1 = %d
AND card_2 = %d
AND card_3 = %d
AND card_4 = %d
AND card_5 = %d
AND card_6 = %d
AND card_7 = %d
AND card_8 = %d
AND player_id = 
";

    $query = sprintf($pattern, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8);
    return fetch_query($db, $query . $playerId);
}