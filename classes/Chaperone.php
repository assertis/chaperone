<?php
class Chaperone {
    
    // Database PDO for operations
    private static $PDO = NULL;
    
    // Schema where Chaperone tables are stored.
    private static $databaseSchema = 'global';
    
    // Dirty hack for now, since we only have one namespace
    private static $namespace = 'b2b';
    private static $namespaceId = 1;

    public static $debug = FALSE;
    
    /*
     * Setter and getter for PDO (saves us passing it around)
     */
    public static function setPDO($PDO) { self::$PDO = $PDO; }
    public static function getPDO() {
        if (self::$PDO === NULL) throw new Exception('Chaperone PDO has not been set');
        return self::$PDO;
    }
    public static function getSchema() { return self::$databaseSchema; }

    /*
     * This helper method splits the namespace from the rest of the name
     * If no namespace is found, the default is returned
     * For now it does not allow namespaces to be specified
     */
    public static function splitResourceName($resourceName) {
        // If the item has a namespace, get it
        $nameSplit = explode('.', $resourceName);
        switch (count($nameSplit)) {
          case 1:
            return array('namespaceId'=>self::$namespaceId , 'namespace'=>self::$namespace , 'resourceName'=>$resourceName);
            break;
          case 2:
            // If namespace matches the one we are set to, we can do this
            if ($nameSplit[0] === self::$namespace) return array('namespaceId'=>self::$namespaceId, 'namespace'=>self::$namespace , 'resourceName'=>$nameSplit[1]);

            // Otherwise, throw an exception
            throw new Exception('Namespace lookup currently unsupported');
            break;
          default:
            throw new Exception('Invalid Resource name "'.$resourceName.'"');
        }
    }
}
?>