<?php
require_once('../classes/ChaperoneContextFilter.php');
class ChaperoneContextFilterTest extends PHPUnit_Framework_TestCase
{
    /*
     * Tests that a freshly-instantiated list is empty
     */
    public function testEmpty() {
        
        $cltObj = new ChaperoneContextFilter();
        $this->assertEquals($cltObj->isWildcard(), FALSE);
        $this->assertEquals($cltObj->isEmpty(), TRUE);
        $this->assertEquals($cltObj->getItems(), array());
    }

    /*
     * Tests that a single item can be added
     */
    public function testOneItem() {
        
        $cltObj = new ChaperoneContextFilter();
        $cltObj->addItem('foo');
        $this->assertEquals($cltObj->isWildcard(), FALSE);
        $this->assertEquals($cltObj->isEmpty(), FALSE);
        $this->assertEquals($cltObj->getItems(), array('foo'));
    }

    /*
     * Tests that two items can be added
     */
    public function testTwoItems() {
        
        $cltObj = new ChaperoneContextFilter();
        $cltObj->addItem('foo');
        $cltObj->addItem('bar');
        $this->assertEquals($cltObj->isWildcard(), FALSE);
        $this->assertEquals($cltObj->isEmpty(), FALSE);
        $this->assertEquals($cltObj->getItems(), array('foo', 'bar'));
    }

    /*
     * Tests that duplicate items are handled gracefully
     */
    public function testDuplicateItems() {
        
        $cltObj = new ChaperoneContextFilter();
        $cltObj->addItem('foo');
        $cltObj->addItem('bar');
        $cltObj->addItem('foo');
        $this->assertEquals($cltObj->isWildcard(), FALSE);
        $this->assertEquals($cltObj->isEmpty(), FALSE);
        $this->assertEquals($cltObj->getItems(), array('foo', 'bar'));
    }

    /*
     * Tests that a Null item is rejected
     */
    public function testNullItem() {
        
        $cltObj = new ChaperoneContextFilter();
        try {
            $cltObj->addItem(NULL);
            $this->fail('addItem() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Filter item "" is invalid');
        }
    }

    
    /*
     * Tests that an array item is rejected
     */
    public function testArrayItem() {
        
        $cltObj = new ChaperoneContextFilter();
        try {
            $cltObj->addItem(array(1, 2, 3));
            $this->fail('addItem() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Filter item "Array" is invalid');
        }
    }

    
    /*
     * Tests that a non-interger item is rejected
     */
    public function testNonIntegerItem() {
        
        $cltObj = new ChaperoneContextFilter();
        try {
            $cltObj->addItem(1.23);
            $this->fail('addItem() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Filter item "1.23" is invalid');
        }
    }

    
    /*
     * Tests that an item with the value of TRUE is rejected
     */
    public function testTrueItem() {
        
        $cltObj = new ChaperoneContextFilter();
        try {
            $cltObj->addItem(TRUE);
            $this->fail('addItem() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Filter item "1" is invalid');
        }
    }

    
    /*
     * Tests that an item with the value of FALSE is rejected
     */
    public function testFalseItem() {
        
        $cltObj = new ChaperoneContextFilter();
        try {
            $cltObj->addItem(FALSE);
            $this->fail('addItem() failed to generate an exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Filter item "" is invalid');
        }
    }

    
    /*
     * Tests that a wildcard can be added
     */
    public function testWildcard() {
        
        $cltObj = new ChaperoneContextFilter();
        $cltObj->addWildcard();
        $this->assertEquals($cltObj->isWildcard(), TRUE);
        $this->assertEquals($cltObj->isEmpty(), FALSE);
        $this->assertEquals($cltObj->getItems(), NULL);
    }

    /*
     * Tests that a wildcard can be added after an item
     */
    public function testItemThenWildcard() {
        
        $cltObj = new ChaperoneContextFilter();
        $cltObj->addItem('foo');
        $this->assertEquals($cltObj->isWildcard(), FALSE);
        $cltObj->addWildcard();
        $this->assertEquals($cltObj->isWildcard(), TRUE);
        $this->assertEquals($cltObj->isEmpty(), FALSE);
        $this->assertEquals($cltObj->getItems(), NULL);
    }

    /*
     * Tests that an item can be added after a wildcard (although the list will stay as wildcard)
     */
    public function testWildcardThenItem() {
        
        $cltObj = new ChaperoneContextFilter();
        $cltObj->addWildcard();
        $this->assertEquals($cltObj->isWildcard(), TRUE);
        $cltObj->addItem('foo');
        $this->assertEquals($cltObj->isWildcard(), TRUE);
        $this->assertEquals($cltObj->isEmpty(), FALSE);
        $this->assertEquals($cltObj->getItems(), NULL);
    }

