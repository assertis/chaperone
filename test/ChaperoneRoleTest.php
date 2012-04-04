<?php
require_once('../classes/ChaperoneRole.php');
require_once('helperMockPdo.php');
class ChaperoneRoleTest extends PHPUnit_Framework_TestCase
{
    private $sqlLookupRole = 
               'SELECT  id, namespace, role, rule_set
                FROM    global.chaperone_role
                WHERE   namespace = :namespace
                AND     role = :role';

    
    /*
     * Add a fake entry into Namespace
     */
    static function setUpBeforeClass() {
        ChaperoneNamespace::reset();
        ChaperoneRuleSet::flushCache();
        
        // Pull in the Namespace Test class and use the testGetNameForId() test to load "test" as namespace 1
        require_once('ChaperoneNamespaceTest.php');
        $nstObj = new ChaperoneNamespaceTest();
        $nstObj->testGetNameForId();

        // Pull in the Namespace Test class and use the loadTest() test to load RuleSet #1
        require_once('ChaperoneRuleSetTest.php');
        $nstObj = new ChaperoneRuleSetTest();
        $nstObj->loadTest();
    }
    

    /*
     * Attempts to load an item with a RuleSet
     */
    function testLoadSuccessful() {

        $helperMockPdoObj = new helperMockPdo($this);
        $helperMockPdoObj->addMockPdoFetchStatement($this->sqlLookupRole,
                                                    array(':namespace'=>1, ':role'=>'test_role'),
                                                    array(array('id'=>1, 'namespace'=>1, 'role'=>'test_role', 'rule_set'=>1)),
                                                    1);
        $mockPDO = $helperMockPdoObj->getPDO();

        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO
        $roleObj = ChaperoneRole::loadByName('test.test_role');
        
        $this->assertEquals($roleObj->getFullName(), 'test.test_role');

        // Ruleset should have been loaded from cache
        $this->assertEquals($roleObj->getReadableRules(), 'eenie=*, minie=*, meenie=..., mo=...');

        // Should not be able to get Context RuleSet without any context
        try {
            $crsObj = $roleObj->getContextRuleSet();
            $this->fail('getContextRuleSet() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Missing context items: "meenie", "mo"');
        }

        // If we provide the missing context items, we should be able to get the Context RuleSet
        $crsObj = $roleObj->getContextRuleSet(array('meenie'=>'meenie', 'mo'=>'mo'));
        
        // Create a helper for creating a mock PDO
        $helperMockPdoObj = new helperMockPdo($this);

        // Add PDO statement that gets a list of actions for the role
        $sql = 'SELECT      ca.id
                FROM        global.chaperone_role_action AS cra
                JOIN        global.chaperone_action AS ca ON ca.id = cra.action
                WHERE       cra.role = :role
                AND         ca.namespace = :namespace
                ORDER BY    ca.action, ca.id';

        $helperMockPdoObj->addMockPdoFetchStatement($sql,
                                                    array(':namespace'=>1, ':role'=>1),
                                                    array(array('id'=>1)));

        // Add PDO statements that load the action and its ruleset
        require_once('ChaperoneActionTest.php');
        $atObj = new ChaperoneActionTest();
        $atObj->populateMockPdo($this, $helperMockPdoObj);

        // Get the PDO from the helper and set it within Chaperone
        $mockPDO = $helperMockPdoObj->getPDO();
        Chaperone::setPDO($mockPDO);

        // We should get back an array containing a single action
        $actionArray = $roleObj->getActions();
        $this->assertEquals(count($actionArray), 1);
    }

    
    
    /*
     * Attempts to load an item with no RuleSet
     */
    function testLoadSuccessfulNoRuleset() {

        $helperMockPdoObj = new helperMockPdo($this);
        $helperMockPdoObj->addMockPdoFetchStatement($this->sqlLookupRole,
                                                    array(':namespace'=>1, ':role'=>'test_role2'),
                                                    array(array('id'=>2, 'namespace'=>1, 'role'=>'test_role2', 'rule_set'=>NULL)),
                                                    1);
        $mockPDO = $helperMockPdoObj->getPDO();
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO
        $roleObj = ChaperoneRole::loadByName('test.test_role2');
        
        $this->assertEquals($roleObj->getFullName(), 'test.test_role2');
        
        // Ruleset should be empty
        $this->assertEquals($roleObj->getReadableRules(), '- None -');
        
        // Should be able to get Context RuleSet without any context
        $crsObj = $roleObj->getContextRuleSet();
    }

    
    
    /*
     * Failure scenario - Zero rows returned.  Should generate an exception
     */
    function testLoadNotFound() {

        $helperMockPdoObj = new helperMockPdo($this);
        $helperMockPdoObj->addMockPdoFetchStatement($this->sqlLookupRole,
                                                    array(':namespace'=>1, ':role'=>'test_role'),
                                                    array(),
                                                    0);
        $mockPDO = $helperMockPdoObj->getPDO();

        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO.  We expect an exception because the item is not found
        try {
            $roleObj = ChaperoneRole::loadByName('test.test_role');
            $this->fail('Exception not generated');
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), 'Role "test.test_role" not found');
        }
    }
    
    /*
     * Failure scenario - Multiple rows returned.  Should generate an exception
     */
    function testLoadMultipleRows() {

        $helperMockPdoObj = new helperMockPdo($this);
        $helperMockPdoObj->addMockPdoFetchStatement($this->sqlLookupRole,
                                                    array(':namespace'=>1, ':role'=>'test_role'),
                                                    array(
                                                        array('id'=>1, 'namespace'=>1, 'role'=>'test_role', 'rule_set'=>NULL),
                                                        array('id'=>1, 'namespace'=>1, 'role'=>'test_role', 'rule_set'=>NULL)
                                                    ),
                                                    0);
        $mockPDO = $helperMockPdoObj->getPDO();
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO.  We expect an exception because the item is not found
        try {
            $roleObj = ChaperoneRole::loadByName('test.test_role');
            $this->fail('Exception not generated');
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), 'Multiple instances of Role "test.test_role" found');
        }
    }

    public static function tearDownAfterClass() {
        ChaperoneNamespace::reset();
        ChaperoneRuleSet::flushCache();
    }
}
?>