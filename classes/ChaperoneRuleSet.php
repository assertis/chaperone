<?php
require_once('Chaperone.php');
/**
 * A RuleSet is a set of rules defining Context Items and whether they can be
 * Wildcard entries or not.  Each rule is simply a Context Item and a Boolean
 * denoting whether Wildcard is allowed or not.
 * 
 * Because the same RuleSet may (and probably will) be referenced multiple
 * times, the loadById() method employs a caching mechanism that will return a
 * reference to the same item if duplicates are requested (rather than creating
 * a new object).
 *
 * @author Steve Criddle
 */
class ChaperoneRuleSet {
    
    // Cache array used by load() to prevent multiple lookups for the same item
    private static $cacheIdArray = array();
    
    private $id = NULL;
    private $namespaceId = NULL;
    private $ruleArray;
    
    /*
     * For now, loadById() factory method should be used
     */
    private function __construct() {
        $this->ruleArray = array();
    }

    public function flushCache() {
        self::$cacheIdArray = array();
    }

    public function getNamespaceId() {
        return $this->namespaceId;
    }
    
    /*
     * Loads a given Rule Set from the database.  Returns a RuleSet object or NULL
     * 
     * @param   int                         $ruleSetId
     * @returns ChaperoneRuleSet
     * @throws  ChaperoneException          Rule Set Not Found
     * @throws  ChaperoneException          Multiple instances of Rule Set found
     */
    public static function loadById($ruleSetId) {
        
        // If a NULL Rule Set ID is passed in, pass back NULL
        if ($ruleSetId === NULL)
            return NULL;
        
        // Look in cache for item first.  If it exists, return it
        if (array_key_exists($ruleSetId, self::$cacheIdArray))
            return self::$cacheIdArray[$ruleSetId];
        
        // If there is no RuleSet ID, there is nothing to load
        if ($ruleSetId === NULL)
            return NULL;

        // Look up namespace ID on ruleset.  This is used to ensure roles and
        // actions are attached to rulesets in the same namespace
        $pdo = Chaperone::getPDO();
        $schema = Chaperone::databaseSchema;
        $sql = 'SELECT  namespace
                FROM    '.$schema.'.chaperone_rule_set
                WHERE   id = :rule_set';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':rule_set', $ruleSetId);
        $stmt->execute();

        // Ensure there is exactly one item
        $rowCount = $stmt->rowCount();
        if ($rowCount === 0) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Rule Set "'.$ruleSetId.'" not found');
        }
        if ($rowCount > 1) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Multiple instances of Rule Set "'.$ruleSetId.'" found');    // Should never happen!
        }

        // Get data
        $ruleSetRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Create object
        $ruleSetObj = new ChaperoneRuleSet();
        $ruleSetObj->id = (int)$ruleSetId;
        $ruleSetObj->namespaceId = (int)$ruleSetRow['namespace'];

        // Get rules
        $sql = 'SELECT      context_item, wildcard
                FROM        '.$schema.'.chaperone_rule
                WHERE       rule_set = :rule_set
                ORDER BY    context_item';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':rule_set', $ruleSetId);
        $stmt->execute();

        // If there are rules, add them to the object
        if ($stmt->rowCount() > 0) {
            while($ruleRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ruleSetObj->addRule($ruleRow['context_item'], (bool) $ruleRow['wildcard']);
            }
        }
        
        // Store item in cache array and return it
        self::$cacheIdArray[$ruleSetId] = $ruleSetObj;
        return $ruleSetObj;
    }


    /*
     * Method for adding a rule to the Rule Array.  Private for now, since we probably don't want outsiders tinkering
     * 
     * @param   string                      $contextItem
     * @param   boolean                     $wildcard
     * @throws  ChaperoneException          Rule item already defined
     */
    private function addRule($contextItem, $wildcard) {
        if (array_key_exists($contextItem, $this->ruleArray)) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Rule item "'.$contextItem.'" already defined');
        }
        $this->ruleArray[$contextItem] = $wildcard;
    }


    /*
     * Helper method.  Returns Boolean indicating whether a wildcard Rule exists for the given Context Item
     * 
     * @param   string                      $contextItem
     * @returns boolean
     */
    public function isWildcardRuleFor($contextItem) {
        return (array_key_exists($contextItem, $this->ruleArray) AND $this->ruleArray[$contextItem]);
    }
    
    
    /*
     * Helper method for getting rules based on their wildcard flag
     * 
     * @param   boolean                     $matchWildcard
     * @returns array
     */
    private function getMatchingRules($matchWildcard) {
        $ruleArray = array();
        foreach ($this->ruleArray AS $contextItem=>$wildcard) {
            if ($wildcard === $matchWildcard) $ruleArray[] = $contextItem;
        }
        return $ruleArray;
    }
    
    /*
     * These two methods use the helper above
     * 
     * @returns array
     */
    public function getWildcardRules() { return $this->getMatchingRules(TRUE); }
    public function getContextRules() { return $this->getMatchingRules(FALSE); }


    /*
     * This method returns the rules in a human-readable form
     * 
     * @returns string
     */
    public function getReadableRules() {
        $ruleArray = array();
        foreach($this->getWildcardRules() AS $context_item) $ruleArray[] = htmlspecialchars ($context_item).'=*';
        foreach($this->getContextRules() AS $context_item) $ruleArray[] = htmlspecialchars ($context_item).'=...';
        $rules = (count($ruleArray) == 0) ? '- None -' : join(', ', $ruleArray);
        return $rules;
    }


    /*
     * Returns a Context RuleSet for a given context, using this object's Rules
     * If any required context items are missing, an exception is thrown
     * 
     * @param   array                       $contextArray (optional)
     * @returns ChaperoneContextRuleSet
     */
    public function getContextRuleSet($contextArray=array()) {
        return $this->getContextRuleSetExceptFor(NULL, $contextArray);
    }


    /*
     * Returns a Context RuleSet for a given context, using this object's Rules,
     * with the exception of $exclude.  This allows actionLists to be built, by
     * specifically excluding a single rule
     * If any required context items are missing, an exception is thrown
     * This method is called by getContextRuleSet(), with $except set to NULL
     * 
     * @param   string/null                 $exclude
     * @param   array                       $contextArray (optional)
     * @returns ChaperoneContextRuleSet
     * @throws  ChaperoneException          Missing context item(s)
     */
    public function getContextRuleSetExceptFor($exclude, $contextArray=array()) {
        
        require_once('ChaperoneContextRuleSet.php');
        $ccrsObj = new ChaperoneContextRuleSet();

        // We check that all Context Rules are present in the context array (unless excluded)
        $missingArray = array();
        foreach ($this->getContextRules() AS $contextItem) {
            if ($contextItem !== $exclude) {
                if (array_key_exists($contextItem, $contextArray)) {
                    $ccrsObj->addContextRule($contextItem, $contextArray[$contextItem]);
                } else {
                    $missingArray[] = $contextItem;
                }
            }
        }

        // If any items are missing, throw an exception
        if (count($missingArray) > 0) {
            require_once('ChaperoneException.php');
            unset($ccrsObj);
            if (count($missingArray) === 1)
                throw new ChaperoneException('Missing context item: "'.$missingArray[0].'"');
            $missing = implode('", "', $missingArray);
            throw new ChaperoneException('Missing context items: "'.$missing.'"');
        }

        // If we made it through to here, add any Wildcard Rules (unless excluded)
        foreach ($this->getWildcardRules() AS $contextItem) {
            if ($contextItem !== $exclude)
                $ccrsObj->addWildcardRule($contextItem);
        }
        
        // Return the Context Rule Set object
        return $ccrsObj;
    }
}
?>