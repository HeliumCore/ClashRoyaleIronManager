<?php
/**
 * Created by PhpStorm.
 * User: ADufresne
 * Date: 11/06/2018
 * Time: 14:53
 */

if (!defined('CONFIGURED'))
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

// ================= PLAYER =================

function insertPlayerTrophy($db, $playerTag, $trophies)
{
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

function updateSeekCard($db, $card, $playerId)
{
    $pattern = "
    UPDATE card_level
    SET seek = 1,
    keep = 1
    WHERE card_id = %d
    AND player_id = %d
    ";

    execute_query($db, sprintf($pattern, $card, $playerId));
}

function updateKeepCard($db, $card, $playerId)
{
    $pattern = "
    UPDATE card_level
    SET seek = 0,
    keep = 1
    WHERE card_id = %d
    AND player_id = %d
    ";

    execute_query($db, sprintf($pattern, $card, $playerId));
}

function updateDonateCard($db, $card, $playerId)
{
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

function getPossibleTrade($db, $playerId)
{
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
    $rarities = [250 => 'Common', 50 => 'Rare', 10 => 'Epic', 1 => 'Legendary'];
    //TODO revoir ca avec fabien S
    foreach ($rarities as $key => $rarity) {
        $query = sprintf($pattern, $playerId, $rarity, $key);
        fetch_all_query($db, $query);
    }
}

// ==========================================

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