<?php
require 'arena.class.php';
require 'deck.class.php';
require 'pause.class.php';

class Player {
    private $id = null;
    private $sTag = null;
    private $name = null;
    private $trophies = null;
    private $maxTrophies = null;
    private $level = null;
    private $role = null;
    private $rank = null;
    private $arena = null;
    private $donations = null;
    private $donationsReceived = null;
    private $warPlayed = null;
    private $collectionPlayed = null;
    private $collectionMissed = null;
    private $collectionWon = null;
    private $cardsEarned = null;
    private $battlePlayed = null;
    private $battleMissed = null;
    private $battleWon = null;
    private $aNextChests = null;
    private $currentDeck = null;
    private $pauses = null;

    public function __construct($sTag) {
        $this->sTag = $sTag;
    }

    /**
     * Récupère la liste des prochains coffres du joueur
     * @return array liste des prochains coffres
     */
    public function getNextChests() {
        $url = "players/%23" . $this->sTag . "/upcomingchests";
        return ClashRoyaleApi::getRequest($url)['items'];
    }

    /**
     * Récupère les informations d'un joueur
     * @return Player un objet player
     */
    public function getPlayerInfos() {
        $query = "
            SELECT pw.id
            FROM player_war pw
            JOIN players p ON pw.player_id = p.id
            WHERE p.tag = \"%s\"
         ";

        $pattern = "
            SELECT
            p.id as playerId, p.tag, p.name as playerName, p.rank, p.trophies, p.max_trophies, role.name as playerRole, p.exp_level as level,
            p.donations, p.donations_received, 
            arena.arena as arena, arena.name as arenaName, arena.trophy_limit, arena.arena_id, 
            pw1.total_cards_earned, pw1.total_collection_played, pw1.total_collection_won, pw1.total_battle_played, pw1.total_battle_won,
            GROUP_CONCAT(DISTINCT c.cr_id) cr_ids, GROUP_CONCAT(DISTINCT c.card_key) card_keys, ROUND(AVG(c.elixir), 1) as elixir_cost,
            COUNT(DISTINCT war_played.id) total_war_played,
            COUNT(DISTINCT war_collection.id) missed_collection,
            COUNT(DISTINCT war_missed.id) missed_war
            FROM players p
            INNER JOIN arena ON arena.arena_id = p.arena
            INNER JOIN role ON role.id = p.role_id
            INNER JOIN player_war ON player_war.player_id = p.id
            INNER JOIN war ON player_war.war_id = war.id
            LEFT JOIN player_war war_played ON war_played.player_id = p.id AND war_played.collection_played > 0
            LEFT JOIN player_war war_collection ON war_collection.player_id = p.id AND war_collection.collection_played < 3 AND war_collection.war_id > 24 AND war_collection.war_id != (SELECT MAX(id) FROM war)
            LEFT JOIN player_war war_missed ON war_missed.player_id = p.id AND war_missed.battle_played = 0 AND war_missed.collection_played > 0 AND war_missed.war_id > 24 AND war_missed.war_id != (SELECT MAX(id) FROM war)
            LEFT JOIN (
                SELECT player_id,
                SUM(cards_earned) as total_cards_earned,
                SUM(collection_played) as total_collection_played,
                SUM(collection_won) as total_collection_won,
                SUM(battle_played) as total_battle_played,
                SUM(battle_won) as total_battle_won
                FROM player_war
                GROUP BY player_id
            ) pw1 ON p.id = pw1.player_id
            JOIN player_deck pd ON pd.player_id = p.id AND pd.current = 1
            JOIN card_deck cd ON cd.deck_id = pd.deck_id
            JOIN cards c ON c.id = cd.card_id
            WHERE tag = \"%s\"
            AND war.id > 24
        ";

        $secondPattern = "
            SELECT
            GROUP_CONCAT(DISTINCT c.cr_id) cr_ids, GROUP_CONCAT(DISTINCT c.card_key) card_keys, ROUND(AVG(c.elixir), 1) as elixir_cost,
            players.id as playerId, players.tag, players.name as playerName, players.rank, players.trophies, players.max_trophies, role.name as playerRole, players.exp_level as level,
            arena.arena as arena, arena.name as arenaName, players.donations, players.donations_received,
            arena.trophy_limit, arena.arena_id
            FROM players
            INNER JOIN arena ON arena.arena_id = players.arena
            INNER JOIN role ON role.id = players.role_id
            JOIN player_deck pd ON pd.player_id = players.id AND pd.current = 1
            JOIN card_deck cd ON cd.deck_id = pd.deck_id
            JOIN cards c ON c.id = cd.card_id
            WHERE tag = \"%s\"
        ";

        if (!is_array($GLOBALS['db']->query(sprintf($query, $this->sTag))->fetch())) {
            $player = $GLOBALS['db']->query(sprintf($secondPattern, $this->sTag))->fetch();
            $this->id = $player['playerId'];
            $this->name = $player['playerName'];
            $this->trophies = $player['trophies'];
            $this->maxTrophies = $player['max_trophies'];
            $this->role = $player['playerRole'];
            $this->level = $player['level'];
            $this->rank = $player['rank'];
            $this->arena = new Arena($player['arena_id'], $player['arenaName'], $player['arena'], $player['trophy_limit']);
            $this->donations = $player['donations'];
            $this->donationsReceived = $player['donations_received'];
            $this->currentDeck = new Deck($player['cr_ids'], $player['card_keys'], $player['elixir_cost']);
            $this->warPlayed = 0;
            $this->collectionMissed = 0;
            $this->battleMissed = 0;
            $this->battlePlayed = 0;
            $this->battleWon = 0;
            $this->collectionPlayed = 0;
            $this->collectionWon = 0;
            $this->cardsEarned = 0;
        } else {
            $player = $GLOBALS['db']->query(sprintf($pattern, $this->sTag))->fetch();
            $this->id = $player['playerId'];
            $this->name = $player['playerName'];
            $this->trophies = $player['trophies'];
            $this->maxTrophies = $player['max_trophies'];
            $this->role = $player['playerRole'];
            $this->level = $player['level'];
            $this->rank = $player['rank'];
            $this->arena = new Arena($player['arena_id'], $player['arenaName'], $player['arena'], $player['trophy_limit']);
            $this->donations = $player['donations'];
            $this->donationsReceived = $player['donations_received'];
            $this->currentDeck = new Deck($player['cr_ids'], $player['card_keys'], $player['elixir_cost']);
            $this->warPlayed = $player['total_war_played'];
            $this->collectionMissed = $player['missed_collection'];
            $this->battleMissed = $player['missed_war'];
            $this->battlePlayed = $player['total_battle_played'];
            $this->battleWon = $player['total_battle_won'];
            $this->collectionPlayed = $player['total_collection_played'];
            $this->collectionWon = $player['total_collection_won'];
            $this->cardsEarned = $player['total_cards_earned'];
        }

        return $this;
    }

