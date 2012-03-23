<?php
require_once('../classes/ChaperoneContextRuleSet.php');
class ChaperoneContextRuleSetTest extends PHPUnit_Framework_TestCase {

    /*
     * This method tests that Context RuleSets can be created and given some rules
     */
    public function testValidContextRuleSet() {
        $crsObj = new ChaperoneContextRuleSet();
        $crsObj->addWildcardRule('aaa');
        $crsObj->addContextRule('bbb', 'BBBBB');
        $crsObj->addContextRule('ccc', 123);        // Integer context values are allowed
        
        // Do some tests on the Context RuleSet to make sure it's in the expected state (and that the methods work correctly)
        $this->assertEquals($crsObj->isRuleFor('aaa'), TRUE);
        $this->assertEquals($crsObj->isWildcardRuleFor('aaa'), TRUE);
        $this->assertEquals($crsObj->getContextRuleValue('aaa'), NULL);

        $this->assertEquals($crsObj->isRuleFor('bbb'), TRUE);
        $this->assertEquals($crsObj->isWildcardRuleFor('bbb'), FALSE);
        $this->assertEquals($crsObj->getContextRuleValue('bbb'), 'BBBBB');

        $this->assertEquals($crsObj->isRuleFor('ccc'), TRUE);
        $this->assertEquals($crsObj->isWildcardRuleFor('ccc'), FALSE);
        $this->assertEquals($crsObj->getContextRuleValue('ccc'), 123);

        $this->assertEquals($crsObj->isRuleFor('ddd'), FALSE);
        $this->assertEquals($crsObj->isWildcardRuleFor('ddd'), FALSE);
        $this->assertEquals($crsObj->getContextRuleValue('ddd'), NULL);
    }
    
    
    /*
     * This method tests that isRuleFor() method fails for invalid context items
     */
    public function testIsRuleForInvalid() {
        $crsObj = new ChaperoneContextRuleSet();

        // Test NULL
        try {
            $crsObj->isRuleFor(NULL);
            $this->fail('isRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "" has an invalid name');
        }
        
        // Test Array
        try {
            $crsObj->isRuleFor(array(1, 2, 3));
            $this->fail('isRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "Array" has an invalid name');
        }

        // Test Integer
        try {
            $crsObj->isRuleFor(123);
            $this->fail('isRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "123" has an invalid name');
        }

        // Test Non-integer
        try {
            $crsObj->isRuleFor(1.23);
            $this->fail('isRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "1.23" has an invalid name');
        }

        // Test TRUE
        try {
            $crsObj->isRuleFor(TRUE);
            $this->fail('isRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "1" has an invalid name');
        }

        // Test FALSE
        try {
            $crsObj->isRuleFor(NULL);
            $this->fail('isRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "" has an invalid name');
        }
    }
    
    
    
    
    /*
     * This method tests that isWildcardRuleFor() method fails for invalid context items
     */
    public function testIsWildcardRuleForInvalid() {
        $crsObj = new ChaperoneContextRuleSet();

        // Test NULL
        try {
            $crsObj->isWildcardRuleFor(NULL);
            $this->fail('isWildcardRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "" has an invalid name');
        }
        
        // Test Array
        try {
            $crsObj->isWildcardRuleFor(array(1, 2, 3));
            $this->fail('isWildcardRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "Array" has an invalid name');
        }

        // Test Integer
        try {
            $crsObj->isWildcardRuleFor(123);
            $this->fail('isWildcardRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "123" has an invalid name');
        }

        // Test Non-integer
        try {
            $crsObj->isWildcardRuleFor(1.23);
            $this->fail('isWildcardRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "1.23" has an invalid name');
        }

        // Test TRUE
        try {
            $crsObj->isWildcardRuleFor(TRUE);
            $this->fail('isWildcardRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "1" has an invalid name');
        }

        // Test FALSE
        try {
            $crsObj->isWildcardRuleFor(NULL);
            $this->fail('isWildcardRuleFor() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "" has an invalid name');
        }
    }
    
    
    
    
    /*
     * This method tests that getContextRuleValue() method fails for invalid context items
     */
    public function testGetContextRuleValueInvalid() {
        $crsObj = new ChaperoneContextRuleSet();

        // Test NULL
        try {
            $crsObj->getContextRuleValue(NULL);
            $this->fail('getContextRuleValue() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "" has an invalid name');
        }
        
        // Test Array
        try {
            $crsObj->getContextRuleValue(array(1, 2, 3));
            $this->fail('getContextRuleValue() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "Array" has an invalid name');
        }

        // Test Integer
        try {
            $crsObj->getContextRuleValue(123);
            $this->fail('getContextRuleValue() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "123" has an invalid name');
        }

        // Test Non-integer
        try {
            $crsObj->getContextRuleValue(1.23);
            $this->fail('getContextRuleValue() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "1.23" has an invalid name');
        }

        // Test TRUE
        try {
            $crsObj->getContextRuleValue(TRUE);
            $this->fail('getContextRuleValue() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "1" has an invalid name');
        }

        // Test FALSE
        try {
            $crsObj->getContextRuleValue(NULL);
            $this->fail('getContextRuleValue() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "" has an invalid name');
        }
    }
    
    
    /*
     * This method tests validation rules within addWildcardRule
     */
    public function testWildcardRuleValidation() {
        $crsObj = new ChaperoneContextRuleSet();

        // Test NULL
        try {
            $crsObj->addWildcardRule(NULL);
            $this->fail('addWildcardRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "" has an invalid name');
        }

        // Test Array
        try {
            $crsObj->addWildcardRule(array(1, 2, 3));
            $this->fail('addWildcardRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "Array" has an invalid name');
        }

        // Test integer
        try {
            $crsObj->addWildcardRule(123);
            $this->fail('addWildcardRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "123" has an invalid name');
        }

        // Test non-integer
        try {
            $crsObj->addWildcardRule(1.23);
            $this->fail('addWildcardRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "1.23" has an invalid name');
        }

        // Test TRUE
        try {
            $crsObj->addWildcardRule(TRUE);
            $this->fail('addWildcardRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "1" has an invalid name');
        }

        // Test FALSE
        try {
            $crsObj->addWildcardRule(FALSE);
            $this->fail('addWildcardRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "" has an invalid name');
        }
    }


