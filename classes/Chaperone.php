<?php
require_once('ChaperoneSession.php');
class Chaperone {
    
    // Database PDO for operations
    private static $PDO = NULL;
    
    // Schema where Chaperone tables are stored.
    private static $databaseSchema = 'global';
    
    // Dirty hack for now, since we only have one namespace
    private static $namespace = 'b2b';

    /*
     * Setter and getter for PDO (saves us passing it around)
     */
    public static function setPDO($PDO) { self::$PDO = $PDO; }
    public static function getPDO() {
        if (self::$PDO === NULL) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Chaperone PDO has not been set');
        }
        return self::$PDO;
    }
    
    public static function setNamespace($namespace) {
        require_once('ChaperoneNamespace.php');
        ChaperoneNamespace::setNamespace($namespace);
    }
    
    public static function getNamespace() {
        return ChaperoneNamespace::getNamespace();
    }
    
    public static function getSchema() { return self::$databaseSchema; }
    
    public static function clearSession() {
        ChaperoneSession::getSession()->clear();
    }

    public static function setEmailAddress($email) {
        ChaperoneSession::getSession()->setEmailAddress($email);
    }

    public static function getEmailAddress() {
        return ChaperoneSession::getSession()->getEmailAddress();
    }

    public static function isLoggedIn() {
        return ChaperoneSession::getSession()->isLoggedIn();
    }
    
    public static function attachRole($role, $contextArray=array()) {
        ChaperoneSession::getSession()->attachRole($role, $contextArray);
    }
    
    public static function isActionAllowed($action, $contextArray=array()) {
        return ChaperoneSession::getSession()->isActionAllowed($action, $contextArray);
    }

    public static function getContextValueList($action, $contextItem, $contextArray=array()) {
        return ChaperoneSession::getSession()->getContextValueList($action, $contextItem, $contextArray);
    }

}
?>