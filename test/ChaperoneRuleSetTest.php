<?php
require_once('../classes/ChaperoneRuleSet.php');
class ChaperoneRuleSetTest extends PHPUnit_Framework_TestCase
{
    /*
     * This method is called twice - once before the cache test and once after the flushCache test
     */
    function loadTest() {
        /*
         * Mock PDO Statement #1
         */
        $mockPDOStmt1 = $this->getMock('MockPDOStatement', array('bindValue', 'execute', 'fetch', 'rowCount'));

        $mockPDOStmt1->expects($this->at(0))
                ->method('bindValue')
                ->with($this->equalTo(':rule_set'),
                       $this->equalTo(1))
                ->will($this->returnValue(TRUE));

        $mockPDOStmt1->expects($this->at(1))
                ->method('execute')
                ->will($this->returnValue(TRUE));

        $mockPDOStmt1->expects($this->at(2))
                ->method('rowCount')
                ->will($this->returnValue(1));

        $dataArray = array('namespace'=>1);
        $mockPDOStmt1->expects($this->at(3))
                ->method('fetch')
                ->with($this->equalTo(PDO::FETCH_ASSOC))
                ->will($this->returnValue($dataArray));

        // Overrun tests
        $mockPDOStmt1->expects($this->exactly(1))->method('bindValue');
        $mockPDOStmt1->expects($this->exactly(1))->method('execute');
        $mockPDOStmt1->expects($this->exactly(1))->method('rowCount');
        $mockPDOStmt1->expects($this->exactly(1))->method('fetch');


        /*
         * Mock PDO Statement #2
         * 
         * Four rules - Eenie, Meenie, Minie, Mo.  Two wildcards, two context
         */
        $mockPDOStmt2 = $this->getMock('MockPDOStatement', array('bindValue', 'execute', 'fetch', 'rowCount'));

        $mockPDOStmt2->expects($this->at(0))
                ->method('bindValue')
                ->with($this->equalTo(':rule_set'),
                       $this->equalTo(1))
                ->will($this->returnValue(TRUE));

        $mockPDOStmt2->expects($this->at(1))
                ->method('execute')
                ->will($this->returnValue(TRUE));

        $mockPDOStmt2->expects($this->at(2))
                ->method('rowCount')
                ->will($this->returnValue(2));

        $dataArray = array('context_item'=>'eenie', 'wildcard'=>1);
        $mockPDOStmt2->expects($this->at(3))
                ->method('fetch')
                ->with($this->equalTo(PDO::FETCH_ASSOC))
                ->will($this->returnValue($dataArray));

        $dataArray = array('context_item'=>'meenie', 'wildcard'=>0);
        $mockPDOStmt2->expects($this->at(4))
                ->method('fetch')
                ->with($this->equalTo(PDO::FETCH_ASSOC))
                ->will($this->returnValue($dataArray));

        $dataArray = array('context_item'=>'minie', 'wildcard'=>1);
        $mockPDOStmt2->expects($this->at(5))
                ->method('fetch')
                ->with($this->equalTo(PDO::FETCH_ASSOC))
                ->will($this->returnValue($dataArray));

        $dataArray = array('context_item'=>'mo', 'wildcard'=>0);
        $mockPDOStmt2->expects($this->at(6))
                ->method('fetch')
                ->with($this->equalTo(PDO::FETCH_ASSOC))
                ->will($this->returnValue($dataArray));

        $mockPDOStmt2->expects($this->at(7))
                ->method('fetch')
                ->with($this->equalTo(PDO::FETCH_ASSOC))
                ->will($this->returnValue(FALSE));

        // Overrun tests
        $mockPDOStmt2->expects($this->exactly(1))->method('bindValue');
        $mockPDOStmt2->expects($this->exactly(1))->method('execute');
        $mockPDOStmt2->expects($this->exactly(1))->method('rowCount');
        $mockPDOStmt2->expects($this->exactly(5))->method('fetch');


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
        
        $sql = 'SELECT      context_item, wildcard
                FROM        global.chaperone_rule
                WHERE       rule_set = :rule_set
                ORDER BY    context_item';
        $mockPDO->expects($this->at(1))
             ->method('prepare')
             ->with($this->equalTo($sql))
             ->will($this->returnValue($mockPDOStmt2));
        
        // Overrun test
        $mockPDO->expects($this->exactly(2))->method('prepare');


        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO
        $crsObj = ChaperoneRuleSet::loadById(1);
        $this->assertInstanceOf('ChaperoneRuleSet', $crsObj);

        // Check that rules are what we expect
        $this->assertEquals($crsObj->getWildcardRules(), array('eenie', 'minie'));
        $this->assertEquals($crsObj->getContextRules(), array('meenie', 'mo'));
    }
    
    
    /*
     * This test loads an item from databse
     */
    function testLoad() {
        $this->loadTest();
    }
    
    /*
     * @depends testLoad
     * The previous test should have loaded a RuleSet object and put it into the cache
     * This test ensures that a subsequent call for the same item returns the cached version
     */
    function testLoadFromCache() {
        
        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);

        // Load item from cache.  No need for a mock PDO
        $crsObj = ChaperoneRuleSet::loadById(1);
        $this->assertInstanceOf('ChaperoneRuleSet', $crsObj);

        // Check that rules are what we expect (the same as the previous one)
        $this->assertEquals($crsObj->getWildcardRules(), array('eenie', 'minie'));
        $this->assertEquals($crsObj->getContextRules(), array('meenie', 'mo'));
        
        // Load the item again and ensure it is the same as the previous one
        $crs2Obj = ChaperoneRuleSet::loadById(1);
        $this->assertInstanceOf('ChaperoneRuleSet', $crs2Obj);
        $this->assertEquals($crsObj, $crs2Obj);
    }
    
    /*
     * @depends testLoad
     * This test flushes the cache and loads the same item that testLoad() did
     * It should access the database because it is no longer in cache
     */
    function testFlushCacheAndLoad() {
        ChaperoneRuleSet::flushCache();
        $this->loadTest();
    }
    
    /*
     * This test ensures that a NULL Rule Set ID results in NULL being passed back
     */
    function testNullRuleSetId() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);

        $this->assertEquals(ChaperoneRuleSet::loadById(NULL), NULL);
    }
    
    /*
     * @depends testLoad
     * This test ensures getContextRuleSet() works correctly.  It loads from cache
     */
    function testGetContextRuleSet() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $crsObj = ChaperoneRuleSet::loadById(1);

        $contextArray = array('meenie'=>'aaa', 'mo'=>'bbb');
        $crsObj = $crsObj->getContextRuleset($contextArray);
    }
}
?>