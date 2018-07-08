<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 11/06/2018
 * Time: 14:53
 */
require_once('conf.php');
try {
    $db = new PDO('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);
} catch (PDOException $e) {
    echo $e->getMessage();
}

// ----------------- SQL -----------------
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

// ----------------- PLAYER WAR -----------------
function updatePlayerWar($db, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId)
{
    $pattern = "
UPDATE player_war 
SET cards_earned = %d, battle_played = %d, battle_won = %d 
WHERE player_id = %d 
AND war_id = %d
";

    execute_query($db, sprintf($pattern, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId));
}

function insertPlayerWar($db, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId)
{
    $pattern = "
INSERT INTO player_war (cards_earned, battle_played, battle_won, player_id, war_id)
VALUE (%d, %d, %d, %d, %d)
";

    execute_query($db, sprintf($pattern, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId));
}

function getPlayerWar($db, $playerId, $warId)
{
    $pattern = "
SELECT player_war.id as player_war_id, cards_earned, collection_played, collection_won, battle_played, battle_won
FROM player_war
WHERE player_id = %d
AND war_id = %d
";

    return fetch_query($db, sprintf($pattern, $playerId, $warId));
}

// ----------------- WAR -----------------
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
            execute_query($db, sprintf($updateCurrentWarPattern, $created, intval($currentWar['id'])));
            return getWarID($db, getWar($db, $created), $currentWar, $created);
        }
    } else {
        return intval($war['id']);
    }
}

function getWar($db, $created)
{
    $pattern = "
SELECT id
FROM war
WHERE created = %d
";
    return fetch_query($db, sprintf($pattern, $created));
}

function getCurrentWar($db)
{
    $query = "
SELECT id
FROM war
WHERE past_war = 0
LIMIT 1
";
    return fetch_query($db, $query);
}

function getLastWarEndDate($db)
{
    $query = "
SELECT id, created
FROM war
WHERE past_war = 1
ORDER BY id DESC
LIMIT 1
";
    return fetch_query($db, $query);
}

function insertNewWar($db)
{
    $query = "
INSERT INTO war
VALUES ('', 0, 0)
";
    execute_query($db, $query);
}

function getCurrentWarId($db)
{
    $currentWar = 0;
    if (isWarStarted($db))
        $currentWar = getCurrentWar($db);

    if (is_array($currentWar)) {
        return intval($currentWar['id']);
    } else {
        insertNewWar($db);
        return $db->lastInsertId();
    }
}

function getNumberOfCurrentPlayersInWar($db)
{
    $query = "
SELECT COUNT(player_war.id) as numberOfCurrentPlayers
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE war.past_war = 0
";

    return intval(fetch_query($db, $query)['numberOfCurrentPlayers']);
}

function isWarStarted($db)
{
    $query = "
SELECT COUNT(player_war.id) as numberOfCurrentPlayers
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE war.past_war = 0
";
    return fetch_query($db, $query);
}

function insertCollectionDay($db, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId)
{
    $pattern = "
INSERT INTO player_war (cards_earned, collection_played, collection_won, player_id, war_id)
VALUES (%d, %d, %d, %d, %d)
";
    execute_query($db, sprintf($pattern, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId));

}

function updateWarDay($db, $battlesPlayed, $wins, $id)
{
    $pattern = "
UPDATE player_war
SET battle_played = %d, battle_won = %d
WHERE id = %d
";
    execute_query($db, sprintf($pattern, $battlesPlayed, $wins, $id));
}

function updateCollectionDay($db, $cardsEarned, $battlesPlayed, $wins, $id)
{
    $pattern = "
UPDATE player_war
SET cards_earned = %d, collection_played = %d, collection_won = %d
WHERE id = %d
";
    execute_query($db, sprintf($pattern, $cardsEarned, $battlesPlayed, $wins, $id));
}

function getStandings($db, $tag, $warId)
{
    $pattern = "
SELECT id
FROM standings
WHERE tag = \"%s\"
AND war_id = %d
";
    return fetch_query($db, utf8_decode(sprintf($pattern, $tag, $warId)));
}