    /*
     * This method tests that duplicate wildcard rules are intercepted
     */
    public function testWildcardRuleDuplicate() {
        $crsObj = new ChaperoneContextRuleSet();
        $crsObj->addWildcardRule('foo');
        
        // Should not be able to add a second wildcard rule for foo
        try {
            $crsObj->addWildcardRule('foo');
            $this->fail('addWildcardRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "foo" already exists in rule');
        }

        // Should not be able to add a context rule for foo
        try {
            $crsObj->addContextRule('foo', 'bar');
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "foo" already exists in rule');
        }
    }
    
    
    /*
     * This method tests validation rules within addContextRule
     */
    public function testContextRuleValidation() {
        $crsObj = new ChaperoneContextRuleSet();

        /*
         * Test invalid context items
         */
        
        // Test NULL
        try {
            $crsObj->addContextRule(NULL, 'aaa');
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "" has an invalid name');
        }

        // Test Array
        try {
            $crsObj->addContextRule(array(1, 2, 3), 'aaa');
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "Array" has an invalid name');
        }

        // Test integer
        try {
            $crsObj->addContextRule(123, 'aaa');
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "123" has an invalid name');
        }

        // Test non-integer
        try {
            $crsObj->addContextRule(1.23, 'aaa');
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "1.23" has an invalid name');
        }

        // Test TRUE
        try {
            $crsObj->addContextRule(TRUE, 'aaa');
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "1" has an invalid name');
        }

        // Test FALSE
        try {
            $crsObj->addContextRule(FALSE, 'aaa');
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "" has an invalid name');
        }


        /*
         * Test invalid context values
         */
        
        // Test NULL
        try {
            $crsObj->addContextRule('aaa', NULL);
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "aaa" has an invalid value');
        }

        // Test Array
        try {
            $crsObj->addContextRule('aaa', array(1, 2, 3));
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "aaa" has an invalid value');
        }

        // Test non-integer
        try {
            $crsObj->addContextRule('aaa', 1.23);
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "aaa" has an invalid value');
        }

        // Test TRUE
        try {
            $crsObj->addContextRule('aaa', TRUE);
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "aaa" has an invalid value');
        }

        // Test FALSE
        try {
            $crsObj->addContextRule('aaa', FALSE);
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "aaa" has an invalid value');
        }
    }


    /*
     * This method tests that context rules for duplicate items are intercepted
     */
    public function testContextRuleDuplicate() {
        $crsObj = new ChaperoneContextRuleSet();
        $crsObj->addContextRule('foo', 'bar');
        
        // Should not be able to add another context rule for foo
        try {
            $crsObj->addContextRule('foo', 'bar');
            $this->fail('addContextRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "foo" already exists in rule');
        }
        
        // Should not be able to add a wildcard rule for foo
        try {
            $crsObj->addWildcardRule('foo');
            $this->fail('addWildcardRule() failed to throw expected exception');
        } catch (ChaperoneException $e) {
            $this->assertEquals($e->getMessage(), 'Context item "foo" already exists in rule');
        }
    }
    
    
    /*
     * This method tests that RuleSets made up of only wildcards correctly
     * calculate isSubsetOf()
     */
    public function testWildcardIsSubsetOf() {
        
        /*
         * Define superset (we will use the same one for each test)
         */
        $supersetObj = new ChaperoneContextRuleSet();
        $supersetObj->addWildcardRule('aaa');
        $supersetObj->addWildcardRule('bbb');
        $supersetObj->addWildcardRule('ccc');


        /*
         * Test a subset works
         */
        $subsetObj = new ChaperoneContextRuleSet();
        $subsetObj->addWildcardRule('aaa');
        $subsetObj->addWildcardRule('bbb');

        // $subsetObj is a subset of $supersetObj
        $this->assertEquals($subsetObj->isSubsetOf($supersetObj), TRUE);


        /*
         * Test something that isn't a subset
         */
        $notSubsetObj = new ChaperoneContextRuleSet();
        $notSubsetObj->addWildcardRule('aaa');
        $notSubsetObj->addWildcardRule('zzz');

        // $notSubsetObj is not a subset of $supersetObj
        $this->assertEquals($notSubsetObj->isSubsetOf($supersetObj), FALSE);


        /*
         * Test a ContextRuleSet with no rules is considered a subset
         */
        $emptyObj = new ChaperoneContextRuleSet();
        $this->assertEquals($emptyObj->isSubsetOf($supersetObj), TRUE);
    }