    /*
     * Tests that two lists with no overlap can be merged
     */
    public function testMergeNoOverlap() {
        $cltObj1 = new ChaperoneContextFilter();
        $cltObj1->addItem('eenie');
        $cltObj1->addItem('meenie');

        $cltObj2 = new ChaperoneContextFilter();
        $cltObj2->addItem('minie');
        $cltObj2->addItem('mo');

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), FALSE);
        $this->assertEquals($cltObj1->isEmpty(), FALSE);
        $this->assertEquals($cltObj1->getItems(), array('eenie', 'meenie', 'minie', 'mo'));
    }

    /*
     * Tests that two lists with overlap can be merged
     */
    public function testMergeWithOverlap() {
        $cltObj1 = new ChaperoneContextFilter();
        $cltObj1->addItem('One');
        $cltObj1->addItem('Two');

        $cltObj2 = new ChaperoneContextFilter();
        $cltObj2->addItem('Two');
        $cltObj2->addItem('Three');

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), FALSE);
        $this->assertEquals($cltObj1->isEmpty(), FALSE);
        $this->assertEquals($cltObj1->getItems(), array('One', 'Two', 'Three'));
    }

    /*
     * Tests merging a populated list with an empty list (empty list being merged in)
     */
    public function testMergePopulatedWithEmpty() {
        $cltObj1 = new ChaperoneContextFilter();
        $cltObj1->addItem('foo');
        $cltObj1->addItem('bar');

        $cltObj2 = new ChaperoneContextFilter();

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), FALSE);
        $this->assertEquals($cltObj1->isEmpty(), FALSE);
        $this->assertEquals($cltObj1->getItems(), array('foo', 'bar'));
    }

    /*
     * Tests merging an empty list with a populated list (populated list being merged in)
     */
    public function testMergeEmptyWithPopulated() {
        $cltObj1 = new ChaperoneContextFilter();

        $cltObj2 = new ChaperoneContextFilter();
        $cltObj2->addItem('foo');
        $cltObj2->addItem('bar');

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), FALSE);
        $this->assertEquals($cltObj1->isEmpty(), FALSE);
        $this->assertEquals($cltObj1->getItems(), array('foo', 'bar'));
    }

    /*
     * Tests merging an empty list with a populated list (populated list being merged in)
     */
    public function testMergeEmptyWithEmpty() {
        $cltObj1 = new ChaperoneContextFilter();

        $cltObj2 = new ChaperoneContextFilter();

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), FALSE);
        $this->assertEquals($cltObj1->isEmpty(), TRUE);
        $this->assertEquals($cltObj1->getItems(), array());
    }

    /*
     * Tests merging a non-wildcard list with a wildcard list (wildcard being merged in)
     */
    public function testMergeNonWildcardWithWildcard() {
        $cltObj1 = new ChaperoneContextFilter();
        $cltObj1->addItem('foo');
        $cltObj1->addItem('bar');

        $cltObj2 = new ChaperoneContextFilter();
        $cltObj2->addWildcard();

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), TRUE);
        $this->assertEquals($cltObj1->isEmpty(), FALSE);
        $this->assertEquals($cltObj1->getItems(), NULL);
    }

    /*
     * Tests merging a wildcard list with a non-wildcard list (non-wildcard being merged in)
     */
    public function testMergeWildcardWithNonWildcard() {
        $cltObj1 = new ChaperoneContextFilter();
        $cltObj1->addWildcard();

        $cltObj2 = new ChaperoneContextFilter();
        $cltObj2->addItem('foo');
        $cltObj2->addItem('bar');

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), TRUE);
        $this->assertEquals($cltObj1->isEmpty(), FALSE);
        $this->assertEquals($cltObj1->getItems(), NULL);
    }

    /*
     * Tests merging two wildcard lists
     */
    public function testMergeWildcardWithWildcard() {
        $cltObj1 = new ChaperoneContextFilter();
        $cltObj1->addWildcard();

        $cltObj2 = new ChaperoneContextFilter();
        $cltObj2->addWildcard();

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), TRUE);
        $this->assertEquals($cltObj1->isEmpty(), FALSE);
        $this->assertEquals($cltObj1->getItems(), NULL);
    }

    /*
     * Tests merging two lists with numeric keys
     */
    public function testMergeNumeric() {
        $cltObj1 = new ChaperoneContextFilter();
        $cltObj1->addItem(123);

        $cltObj2 = new ChaperoneContextFilter();
        $cltObj2->addItem(456);

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), FALSE);
        $this->assertEquals($cltObj1->isEmpty(), FALSE);
        $this->assertEquals($cltObj1->getItems(), array(123, 456));
    }
}
?>