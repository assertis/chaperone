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
}
?>