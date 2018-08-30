<?php
require 'arena.class.php';
require 'deck.class.php';

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

    public function __construct($sTag) {
        $this->sTag = $sTag;
    }

    /**
     * Récupère la liste des prochains coffres du joueur
     * @return array liste des prochains coffres
     */
    public function getNextChests() {
        if ($this->aNextChests != null)
            return $this->aNextChests;

        $aChests = getPlayerChestsFromApi($GLOBALS['api'], $this->sTag);

        $this->aNextChests = array();
        $fAddChest = function ($sChestType, $iChestCount) {
            array_push($this->aNextChests, array('type' => $sChestType, 'count' => $iChestCount));
        };
        foreach ($aChests['upcoming'] as $iChestIndex => $sChestType) {
            $fAddChest($sChestType, $iChestIndex + 1);
            if (count($this->aNextChests) == 3) {
                break;
            }
        }
        unset($aChests['upcoming']);
        foreach ($aChests as $sChestType => $iChestCount) {
            $fAddChest($sChestType, $iChestCount);
        }

        uasort($this->aNextChests, function ($aChestA, $aChestB) {
            if ($aChestA['count'] == $aChestB['count']) {
                return 0;
            }
            return ($aChestA['count'] < $aChestB['count']) ? -1 : 1;
        });

        return $this->aNextChests;
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
            p.donations, p.donations_received as received, 
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
            arena.arena as arena, arena.name as arenaName, players.donations, players.donations_received as received,
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

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
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
}