    public function setLastUpdated() {
        if ($this->getLastUpdated() != null) {
            $this->setLastUpdatedPlayer();
        } else {
            $this->insertLastUpdatedPlayer();
        }
    }

    /**
     * Date de dernière mise à jour du joueur
     */
    public function getLastUpdated() {
        $pattern = "
            SELECT id, updated
            FROM last_updated
            WHERE tag = \"%s\"
        ";

        $result = $GLOBALS['db']->query(utf8_decode(sprintf($pattern, $this->sTag)))->fetch();

        if (!empty($result['updated']))
            return strtotime($result['updated']);

        return null;
    }

    public function setLastUpdatedPlayer() {
        $pattern = "
            UPDATE last_updated
            SET updated = NOW()
            WHERE page_name = 'player'
            AND tag = \"%s\"
        ";
        $GLOBALS['db']->query(sprintf($pattern, $this->sTag));
    }

    public function insertLastUpdatedPlayer() {
        $pattern = "
            INSERT INTO last_updated (page_name, updated, tag)
            VALUES ('player', NOW(), \"%s\")
        ";
        $GLOBALS['db']->query(sprintf($pattern, $this->sTag));
    }

    public function updatePlayer($name, $rank, $trophies, $role, $expLevel, $arena, $donations, $donationsReceived) {
        $roleId = $this->getRoleIdByMachineName($role);
        $arenaId = $this->getArenaIdByMachineName($arena);
        $this->setPlayerId();
        if ($this->getId() != null && $this->getId() > 0) {
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
            players.in_clan = 1
            WHERE players.tag = \"%s\"
        ";
            $query = utf8_decode(sprintf($pattern, $name, $rank, $trophies, $roleId, $expLevel, $arenaId, $donations, $donationsReceived, $this->sTag));
        } else {
            $pattern = "
            INSERT INTO players (players.name, tag, rank, trophies, role_id, exp_level, in_clan, arena, donations,
            donations_received)
            VALUES (\"%s\", \"%s\", %d, %d, %d, %d, %d, %d, %d, %d)
        ";
            $query = utf8_decode(sprintf($pattern, $name, $this->sTag, $rank, $trophies, $roleId,
                $expLevel, 1, $arenaId, $donations, $donationsReceived));
        }
        $GLOBALS['db']->query($query);
        //TODO revoir l'update pour la courbe des trophées
    }

    public function getRoleIdByMachineName($machineName) {
        $pattern = "
            SELECT id
            FROM role
            WHERE machine_name
            LIKE \"%s\"
        ";
        return $GLOBALS['db']->query(sprintf($pattern, $machineName))->fetch()['id'];
    }

    public function getArenaIdByMachineName($machineName) {
        $pattern = "
            SELECT arena_id
            FROM arena
            WHERE arena LIKE \"%s\"
        ";
        return $GLOBALS['db']->query(sprintf($pattern, $machineName))->fetch()['arena_id'];
    }

    public function setPlayerId() {
        $pattern = "
        SELECT p.id
        FROM players p
        WHERE p.tag = \"%s\"
        ";

        $this->id = $GLOBALS['db']->query(sprintf($pattern, $this->sTag))->fetch()['id'];
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Récupère les infos de l'API sur le joueur
     */
    public function getPlayerFromApi() {
        $url = "players/%23" . $this->sTag;
        return ClashRoyaleApi::getRequest($url);
    }

    public function getPlayerBattlesFromApi() {
        $url = "players/%23" . $this->sTag . "/battlelog";
        return ClashRoyaleApi::getRequest($url);
    }

    /**
     * Permet d'update en base les trophées max d'un joueur
     * @param $trophies int trophées max
     */
    public function updateMaxTrophies($trophies) {
        $pattern = "
            UPDATE players
            SET players.max_trophies = %d
            WHERE players.tag = \"%s\"
        ";
        $GLOBALS['db']->query(sprintf($pattern, $trophies, $this->sTag));
    }

    /**
     * Permet de retourner les ID de cartes du deck en cours d'un joueur
     * @param $deck array de CR_ID
     * @return array de card_id
     */
    public function getCardsIds($deck) {
        $pattern = "
            SELECT cards.id
            FROM cards
            WHERE name IN (%s)
        ";

        $cardIds = "";
        foreach ($deck as $card) {
            $cardIds .= "'" . $card['name'] . "'";
            $cardIds .= ",";
        }
        $currentDeck = array();
        $query = sprintf($pattern, rtrim($cardIds, ","));
        foreach ($GLOBALS['db']->query($query)->fetchAll() as $cardId) {
            array_push($currentDeck, intval($cardId['id']));
        }
        return $currentDeck;
    }

    public function updatePlayerCards($cards) {
        foreach ($cards as $card) {
            $cardId = $this->getCardByCrId($card['id']);

            $level = $this->getCardLevelByPlayer($cardId);
            if (is_array($level)) {
                $this->updateCardLevelByPlayer($cardId, $card['level'], $card['count']);
            } else {
                $this->insertCardLevelByPlayer($cardId, $card['level'], $card['count']);
            }
        }
    }

    public function getCardByCrId($crId) {
        $pattern = "
            SELECT id, card_key, rarity
            FROM cards
            WHERE cards.cr_id = %d
        ";

        return intval($GLOBALS['db']->query(sprintf($pattern, $crId))->fetch()['id']);
    }

    public function getCardLevelByPlayer($card) {
        $pattern = "
            SELECT level
            FROM card_level
            WHERE card_id = %d
            AND player_id = %d
        ";
        return $GLOBALS['db']->query(sprintf($pattern, $card, $this->id))->fetch();
    }

    public function updateCardLevelByPlayer($card, $level, $quantity) {
        $pattern = "
            UPDATE card_level
            SET level = %d,
            quantity = %d
            WHERE card_id = %d
            AND player_id = %d
        ";
        $GLOBALS['db']->query(sprintf($pattern, $level, $quantity, $card, $this->id));
    }

    public function insertCardLevelByPlayer($card, $level, $quantity) {
        $pattern = "
    INSERT INTO card_level(card_id, player_id, level, quantity)
    VALUES (%d, %d, %d, %d)
    ";
        $GLOBALS['db']->query(sprintf($pattern, $card, $this->id, $level, $quantity));
    }

    public function updateDeck($currentDeck) {
        $this->disableAllDeck();
        $deckId = $this->getDeckIdFromCards($currentDeck[0], $currentDeck[1], $currentDeck[2], $currentDeck[3],
            $currentDeck[4], $currentDeck[5], $currentDeck[6], $currentDeck[7]);

        if ($this->getPlayerDeck($deckId) != null) {
            $this->enableOldDeck($deckId);
        } else if ($deckId != null && $deckId > 0) {
            $this->createPlayerDeck($deckId);
        } else {
            $deckId = $this->createDeck();
            for ($i = 0; $i <= 7; $i++) {
                $this->insertCardDeck($currentDeck[$i], $deckId);
            }
            $this->createPlayerDeck($deckId);
        }

    }

    public function disableAllDeck() {
        $pattern = "
            UPDATE player_deck pd
            SET pd.current = 0
            WHERE pd.player_id = %d
        ";

        $GLOBALS['db']->query(sprintf($pattern, $this->id));
    }

    public function getDeckIdFromCards($card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8) {
        $pattern = "
            SELECT deck_id
            FROM card_deck
            WHERE card_id IN (%d, %d, %d, %d, %d, %d, %d, %d)
            GROUP BY deck_id
            HAVING COUNT(card_id) = 8
        ";

        return intval($GLOBALS['db']->query(sprintf($pattern, $card1, $card2, $card3, $card4, $card5, $card6, $card7, $card8))->fetch()['deck_id']);
    }

    public function getPlayerDeck($deckId) {
        $pattern = "
            SELECT pd.id, pd.current
            FROM player_deck pd
            WHERE pd.deck_id = %d
            AND pd.player_id = %d
        ";

        return $GLOBALS['db']->query(sprintf($pattern, $deckId, $this->id))->fetch();
    }

    public function enableOldDeck($deckId) {
        $pattern = "
                UPDATE player_deck pd
                SET pd.current = 1
                WHERE pd.player_id = %d
                AND pd.deck_id = %d
            ";

        $GLOBALS['db']->query(sprintf($pattern, $this->id, $deckId));
    }

    public function createPlayerDeck($deckId) {
        $pattern = "
            INSERT INTO player_deck (deck_id, player_id)
            VALUES (%d, %d)
        ";
        $GLOBALS['db']->query(sprintf($pattern, $deckId, $this->id));
    }

    public function createDeck() {
        $query = "
            INSERT INTO decks (id)
            VALUES ('')
        ";

        $GLOBALS['db']->query($query);
        return $GLOBALS['db']->lastInsertId();
    }

    public function insertCardDeck($card, $deck) {
        $pattern = "
    INSERT INTO card_deck(card_id, deck_id)
    VALUES (%d, %d)
    ";
        $GLOBALS['db']->query(sprintf($pattern, $card, $deck));
    }

    public function getTag() {
        return $this->sTag;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getTrophies() {
        return $this->trophies;
    }

    public function setTrophies($trophies) {
        $this->trophies = $trophies;
    }

    public function getMaxTrophies() {
        return $this->maxTrophies;
    }

    public function setMaxTrophies($maxTrophies) {
        $this->maxTrophies = $maxTrophies;
    }

    public function getLevel() {
        return $this->level;
    }

    public function setLevel($level) {
        $this->level = $level;
    }

    public function getRole() {
        return $this->role;
    }

    public function setRole($role) {
        $this->role = $role;
    }

    public function getRank() {
        return $this->rank;
    }

    public function setRank($rank) {
        $this->rank = $rank;
    }

    public function getArena() {
        return $this->arena;
    }

    public function setArena($arena) {
        $this->arena = $arena;
    }

    public function getDonations() {
        return $this->donations;
    }

    public function setDonations($donations) {
        $this->donations = $donations;
    }

    public function getDonationsReceived() {
        return $this->donationsReceived;
    }

    public function setDonationsReceived($donationsReceived) {
        $this->donationsReceived = $donationsReceived;
    }

    public function getWarPlayed() {
        return $this->warPlayed;
    }

    public function setWarPlayed($warPlayed) {
        $this->warPlayed = $warPlayed;
    }

    public function getCollectionPlayed() {
        return $this->collectionPlayed;
    }

    public function setCollectionPlayed($collectionPlayed) {
        $this->collectionPlayed = $collectionPlayed;
    }

    public function getCollectionMissed() {
        return $this->collectionMissed;
    }

    public function setCollectionMissed($collectionMissed) {
        $this->collectionMissed = $collectionMissed;
    }

    public function getCollectionWon() {
        return $this->collectionWon;
    }

    public function setCollectionWon($collectionWon) {
        $this->collectionWon = $collectionWon;
    }

    public function getCardsEarned() {
        return $this->cardsEarned;
    }

    public function setCardsEarned($cardsEarned) {
        $this->cardsEarned = $cardsEarned;
    }

    public function getBattlePlayed() {
        return $this->battlePlayed;
    }

    public function setBattlePlayed($battlePlayed) {
        $this->battlePlayed = $battlePlayed;
    }

    public function getBattleMissed() {
        return $this->battleMissed;
    }

    public function setBattleMissed($battleMissed) {
        $this->battleMissed = $battleMissed;
    }

    public function getBattleWon() {
        return $this->battleWon;
    }

    public function setBattleWon($battleWon) {
        $this->battleWon = $battleWon;
    }

    public function getCurrentDeck() {
        return $this->currentDeck;
    }

    public function setCurrentDeck($currentDeck) {
        $this->currentDeck = $currentDeck;
    }

    public function setSTag($sTag) {
        $this->sTag = $sTag;
    }

    public function setANextChests($aNextChests) {
        $this->aNextChests = $aNextChests;
    }

    public function getPausesString() {
        $stringList = array();
        $dateMap = array();
        foreach ($this->getPauses() as $i => $pause) {
            $time = $pause->getPauseDate();
            $previousDay = $time - 86400000;

            if (in_array($previousDay, array_values($dateMap))) {
                $key = array_keys($dateMap)[count($dateMap) - 1];
                $dateMap[$key] = $time;
            } else {
                $dateMap[$time] = $time;
            }
        }

        foreach ($dateMap as $key => $value) {
            if ($key == $value) {
                array_push($stringList, "Le " . date('d/m/Y', ($value / 1000)));
            } else {
                array_push($stringList, "Du " . date('d/m/Y', ($key / 1000)) . ' au ' . date('d/m/Y', ($value / 1000)));
            }
        }
        return $stringList;
    }

    public function getPauses() {
        return $this->pauses;
    }

    public function setPauses($pauses) {
        $this->pauses = $pauses;
    }
}