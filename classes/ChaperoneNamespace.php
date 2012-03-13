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
    
    /*
     * Looks up a given ID and returns the name for the item.  If not found, returns NULL
     */
    public function getNameForId($namespaceId) {

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
        if (array_key_exists($namespace, self::$cacheNameArray)) throw new Exception('Duplicate namespace "'.$namespace.'" found');
        self::$cacheNameArray[$namespace] = $namespaceId;
        return (self::$cacheIdArray[$namespaceId] = $namespace);
    }

    /*
     * Looks up a given name and returns the ID for the item.  If not found, returns NULL
     */
    public function getIdForName($namespace) {

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
        if ($rowCount > 1) throw new Exception('More than one instance of "'.$namespace.'" was found');

        // Otherwise, get the data, cache it (in both directions) and return it
        $namespaceArray = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespaceId = $namespaceArray['id'];
        if (array_key_exists($namespaceId, self::$cacheIdArray)) throw new Exception('Duplicate namespace ID "'.$namespaceId.'" found');
        self::$cacheIdArray[$namespaceId] = $namespace;
        return (self::$cacheNameArray[$namespace] = $namespaceId);
    }
}
?>