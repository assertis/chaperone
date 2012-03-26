<?php
require_once('../classes/ChaperoneRole.php');
class ChaperoneTest extends PHPUnit_Framework_TestCase
{
    public function testPDO() {
        
        /*
         * Uninitialised PDO should cause an exception
         */
        Chaperone::resetPDO();
        try {
            $fail = Chaperone::getPDO();
            $this->fail('getPDO failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Chaperone PDO has not been set');
        }

        // Set a dummy PDO and the retrieve it
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(Chaperone::getPDO(), $mockPDO);
    }
    
    /*
     * Schema value is currently hard-coded
     */
    public function testSchema() {
        $this->assertEquals(Chaperone::databaseSchema, 'global');
    }
    
    public function testNamespace() {
        Chaperone::setNamespace('testing');
        $this->assertEquals(Chaperone::getNamespace(), 'testing');
    }
}
?>