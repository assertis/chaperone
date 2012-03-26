<?php
require_once('../classes/ChaperoneRuleSet.php');
class ChaperoneRuleSetTest extends PHPUnit_Framework_TestCase
{
    // Test data for successful loads.  We use this twice - once for a successful load and once after flusing the cache
    private static $testRuleSetArray = array(array('namespace'=>1));
    
    private static $testRuleArray = array(
        array('context_item'=>'eenie', 'wildcard'=>1),
        array('context_item'=>'meenie', 'wildcard'=>0),
        array('context_item'=>'minie', 'wildcard'=>1),
        array('context_item'=>'mo', 'wildcard'=>0));

    /*
     * This method is called multiple times for different scenarios
     * @param   int                         $ruleSetId              Rule set ID to look for
     * @param   array                       $resultArray1           Results to return for RuleSet lookup
     * @param   array                       $resultArray2           Results to return for rules lookup
     */
    private function getMockPDO($ruleSetId, $resultArray1, $resultArray2, $fetchCount2 = NULL) {
        /*
         * Mock PDO Statement #1
         */
        $rowCount1 = ($resultArray1 === NULL) ? 0 : count($resultArray1);
        $fetchCount1 = ($rowCount1 === 1) ? 1 : 0;
        $i = 0;

        $mockPDOStmt1 = $this->getMock('MockPDOStatement', array('bindValue', 'execute', 'fetch', 'rowCount'));

        $mockPDOStmt1->expects($this->at(0))
                ->method('bindValue')
                ->with($this->equalTo(':rule_set'),
                       $this->equalTo($ruleSetId))
                ->will($this->returnValue(TRUE));

        $mockPDOStmt1->expects($this->at(1))
                ->method('execute')
                ->will($this->returnValue(TRUE));

        $mockPDOStmt1->expects($this->at(2))
                ->method('rowCount')
                ->will($this->returnValue($rowCount1));

        if ($rowCount1 === 1) {
            $mockPDOStmt1->expects($this->at(3))
                ->method('fetch')
                ->with($this->equalTo(PDO::FETCH_ASSOC))
                ->will($this->returnValue($resultArray1[0]));
        }

        // Overrun tests
        $mockPDOStmt1->expects($this->exactly(1))->method('bindValue');
        $mockPDOStmt1->expects($this->exactly(1))->method('execute');
        $mockPDOStmt1->expects($this->exactly(1))->method('rowCount');
        $mockPDOStmt1->expects($this->exactly($fetchCount1))->method('fetch');

        /*
         * Mock PDO Statement #2
         * 
         * Returns rules defined in $resultArray2.
         * Number of expects fetch() requests defined in $fetchCount.  If NULL, use the number of rows in $resultArray2 + 1 (unless 0, in which case, 0)
         */
        if ($rowCount1 === 1) {
            $rowCount2 = ($resultArray2 === NULL) ? 0 : count($resultArray2);
            if ($fetchCount2 === NULL) $fetchCount2 = ($rowCount2 === 0) ? 0 : $rowCount2 + 1;
            $i = 0;

            $mockPDOStmt2 = $this->getMock('MockPDOStatement', array('bindValue', 'execute', 'fetch', 'rowCount'));

            $mockPDOStmt2->expects($this->at($i++))
                    ->method('bindValue')
                    ->with($this->equalTo(':rule_set'),
                           $this->equalTo($ruleSetId))
                    ->will($this->returnValue(TRUE));

            $mockPDOStmt2->expects($this->at($i++))
                    ->method('execute')
                    ->will($this->returnValue(TRUE));

            $mockPDOStmt2->expects($this->at($i++))
                    ->method('rowCount')
                    ->will($this->returnValue($rowCount2));

            for ($j=0; $j<$rowCount2; $j++) {
                $mockPDOStmt2->expects($this->at($i++))
                        ->method('fetch')
                        ->with($this->equalTo(PDO::FETCH_ASSOC))
                        ->will($this->returnValue($resultArray2[$j]));
            }

            // Final fetch indicates we have reached the end
            if ($fetchCount2 > $rowCount2) {
                $mockPDOStmt2->expects($this->at($i++))
                        ->method('fetch')
                        ->with($this->equalTo(PDO::FETCH_ASSOC))
                        ->will($this->returnValue(FALSE));
            }

            // Overrun tests
            $mockPDOStmt2->expects($this->exactly(1))->method('bindValue');
            $mockPDOStmt2->expects($this->exactly(1))->method('execute');
            $mockPDOStmt2->expects($this->exactly(1))->method('rowCount');
            $mockPDOStmt2->expects($this->exactly($fetchCount2))->method('fetch');
        }
        
        /*
         * Mock PDO
         */
        $mockPDO = $this->getMock('MockPDO', array('prepare'));
        $sql = 'SELECT  namespace
                FROM    global.chaperone_rule_set
                WHERE   id = :rule_set';
        $mockPDO->expects($this->at(0))
             ->method('prepare')
             ->with($this->equalTo($sql))
             ->will($this->returnValue($mockPDOStmt1));

        if ($rowCount1 === 1) {
            $sql = 'SELECT      context_item, wildcard
                FROM        global.chaperone_rule
                WHERE       rule_set = :rule_set
                ORDER BY    context_item';
            $mockPDO->expects($this->at(1))
                 ->method('prepare')
                 ->with($this->equalTo($sql))
                 ->will($this->returnValue($mockPDOStmt2));
        }

        // Overrun test
        $mockPDO->expects($this->exactly(($rowCount1 === 1) ? 2 : 1))->method('prepare');

        return $mockPDO;
    }

    
    // This method is called twice - once as a normal load and again after flushing the cache
    public function loadTest() {

        // Get mock PDO
        $mockPDO = $this->getMockPDO(1, self::$testRuleSetArray, self::$testRuleArray);

        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO
        $rsObj = ChaperoneRuleSet::loadById(1);
        $this->assertInstanceOf('ChaperoneRuleSet', $rsObj);

        // Check the namespace ID is correct
        $this->assertEquals($rsObj->getNamespaceId(), 1);
        
        // Check that rules are what we expect
        $this->assertEquals($rsObj->getWildcardRules(), array('eenie', 'minie'));
        $this->assertEquals($rsObj->getContextRules(), array('meenie', 'mo'));

        // Check that readable rules come back in the correct format
        $this->assertEquals($rsObj->getReadableRules(), 'eenie=*, minie=*, meenie=..., mo=...');

        // Check that wildcard rule checking works correctly
        $this->assertEquals($rsObj->isWildcardRuleFor('eenie'), TRUE);
        $this->assertEquals($rsObj->isWildcardRuleFor('meenie'), FALSE);
        $this->assertEquals($rsObj->isWildcardRuleFor('minie'), TRUE);
        $this->assertEquals($rsObj->isWildcardRuleFor('mo'), FALSE);
        $this->assertEquals($rsObj->isWildcardRuleFor('unknown'), FALSE);
    }
    
    
    /*
     * This test loads an item from databse
     */
    public function testLoad() {
        $this->loadTest();
    }