    /*
     * This method tests that RuleSets made up of only contexts correctly
     * calculate isSubsetOf()
     */
    public function testContextIsSubsetOf() {
        
        /*
         * Define a superset (we will use the same one for each test)
         */
        $supersetObj = new ChaperoneContextRuleSet();
        $supersetObj->addContextRule('aaa', 'AAAAA');
        $supersetObj->addContextRule('bbb', 'BBBBB');
        $supersetObj->addContextRule('ccc', 'CCCCC');


        /*
         * Test a subset works
         */
        $subsetObj = new ChaperoneContextRuleSet();
        $subsetObj->addContextRule('aaa', 'AAAAA');
        $subsetObj->addContextRule('bbb', 'BBBBB');

        // $subsetObj is a subset of $supersetObj
        $this->assertEquals($subsetObj->isSubsetOf($supersetObj), TRUE);


        /*
         * Test something that isn't a subset (context items don't match)
         */
        $notSubsetObj = new ChaperoneContextRuleSet();
        $notSubsetObj->addContextRule('aaa', 'AAAAA');
        $notSubsetObj->addContextRule('zzz', 'ZZZZZ');

        // $notSubsetObj is not a subset of $supersetObj
        $this->assertEquals($notSubsetObj->isSubsetOf($supersetObj), FALSE);


        /*
         * Test something that isn't a subset (context values don't match)
         */
        $notSubsetObj = new ChaperoneContextRuleSet();
        $notSubsetObj->addContextRule('aaa', 'AAAAA');
        $notSubsetObj->addContextRule('bbb', 'ZZZZZ');

        // $notSubsetObj is not a subset of $supersetObj
        $this->assertEquals($notSubsetObj->isSubsetOf($supersetObj), FALSE);


        /*
         * Test a ContextRuleSet with no rules is considered a subset
         */
        $emptyObj = new ChaperoneContextRuleSet();
        $this->assertEquals($emptyObj->isSubsetOf($supersetObj), TRUE);
    }

    /*
     * This method tests RuleSets using a mixture of Wildcard rules and Context
     * rules
     */
    public function testMixtureIsSubsetOf() {
        
        /*
         * Define a superset (we will use the same one for each test)
         */
        $supersetObj = new ChaperoneContextRuleSet();
        $supersetObj->addWildcardRule('aaa');
        $supersetObj->addContextRule('bbb', 'BBBBB');
        $supersetObj->addContextRule('ccc', 'CCCCC');


        /*
         * Test a subset with two context rules
         */
        $subsetObj = new ChaperoneContextRuleSet();
        $subsetObj->addContextRule('aaa', 'AAAAA');
        $subsetObj->addContextRule('bbb', 'BBBBB');

        // $subsetObj is a subset of $supersetObj
        $this->assertEquals($subsetObj->isSubsetOf($supersetObj), TRUE);


        /*
         * Test a subset with a wildcard rule and a context rule ('aaa' must be a wildcard)
         */
        $subsetObj = new ChaperoneContextRuleSet();
        $subsetObj->addWildcardRule('aaa');
        $subsetObj->addContextRule('bbb', 'BBBBB');

        // $subsetObj is a subset of $supersetObj
        $this->assertEquals($subsetObj->isSubsetOf($supersetObj), TRUE);


        /*
         * Test a Context RuleSet that is not a subset.  'bbb' must be a wildcard, so it fails
         */
        $notSubsetObj = new ChaperoneContextRuleSet();
        $notSubsetObj->addWildcardRule('aaa');
        $notSubsetObj->addWildcardRule('bbb');

        // $subsetObj is a subset of $supersetObj
        $this->assertEquals($notSubsetObj->isSubsetOf($supersetObj), FALSE);


        /*
         * Test a ContextRuleSet with no rules is considered a subset
         */
        $emptyObj = new ChaperoneContextRuleSet();
        $this->assertEquals($emptyObj->isSubsetOf($supersetObj), TRUE);
    }
}
?>