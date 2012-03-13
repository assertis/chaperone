<?php
/**
 * A ContextRuleSet is a specific instance of a Role.  Whereas a Ruleset has
 * rules defining which context items must be defined, a ContextRuleSet has
 * actually been attached to a context.  The rules attached to the context are
 * either Wildcard rules (all values for that context item are allowed) or
 * Context Rules (a specific value is defined for that context item)
 *
 * @author steve
 */
class ChaperoneContextRuleSetCopy {
    private $wildcardRuleArray;
    private $contextRuleArray;

    public function __construct() {
        $this->wildcardRuleArray = array();
        $this->contextRuleArray = array();
    }


    /*
     * This method adds a Context Rule.  This consists of a context item and a value for it
     */
    public function addContextRule($contextItem, $contextValue) {
        if (!is_scalar($contextItem))
            throw new Exception('Context item "'.$contextItem.'" has an invalid name');
        if (array_key_exists($contextItem, $this->wildcardRuleArray) OR array_key_exists($contextItem, $this->contextRuleArray))
            throw new Exception('Context item "'.$contextItem.'" already exists in rule');
        if (!is_scalar($contextValue))
            throw new Exception('Context item "'.$contextItem.'" has an invalid value');
        $this->contextRuleArray[$contextItem] = $contextValue;
    }


    /*
     * This method adds a Wildcard Rule.  This only consists of a context item, as all values are valid for it
     */
    public function addWildcardRule($contextItem) {
        if (!is_scalar($contextItem))
            throw new Exception('Context item "'.$contextItem.'" has an invalid name');
        if (array_key_exists($contextItem, $this->wildcardRuleArray) OR array_key_exists($contextItem, $this->contextRuleArray))
            throw new Exception('Context item "'.$contextItem.'" already exists in rule');
        $this->wildcardRuleArray[$contextItem] = TRUE;
    }


    /*
     * Returns a Boolean indicating whether a rule (either context or wildcard) exists for the given Context Item
     */
    public function isRuleFor($contextItem) {
        return (array_key_exists($contextItem, $this->wildcardRuleArray) OR array_key_exists($contextItem, $this->contextRuleArray));        
    }


    /*
     * Returns a Boolean indicating whether a wildcard rule exists for the given Context Item
     */
    public function isWildcardRuleFor($contextItem) {
        return (array_key_exists($contextItem, $this->wildcardRuleArray));
    }


    /*
     * If a context rule for the Context Item exists, this returns the value.
     * If it doesn't exist, it returns NULL
     */
    public function getContextRuleValue($contextItem) {
        return (array_key_exists($contextItem, $this->contextRuleArray)) ? $this->contextRuleArray[$contextItem] : NULL;
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
         * Test all of our own wildcard items.  They must have matching wildcard
         * items in the superset object
         */
        foreach ($this->wildcardRuleArray AS $context_item=>$ignore) {
            if (!array_key_exists($context_item, $supersetObj->wildcardRuleArray))
                return FALSE;
        }

        /*
         * Test all of our own context items.  They must have a matching context
         * item OR a wildcard item in the superset object
         */
        foreach ($this->contextRuleArray AS $context_item=>$value) {
            if (!array_key_exists($context_item, $supersetObj->wildcardRuleArray)) {
                if (!array_key_exists($context_item, $supersetObj->contextRuleArray) OR ($supersetObj->contextRuleArray[$context_item] !== $value))
                    return FALSE;
            }
        }

        // Success!
        return TRUE;
    }
}
?>