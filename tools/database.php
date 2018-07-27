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
function getWarId($db, $war, $currentWar, $created, $season = null)
{
    $insertWarPattern = "
INSERT INTO war
VALUES ('', %d, %d, 0)
";

    $updateCurrentWarPattern = "
UPDATE war
SET created = %d,
past_war = 1,
season = %d
WHERE id = %d
";
    if (!is_array($war)) {
        if (!is_array($currentWar)) {
            execute_query($db, sprintf($insertWarPattern, $created, 1));
            return $db->lastInsertId();
        } else {
            execute_query($db, sprintf($updateCurrentWarPattern, $created, $season, intval($currentWar['id'])));
            return getWarID($db, getWar($db, $created), $currentWar, $created, $season);
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
    if ($order == null)
        $query = sprintf($pattern, "ORDER BY players.rank ASC");
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

function getLastSeason($db)
{
    $query = "
    SELECT MAX(season) as number
    FROM war
    ";

    return intval(fetch_query($db, $query)['number']);
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
WHERE players.tag = \"%s\"
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
players.donations_ratio= %f,
players.in_clan = 1
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
SELECT players.id 
FROM players 
WHERE players.id NOT IN
(
  SELECT pw.player_id 
  FROM player_war pw 
  JOIN war ON pw.war_id = war.id 
  WHERE war.past_war = 0
)
AND players.in_clan = 1
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
function createDeck($db, $playerId)
{
    $pattern = "
    INSERT INTO decks
    VALUES ('', %d, 1)
    ";

    execute_query($db, sprintf($pattern, $playerId));
    return $db->lastInsertId();
}

function insertCardDeck($db, $card, $deck)
{
    $pattern = "
    INSERT INTO card_deck(card_id, deck_id)
    VALUES (%d, %d)
    ";
    execute_query($db, sprintf($pattern, $card, $deck));
}

function insertDeckWar($db, $deck, $war)
{
    $pattern = "
    INSERT INTO war_decks(deck_id, war_id)
    VALUES (%d, %d)
    ";
    execute_query($db, sprintf($pattern, $deck, $war));
}

function getDeckFromCards($db, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8, $playerId = null)
{
    $pattern = "
    SELECT deck_id
    FROM card_deck
    %s
    WHERE card_id IN (%d, %d, %d, %d, %d, %d, %d, %d)
    GROUP BY deck_id
    HAVING COUNT(card_id) = 8
    ";

    if ($playerId == null) {
        $join = sprintf("JOIN decks ON decks.id = card_deck.deck_id AND decks.player_id = %d", 610);
        return fetch_query($db, sprintf($pattern, $join, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8));
    } else {
        $join = sprintf("JOIN decks ON decks.id = card_deck.deck_id AND decks.player_id = %d", $playerId);
        return fetch_query($db, sprintf($pattern, $join, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8));
    }
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

function cleanDeckResults($db, $warId)
{
    $pattern = "
    DELETE dr FROM deck_results as dr 
    INNER JOIN decks as d ON dr.deck_id = d.id
    INNER JOIN war_decks as wd ON d.id = wd.deck_id
    WHERE wd.war_id = %d
    ";
    execute_query($db, sprintf($pattern, $warId));
}

function getAllCurrentWarDecksId($db, $warId)
{
    $pattern = "
    SELECT decks.id
    FROM decks
    JOIN war_decks ON decks.id = war_decks.deck_id
    WHERE decks.player_id = 610
    AND war_decks.war_id = %d
    ";

    return fetch_all_query($db, sprintf($pattern, $warId));
}

function getDeckById($db, $id)
{
    $pattern = "
    SELECT card_id
    FROM card_deck
    JOIN decks ON card_deck.deck_id = decks.id
    WHERE decks.id = %d
    ";

    return fetch_query($db, sprintf($pattern, $id));
}

function getDeckByPlayerId($db, $playerId)
{
    $pattern = "
    SELECT c.card_key, c.cr_id
    FROM cards c
    JOIN card_deck cd ON c.id = cd.card_id
    JOIN decks d ON cd.deck_id = d.id
    JOIN players p ON d.player_id = p.id
    WHERE p.id = %d
    AND d.current = 1
    ";
    return fetch_all_query($db, sprintf($pattern, $playerId));
}

function getAllWarDecks($db)
{
    $query = "
    SELECT dr.played, dr.wins, dr.crowns, GROUP_CONCAT(c.card_key) as card_keys, GROUP_CONCAT(c.cr_id) as cr_ids
    FROM decks d
    JOIN deck_results dr ON d.id = dr.deck_id
    LEFT JOIN card_deck cd ON d.id = cd.deck_id
    LEFT JOIN cards c ON cd.card_id = c.id
    WHERE d.player_id = 610
    GROUP BY d.id
    ORDER BY dr.played DESC, dr.wins DESC, dr.crowns DESC
    ";
    return fetch_all_query($db, $query);
}

function getAllWarDecksWithPagination($db, $current, $page)
{
    $pattern = "
    SELECT dr.played, dr.wins, dr.crowns, GROUP_CONCAT(c.card_key) as card_keys, GROUP_CONCAT(c.cr_id) as cr_ids
    FROM decks d
    JOIN deck_results dr ON d.id = dr.deck_id
    LEFT JOIN card_deck cd ON d.id = cd.deck_id
    LEFT JOIN cards c ON cd.card_id = c.id
    LEFT JOIN war_decks wd ON d.id = wd.deck_id
    LEFT JOIN war w ON wd.war_id = w.id
    %s
    GROUP BY d.id
    ORDER BY dr.played DESC, dr.wins DESC, dr.crowns DESC
    LIMIT %d,10
    ";

    if ($current)
        $condition = "WHERE w.past_war = 0 AND d.player_id = 610";
    else
        $condition = "WHERE d.player_id = 610";

    $offset = intval(($page - 1) * 10);
    return fetch_all_query($db, sprintf($pattern, $condition, $offset));
}

function getNumberOfPages($db, $current)
{
    $pattern = "
    SELECT COUNT(d.id) as did, sum(dr.wins) as wins
    FROM decks d
    JOIN deck_results dr ON d.id = dr.deck_id 
    JOIN war_decks wd ON d.id = wd.deck_id
    JOIN war w ON wd.war_id = w.id
    %s
    AND wins > 0
    ";

    if ($current)
        $condition = "WHERE w.past_war = 0 AND d.player_id = 610";
    else
        $condition = "WHERE d.player_id = 610";

    $decks = intval(fetch_query($db, sprintf($pattern, $condition))['did']);
    return ceil($decks / 10);
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
SELECT id, card_key
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
AND war.id > 24
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

function insertCardLevelByPlayer($db, $card, $playerId, $level)
{
    $pattern = "
    INSERT INTO card_level(card_id, player_id, level)
    VALUES (%d, %d, %d)
    ";

    execute_query($db, sprintf($pattern, $card, $playerId, $level));
}

function updateCardLevelByPlayer($db, $card, $playerId, $level)
{
    $pattern = "
    UPDATE card_level
    SET level = %d
    WHERE card_id = %d
    AND player_id = %d
    ";

    execute_query($db, sprintf($pattern, $level, $card, $playerId));
}

function getCardLevelByPlayer($db, $card, $playerId)
{
    $pattern = "
    SELECT level
    FROM card_level
    WHERE card_id = %d
    AND player_id = %d
    ";

    return fetch_query($db, sprintf($pattern, $card, $playerId));
}

// ----------------- WAR STATS -----------------
function getWarStats($db, $order = null, $season = null)
{
    $pattern = "
SELECT players.id, players.name, players.rank, players.tag,
SUM(IFNULL(cards_earned, 0)) as total_cards_earned, 
SUM(IFNULL(collection_played, 0)) as total_collection_played, 
SUM(IFNULL(collection_won, 0)) as total_collection_won,
SUM(IFNULL(battle_played, 0)) as total_battle_played,
SUM(IFNULL(battle_won, 0)) as total_battle_won
FROM player_war
JOIN war ON player_war.war_id = war.id %s
JOIN players ON player_war.player_id = players.id
AND war.past_war > 0
AND war.id > 24
AND players.in_clan > 0
GROUP BY player_war.player_id
%s
";

    if ($order == null) {
        if ($season == null)
            $query = sprintf($pattern, "", "ORDER BY players.rank ASC");
        else
            $query = sprintf($pattern, "AND war.season = " . $season, "ORDER BY players.rank ASC");
    } else {
        $customOrderPattern = "ORDER BY %s DESC, players.rank ASC";
        $orderPattern = sprintf($customOrderPattern, $order);
        if ($season == null) {
            $query = sprintf($pattern, "", $orderPattern);
        } else {
            $query = sprintf($pattern, "AND war.season = " . $season, $orderPattern);
        }
    }

    return fetch_all_query($db, $query);
}

function countMissedWar($db, $playerId, $season = null)
{
    $pattern = "
SELECT COUNT(player_war.id) as missed_war
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE player_war.battle_played = 0
AND player_war.collection_played > 0
AND war.past_war > 0
AND war.id > 24
AND player_war.player_id = %d
%s
";
    if ($season === null)
        $query = sprintf($pattern, $playerId, "");
    else
        $query = sprintf($pattern, $playerId, "AND war.season = " . $season);

    return fetch_query($db, $query);
}

function countMissedCollection($db, $playerId, $season = null)
{
    $pattern = "
SELECT COUNT(player_war.id) as missed_collection
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE player_war.collection_played = 0
AND war.past_war > 0
AND war.id > 24
AND player_war.player_id = %d
%s
";
    if ($season == null)
        $query = sprintf($pattern, $playerId, "");
    else
        $query = sprintf($pattern, $playerId, "AND war.season = " . $season);

    return fetch_query($db, $query);
}

function getFirstWarDate($db)
{
    $query = "
SELECT created
FROM war
WHERE past_war > 0
AND war.id > 24
LIMIT 1
";
    return fetch_query($db, $query);
}

function getNumberOfEligibleWarByPlayerId($db, $playerId, $season)
{
    $pattern = "
SELECT COUNT(pw.id) as number_of_war
FROM player_war pw
JOIN war ON pw.war_id = war.id 
WHERE war.past_war = 1
AND pw.player_id = %d
AND war.season = %d
";

    return intval(fetch_query($db, sprintf($pattern, $playerId, $season))['number_of_war']);
}

// ----------------- LAST UPDATED ---------------
function setLastUpdated($db, $pageName)
{
    $pattern = "
    UPDATE last_updated
    SET updated = NOW()
    WHERE page_name = \"%s\"
    ";
    execute_query($db, utf8_decode(sprintf($pattern, $pageName)));
}

function getLastUpdated($db, $pageName)
{
    $pattern = "
    SELECT updated
    FROM last_updated
    WHERE page_name = \"%s\"
    ";
    return fetch_query($db, utf8_decode(sprintf($pattern, $pageName)));
}

function setLastUpdatedPlayer($db, $playerTag)
{
    $pattern = "
    UPDATE last_updated
    SET updated = NOW()
    WHERE page_name = 'player'
    AND tag = \"%s\"
    ";
    execute_query($db, utf8_decode(sprintf($pattern, $playerTag)));
}

function insertLastUpdatedPlayer($db, $playerTag)
{
    $pattern = "
    INSERT INTO last_updated (page_name, updated, tag)
    VALUES ('player', NOW(), \"%s\")
    ";
    execute_query($db, utf8_decode(sprintf($pattern, $playerTag)));
}

function getLastUpdatedPlayer($db, $playerTag)
{
    $pattern = "
    SELECT id, updated
    FROM last_updated
    WHERE tag = \"%s\"
    ";
    return fetch_query($db, utf8_decode(sprintf($pattern, $playerTag)));
}