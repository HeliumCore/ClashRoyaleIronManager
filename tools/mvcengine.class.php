<?php
/**
 * Moteur pour la gestion Model/Vue/Controleur
 * Initialise smarty
 */
class MVCEngine {

    private static $oInstance = null;

    private $oSmarty = null;
    private $aScripts = array();

    /**
     * Construit le moteur (initialise Smarty)
     */
    private function __construct() {
        $this->oSmarty = new Smarty();

        // Dossier des templates compilées de Smarty
        if(defined('SMARTY_COMPILE_DIR') && !empty(SMARTY_COMPILE_DIR)){
            if(!is_dir(SMARTY_COMPILE_DIR)){
                mkdir(SMARTY_COMPILE_DIR, 0777, true);
            }
            $this->oSmarty->setCompileDir(SMARTY_COMPILE_DIR);
        }

        // Dossier de cache Smarty
        if(defined('SMARTY_CACHE_DIR') && !empty(SMARTY_CACHE_DIR)){
            if(!is_dir(SMARTY_CACHE_DIR)){
                mkdir(SMARTY_CACHE_DIR, 0777, true);
            }
            $this->oSmarty->setCacheDir(SMARTY_CACHE_DIR);
        }

        // Dossier contenant les templates pour Smarty
        $this->oSmarty->setTemplateDir('views');
    }

    /**
     * Initialise le singleton
     */
    public static function create(){
        assert(self::$oInstance === null);
        self::$oInstance = new MVCEngine();
    }

    /**
     * Récupère l'instance du singleton (crée la nouvelle instance si nécessaire)
     */
    public static function getInstance() {
        assert(self::$oInstance != null);
        return self::$oInstance;
    }

    /**
     * Récupère l'objet Smarty associé à la classe
     * @return Smarty objet Smarty
     */
    public function getSmarty(){
        return $this->oSmarty;
    }

    /**
     * Fais le rendu de la template en fonction de la page, ou du nom de la template passée en paramètre
     * @param string $sTemplateName Nom de la template (sans l'extension et le dossier)
     * @throws SmartyException
     */
    public function renderTemplate($sTemplateName = null){
        $sTemplateName = empty($sTemplateName)?basename($_SERVER['SCRIPT_NAME'],'.php'):$sTemplateName;

        if(file_exists('js/'.$sTemplateName.'.js'))
            $this->_addScript($sTemplateName);

        $this->oSmarty->assign('scripts', $this->aScripts);

        $this->oSmarty->display('header.tpl.html');
        $this->oSmarty->display($sTemplateName.'.tpl.html');
        $this->oSmarty->display('footer.tpl.html');
    }

    /**
     * Permet de faire simplement le rendu de la page (par appel static)
     */
    public static function render($sTemplateName = null){
        self::getInstance()->renderTemplate($sTemplateName);
    }

    /**
     * Raccourci statique pour l'assignation de variables Smarty
     */
    public static function assign($mVar, $mValue=null){
        self::getInstance()->getSmarty()->assign($mVar, $mValue);
    }

    /**
     * Permet de définir facilement le titre d'une page (définit la variable de template : title)
     * @param string $sTitle Titre de la page
     */
    public static function setTitle($sTitle){
        self::assign('title', $sTitle);
    }

    private function _addScript($sScriptName){
        if(!in_array($sScriptName, $this->aScripts)){
            $this->aScripts[] = $sScriptName;
        }
    }

    /**
     * Ajoute un fichier JS à la page
     * @param string $sScriptName Nom du script
     */
    public static function addScript($sScriptName){
        self::getInstance()->_addScript($sScriptName);
    }
}
