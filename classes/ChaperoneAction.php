<?php
require_once('Chaperone.php');
require_once('ChaperoneNamespace.php');
/**
 * Description of ChaperoneAction
 *
 * @author steve
 */
class ChaperoneAction {
    private $id = NULL;
    private $namespaceId = NULL;
    private $namespace = NULL;
    private $action = NULL;

    private $ruleSetArray;
    
    public function __construct() {
        $this->ruleSetArray = array();
    }

    public function getFullName() { return $this->namespace.'.'.$this->action; }

    /*
     * Returns a human-readable string of rules.  Calls getReadableRules() for
     * each RuleSet in the ruleSetArray and puts \n between each line
     */
    public function getReadableRules() {
        if (count($this->ruleSetArray) === 0) return '- None -';
        $rulesArray = array();
        foreach ($this->ruleSetArray AS $ruleSetObj) {
            $rulesArray[] = $ruleSetObj->getReadableRules();
        }
        return implode("\n", $rulesArray);
    }

    /*
     * Loads an action by unique ID.  No caching
     */
    public function loadById($id) {
        $pdo = Chaperone::getPDO();
        $schema = Chaperone::databaseSchema;
        $sql = 'SELECT      namespace, id, action
                FROM        '.$schema.'.chaperone_action
                WHERE       id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        $stmt->execute();

        $rowCount = $stmt->rowCount();
        if ($rowCount === 0) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Action #"'.$id.'" not found');
        }
        if ($rowCount > 1) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Multiple instances of Action #"'.$id.'" found');   // Should never happen!
        }

        // Load data and create object
        $actionRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $actionObj = new ChaperoneAction();
        $actionObj->id = $id;
        $actionObj->namespaceId = (int)$actionRow['namespace'];
        $actionObj->namespace = ChaperoneNamespace::getNameForId($actionObj->namespaceId);
        $actionObj->action = $actionRow['action'];
        
        $actionObj->loadRuleSets();
        
        return $actionObj;
    }

    /*
    public function loadByName($name) {
        $pdo = Chaperone::getPDO();
        $schema = Chaperone::databaseSchema;
    }
    */

    /*
     * Loads RuleSets for Action.  Each one may be cached, in which case duplicates
     * will be references to the same RuleSet
     */
    private function loadRuleSets() {

        require_once('ChaperoneRuleSet.php');
        
        // Sanity checking
        if ($this->id === NULL) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Cannot load Rule Sets - Action ID is not set');
        }
   
        $pdo = Chaperone::getPDO();
        $schema = Chaperone::databaseSchema;
        $sql = 'SELECT      crs.id AS rule_set
                FROM        '.$schema.'.chaperone_action_rule_set AS cars
                JOIN        '.$schema.'.chaperone_rule_set AS crs ON crs.id = cars.rule_set
                WHERE       cars.action = :id
                AND         crs.namespace = :namespace';    // Only allow rules in the same namespace as the action
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':namespace', $this->namespaceId);
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();

        $this->ruleSetArray = array();
        while($ruleRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->ruleSetArray[] = ChaperoneRuleSet::loadById($ruleRow['rule_set']);
        }
    }


    /*
     * Checks whether the passed Role Context RuleSet can be satisfied by the Action's RuleSet(s)
     * If there are no RuleSets attached to the action, permission is granted
     * $contextArray is passed because an Action Context RuleSet potentially needs to be created for the test
     * 
     * @param   ChaperoneContextRuleSet     $rcrsObj
     * @param   array (optional)            $contextArray
     * @returns boolean
     */
    public function isRoleContextRuleSetAllowed(ChaperoneContextRuleSet $rcrsObj, $contextArray=array()) {

        // If there are no Action RuleSets, permission is granted
        if (count($this->ruleSetArray) === 0) return TRUE;

        // Otherwise, iterate through Action RuleSets, testing the passed Role Context RuleSet against each until we get one that grants permission
        foreach ($this->ruleSetArray AS $actionRuleSetObj) {
            try {
                $acrsObj = $actionRuleSetObj->getContextRuleSet($contextArray);

                // If the passed Context RuleSet is a subset of the Action Context RuleSet, permission is granted
                if ($acrsObj->isSubsetOf($rcrsObj)) {
                    echo '<pre>';
                    var_dump($actionRuleSetObj->getContextRuleSet($contextArray));
                    var_dump($rcrsObj);
                    echo '</pre>';
                    return TRUE;
                }
                unset($acrsObj);
                
            // Assume that exceptions mean permission is denied for this ruleset (most likely Action RuleSets not having sufficient context)
            } catch(ChaperoneException $e) {
            }
        }

        // Permission denied
        return FALSE;
    }

    /*
     * This method is related to isActionAllowed(), but instead of asking whether you have
     * permission to perform the current action for a given Context RuleSet within a given Context,
     * this method allows you to ask for a list of permitted items within a given Context Item.
     * It is called by ChaperoneSession->getContextValueList() for each matching Action object.
     */
    public function getAllowedContextValues($contextItem, ChaperoneContextRuleSet $rcrsObj, $contextArray=array()) {

        require_once('ChaperoneContextValueList.php');
        $contextValueListObj = new ChaperoneContextValueList();

        // If there are no Action RuleSets, there is nothing to merge
        if (count($this->ruleSetArray) === 0)
            return $contextValueListObj;

        // Otherwise, iterate through Action RuleSets, testing the passed Role Context RuleSet against each
        foreach ($this->ruleSetArray AS $actionRuleSetObj) {

            // If the Action RuleSet has the context item as a wildcard, use the Context RuleSet as is
            if ($actionRuleSetObj->isWildcardRuleFor($contextItem)) {
                $acrsObj = $actionRuleSetObj->getContextRuleSet($contextArray);

            // If the Action RuleSet does not have the context item as a wildcard, create a Context RuleSet that excludes it
            // (it may not be there anyway)
            } else {
                $acrsObj = $actionRuleSetObj->getContextRuleSetExceptFor($contextItem, $contextArray);
            }
            
            // If the Action Context RuleSet is not a subset of the Role Context RuleSet, look at the next ruleset
            if (!$acrsObj->isSubsetOf($rcrsObj)) continue;

            // Get the context rule value.  It will be either NULL (denoting wildcard) or a specific value
            $contextValue = $rcrsObj->getContextRuleValue($contextItem);
            
            // If it's a wildcard, we can just return a Context List with a wildcard.  No need to look any further
            if ($contextValue === NULL) {
                $contextValueListObj->addWildcard();
                return $contextValueListObj;
            }

            // It's a specific value, so we add it to the Context List and continue
            $contextValueListObj->addItem($contextValue);
        }

        // Return what we've got.  It should always be a list (possibly empty), but never a wildcard
        return $contextValueListObj;
    }
}
?>