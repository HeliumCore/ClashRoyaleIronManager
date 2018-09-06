<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 11/06/2018
 * Time: 14:53
 */

if(!defined('CONFIGURED'))
    require_once('conf.php');

$db = new PDO('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);

// ================== SQL ===================
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

// ==========================================


// ================= PLAYER =================

function insertPlayerTrophy($db, $playerTag, $trophies) {
    $pattern = "
        INSERT INTO player_trophies(player_id, trophies, date)
        VALUES ((SELECT id FROM players WHERE tag = \"%s\"), %d, UNIX_TIMESTAMP())
    ";

    execute_query($db, sprintf($pattern, $playerTag, $trophies));
}

function getAllPlayersInClan($db)
{
    $query = "
SELECT players.id, players.tag, players.name
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

function getNumberOfPlayersInClan($db)
{
    return sizeof(getAllPlayersInClan($db));
}

function getPlayerTagByAccountId($db, $accountId)
{
    $pattern = "
    SELECT p.tag, p.name
    FROM players p
    JOIN account a ON p.id = a.player_id
    WHERE a.id = %d
    ";
    return fetch_query($db, sprintf($pattern, $accountId));
}

// ==========================================


// ================== DECKS =================
// ----------------- INSERT -----------------


function insertDeckWar($db, $deck, $war)
{
    $pattern = "
    INSERT INTO war_decks(deck_id, war_id)
    VALUES (%d, %d)
    ";
    execute_query($db, sprintf($pattern, $deck, $war));
}

function insertDeckResults($db, $deckId, $win, $crowns, $combatTime)
{
    $pattern = "
    INSERT INTO deck_results (deck_id, win, crowns, time)
    VALUES (%d, %d, %d, %d)
    ";
    execute_query($db, sprintf($pattern, $deckId, $win, $crowns, $combatTime));
}

// ----------------- UPDATE -----------------


// -----------------   GET  -----------------
function getAllWarDecksWithPagination($db, $current, $page)
{
    $pattern = "
    SELECT dr.deck_id, COUNT(dr.id) as played, SUM(win) as wins, SUM(crowns) as total_crowns,
    subQuery.elixir_cost, subQuery.card_keys, subQuery.cr_ids,
    (
        SELECT CEIL(COUNT(d.id) / 10)
        FROM decks d
        RIGHT JOIN war_decks wd ON d.id = wd.deck_id
        JOIN war w ON wd.war_id = w.id
        %s
    ) as number_of_pages
    FROM war_decks wd
    LEFT JOIN deck_results dr ON dr.deck_id = wd.deck_id
    LEFT JOIN decks d ON d.id = wd.deck_id
    LEFT JOIN war w ON w.id = wd.war_id
    LEFT JOIN
        (
            SELECT cd.deck_id, GROUP_CONCAT(c.card_key) as card_keys, GROUP_CONCAT(c.cr_id) as cr_ids, ROUND(AVG(c.elixir), 1) as elixir_cost
            FROM card_deck cd
            LEFT JOIN cards c ON c.id = cd.card_id
            GROUP BY cd.deck_id
        ) subQuery ON subQuery.deck_id = d.id
    %s
    GROUP BY wd.deck_id
    ORDER BY played DESC, wins DESC, crowns DESC
    LIMIT %d, 10
    ";
    $offset = intval(($page - 1) * 10);
    $condition = "";
    if ($current) {
        $condition = "WHERE w.past_war = 0";
    }

    return fetch_all_query($db, sprintf($pattern, $condition, $condition, $offset));
}

function getPlayerDeck($db, $deckId, $playerId)
{
    $pattern = "
    SELECT pd.id, pd.current
    FROM player_deck pd
    WHERE pd.deck_id = %d
    AND pd.player_id = %d
    ";

    return fetch_query($db, sprintf($pattern, $deckId, $playerId));
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

function getDeckResultsByTime($db, $combatTime)
{
    $pattern = "
    SELECT dr.id, dr.win, dr.crowns, dr.time
    FROM deck_results dr
    WHERE dr.time = %d
    ";
    return fetch_query($db, sprintf($pattern, $combatTime));
}

function isDeckUsedInCurrentWar($db, $warId, $deckId)
{
    $pattern = "
    SELECT id
    FROM war_decks
    WHERE war_id = %d
    AND deck_id = %d
    ";
    return fetch_query($db, sprintf($pattern, $warId, $deckId)) != null;
}

// ==========================================


// ================== CARDS =================
// ----------------- INSERT -----------------


// ----------------- UPDATE -----------------


function updateSeekCard($db, $card, $playerId) {
    $pattern = "
    UPDATE card_level
    SET seek = 1,
    keep = 1
    WHERE card_id = %d
    AND player_id = %d
    ";

    execute_query($db, sprintf($pattern, $card, $playerId));
}

function updateKeepCard($db, $card, $playerId) {
    $pattern = "
    UPDATE card_level
    SET seek = 0,
    keep = 1
    WHERE card_id = %d
    AND player_id = %d
    ";

    execute_query($db, sprintf($pattern, $card, $playerId));
}

function updateDonateCard($db, $card, $playerId) {
    $pattern = "
    UPDATE card_level
    SET seek = 0,
    keep = 0
    WHERE card_id = %d
    AND player_id = %d
    ";

    execute_query($db, sprintf($pattern, $card, $playerId));
}
// -----------------   GET  -----------------

function getCardsLevelsByPlayerId($db, $playerId)
{
    $pattern = "
    SELECT DISTINCT c.card_key, cl.level, c.rarity
    FROM players p
    JOIN player_deck pd ON p.id = pd.player_id
    JOIN card_deck cd ON cd.deck_id = pd.deck_id AND pd.current = 1
    JOIN cards c ON cd.card_id = c.id
    JOIN card_level cl ON cd.card_id = cl.card_id AND cl.player_id = p.id
    WHERE p.id = %d
    ";

    return fetch_all_query($db, sprintf($pattern, $playerId));
}

function getFavCards($db)
{
    $query = "
    SELECT COUNT(c.id) as occurence, c.card_key
    FROM card_deck cd
    JOIN `cards` c ON c.id = cd.card_id
    GROUP BY c.cr_id
    ORDER BY occurence DESC
    LIMIT 9
    ";


    return fetch_all_query($db, $query);
}

function getPossibleTrade($db, $playerId) {
    $pattern = "
    SELECT p.id, p.name, c.card_key, cl.seek, cl.keep, cl.quantity
    FROM players p
    JOIN card_level cl ON p.id = cl.player_id
    JOIN cards c ON cl.card_id = c.id
    WHERE p.id != %d
    AND c.rarity = \"%s\"
    AND cl.quantity = %d 
    AND cl.keep = 0
    ";
    $rarities = [250=>'Common', 50=>'Rare', 10=>'Epic', 1=>'Legendary'];
    //TODO revoir ca avec fabien S
    foreach ($rarities as $key=>$rarity) {
        $query = sprintf($pattern, $playerId, $rarity, $key);
        var_dump($query);
        fetch_all_query($db, $query);
    }
}
// ==========================================


// =================== WAR ==================
// ----------------- INSERT -----------------
function insertPlayerWar($db, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId)
{
    $pattern = "
INSERT INTO player_war (cards_earned, battle_played, battle_won, player_id, war_id)
VALUE (%d, %d, %d, %d, %d)
";

    execute_query($db, sprintf($pattern, $cardsEarned, $battlesPlayed, $wins, $playerId, $warId));
}


// ----------------- UPDATE -----------------
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



// -----------------   GET  -----------------
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

function getWarId($db, $war, $currentWar, $created, $season = null)
{
    $insertWarPattern = "
INSERT INTO war
VALUES ('', %d, 0, %d)
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
            execute_query($db, sprintf($insertWarPattern, $created, $season));
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
JOIN war ON standings.war_id = war.id AND war.past_war = 0
ORDER BY battles_won DESC, crowns DESC
";
    return fetch_all_query($db, $query);
}

function getAllWarStats($db)
{
    $query = "
    SELECT *
    FROM (
        (
            SELECT
            p.id, p.name, p.rank, p.tag,
            SUM(IFNULL(cards_earned, 0)) as total_cards_earned,
            SUM(IFNULL(collection_played, 0)) as total_collection_played,
            SUM(IFNULL(collection_won, 0)) as total_collection_won,
            SUM(IFNULL(battle_played, 0)) as total_battle_played,
            SUM(IFNULL(battle_won, 0)) as total_battle_won,
            w.season
            FROM player_war pw
            JOIN players p ON pw.player_id = p.id AND p.in_clan = 1
            JOIN war w ON pw.war_id = w.id AND w.past_war > 0
            WHERE w.season != 0
            GROUP BY pw.player_id, w.season
            ORDER BY p.rank ASC, w.season DESC
        )
        UNION
        (
            SELECT
            p.id, p.name, p.rank, p.tag,
            SUM(IFNULL(cards_earned, 0)) as total_cards_earned,
            SUM(IFNULL(collection_played, 0)) as total_collection_played,
            SUM(IFNULL(collection_won, 0)) as total_collection_won,
            SUM(IFNULL(battle_played, 0)) as total_battle_played,
            SUM(IFNULL(battle_won, 0)) as total_battle_won, 0
            FROM player_war pw
            JOIN players p ON pw.player_id = p.id AND p.in_clan = 1
            JOIN war w ON pw.war_id = w.id AND w.past_war > 0
            WHERE w.season != 0
            GROUP BY pw.player_id
        )
    ) sub
    ORDER BY sub.rank ASC
";
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
SELECT ((COUNT(player_war.id) * 3) - SUM(player_war.collection_played)) as missed_collection
FROM player_war
JOIN war ON player_war.war_id = war.id
WHERE player_war.collection_played > 0
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



function getWarNumber($db)
{
    $query = "
    SELECT COUNT(w.id) as warNumber
    FROM war w
    WHERE w.id > 24
    ";

    return fetch_query($db, $query)['warNumber'];
}

// ==========================================


// ================= UPDATED ================
// ----------------- INSERT -----------------


// ----------------- UPDATE -----------------
function setLastUpdated($db, $pageName)
{
    $pattern = "
    UPDATE last_updated
    SET updated = NOW()
    WHERE page_name = \"%s\"
    ";
    execute_query($db, utf8_decode(sprintf($pattern, $pageName)));
}



// -----------------   GET  -----------------
function getLastUpdated($db, $pageName)
{
    $pattern = "
    SELECT updated
    FROM last_updated
    WHERE page_name = \"%s\"
    ";
    return fetch_query($db, utf8_decode(sprintf($pattern, $pageName)));
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

// ==========================================

// ================= ACCOUNTS ===============
// ----------------- INSERT -----------------
function createAccount($db, $playerId, $password, $time)
{
    $pattern = "
    INSERT INTO account(player_id, password, last_visit)
    VALUES (%d, \"%s\", %d)
    ";
    execute_query($db, sprintf($pattern, $playerId, $password, $time));
    return $db->lastInsertId();
}

// ----------------- UPDATE -----------------
function updatePassword($db, $playerId, $password)
{
    $pattern = "
    UPDATE account
    SET password = \"%s\"
    WHERE player_id = %d
    ";
    execute_query($db, sprintf($pattern, $password, $playerId));
}

function setLastVisit($db, $accountId, $time)
{
    $pattern = "
    UPDATE account
    SET last_visit = %d
    WHERE id = %d
    ";

    execute_query($db, sprintf($pattern, $time, $accountId));
}

// -----------------   GET  -----------------
function getAccountInfos($db, $playerdTag)
{
    $pattern = "
    SELECT a.password, a.id, a.last_visit
    FROM account a
    JOIN players p ON a.player_id = p.id
    WHERE p.tag = \"%s\"
    ";
    return fetch_query($db, sprintf($pattern, $playerdTag));
}

function getPlayerInfoByAccountId($db, $accountId)
{
    $pattern = "
    SELECT p.id, p.tag, p.name
    FROM players p
    JOIN account a ON p.id = a.player_id
    WHERE a.id = %d
    ";
    return fetch_query($db, sprintf($pattern, $accountId));
}

// ==========================================


// ================= PAUSES =================
// ----------------- INSERT -----------------
function insertPause($db, $accountId, $dates)
{
    $pattern = "
    INSERT INTO player_pause(account_id, pause)
    VALUES %s
    ";

    $secondPattern = "";
    if (sizeof($dates) > 1) {
        foreach ($dates as $date) {
            $firstPattern = "(%d, \"%s\"),";
            $firstQuery = sprintf($firstPattern, $accountId, $date);
            $secondPattern .= $firstQuery;
        }

        $secondPattern = substr($secondPattern, 0, -1);
    } else if (sizeof($dates) == 1) {
        $firstPattern = "(%d, \"%s\")";
        $firstQuery = sprintf($firstPattern, $accountId, $dates[0]);
        $secondPattern .= $firstQuery;
    } else {
        return;
    }
    execute_query($db, sprintf($pattern, $secondPattern));
}

// ----------------- UPDATE -----------------
function deletePause($db, $accountId, $date)
{
    $pattern = "
    DELETE FROM player_pause
    WHERE pause = %s
    AND account_id = %d
    ";

    execute_query($db, sprintf($pattern, $date, $accountId));
}

function deleteAllPauseByAccount($db, $accountId)
{
    $pattern = "
    DELETE FROM player_pause
    WHERE account_id = %d
    ";

    execute_query($db, sprintf($pattern, $accountId));
}

// -----------------   GET  -----------------
function getAllPauseByAccount($db, $accountId)
{
    $pattern = "
    SELECT pause
    FROM player_pause
    WHERE account_id = %d
    ";

    $pauses = array();
    foreach (fetch_all_query($db, sprintf($pattern, $accountId)) as $pause) {
        array_push($pauses, $pause['pause']);
    }
    return $pauses;
}

function getAllPauses($db)
{
    $query = "
    SELECT p.name, GROUP_CONCAT(pp.pause) as pauses
    FROM player_pause pp
    JOIN account a ON pp.account_id = a.id
    JOIN players p ON a.player_id = p.id AND in_clan > 0
    GROUP BY account_id
    ";
    return fetch_all_query($db, $query);
}

function isAccountAdmin($db, $accountId)
{
    $pattern = "
    SELECT p.id
    FROM account a
    JOIN players p ON a.player_id = p.id
    WHERE p.role_id <= 2
    AND a.id = %d
    ";

    return fetch_query($db, sprintf($pattern, $accountId)) != null;
}
// ==========================================