<?php

/**
 * This is a helper class for creating mock PDOs and statements
 *
 * @author Steve Criddle
 */
class helperMockPdo {
    
    private $testObj = NULL;
    private $mockPdoObj = NULL;
    private $pdoCounter = 0;
    
    public function __construct($testObj, $methodArray = array('prepare')) {
        $this->testObj = $testObj;
        $this->mockPdoObj = $testObj->getMock('MockPDO', $methodArray);
    }

    /*
     * @params  string                      $sql            SQL for PDO Statement
     * @params  array                       $bindArray      Array of parameters to bind
     * @params  array                       $resultArray    Array of results (if any) to return
     * @params  int                         $fetchCount     Number of fetches to expect
     */
    public function addMockPdoFetchStatement($sql, $bindArray = array(), $resultArray = array(), $fetchCount = NULL) {
        
        $mockPDOStmt = $this->getMockPdoFetchStatement($bindArray, $resultArray, $fetchCount);
        
        $this->mockPdoObj->expects($this->testObj->at($this->pdoCounter++))
             ->method('prepare')
             ->with($this->testObj->equalTo($sql))
             ->will($this->testObj->returnValue($mockPDOStmt));
    }

    /*
     * @params  array                       $bindArray      Array of parameters to bind
     * @params  array                       $resultArray    Array of results (if any) to return
     * @params  int                         $fetchCount     Number of fetches to expect
     */
    public function getMockPdoFetchStatement($bindArray = array(), $resultArray = array(), $fetchCount = NULL) {
        /*
         * Mock PDO Statement
         */
        $mockPDOStmt = $this->testObj->getMock('MockPDOStatement', array('bindValue', 'execute', 'fetch', 'rowCount'));
        $i = 0;
        $bindCount = ($bindArray === NULL) ? 0 : count($bindArray);
        $rowCount = ($resultArray === NULL) ? 0 : count($resultArray);
        if ($fetchCount === NULL) $fetchCount = $rowCount + 1;          // Assume there is an additional fetch unless told otherwise

        if ($bindCount > 0) {
            foreach ($bindArray AS $key=>$value) {
                $mockPDOStmt->expects($this->testObj->at($i++))
                        ->method('bindValue')
                        ->with($this->testObj->equalTo($key),
                               $this->testObj->equalTo($value))
                        ->will($this->testObj->returnValue(TRUE));
            }
        }

        $mockPDOStmt->expects($this->testObj->at($i++))
                ->method('execute')
                ->will($this->testObj->returnValue(TRUE));

        $mockPDOStmt->expects($this->testObj->at($i++))
                ->method('rowCount')
                ->will($this->testObj->returnValue($rowCount));

        // Iterate through data simulating a fetch() call
        $loopCount = min($rowCount, $fetchCount);
        for ($j=0; $j<$loopCount; $j++) {
            $mockPDOStmt->expects($this->testObj->at($i++))
                    ->method('fetch')
                    ->with($this->testObj->equalTo(PDO::FETCH_ASSOC))
                    ->will($this->testObj->returnValue($resultArray[$j]));
        }

        // If fetchCount > $rowCount, simluate fetch() calls returning no data (FALSE)
        for ($j=$rowCount; $j<$fetchCount; $j++) {
            $mockPDOStmt->expects($this->testObj->at($i++))
                    ->method('fetch')
                    ->with($this->testObj->equalTo(PDO::FETCH_ASSOC))
                    ->will($this->testObj->returnValue(FALSE));
        }

        // Overrun tests
        $mockPDOStmt->expects($this->testObj->exactly($bindCount))->method('bindValue');
        $mockPDOStmt->expects($this->testObj->exactly(1))->method('execute');
        $mockPDOStmt->expects($this->testObj->exactly(1))->method('rowCount');
        $mockPDOStmt->expects($this->testObj->exactly($fetchCount))->method('fetch');
//var_dump($mockPDOStmt);
        return $mockPDOStmt;
    }
    
    public function getPDO() {

        // Overrun test
        $this->mockPdoObj->expects($this->testObj->exactly($this->pdoCounter))->method('prepare');

        return $this->mockPdoObj;
    }
}
?>