function updateStanding($db, $participants, $battlesPlayed, $wins, $crowns, $warTrophies, $id)
{
    $pattern = "
UPDATE standings
SET participants = %d, 
battles_played = %d, 
battles_won = %d, 
crowns = %d, 
war_trophies = %d
WHERE id = %d
";

    execute_query($db, utf8_decode(sprintf($pattern, $participants, $battlesPlayed, $wins, $crowns, $warTrophies, $id)));
}

function insertStanding($db, $tag, $name, $participants, $battlesPlayed, $wins, $crowns, $warTrophies, $warId)
{
    $pattern = "
INSERT INTO standings (tag, standings.name, participants, battles_played, battles_won, crowns, war_trophies, war_id)
VALUES (\"%s\", \"%s\", %d, %d, %d, %d, %d, %d)
";
    $clanName = utf8_decode($name);
    if (strpos(trim($clanName), '???') !== false) {
        $clanName = "Nom Arabe ou chinois";
    }
    execute_query($db, utf8_decode(sprintf($pattern, $tag, $clanName, $participants, $battlesPlayed, $wins, $crowns,
        $warTrophies, $warId)));
}

function getWarPlayers($db, $order = null)
{
    $pattern = "
SELECT players.rank, players.tag, players.name, role.name as role_name, players.trophies, player_war.battle_played, 
player_war.battle_won, player_war.collection_played, player_war.collection_won, player_war.cards_earned as cards
FROM player_war
INNER JOIN war ON war.id = player_war.war_id
INNER JOIN players ON players.id = player_war.player_id
INNER JOIN role ON role.id = players.role_id
WHERE war.past_war = 0
%s
";
    $defaultOrder = "ORDER BY players.rank ASC";
    if ($order == null)
        $query = sprintf($pattern, $defaultOrder);
    else {
        $customOrderPattern = "ORDER BY %s DESC, players.rank ASC";
        $orderPattern = sprintf($customOrderPattern, $order);
        $query = sprintf($pattern, $orderPattern);
    }
    return fetch_all_query($db, $query);
}

function getAllStandings($db)
{
    $query = "
SELECT standings.name, participants, battles_played, battles_won, crowns, war_trophies
FROM standings
JOIN war ON standings.war_id = war.id
AND war.past_war = 0
ORDER BY battles_won DESC, crowns DESC 
";
    return fetch_all_query($db, $query);
}

// ----------------- PLAYERS -----------------
function getAllPlayersInClan($db)
{
    $query = "
SELECT players.id, players.tag
FROM players
WHERE in_clan > 0
";

    return fetch_all_query($db, $query);
}

function getPlayerByTag($db, $tag)
{
    $pattern = "
SELECT players.id, players.tag
FROM players
WHERE players.in_clan = 1
AND players.tag = \"%s\"
";
    return fetch_query($db, sprintf($pattern, utf8_decode($tag)));
}

function getAllPlayersForIndex($db)
{
    $query = "
SELECT players.tag, players.name as playerName, players.rank, players.trophies, role.name as playerRole, 
arena.arena as arena, arena.arena_id as arena_id, players.donations, players.donations_received  
FROM players
INNER JOIN role ON role.id = players.role_id
INNER JOIN arena ON arena.arena_id = players.arena
WHERE players.in_clan = 1
ORDER BY players.rank ASC
";
    return fetch_all_query($db, $query);
}

function updatePlayer($db, $name, $rank, $trophies, $role, $expLevel, $arenaId, $donations, $donationsReceived,
                      $donationsDelta, $donationsPercent, $tag)
{
    $pattern = "
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
    $query = utf8_decode(sprintf($pattern, $name, $rank, $trophies, getRoleIdByMachineName($db, $role), $expLevel,
        $arenaId, $donations, $donationsReceived, $donationsDelta, $donationsPercent, $tag));
    execute_query($db, $query);
}

function insertPlayer($db, $name, $tag, $rank, $trophies, $role, $expLevel, $arenaId, $donations, $donationsReceived,
                      $donationsDelta, $donationsPercent)
{
    $pattern = "
INSERT INTO players (players.name, tag, rank, trophies, role_id, exp_level, in_clan, arena, donations, 
donations_received, donations_delta, donations_ratio)
VALUES (\"%s\", \"%s\", %d, %d, %d, %d, %d, %d, %d, %d, %d, %f)
";
    $query = utf8_decode(sprintf($pattern, $name, $tag, $rank, $trophies, getRoleIdByMachineName($db, $role),
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
    $pattern = "
UPDATE players
SET players.max_trophies = %d
WHERE players.tag = \"%s\" 
";
    execute_query($db, utf8_decode(sprintf($pattern, $maxTrophies, $tag)));
}

function getNumberOfPlayersInClan($db)
{
    return sizeof(getAllPlayersInClan($db));
}

function getNotEligiblePlayers($db)
{
    $query = "
SELECT DISTINCT players.id 
FROM players 
WHERE players.id NOT IN
(
  SELECT DISTINCT pw.player_id 
  FROM player_war pw 
  JOIN war ON pw.war_id = war.id 
  WHERE war.past_war = 0
)
";
    return fetch_all_query($db, $query);
}

function getAllPlayersByRank($db)
{
    $query = "
SELECT players.id, players.name, players.rank, players.tag
FROM players
WHERE in_clan > 0
ORDER BY rank ASC
";

    return fetch_all_query($db, $query);
}

function removePlayerFromClan($db, $tag)
{
    $pattern = "
    UPDATE players
    SET in_clan = 0
    WHERE tag = \"%s\"
    ";

    execute_query($db, utf8_decode(sprintf($pattern, $tag)));
}

// ----------------- DECKS -----------------
function insertDeck($db, $playerId, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8, $warId)
{
    if ($warId == null) {
        $pattern = "
INSERT INTO decks (player_id, card_1, card_2, card_3, card_4, card_5, card_6, card_7, card_8, decks.current)
VALUES(%d, %d, %d, %d, %d, %d, %d, %d, %d)
";
        execute_query($db,
            sprintf($pattern, $playerId, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8));

    } else {
        $pattern = "
INSERT INTO decks (player_id, card_1, card_2, card_3, card_4, card_5, card_6, card_7, card_8, decks.current, war_id)
VALUES(%d, %d, %d, %d, %d, %d, %d, %d, %d, 0, %d)
";
        execute_query($db,
            sprintf($pattern, $playerId, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8, $warId));
    }
}

function getLastDeckId($db, $playerId, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8, $warId)
{
    insertDeck($db, $playerId, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8, $warId);
    return $db->lastInsertId();
}

function insertDeckResults($db, $deckId, $played, $win, $crowns)
{
    $pattern = "
    INSERT INTO deck_results (deck_id, played, wins, crowns)
    VALUES (%d, %d, %d, %d)
    ";
    execute_query($db, sprintf($pattern, $deckId, $played, $win, $crowns));
}

function updateDeckResults($db, $deckId, $played, $win, $crowns)
{
    $pattern = "
    UPDATE deck_results
    SET played = %d,
    wins = %d,
    crowns = %d
    WHERE deck_id = %d
    ";
    execute_query($db, sprintf($pattern, $played, $win, $crowns, $deckId));
}

function cleanDeckResults($db, $warId)
{
    $pattern = "
    DELETE FROM deck_results
USING deck_results, decks
WHERE decks.id = deck_results.deck_id
AND decks.war_id = %d
    ";
    execute_query($db, sprintf($pattern, $warId));
}

function getDeckResults($db, $id)
{
    $pattern = "
    SELECT played, wins, crowns
    FROM deck_results
    WHERE deck_id = %d
    ";
    return fetch_query($db, sprintf($pattern, $id));
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
    $pattern = "
SELECT cards.id
FROM cards
WHERE cr_id = %d
";
    $currentDeck = [];
    foreach ($deck as $card) {
        $cardId = fetch_query($db, sprintf($pattern, $card['id']))['id'];
        array_push($currentDeck, $cardId);
    }
    return $currentDeck;
}

function getExistingDeck($db, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8, $playerId)
{
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

function getDeckById($db, $id)
{
    $pattern = "
    SELECT card_1, card_2, card_3, card_4, card_5, card_6, card_7, card_8
    FROM decks
    WHERE id = %d
    ";

    return fetch_query($db, sprintf($pattern, $id));
}

function getAllCurrentWarDecks($db, $warId)
{
    $pattern = "
    SELECT id, card_1, card_2, card_3, card_4, card_5, card_6, card_7, card_8
    FROM decks
    WHERE player_id = 610
    AND war_id = %d
    ";

    return fetch_all_query($db, sprintf($pattern, $warId));
}

function getAllWarDecks($db, $current)
{
    $query = "
    SELECT c1.card_key as c1key, c2.card_key as c2key, c3.card_key as c3key, c4.card_key as c4key, c5.card_key as c5key,
    c6.card_key as c6key, c7.card_key as c7key, c8.card_key as c8key, c1.cr_id as crid1, c2.cr_id as crid2, 
    c3.cr_id as crid3, c4.cr_id as crid4, c5.cr_id as crid5, c6.cr_id as crid6, c7.cr_id as crid7, c8.cr_id as crid8, 
    sum(played) as played, sum(wins) as wins, sum(crowns) as crowns
    FROM `decks`
    JOIN deck_results ON deck_results.deck_id = decks.id
    JOIN cards as c1 ON c1.id = decks.card_1
    JOIN cards as c2 ON c2.id = decks.card_2
    JOIN cards as c3 ON c3.id = decks.card_3
    JOIN cards as c4 ON c4.id = decks.card_4
    JOIN cards as c5 ON c5.id = decks.card_5
    JOIN cards as c6 ON c6.id = decks.card_6
    JOIN cards as c7 ON c7.id = decks.card_7
    JOIN cards as c8 ON c8.id = decks.card_8
    JOIN war ON decks.war_id = war.id
    %s
    AND player_id = 610
    GROUP BY card_1, card_2, card_3, card_4, card_5, card_6, card_7, card_8
    ORDER BY played DESC, wins DESC, crowns DESC
    ";

    if ($current)
        $pattern = "WHERE war.past_war = 0";
    else
        $pattern = "WHERE war_id IS NOT NULL";

    return fetch_all_query($db, sprintf($query, $pattern));
}

// ----------------- CARDS -----------------
function insertCard($db, $key, $name, $elixir, $type, $rarity, $arena, $crId)
{
    $pattern = "
INSERT INTO cards (card_key, cards.name, elixir, cards.type, rarity, arena, cr_id)
VALUES(\"%s\", \"%s\", %d, \"%s\", \"%s\", %d, %d)
";

    execute_query($db, utf8_decode(sprintf($pattern, $key, $name, $elixir, $type, $rarity, $arena, $crId)));
}

function updateCard($db, $key, $name, $elixir, $type, $rarity, $arena, $crId)
{
    $pattern = "
UPDATE cards
SET cards.name = \"%s\",
elixir = %d,
cards.type = \"%s\",
rarity = \"%s\",
arena = %d,
cr_id = %d
WHERE cards.card_key = \"%s\" 
";
    execute_query($db, utf8_decode(sprintf($pattern, $name, $elixir, $type, $rarity, $arena, $crId, $key)));
}

function getCardByKey($db, $key)
{
    $pattern = "
SELECT id
FROM cards
WHERE cards.card_key = \"%s\"
";
    return fetch_query($db, utf8_decode(sprintf($pattern, $key)));
}

function getCardByCrId($db, $crId)
{
    $pattern = "
SELECT id
FROM cards
WHERE cards.cr_id = %d
";
    return fetch_query($db, sprintf($pattern, $crId));
}

function getPlayersInfoByTag($db, $tag)
{
    $pattern = "
SELECT players.id as playerId, players.tag, players.name as playerName, players.rank, players.trophies, players.max_trophies, role.name as playerRole, players.exp_level as level,
players.donations_delta as delta, players.donations_ratio as ratio, arena.arena as arena, players.donations, players.donations_received as received,
arena.trophy_limit, arena.arena_id, player_war.battle_played, player_war.battle_won, player_war.collection_played, player_war.collection_won, player_war.cards_earned,
SUM(player_war.cards_earned) as total_cards_earned,
SUM(player_war.collection_played) as total_collection_played, 
SUM(player_war.collection_won) as total_collection_won,
SUM(player_war.battle_played) as total_battle_played,
SUM(player_war.battle_won) as total_battle_won
FROM players
INNER JOIN arena ON arena.arena_id = players.arena
INNER JOIN role ON role.id = players.role_id
INNER JOIN player_war ON player_war.player_id = players.id
INNER JOIN war ON player_war.war_id = war.id
WHERE tag = \"%s\"
AND war.id > 23
";
    return fetch_query($db, utf8_decode(sprintf($pattern, $tag)));
}

function getTotalWarPlayedByPlayerId($db, $id)
{
    $pattern = "
    SELECT COUNT(player_war.id) as total_war_played
    FROM player_war
    WHERE collection_played > 0
    AND player_id = %d
    ";

    return fetch_query($db, sprintf($pattern, $id));
}

function getCardsInCurrentDeck($db, $playerId)
{
    $pattern = "
SELECT c1.card_key as c1key, c2.card_key as c2key, c3.card_key as c3key, c4.card_key as c4key, c5.card_key as c5key, 
c6.card_key as c6key, c7.card_key as c7key, c8.card_key as c8key, c1.cr_id as crid1, c2.cr_id as crid2, 
c3.cr_id as crid3, c4.cr_id as crid4, c5.cr_id as crid5, c6.cr_id as crid6, c7.cr_id as crid7, c8.cr_id as crid8
FROM decks
JOIN cards as c1 ON c1.id = decks.card_1
JOIN cards as c2 ON c2.id = decks.card_2
JOIN cards as c3 ON c3.id = decks.card_3
JOIN cards as c4 ON c4.id = decks.card_4
JOIN cards as c5 ON c5.id = decks.card_5
JOIN cards as c6 ON c6.id = decks.card_6
JOIN cards as c7 ON c7.id = decks.card_7
JOIN cards as c8 ON c8.id = decks.card_8
WHERE decks.player_id = %d
AND decks.current > 0
";

    return fetch_query($db, sprintf($pattern, $playerId));
}

function getCrIdsByCards($db, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8)
{
    $pattern = "
SELECT cr_id
FROM cards
WHERE cards.id = %d
OR cards.id = %d
OR cards.id = %d
OR cards.id = %d
OR cards.id = %d
OR cards.id = %d
OR cards.id = %d
OR cards.id = %d
";

    return fetch_all_query($db, sprintf($pattern, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8));
}

// ----------------- WAR STATS -----------------
function getWarStatsByPlayerId($db, $playerId)
{
    $pattern = "
SELECT SUM(cards_earned) as total_cards_earned, 
SUM(collection_played) as total_collection_played, 
SUM(collection_won) as total_collection_won,
SUM(battle_played) as total_battle_played,
SUM(battle_won) as total_battle_won
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE player_id = %d
AND war.past_war > 0
AND war.id > 23
";

    return fetch_query($db, sprintf($pattern, $playerId));
}

function countMissedWar($db, $playerId)
{
    $pattern = "
SELECT COUNT(player_war.id) as missed_war
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE player_war.battle_played = 0
AND war.past_war > 0
AND war.id > 23
AND player_war.player_id = %d
";
    return fetch_query($db, sprintf($pattern, $playerId));
}

function countMissedCollection($db, $playerId)
{
    $pattern = "
SELECT COUNT(player_war.id) as missed_collection
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE player_war.collection_played = 0
AND war.past_war > 0
AND war.id > 23
AND player_war.player_id = %d
";
    return fetch_query($db, sprintf($pattern, $playerId));
}

function getFirstWarDate($db)
{
    $query = "
SELECT created
FROM war
WHERE past_war > 0
AND war.id > 23
LIMIT 1
";
    return fetch_query($db, $query);
}