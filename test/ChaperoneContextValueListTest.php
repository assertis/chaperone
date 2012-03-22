<?php
require_once('../classes/ChaperoneContextValueList.php');
class ChaperoneContextValueListTest extends PHPUnit_Framework_TestCase
{
    /*
     * Tests that a freshly-instantiated list is empty
     */
    function testEmpty() {
        
        $cltObj = new ChaperoneContextValueList();
        $this->assertEquals($cltObj->isWildcard(), FALSE);
        $this->assertEquals($cltObj->isEmpty(), TRUE);
        $this->assertEquals($cltObj->getItems(), array());
    }

    /*
     * Tests that a single item can be added
     */
    function testOneItem() {
        
        $cltObj = new ChaperoneContextValueList();
        $cltObj->addItem('foo');
        $this->assertEquals($cltObj->isWildcard(), FALSE);
        $this->assertEquals($cltObj->isEmpty(), FALSE);
        $this->assertEquals($cltObj->getItems(), array('foo'));
    }

    /*
     * Tests that two items can be added
     */
    function testTwoItems() {
        
        $cltObj = new ChaperoneContextValueList();
        $cltObj->addItem('foo');
        $cltObj->addItem('bar');
        $this->assertEquals($cltObj->isWildcard(), FALSE);
        $this->assertEquals($cltObj->isEmpty(), FALSE);
        $this->assertEquals($cltObj->getItems(), array('foo', 'bar'));
    }

    /*
     * Tests that duplicate items are handled gracefully
     */
    function testDuplicateItems() {
        
        $cltObj = new ChaperoneContextValueList();
        $cltObj->addItem('foo');
        $cltObj->addItem('bar');
        $cltObj->addItem('foo');
        $this->assertEquals($cltObj->isWildcard(), FALSE);
        $this->assertEquals($cltObj->isEmpty(), FALSE);
        $this->assertEquals($cltObj->getItems(), array('foo', 'bar'));
    }

    /*
     * Tests that a wildcard can be added
     */
    function testWildcard() {
        
        $cltObj = new ChaperoneContextValueList();
        $cltObj->addWildcard();
        $this->assertEquals($cltObj->isWildcard(), TRUE);
        $this->assertEquals($cltObj->isEmpty(), FALSE);
        $this->assertEquals($cltObj->getItems(), NULL);
    }

    /*
     * Tests that a wildcard can be added after an item
     */
    function testItemThenWildcard() {
        
        $cltObj = new ChaperoneContextValueList();
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
    function testWildcardThenItem() {
        
        $cltObj = new ChaperoneContextValueList();
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
    function testMergeNoOverlap() {
        $cltObj1 = new ChaperoneContextValueList();
        $cltObj1->addItem('eenie');
        $cltObj1->addItem('meenie');

        $cltObj2 = new ChaperoneContextValueList();
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
    function testMergeWithOverlap() {
        $cltObj1 = new ChaperoneContextValueList();
        $cltObj1->addItem('One');
        $cltObj1->addItem('Two');

        $cltObj2 = new ChaperoneContextValueList();
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
    function testMergePopulatedWithEmpty() {
        $cltObj1 = new ChaperoneContextValueList();
        $cltObj1->addItem('foo');
        $cltObj1->addItem('bar');

        $cltObj2 = new ChaperoneContextValueList();

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), FALSE);
        $this->assertEquals($cltObj1->isEmpty(), FALSE);
        $this->assertEquals($cltObj1->getItems(), array('foo', 'bar'));
    }

    /*
     * Tests merging an empty list with a populated list (populated list being merged in)
     */
    function testMergeEmptyWithPopulated() {
        $cltObj1 = new ChaperoneContextValueList();

        $cltObj2 = new ChaperoneContextValueList();
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
    function testMergeEmptyWithEmpty() {
        $cltObj1 = new ChaperoneContextValueList();

        $cltObj2 = new ChaperoneContextValueList();

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), FALSE);
        $this->assertEquals($cltObj1->isEmpty(), TRUE);
        $this->assertEquals($cltObj1->getItems(), array());
    }

    /*
     * Tests merging a non-wildcard list with a wildcard list (wildcard being merged in)
     */
    function testMergeNonWildcardWithWildcard() {
        $cltObj1 = new ChaperoneContextValueList();
        $cltObj1->addItem('foo');
        $cltObj1->addItem('bar');

        $cltObj2 = new ChaperoneContextValueList();
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
    function testMergeWildcardWithNonWildcard() {
        $cltObj1 = new ChaperoneContextValueList();
        $cltObj1->addWildcard();

        $cltObj2 = new ChaperoneContextValueList();
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
    function testMergeWildcardWithWildcard() {
        $cltObj1 = new ChaperoneContextValueList();
        $cltObj1->addWildcard();

        $cltObj2 = new ChaperoneContextValueList();
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
    function testMergeNumeric() {
        $cltObj1 = new ChaperoneContextValueList();
        $cltObj1->addItem(123);

        $cltObj2 = new ChaperoneContextValueList();
        $cltObj2->addItem(456);

        // Merge second list into first
        $cltObj1->merge($cltObj2);
        $this->assertEquals($cltObj1->isWildcard(), FALSE);
        $this->assertEquals($cltObj1->isEmpty(), FALSE);
        $this->assertEquals($cltObj1->getItems(), array(123, 456));
    }
}
?>