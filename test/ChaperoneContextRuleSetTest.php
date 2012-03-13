<?php
require_once('../classes/ChaperoneContextRuleSet.php');
class ChaperoneContextRuleSetTest extends PHPUnit_Framework_TestCase {

    /*
     * This method tests that RuleSets made up of only wildcards correctly
     * calculate isSubsetOf()
     */
    function testWildcardIsSubsetOf() {
        
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
    function testContextIsSubsetOf() {
        
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
    function testMixtureIsSubsetOf() {
        
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