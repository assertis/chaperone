<?php
require_once('ChaperoneCurrentSession.php');
/*
 * @author Steve Criddle
 */
class Chaperone extends ChaperoneCurrentSession {

    // Schema where Chaperone tables are stored.
    const databaseSchema='global';

    // Database PDO for operations
    private static $PDO = NULL;
    
    /*
     * Setter and getter for PDO (saves us passing it around)
     */
    public static function setPDO($PDO) {
        self::$PDO = $PDO;
    }
    public static function getPDO() {
        if (self::$PDO === NULL) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Chaperone PDO has not been set');
        }
        return self::$PDO;
    }
    
    /*
     * This method is provided so that unit testing can reset the PDO
     */
    public static function resetPDO() {
        self::$PDO = NULL;
    }
    
    /*
     * Allows a namespace to be set.  This allows you to specify contexts
     * without namespaces when attaching roles or checking actions.
     */
    public static function setNamespace($namespace) {
        require_once('ChaperoneNamespace.php');
        ChaperoneNamespace::setNamespace($namespace);
    }
    
    /*
     * Returns the current namespace
     */
    public static function getNamespace() {
        return ChaperoneNamespace::getNamespace();
    }
}
?>