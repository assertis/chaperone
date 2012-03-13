<?php
require_once('../classes/ChaperoneRole.php');
class ChaperoneTest extends PHPUnit_Framework_TestCase
{
    function testPDO() {
        
        /*
         * Uninitialised PDO should cause an exception
         */
        try {
            $fail = Chaperone::getPDO();
            $this->fail('getPDO failed to generate an exception');
        } catch (Exception $e) {
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
    function testSchema() {
        $this->assertEquals(Chaperone::getSchema(), 'global');
    }

    
    /*
     * 
     */
    function testSplitResourceName() {

        // With no namespace, return the resource name with the current namespace ID
        $this->assertEquals(Chaperone::splitResourceName('testing'), array('namespaceId'=>1, 'namespace'=>'b2b', 'resourceName'=>'testing'));

        // With the current namespace, return the current namespace ID and the resource name
        $this->assertEquals(Chaperone::splitResourceName('b2b.testing'), array('namespaceId'=>1, 'namespace'=>'b2b', 'resourceName'=>'testing'));

        // With an unrecognised namespace, expect an exception
        try {
            $fail = Chaperone::splitResourceName('foo.testing');
            $this->fail('Chaperone::splitResourceName() failed to generate an exception');
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), 'Namespace lookup currently unsupported');
        }
        
        // If there is more than one dot, that's an error
        try {
            $fail = Chaperone::splitResourceName('this.should.fail');
            $this->fail('Chaperone::splitResourceName() failed to generate an exception');
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Resource name "this.should.fail"');
        }
    }
}
?>