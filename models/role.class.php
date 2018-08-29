<?php
/**
 * Created by PhpStorm.
 * User: lmironne
 * Date: 28/08/18
 * Time: 14:23
 */

class Role {
//    private $id;
    private $name;
    private $machineName;

    public function __construct($name, $machineName) {
        $this->name = $name;
        $this->machineName = $machineName;
    }

    /**
     * @return string contenant le machine name du role
     */
    public function getMachineName() {
        return $this->machineName;
    }

    /**
     * @return string contenant le nom du role
     */
    public function getName() {
        return $this->name;
    }


}