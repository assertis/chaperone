<?php
require_once('../classes/ChaperoneNamespace.php');
require_once('helperMockPdo.php');
class ChaperoneNamespaceTest extends PHPUnit_Framework_TestCase
{
    // SQL for various lookups.  We define them here because we use them in multiple tests
    private $sqlGetNameForId =
               'SELECT  name
                FROM    global.chaperone_namespace
                WHERE   id = :id';
   
    private $sqlGetIdForName = 
               'SELECT  id
                FROM    global.chaperone_namespace
                WHERE   name = :name';

    /*
     * Reset static object before running unit tests (in case other tests left it in an odd state)
     */
    public static function setUpBeforeClass() {
        ChaperoneNamespace::reset();
    }

   
    /*
     * Tests that we can successfully load the namespace for an ID
     */
    public function testGetNameForId() {

        $helperMockPdoObj = new helperMockPdo($this);
        $helperMockPdoObj->addMockPdoFetchStatement($this->sqlGetNameForId, array(':id'=>1), array(array('name'=>'test')), 1);
        $mockPDO = $helperMockPdoObj->getPDO();
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
    public function testIdCache() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getNameForId(1), 'test');
    }
    
    
    /*
     * Tests that caching for name lookups works.  This tests that the cross-caching works
     * @depends testGetNameForId
     */
    public function testNameCrossCache() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getIdForName('test'), 1);
    }
    
    
    /*
     * Tests that we get back NULL if the ID cannot be found
     */
    public function testGetNameForIdMissing() {
        $helperPdoObj = new helperMockPdo($this);
        $helperPdoObj->addMockPdoFetchStatement($this->sqlGetNameForId, array(':id'=>2), array(), 0);
        $mockPDO = $helperPdoObj->getPDO();

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
    public function testGetIdCacheMissing() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getNameForId(2), NULL);
    }


    /*
     * Tests that we can successfully load the ID for a given namespace
     */
    public function testGetIdForName() {

        $helperMockPdoObj = new helperMockPdo($this);
        $helperMockPdoObj->addMockPdoFetchStatement($this->sqlGetIdForName, array(':name'=>'test2'), array(array('id'=>3)), 1);
        $mockPDO = $helperMockPdoObj->getPDO();

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
    public function testNameCache() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getIdForName('test2'), 3);
    }
    
    
    /*
     * Tests that caching for id lookups works.  This tests that the cross-caching works
     * @depends testGetIdForName
     */
    public function testIdCrossCache() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getNameForId(3), 'test2');
    }
    
    
    /*
     * Tests that we get back NULL if the ID cannot be found
     */
    public function testGetIdForNameMissing() {

        $helperPdoObj = new helperMockPdo($this);
        $helperPdoObj->addMockPdoFetchStatement($this->sqlGetIdForName, array(':name'=>'missing'), array(), 0);
        $mockPDO = $helperPdoObj->getPDO();


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
    public function testNameCacheMissing() {

        // Mock object with no methods.  Should not be called because the item is coming from cache
        $mockPDO = $this->getMock('MockPDO');
        Chaperone::setPDO($mockPDO);
        $this->assertEquals(ChaperoneNamespace::getIdForName('missing'), NULL);
    }


    /*
     * Simulates a scenario where the same namespace name exists for two different IDs
     * Unique indexing should prevent this from ever happening in the wild
     * In this test, we ask for ID 4 and get back a namespace "test", which we've previously found for ID 1
     */
    public function testDuplicateNamespaceName() {

        $helperPdoObj = new helperMockPdo($this);
        $helperPdoObj->addMockPdoFetchStatement($this->sqlGetNameForId, array(':id'=>4), array(array('name'=>'test')), 1);
        $mockPDO = $helperPdoObj->getPDO();

        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Run lookup, which should fail with an exception
        try {
            ChaperoneNamespace::getNameForId(4);
            $this->fail('getNameForId() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Duplicate namespace "test" found');
        }
    }
    
    
    /*
     * Simulates a scenario where the same namespace id exists for two different names
     * This should never happen in the wild because the ID column is a primary key
     * In this test, we ask for "test3" and get back a id 1, which we've previously found for "test"
     */
    public function testDuplicateNamespaceId() {

        $helperPdoObj = new helperMockPdo($this);
        $helperPdoObj->addMockPdoFetchStatement($this->sqlGetIdForName, array(':name'=>'test3'), array(array('id'=>1)), 1);
        $mockPDO = $helperPdoObj->getPDO();

        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Run lookup, which should fail with an exception
        try {
            ChaperoneNamespace::getIdForName('test3');
            $this->fail('getNameForId() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Duplicate namespace ID "1" found');
        }
    }
    
    
    /*
     * Simulates a scenario where multiple rows are returned when looking up a namespace name
     * This should never happen in the wild because the name column should have a unique index on it
     * In this test, we ask for "test4" and get back multiple rows
     */
    public function testMultipleNamespaceRows() {

        $helperPdoObj = new helperMockPdo($this);
        $helperPdoObj->addMockPdoFetchStatement($this->sqlGetIdForName, array(':name'=>'test4'), array(array('id'=>8), array('id'=>9)), 0);
        $mockPDO = $helperPdoObj->getPDO();

        /*
         * Unit test
         */
        
        // Set mock PDO
        Chaperone::setPDO($mockPDO);

        // Run lookup, which should fail with an exception
        try {
            ChaperoneNamespace::getIdForName('test4');
            $this->fail('getNameForId() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'More than one instance of Namespace "test4" was found');
        }
    }
    
    
    /*
     * Tests that the namespace can be set and retrieved
     */
    public function testSetNamespace() {
        ChaperoneNamespace::reset();
        ChaperoneNamespace::setNamespace('foo');
        $this->assertEquals(ChaperoneNamespace::getNamespace(), 'foo');
        ChaperoneNamespace::setNamespace('bar');
        $this->assertEquals(ChaperoneNamespace::getNamespace(), 'bar');
        ChaperoneNamespace::reset();
    }

    
    /*
     * Tests that the namespace cannot be set to NULL
     */
    public function testSetNamespaceNull() {
        ChaperoneNamespace::reset();
        try {
            ChaperoneNamespace::setNamespace(NULL);
            $this->fail('setNamespace() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Namespace "" is invalid');
        }
        ChaperoneNamespace::reset();
    }

    
    /*
     * Tests that the namespace cannot be set to an array
     */
    public function testSetNamespaceArray() {
        ChaperoneNamespace::reset();
        try {
            ChaperoneNamespace::setNamespace(array(1, 2, 3));
            $this->fail('setNamespace() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Namespace "Array" is invalid');
        }
        ChaperoneNamespace::reset();
    }

    
    /*
     * Tests that the namespace cannot be set to an integer
     */
    public function testSetNamespaceInteger() {
        ChaperoneNamespace::reset();
        try {
            ChaperoneNamespace::setNamespace(123);
            $this->fail('setNamespace() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Namespace "123" is invalid');
        }
        ChaperoneNamespace::reset();
    }

    
    /*
     * Tests that the namespace cannot be set to an non-integer
     */
    public function testSetNamespaceNonInteger() {
        ChaperoneNamespace::reset();
        try {
            ChaperoneNamespace::setNamespace(1.23);
            $this->fail('setNamespace() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Namespace "1.23" is invalid');
        }
        ChaperoneNamespace::reset();
    }

    
    /*
     * Tests that the namespace cannot be set to TRUE
     */
    public function testSetNamespaceTrue() {
        ChaperoneNamespace::reset();
        try {
            ChaperoneNamespace::setNamespace(TRUE);
            $this->fail('setNamespace() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Namespace "1" is invalid');
        }
        ChaperoneNamespace::reset();
    }

    
    /*
     * Tests that the namespace cannot be set to FALSE
     */
    public function testSetNamespaceFalse() {
        ChaperoneNamespace::reset();
        try {
            ChaperoneNamespace::setNamespace(FALSE);
            $this->fail('setNamespace() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Namespace "" is invalid');
        }
        ChaperoneNamespace::reset();
    }

    
    /*
     * Tests that the namespace cannot be set to a value containing a dot
     */
    public function testSetNamespaceContainsDot() {
        ChaperoneNamespace::reset();
        try {
            ChaperoneNamespace::setNamespace('a.b');
            $this->fail('setNamespace() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Namespace "a.b" contains a dot');
        }
        try {
            ChaperoneNamespace::setNamespace('a.b.c');
            $this->fail('setNamespace() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Namespace "a.b.c" contains a dot');
        }
        ChaperoneNamespace::reset();
    }
    
    
    /*
     * Tests that splitResourceName() method works correctly
     */
    public function testSplitResourceName() {
        ChaperoneNamespace::reset();

        // Two parts to name
        $this->assertEquals(ChaperoneNamespace::splitResourceName('foo.bar'), array('namespace'=>'foo' , 'resourceName'=>'bar'));

        // One part without setting the namespace will fail
        try {
            ChaperoneNamespace::splitResourceName('bar');
            $this->fail('splitResourceName() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Default namespace has not been set');
        }

        // Setting the namespace should allow it to work
        ChaperoneNamespace::setNamespace('foo');
        $this->assertEquals(ChaperoneNamespace::splitResourceName('bar'), array('namespace'=>'foo' , 'resourceName'=>'bar'));

        // Three parts should fail
        try {
            ChaperoneNamespace::splitResourceName('a.b.c');
            $this->fail('splitResourceName() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Resource name "a.b.c"');
        }

        // Reset static class's attributes
        ChaperoneNamespace::reset();
    }

    
    /*
     * Tests that getFullName() method works correctly
     */
    public function testGetFullName() {
        ChaperoneNamespace::reset();

        // Two parts to name
        $this->assertEquals(ChaperoneNamespace::getFullName('foo.bar'), 'foo.bar');

        // One part without setting the namespace will fail
        try {
            ChaperoneNamespace::getFullName('bar');
            $this->fail('getFullName() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Default namespace has not been set');
        }

        // Setting the namespace should allow it to work
        ChaperoneNamespace::setNamespace('foo');
        $this->assertEquals(ChaperoneNamespace::getFullName('bar'), 'foo.bar');

        // Three parts should fail
        try {
            ChaperoneNamespace::getFullName('a.b.c');
            $this->fail('getFullName() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Resource name "a.b.c"');
        }

        // Reset static class's attributes
        ChaperoneNamespace::reset();
    }
    
    
    /*
     * Reset static object before going on to next unit test
     */
    public static function tearDownAfterClass() {
        ChaperoneNamespace::reset();
    }
}
?>