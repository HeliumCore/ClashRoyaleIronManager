<?php
/**
 * Fichier d'initialisation de l'application
 * Prépare Smarty pour le MVC
 */

// Init conf
require('conf.php');

// Init database
require('database.php');

// Init Smarty
require('libs/smarty-3.1.32/bootstrap.php');

// Moteur MVC
require('mvcengine.class.php');
MVCEngine::create();
