<?php
require_once('Chaperone.php');
/**
 * This class is used to look up items in the chaperone_namespace table
 * 
 * Since we are likely to be looking up the same item a lot, this class employs a cache
 *
 * @author steve
 */
class ChaperoneNamespace {
    
    private static $cacheNameArray = array();
    private static $cacheIdArray = array();
    private static $namespace = NULL;


    /*
     * Private constructor prevents instantiation
     */
    private function __constr() {}


    /*
     * Sets the current namespace
     * 
     * @param   string                      $namespace
     */
    public function setNamespace($namespace) {
        self::$namespace = $namespace;
    }


    /*
     * Returns the current namespace
     * 
     * @returns string
     */
    public function getNamespace() {
        return self::$namespace;
    }

    /*
     * Looks up a given ID and returns the name for the item.  If not found, returns NULL
     * 
     * @param   int                         $namespaceId
     * @returns string/null
     * @throws  ChaperoneException          Duplicate namespace found
     */
    public static function getNameForId($namespaceId) {

        // If we already have the item in cache, return it
        if (array_key_exists($namespaceId, self::$cacheIdArray)) return self::$cacheIdArray[$namespaceId];
        
        // Look up the ID in the database
        $pdo = Chaperone::getPDO();
        $schema = Chaperone::getSchema();
        $sql = 'SELECT  name
                FROM    '.$schema.'.chaperone_namespace
                WHERE   id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $namespaceId);
        $stmt->execute();

        // If not found, cache the fact that we couldn't find it and return NULL
        if ($stmt->rowCount() === 0) return (self::$cacheIdArray[$namespaceId] = NULL);

        // Otherwise, get the data, cache it (in both directions) and return it
        $namespaceArray = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespace = $namespaceArray['name'];
        if (array_key_exists($namespace, self::$cacheNameArray)) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Duplicate namespace "'.$namespace.'" found');
        }
        self::$cacheNameArray[$namespace] = $namespaceId;
        return (self::$cacheIdArray[$namespaceId] = $namespace);
    }


    /*
     * Looks up a given name and returns the ID for the item.  If not found, returns NULL
     * 
     * @param   string                      $namespace
     * @returns int/null
     * @throws  ChaperoneException          More than one instance of namespace found
     */
    public static function getIdForName($namespace) {

        // If we already have the item in cache, return it
        if (array_key_exists($namespace, self::$cacheNameArray)) return self::$cacheNameArray[$namespace];
        
        // Look up the name in the database
        $pdo = Chaperone::getPDO();
        $schema = Chaperone::getSchema();
        $sql = 'SELECT  id
                FROM    '.$schema.'.chaperone_namespace
                WHERE   name = :name';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':name', $namespace);
        $stmt->execute();

        // If not found, cache the fact that we couldn't find it and return NULL
        $rowCount = $stmt->rowCount();
        if ($rowCount === 0) return (self::$cacheNameArray[$namespace] = NULL);
        
        // If multiple items were found, there's something wrong with one of the table indexes
        if ($rowCount > 1) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('More than one instance of "'.$namespace.'" was found');
        }

        // Otherwise, get the data, cache it (in both directions) and return it
        $namespaceArray = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespaceId = $namespaceArray['id'];
        if (array_key_exists($namespaceId, self::$cacheIdArray)) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Duplicate namespace ID "'.$namespaceId.'" found');
        }
        self::$cacheIdArray[$namespaceId] = $namespace;
        return (self::$cacheNameArray[$namespace] = $namespaceId);
    }


    /*
     * This helper method splits the namespace from the rest of the name
     * If no namespace is found, the default is returned
     * This method does not look up the namespace
     * 
     * @param   string                      $resourceName
     * @returns array
     * @throws  ChaperoneException          Invalid Resource name
     */
    public static function splitResourceName($resourceName) {
        // If the item has a namespace, get it
        $nameSplit = explode('.', $resourceName);
        switch (count($nameSplit)) {
          case 1:
            if (self::$namespace === NULL) {
                require_once('ChaperoneException.php');
                throw new ChaperoneException('Default namespace has not been set');
            }
            return array('namespace'=>self::$namespace , 'resourceName'=>$resourceName);
            break;
          case 2:
            return array('namespace'=>$nameSplit[0] , 'resourceName'=>$nameSplit[1]);
            break;
          default:
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Invalid Resource name "'.$resourceName.'"');
        }
    }
}
?>