<?php
require_once('../classes/ChaperoneAction.php');
require_once('../classes/ChaperoneContextRuleSet.php');
require_once('helperMockPdo.php');
class ChaperoneActionTest extends PHPUnit_Framework_TestCase
{
    private $sqlGetActionById = 
               'SELECT      namespace, id, action
                FROM        global.chaperone_action
                WHERE       id = :id';

    private $sqlLoadRules = 
               'SELECT      crs.id AS rule_set
                FROM        global.chaperone_action_rule_set AS cars
                JOIN        global.chaperone_rule_set AS crs ON crs.id = cars.rule_set
                WHERE       cars.action = :id
                AND         crs.namespace = :namespace';

    // Mechanism to allow a loaded object from testLoadSuccessful() to be used by subsequent tests.
    // Not sure why it doesn't work when it's not static (ie. $this->...)
    private static $loadedActionObj;

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
        $crstObj = new ChaperoneRuleSetTest();
        $crstObj->loadTest();
    }
    

    /*
     * Attempts to load an item with a RuleSet
     */
    function testLoadSuccessful() {

        $helperMockPdoObj = new helperMockPdo($this);
        $helperMockPdoObj->addMockPdoFetchStatement($this->sqlGetActionById,
                                                    array(':id'=>1),
                                                    array(array('namespace'=>1, 'id'=>1, 'action'=>'test_action')),
                                                    1);

        $helperMockPdoObj->addMockPdoFetchStatement($this->sqlLoadRules,
                                                    array(':namespace'=>1, ':id'=>1),
                                                    array(array('rule_set'=>1)));
        $mockPDO = $helperMockPdoObj->getPDO();

        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO
        $actionObj = ChaperoneAction::loadById(1);
        
        $this->assertEquals($actionObj->getFullName(), 'test.test_action');

        // Ruleset should have been loaded from cache
        $this->assertEquals($actionObj->getReadableRules(), 'eenie=*, minie=*, meenie=..., mo=...');

        // Save object for subsequent tests (saves reloading it)
        self::$loadedActionObj = $actionObj;
    }
    
    /*
     * Test isActionAllowed() with wildcard rules specified throughout
     * @depends testLoadSuccessful
     */
    public function testIsActionAllowedWildcardRules() {
        
        // Fetch loaded action object
        $actionObj = self::$loadedActionObj;

        $crsObj = new ChaperoneContextRuleSet();
        
        // It should not be satisfiable by the Action RuleSet
        $this->assertEquals($actionObj->isActionAllowed($crsObj), FALSE);
        
        // Add some rules to allow access
        $crsObj->addWildcardRule('eenie');
        $crsObj->addWildcardRule('meenie');
        $crsObj->addWildcardRule('minie');
        $crsObj->addWildcardRule('mo');
        
        // If we don't specify a context with the relevant bits, we still aren't allowed in because we haven't stated what we are trying to access
        // @todo: is this how we want it to work?
        $this->assertEquals($actionObj->isActionAllowed($crsObj, array()), FALSE);
        $this->assertEquals($actionObj->isActionAllowed($crsObj, array('meenie'=>'meenie')), FALSE);
        
        // If we do specify a context with the right items, we should be able to get in
        $this->assertEquals($actionObj->isActionAllowed($crsObj, array('meenie'=>'meenie', 'mo'=>'mo')), TRUE);
    }


    /*
     * Test isActionAllowed() with context rules specified throughout
     * @depends testLoadSuccessful
     */
    public function testIsActionAllowedContextRules() {
        
        // Fetch loaded action object
        $actionObj = self::$loadedActionObj;

        // Create a Context RuleSet that has rules for all expected items
        $crsObj = new ChaperoneContextRuleSet();
        $crsObj->addContextRule('eenie', 'eenie');
        $crsObj->addContextRule('meenie', 'meenie');
        $crsObj->addContextRule('minie', 'minie');
        $crsObj->addContextRule('mo', 'mo');
        
        // With all rules specified, we do not have permission because "eenie" and "minie" must be wildcard rules
        $this->assertEquals($actionObj->isActionAllowed($crsObj, array('meenie'=>'meenie', 'mo'=>'mo')), FALSE);
    }

    
    /*
     * Test isActionAllowed() with a mixture of wildcard and context rules
     * @depends testLoadSuccessful
     */
    public function testIsActionAllowedMixedRules() {

        // Fetch loaded action object
        $actionObj = self::$loadedActionObj;

        // Create a Context RuleSet with rules that should allow access
        $crsObj = new ChaperoneContextRuleSet();
        $crsObj->addWildcardRule('eenie');
        $crsObj->addContextRule('meenie', 'meenie');
        $crsObj->addWildcardRule('minie');
        $crsObj->addContextRule('mo', 'mo');

        // Access should not be allowed because there are missing context items
        $this->assertEquals($actionObj->isActionAllowed($crsObj, array()), FALSE);
        $this->assertEquals($actionObj->isActionAllowed($crsObj, array('meenie'=>'meenie')), FALSE);

        // Access should be allowed
        $this->assertEquals($actionObj->isActionAllowed($crsObj, array('meenie'=>'meenie', 'mo'=>'mo')), TRUE);

        // Change a context item value to ensure that access is not allowed when there is a mismatch
        $this->assertEquals($actionObj->isActionAllowed($crsObj, array('meenie'=>'meenie', 'mo'=>'mismatch')), FALSE);
    }

    
    /*
     * Test getContextFilter() with wildcard rules throughout
     * @depends testLoadSuccessful
     */
    public function testGetContextFilterWildcardRules() {

        // Fetch loaded action object
        $actionObj = self::$loadedActionObj;

        $crsObj = new ChaperoneContextRuleSet();

        // With no context, getContextFilter() should throw an exception
        try {
            $discard = $actionObj->getContextFilter('eenie', $crsObj);
            $this->fail('getContextFilter() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Missing context items: "meenie", "mo"');
        }

        // Get the Context Filter for the provided Context RuleSet with Context Rules provided
        $cfObj = $actionObj->getContextFilter('minie', $crsObj, array('meenie'=>'meenie', 'mo'=>'mo'));

        // Should return an empty filter list because we don't have rules for the items
        $this->assertEquals($cfObj->isEmpty(), TRUE);

        // Add wildcard entries and try again
        $crsObj->addWildcardRule('eenie');
        $crsObj->addWildcardRule('meenie');
        $crsObj->addWildcardRule('minie');
        $crsObj->addWildcardRule('mo');

        // Should return a wildcard context filter
        $cfObj = $actionObj->getContextFilter('eenie', $crsObj, array('meenie'=>'meenie', 'mo'=>'mo'));
        $this->assertEquals($cfObj->isWildcard(), TRUE);
    }
    
    
    /*
     * Test getContextFilter() with a mixture of wildcard and context rules
     * @depends testLoadSuccessful
     */
    public function testGetContextFilterMixedRules() {

        // Fetch loaded action object
        $actionObj = self::$loadedActionObj;

        $crsObj = new ChaperoneContextRuleSet();

        // Add rules
        $crsObj->addWildcardRule('eenie');
        $crsObj->addContextRule('meenie', 'meenie');
        $crsObj->addWildcardRule('minie');
        $crsObj->addContextRule('mo', 'mo');

        // See what context item values within "eenie" we are allowed to access.  Should return a wildcard
        $cfObj = $actionObj->getContextFilter('eenie', $crsObj, array('meenie'=>'meenie', 'mo'=>'mo'));
        $this->assertEquals($cfObj->isWildcard(), TRUE);

        // See what context item values within "meenie" we are allowed to access.
        // Should return context ruleset with access for value "meenie"
        $cfObj = $actionObj->getContextFilter('meenie', $crsObj, array('mo'=>'mo'));
        $this->assertEquals($cfObj->getItems(), array('meenie'));
    }

    
    /*
     * Attempts to load an item that is not found
     */
    function testLoadMissing() {

        $helperMockPdoObj = new helperMockPdo($this);
        $helperMockPdoObj->addMockPdoFetchStatement($this->sqlGetActionById,
                                                    array(':id'=>2),
                                                    array(),
                                                    0);

        $mockPDO = $helperMockPdoObj->getPDO();

        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        try {
            $actionObj = ChaperoneAction::loadById(2);
            $this->fail('loadById() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Action #"2" not found');
        }
    }

    /*
     * Attempts to load an item that returns multiple items (should never happen)
     */
    function testLoadMultiple() {

        $helperMockPdoObj = new helperMockPdo($this);
        $helperMockPdoObj->addMockPdoFetchStatement($this->sqlGetActionById,
                                                    array(':id'=>3),
                                                    array(
                                                        array('namespace'=>1, 'id'=>1, 'action'=>'test_action'),
                                                        array('namespace'=>1, 'id'=>1, 'action'=>'test_action')
                                                    ),
                                                    0);

        $mockPDO = $helperMockPdoObj->getPDO();

        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        try {
            $actionObj = ChaperoneAction::loadById(3);
            $this->fail('loadById() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Multiple instances of Action #"3" found');
        }
    }

    
    public static function tearDownAfterClass() {
        ChaperoneNamespace::reset();
        ChaperoneRuleSet::flushCache();
    }
}
?>