<?php
require_once('../classes/ChaperoneNamespace.php');
class ChaperoneNamespaceTest extends PHPUnit_Framework_TestCase
{
    /*
     * This helper method builds a mock PDO Statement, binds the parameters, sets up the return data and overrun tests
     */
    function getMockPDOStatement($bindArray, $resultArray) {
        /*
         * Mock PDO Statement
         */
        $mockPDOStmt = $this->getMock('MockPDOStatement', array('bindValue', 'execute', 'fetch', 'rowCount'));
        $i = 0;
        $rowCount = ($resultArray === NULL) ? 0 : 1;
        foreach ($bindArray AS $key=>$value) {
            $mockPDOStmt->expects($this->at($i++))
                    ->method('bindValue')
                    ->with($this->equalTo($key),
                           $this->equalTo($value))
                    ->will($this->returnValue(TRUE));
        }
        
        $mockPDOStmt->expects($this->at($i++))
                ->method('execute')
                ->will($this->returnValue(TRUE));

        $mockPDOStmt->expects($this->at($i++))
                ->method('rowCount')
                ->will($this->returnValue($rowCount));

        if ($rowCount > 0) {
            $mockPDOStmt->expects($this->at($i++))
                    ->method('fetch')
                    ->with($this->equalTo(PDO::FETCH_ASSOC))
                    ->will($this->returnValue($resultArray));
        }

        // Overrun tests
        $mockPDOStmt->expects($this->exactly(count($bindArray)))->method('bindValue');
        $mockPDOStmt->expects($this->exactly(1))->method('execute');
        $mockPDOStmt->expects($this->exactly(1))->method('rowCount');
        $mockPDOStmt->expects($this->exactly($rowCount))->method('fetch');

        return $mockPDOStmt;
    }
    
    
    /*
     * Tests that we can successfully load the namespace for an ID
     */
    function testGetNameForId() {

        $mockPDOStmt = $this->getMockPDOStatement(array(':id'=>1), array('name'=>'test'));
        
        /*
         * Mock PDO
         */
        $mockPDO = $this->getMock('MockPDO', array('prepare'));
        $sql = 'SELECT  name
                FROM    global.chaperone_namespace
                WHERE   id = :id';
        $mockPDO->expects($this->at(0))
             ->method('prepare')
             ->with($this->equalTo($sql))
             ->will($this->returnValue($mockPDOStmt));

        // Overrun test
        $mockPDO->expects($this->exactly(1))->method('prepare');


        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Run lookup
        $this->assertEquals(ChaperoneNamespace::getNameForId(1), 'test');
    }
    
    
    /*
     * Tests that caching for ID lookups works.  This should not require a database lookup
     * @depends testGetNameForId
     */
    function testIdCache() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getNameForId(1), 'test');
    }
    
    
    /*
     * Tests that caching for name lookups works.  This tests that the cross-caching works
     * @depends testGetNameForId
     */
    function testNameCrossCache() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getIdForName('test'), 1);
    }
    
    
    /*
     * Tests that we get back NULL if the ID cannot be found
     */
    function testGetNameForIdMissing() {

        $mockPDOStmt = $this->getMockPDOStatement(array(':id'=>2), NULL);
        
        /*
         * Mock PDO
         */
        $mockPDO = $this->getMock('MockPDO', array('prepare'));
        $sql = 'SELECT  name
                FROM    global.chaperone_namespace
                WHERE   id = :id';
        $mockPDO->expects($this->at(0))
             ->method('prepare')
             ->with($this->equalTo($sql))
             ->will($this->returnValue($mockPDOStmt));

        // Overrun test
        $mockPDO->expects($this->exactly(1))->method('prepare');


        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Run lookup
        $this->assertEquals(ChaperoneNamespace::getNameForId(2), NULL);
    }

    
    /*
     * Tests that caching records failed lookups.  This should not require a database lookup
     * @depends testGetNameForIdMissing
     */
    function testIdCacheMissing() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getNameForId(2), NULL);
    }


    /*
     * Tests that we can successfully load the ID for a given namespace
     */
    function testGetIdForName() {

        $mockPDOStmt = $this->getMockPDOStatement(array(':name'=>'test2'), array('id'=>3));
        
        /*
         * Mock PDO
         */
        $mockPDO = $this->getMock('MockPDO', array('prepare'));
        $sql = 'SELECT  id
                FROM    global.chaperone_namespace
                WHERE   name = :name';
        $mockPDO->expects($this->at(0))
             ->method('prepare')
             ->with($this->equalTo($sql))
             ->will($this->returnValue($mockPDOStmt));

        // Overrun test
        $mockPDO->expects($this->exactly(1))->method('prepare');


        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Run lookup
        $this->assertEquals(ChaperoneNamespace::getIdForName('test2'), 3);
    }
    
    
    /*
     * Tests that caching for name lookups works.  This should not require a database lookup
     * @depends testGetIdForName
     */
    function testNameCache() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getIdForName('test2'), 3);
    }
    
    
    /*
     * Tests that caching for id lookups works.  This tests that the cross-caching works
     * @depends testGetIdForName
     */
    function testIdCrossCache() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getNameForId(3), 'test2');
    }
    
    
    /*
     * Tests that we get back NULL if the ID cannot be found
     */
    function testGetIdForNameMissing() {

        $mockPDOStmt = $this->getMockPDOStatement(array(':name'=>'missing'), NULL);
        
        /*
         * Mock PDO
         */
        $mockPDO = $this->getMock('MockPDO', array('prepare'));
        $sql = 'SELECT  id
                FROM    global.chaperone_namespace
                WHERE   name = :name';
        $mockPDO->expects($this->at(0))
             ->method('prepare')
             ->with($this->equalTo($sql))
             ->will($this->returnValue($mockPDOStmt));

        // Overrun test
        $mockPDO->expects($this->exactly(1))->method('prepare');


        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Run lookup
        $this->assertEquals(ChaperoneNamespace::getIdForName('missing'), NULL);
    }

    
    /*
     * Tests that caching records failed lookups.  This should not require a database lookup
     * @depends testGetIdForNameMissing
     */
    function testNameCacheMissing() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getIdForName('missing'), NULL);
    }
}
?>