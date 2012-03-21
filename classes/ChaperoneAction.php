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

    public function getRuleSets() {
        return $this->ruleSetArray;
    }
    
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
        $schema = Chaperone::getSchema();
        $sql = 'SELECT      namespace, id, action
                FROM        '.$schema.'.chaperone_action
                WHERE       id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        $stmt->execute();

        $rowCount = $stmt->rowCount();
        if ($rowCount === 0)
            throw new Exception('Action #"'.$id.'" not found');
        if ($rowCount > 1)
            throw new Exception('Multiple instances of Action #"'.$id.'" found');   // Should never happen!
    
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
        $schema = Chaperone::getSchema();
    }
    */

    /*
     * Loads RuleSets for Action.  Each one may be cached, in which case duplicates
     * will be references to the same RuleSet
     */
    private function loadRuleSets() {

        require_once('ChaperoneRuleSet.php');
        
        // Sanity checking
        if ($this->id === NULL)
            throw new Exception('Cannot load Rule Sets - Action ID is not set');
        
        $pdo = Chaperone::getPDO();
        $schema = Chaperone::getSchema();
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
    
    public function ruleSetCheck(ChaperoneContextRuleSet $crsObj, $contextArray=array()) {
        /*
         * Checks whether the passed Context RuleSet can be satisfied by the Action RuleSet.
         * $contextArray is passed because an Action Context RuleSet potentially needs to be created for the test
         */

        // If there are no RuleSets, permission is granted
        if (count($this->ruleSetArray) === 0) return TRUE;

        // Otherwise, iterate through RuleSets, testing the passed Context RuleSet against each until we get one that grants permission
        foreach ($this->ruleSetArray AS $actionRuleSetObj) {
            try {
                $acrsObj = $actionRuleSetObj->getContextRuleSet($contextArray);

                // If the passed Context RuleSet is a subset of the Action Context RuleSet, permission is granted
                if ($acrsObj->isSubsetOf($crsObj)) {
                    echo '<pre>';
                    var_dump($actionRuleSetObj->getContextRuleSet($contextArray));
                    var_dump($crsObj);
                    echo '</pre>';
                    return TRUE;
                }
                unset($acrsObj);
                
            // Assume that exceptions mean permission is denied for this ruleset (most likely Action RuleSets not having sufficient context)
            } catch(Exception $e) {
            }
        }

        // Permission denied
        return FALSE;
    }
}
?>