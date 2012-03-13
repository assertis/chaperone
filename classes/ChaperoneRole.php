<?php
require_once('Chaperone.php');
/**
 * Description of ChaperoneRole
 *
 * @author steve
 */
class ChaperoneRole {
    
    // Attributes
    private $id = NULL;
    private $namespaceId = NULL;
    private $namespace = NULL;
    private $role = NULL;

    private $ruleSetObj = NULL;

    /*
     * Creates a (largely) empty object.  If you want to populate it from
     * a database table, use load()
     */
    public function __construct() {
        $this->ruleset = NULL;
    }
    
    public function getFullName() { return $this->namespace.'.'.$this->role; }

    /*
     * 
     */
    public function loadByName($roleName) {

        // Get namespace and resource name
        $resourceArray = Chaperone::splitResourceName($roleName);

        // Look up the Role in the database
        $pdo = Chaperone::getPDO();
        $schema = Chaperone::getSchema();
        $sql = 'SELECT  id, namespace, role, rule_set
                FROM    '.$schema.'.chaperone_role
                WHERE   namespace = :namespace
                AND     role = :role';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':namespace', $resourceArray['namespaceId']);
        $stmt->bindValue(':role', $resourceArray['resourceName']);
        
        $stmt->execute();
        $rowCount = $stmt->rowCount();
        if ($rowCount === 0)
            throw new Exception('Role "'.$roleName.'" not found');
        if ($rowCount > 1)
            throw new Exception('Multiple instances of Role "'.$roleName.'" found');

        // Load data and create object
        $roleRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $roleObj = new ChaperoneRole();
        $roleObj->id = (int)$roleRow['id'];
        $roleObj->namespaceId = (int)$roleRow['namespace'];    // Namespace ID from data we've just loaded
        $roleObj->namespace = $resourceArray['namespace'];  // Namespace NAME from splitting the resource name
        $roleObj->role = $roleRow['role'];

        $roleObj->loadRuleSet($roleRow['rule_set']);
        
        return $roleObj;
    }


    /*
     * Loads the ruleset for the given Role.  There may be none, but never more than one
     */
    private function loadRuleSet($ruleSetId) {
        
        // If there is no Rule Set, there is nothing to do
        if ($ruleSetId === NULL) {
            $this->ruleSetObj = NULL;
            return;
        }
        
        require_once('ChaperoneRuleSet.php');
        $this->ruleSetObj = ChaperoneRuleSet::loadById($ruleSetId);
    }


    /*
     * Get an array of actions for the role.  Each action will have its Ruleset(s) attached
     */
    public function getActions() {
        require_once('ChaperoneAction.php');
        
        $pdo = Chaperone::getPDO();
        $schema = Chaperone::getSchema();
        $sql = 'SELECT      ca.id
                FROM        '.$schema.'.chaperone_role_action AS cra
                JOIN        '.$schema.'.chaperone_action AS ca ON ca.id = cra.action
                WHERE       cra.role = :role
                AND         ca.namespace = :namespace'; // Only allow Actions in the same namespace as the role
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':namespace', $this->namespaceId);
        $stmt->bindValue(':role', $this->id);
        $stmt->execute();

        // It's valid (but rather pointless) to have a role with no actions
        $actionArray = array();
        while ($actionRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $actionArray[] = ChaperoneAction::loadById($actionRow['id']);
        }

        return $actionArray;
    }

    public function getContextRuleSet($contextArray=array()) {

        require_once('classes/ChaperoneContextRuleSet.php');
        
        // If there is no RuleSet for the Role, we still need a ContextRuleSet,
        // but all it will have is a name
        if ($this->ruleSetObj === NULL)
            return new ChaperoneContextRuleSet($contextArray);

        // Otherwise, call getContextRuleSet() on the RuleSet
        return $this->ruleSetObj->getContextRuleSet($contextArray);
    }
    
    /*
     * Accessor to get Rule Set
     */
    public function getRuleSet() {
        return $this->ruleSetObj;
    }
}
?>