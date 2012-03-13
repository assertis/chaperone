<?php
require_once('../classes/ChaperoneRole.php');
class ChaperoneRoleTest extends PHPUnit_Framework_TestCase
{
    /*
     * Helper method - returns a mock PDO statement that has bindValue and execute methods populated
     */
    function getMockPDOStatement() {
        $mockPDOStmt = $this->getMock('MockPDOStatement', array('bindValue', 'execute', 'fetch', 'rowCount'));

        $mockPDOStmt->expects($this->at(0))
                ->method('bindValue')
                ->with($this->equalTo(':namespace'),
                       $this->equalTo(1))
                ->will($this->returnValue(TRUE));

        $mockPDOStmt->expects($this->at(1))
                ->method('bindValue')
                ->with($this->equalTo(':role'),
                       $this->equalTo('test_role'))
                ->will($this->returnValue(TRUE));

        $mockPDOStmt->expects($this->at(2))
                ->method('execute')
                ->will($this->returnValue(TRUE));

        return $mockPDOStmt;
    }
    
    /*
     * Helper method - returns a mock PDO that with a prepare() call that returns the supplied mock PDO Statement
     */
    function getMockPDO($mockPDOStmt) {
        $mockPDO = $this->getMock('MockPDO', array('prepare'));
        $sql = 'SELECT  id, namespace, role, rule_set
                FROM    global.chaperone_role
                WHERE   namespace = :namespace
                AND     role = :role';
        $mockPDO->expects($this->at(0))
             ->method('prepare')
             ->with($this->equalTo($sql))
             ->will($this->returnValue($mockPDOStmt));
        
        // Overrun test
        $mockPDO->expects($this->exactly(1))->method('prepare');

        return $mockPDO;
    }
    
    /*
     * Attempts to load an item.  For simplicity, the test Role has no Rule Set
     */
    function testLoadSuccessful() {
        /*
         * Mock PDO Statement
         */
        $mockPDOStmt1 = $this->getMockPDOStatement();

        $mockPDOStmt1->expects($this->at(3))
                ->method('rowCount')
                ->will($this->returnValue(1));

        $dataArray = array('id'=>1, 'namespace'=>1, 'role'=>'test_role', 'rule_set'=>NULL);
        $mockPDOStmt1->expects($this->at(4))
                ->method('fetch')
                ->with($this->equalTo(PDO::FETCH_ASSOC))
                ->will($this->returnValue($dataArray));

        // Overrun tests
        $mockPDOStmt1->expects($this->exactly(2))->method('bindValue');
        $mockPDOStmt1->expects($this->exactly(1))->method('execute');
        $mockPDOStmt1->expects($this->exactly(1))->method('rowCount');
        $mockPDOStmt1->expects($this->exactly(1))->method('fetch');


        /*
         * Mock PDO
         */
        $mockPDO = $this->getMockPDO($mockPDOStmt1);


        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO
        $o_cr = ChaperoneRole::loadByName('test_role');
    }

    
    
    /*
     * Failure scenario - Zero rows returned.  Should generate an exception
     */
    function testLoadZeroRows() {
        $mockPDOStmt1 = $this->getMockPDOStatement();

        $mockPDOStmt1->expects($this->at(3))
                ->method('rowCount')
                ->will($this->returnValue(0));

        // Overrun tests
        $mockPDOStmt1->expects($this->exactly(2))->method('bindValue');
        $mockPDOStmt1->expects($this->exactly(1))->method('execute');
        $mockPDOStmt1->expects($this->exactly(1))->method('rowCount');
        $mockPDOStmt1->expects($this->exactly(0))->method('fetch');

        /*
         * Mock PDO
         */
        $mockPDO = $this->getMockPDO($mockPDOStmt1);

        
        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO.  We expect an exception because the item is not found
        try {
            $o_cr = ChaperoneRole::loadByName('test_role');
            $this->fail('Exception not generated');
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), 'Role "test_role" not found');
        }
    }
    
    /*
     * Failure scenario - Multiple rows returned.  Should generate an exception
     */
    function testLoadMultipleows() {
        $mockPDOStmt1 = $this->getMockPDOStatement();

        $mockPDOStmt1->expects($this->at(3))
                ->method('rowCount')
                ->will($this->returnValue(2));

        // Overrun tests
        $mockPDOStmt1->expects($this->exactly(2))->method('bindValue');
        $mockPDOStmt1->expects($this->exactly(1))->method('execute');
        $mockPDOStmt1->expects($this->exactly(1))->method('rowCount');
        $mockPDOStmt1->expects($this->exactly(0))->method('fetch');

        /*
         * Mock PDO
         */
        $mockPDO = $this->getMockPDO($mockPDOStmt1);

        
        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Create object using dummy data in mock PDO.  We expect an exception because the item is not found
        try {
            $o_cr = ChaperoneRole::loadByName('test_role');
            $this->fail('Exception not generated');
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), 'Multiple instances of Role "test_role" found');
        }
    }
}
?>