    /*
     * @depends testLoad
     * The previous test should have loaded a RuleSet object and put it into the cache
     * This test ensures that a subsequent call for the same item returns the cached version
     */
    public function testLoadFromCache() {
        
        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);

        // Load item from cache.  No need for a mock PDO
        $rsObj = ChaperoneRuleSet::loadById(1);
        $this->assertInstanceOf('ChaperoneRuleSet', $rsObj);

        // Check that rules are what we expect (the same as the previous one)
        $this->assertEquals($rsObj->getWildcardRules(), array('eenie', 'minie'));
        $this->assertEquals($rsObj->getContextRules(), array('meenie', 'mo'));
        
        // Load the item again and ensure it is the same as the previous one
        $rs2Obj = ChaperoneRuleSet::loadById(1);
        $this->assertInstanceOf('ChaperoneRuleSet', $rs2Obj);
        $this->assertEquals($rsObj, $rs2Obj);
    }


    /*
     * @depends testLoad
     * This test flushes the cache and loads the same item that testLoad() did
     * It should access the database because it is no longer in cache
     */
    public function testFlushCacheAndLoad() {
        ChaperoneRuleSet::flushCache();
        $this->loadTest();
    }


    /*
     * This test ensures that a NULL Rule Set ID results in NULL being passed back
     */
    public function testNullRuleSetId() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);

        $this->assertEquals(ChaperoneRuleSet::loadById(NULL), NULL);
    }


    /*
     * This test ensures that missing Rule Sets are handled correctly (throws exception)
     */
    public function testMissingRuleSet() {

        // Get mock PDO
        $mockPDO = $this->getMockPDO(2, array(), array());

        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO
        try {
            $rsObj = ChaperoneRuleSet::loadById(2);
            $this->fail('loadById() did not throw excepted exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Rule Set "2" not found');
        }
    }
    
    
    /*
     * This test ensures that duplicate Rule Sets are handled correctly (throws exception)
     * This should never happen in real life because the ID column should be a primary key
     */
    public function testDuplicateRuleSet() {

        // Get mock PDO
        $mockPDO = $this->getMockPDO(3, array(array('namespace'=>1), array('namespace'=>1)), array());

        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO
        try {
            $rsObj = ChaperoneRuleSet::loadById(3);
            $this->fail('loadById() did not throw excepted exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Multiple instances of Rule Set "3" found');
        }
    }
    
    
    /*
     * This test ensures that empty Rule Sets are handled correctly (RuleSet object returned with no rules)
     */
    public function testEmptyRuleSet() {

        // Get mock PDO
        $mockPDO = $this->getMockPDO(4, self::$testRuleSetArray, array());

        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO
        $rsObj = ChaperoneRuleSet::loadById(4);
        $this->assertInstanceOf('ChaperoneRuleSet', $rsObj);
        $this->assertEquals($rsObj->getReadableRules(), '- None -');
    }
    
    
    /*
     * This test ensures that duplicate Rules are handled correctly (throws exception)
     * This should never happen in real life because there should be a unique index across the columns
     */
    public function testDuplicateRules() {

        // Get mock PDO
        $mockPDO = $this->getMockPDO(5,
                                     self::$testRuleSetArray,
                                     array(array('context_item'=>'duplicate', 'wildcard'=>1),   // Duplicate rule should
                                           array('context_item'=>'duplicate', 'wildcard'=>0)),  // cause an exception
                                     2);    // Force the number of fetches in the mock object

        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO
        try {
            $rsObj = ChaperoneRuleSet::loadById(5);
            $this->fail('loadById() did not throw excepted exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Rule item "duplicate" already defined');
        }
    }
    
    
    /*
     * @depends testLoad
     * This test ensures getContextRuleSet() works correctly.  It loads from cache
     */
    public function testGetContextRuleSet() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $rsObj = ChaperoneRuleSet::loadById(1);

        $contextArray = array('meenie'=>'aaa', 'mo'=>'bbb');
        $crsObj = $rsObj->getContextRuleset($contextArray);
        
        // The following code relies on Context RuleSet's interface
        $this->assertEquals($crsObj->isRuleFor('eenie'), TRUE);
        $this->assertEquals($crsObj->isWildcardRuleFor('eenie'), TRUE);
        $this->assertEquals($crsObj->getContextRuleValue('eenie'), NULL);

        $this->assertEquals($crsObj->isRuleFor('meenie'), TRUE);
        $this->assertEquals($crsObj->isWildcardRuleFor('meenie'), FALSE);
        $this->assertEquals($crsObj->getContextRuleValue('meenie'), 'aaa');

        $this->assertEquals($crsObj->isRuleFor('minie'), TRUE);
        $this->assertEquals($crsObj->isWildcardRuleFor('minie'), TRUE);
        $this->assertEquals($crsObj->getContextRuleValue('minie'), NULL);

        $this->assertEquals($crsObj->isRuleFor('mo'), TRUE);
        $this->assertEquals($crsObj->isWildcardRuleFor('mo'), FALSE);
        $this->assertEquals($crsObj->getContextRuleValue('mo'), 'bbb');
    }
    
    
    /*
     * @depends testLoad
     * This test ensures getContextRuleSetExceptFor() works correctly.  It loads from cache
     */
    public function testGetContextRuleSetExceptFor() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $rsObj = ChaperoneRuleSet::loadById(1);

        $contextArray = array('meenie'=>'aaa');
        $crsObj = $rsObj->getContextRulesetExceptFor('mo', $contextArray);
        
        // The following code relies on Context RuleSet's interface
        $this->assertEquals($crsObj->isRuleFor('eenie'), TRUE);
        $this->assertEquals($crsObj->isWildcardRuleFor('eenie'), TRUE);
        $this->assertEquals($crsObj->getContextRuleValue('eenie'), NULL);

        $this->assertEquals($crsObj->isRuleFor('meenie'), TRUE);
        $this->assertEquals($crsObj->isWildcardRuleFor('meenie'), FALSE);
        $this->assertEquals($crsObj->getContextRuleValue('meenie'), 'aaa');

        $this->assertEquals($crsObj->isRuleFor('minie'), TRUE);
        $this->assertEquals($crsObj->isWildcardRuleFor('minie'), TRUE);
        $this->assertEquals($crsObj->getContextRuleValue('minie'), NULL);

        // "mo" should be missing because we asked for it to be excluded
        $this->assertEquals($crsObj->isRuleFor('mo'), FALSE);
    }
    
    /*
     * @depends testLoad
     * This test ensures getContextRuleSet() correctly throws an exception when a context item is missing
     */
    public function testGetContextRuleSetMissing() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $rsObj = ChaperoneRuleSet::loadById(1);

        // "mo" is defined as a context rule, so omitting it should throw an exception
        $contextArray = array('meenie'=>'aaa');
        try {
            $crsObj = $rsObj->getContextRuleset($contextArray);
            $this->fail('getContextRuleset() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Missing context item: "mo"');
        }

        // Providing no context should cause "meenie" and "mo" to both be shown in error
        try {
            $crsObj = $rsObj->getContextRuleset();
            $this->fail('getContextRuleset() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Missing context items: "meenie", "mo"');
        }
    }
}
?>