<?php

class Player {

    private $sTag = null;
    private $aNextChests = null;

    public function __construct($sTag) {
        $this->sTag = $sTag;
    }

    public function getTag(){
        return $this->sTag;
    }

    /**
     * Récupère la liste des prochains coffres du joueur
     * @return array liste des prochains coffres
     */
    public function getNextChests() {
        if($this->aNextChests != null)
            return $this->aNextChests;

        $aChests = getPlayerChestsFromApi($GLOBALS['api'], $this->sTag);

        $this->aNextChests = array();
        $fAddChest = function ($sChestType, $iChestCount){
            array_push($this->aNextChests, array('type' => $sChestType, 'count' => $iChestCount));
        };
        foreach($aChests['upcoming'] as $iChestIndex => $sChestType){
            $fAddChest($sChestType, $iChestIndex+1);
            if(count($this->aNextChests) == 3){
                break;
            }
        }
        unset($aChests['upcoming']);
        foreach($aChests as $sChestType => $iChestCount){
            $fAddChest($sChestType, $iChestCount);
        }

        uasort($this->aNextChests, function($aChestA, $aChestB){
            if ($aChestA['count'] == $aChestB['count']) {
                return 0;
            }
            return ($aChestA['count'] < $aChestB['count']) ? -1 : 1;
        });

        return $this->aNextChests;
    }
}