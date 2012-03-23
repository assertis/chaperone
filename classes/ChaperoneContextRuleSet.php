<?php
/**
 * A ContextRuleSet is a specific instance of a Role.  Whereas a Ruleset has
 * rules defining which context items must be defined, a ContextRuleSet has
 * actually been attached to a context.  The rules attached to the context are
 * either Wildcard rules (all values for that context item are allowed) or
 * Context Rules (a specific value is defined for that context item)
 *
 * @author Steve Criddle
 */
class ChaperoneContextRuleSet {
    private $ruleArray;

    public function __construct() {
        $this->ruleArray = array();
    }


    /*
     * This helper method validates that the passed Context Item is a string that is not duplicated
     */
    private function validateContextItemValue($contextItem) {
        if (!is_string($contextItem)) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Context item "'.$contextItem.'" has an invalid name');
        }
    }

    
    private function validateContextItemNotDuplicate($contextItem) {
        if (array_key_exists($contextItem, $this->ruleArray)) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Context item "'.$contextItem.'" already exists in rule');
        }
    }
    
    
    /*
     * This method adds a Context Rule.  This consists of a context item and a value for it
     */
    public function addContextRule($contextItem, $contextValue) {

        $this->validateContextItemValue($contextItem);
        $this->validateContextItemNotDuplicate($contextItem);
        if (!is_string($contextValue) AND !is_integer($contextValue)) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Context item "'.$contextItem.'" has an invalid value');
        }
        $this->ruleArray[$contextItem] = $contextValue;
    }


    /*
     * This method adds a Wildcard Rule.  This only consists of a context item, as all values are valid for it
     */
    public function addWildcardRule($contextItem) {
        $this->validateContextItemValue($contextItem);
        $this->validateContextItemNotDuplicate($contextItem);
        $this->ruleArray[$contextItem] = NULL;
    }


    /*
     * Returns a Boolean indicating whether a rule (either context or wildcard) exists for the given Context Item
     */
    public function isRuleFor($contextItem) {
        $this->validateContextItemValue($contextItem);
        return (array_key_exists($contextItem, $this->ruleArray));        
    }


    /*
     * Returns a Boolean indicating whether a wildcard rule exists for the given Context Item
     */
    public function isWildcardRuleFor($contextItem) {
        $this->validateContextItemValue($contextItem);
        return ($this->isRuleFor($contextItem) AND ($this->ruleArray[$contextItem] === NULL));
    }


    /*
     * If a context rule for the Context Item exists, this returns the value.
     * If it doesn't exist, it returns NULL
     */
    public function getContextRuleValue($contextItem) {
        $this->validateContextItemValue($contextItem);
        return ($this->isRuleFor($contextItem)) ? $this->ruleArray[$contextItem] : NULL;
    }


    /*
     * Tests whether the current object is a subset of the passed object
     * Both objects are of class ChaperoneContextRuleSet
     * Every context item in the current object should exist in the passed object
     * Wildcard items in the current object must be wildcards in the passed object
     * Context items in the current object must either match or be a wildcard in the passed object
     */
    public function isSubsetOf(ChaperoneContextRuleSet $supersetObj) {

        /*
         * Iterate through our rules, ensuring all of them are fulfilled by the superset
         */
        foreach ($this->ruleArray AS $context_item=>$value) {
            if (!array_key_exists($context_item, $supersetObj->ruleArray))
                return FALSE;

            // Wildcard rules must match.  Context rules can match or be a wildcard in the superset
            if (($supersetObj->ruleArray[$context_item] !== $value) AND ($supersetObj->ruleArray[$context_item] !== NULL))
                return FALSE;
        }

        // Success!
        return TRUE;
    }
}
?>