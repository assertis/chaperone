<?php
/*
 * This code finds out what (if any) items we can perform a particular action on
 */
require_once('classes/ChaperoneRole.php');

define('U_DATABASE_HOST', 'localhost');
define('U_DATABASE_USERNAME', 'chaptest');
define('U_DATABASE_PASSWORD', 'chaptest');

try {
    $pdo = new PDO('mysql:host='.U_DATABASE_HOST, U_DATABASE_USERNAME, U_DATABASE_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    Chaperone::setPDO($pdo);
} catch (Exception $e) {
    die($e);
}

// Attach Role
$roleObj = ChaperoneRole::loadByName('businessadmin');
$crsObj = $roleObj->getContextRuleSet(array('tmc'=>'xyz', 'business'=>123));    // Context Rule Set
$actionArray = $roleObj->getActions();                                          // Actions and their rulesets



// Within the TMC 'xyz', which businesses can I do biz_view on?
$contextArray = array('tmc'=>'xyz');
$contextItem = 'business';

// Idiot check the context to ensure the stated item is not in there
if ($contextArray === NULL) {
    $contextArray = array();
} else {
    if (array_key_exists($contextItem, $contextArray))
        throw new Exception('Context item "'.$contextItem.'" exists in the context array');
}

foreach ($actionArray AS $actionObj) {

    // Action found
    if ($actionObj->getFullName() == 'b2b.biz_view') {

        // The context item must be in the Role Context RuleSet (either as a wildcard
        // or a context)
        if (!$crsObj->isRuleFor($contextItem))
            continue;

        // Are there any rules for the action
        $actionRuleSetArray = $actionObj->getRuleSets();
        if (count($actionRuleSetArray) > 0) {
            foreach ($actionRuleSetArray AS $actionRuleSetObj) {
                
                // If the Action RuleSet has the context item as a wildcard, use the Context RuleSet as is
                if ($actionRuleSetObj->isWildcardRuleFor($contextItem)) {
                    $acrsObj = $actionRuleSetObj->getContextRuleSet($contextArray);

                // If the Action RuleSet does not have the context item as a wildcard, create a Context RuleSet that excludes it
                // (it may not be there anyway)
                } else {
                    $acrsObj = $actionRuleSetObj->getContextRuleSetExceptFor($contextItem, $contextArray);
                }
                
                // Ensure the Action CRS can be satisfied by the Role CRS
                if (!$acrsObj->isSubsetOf($crsObj)) continue;

                echo '<pre>';
                var_dump($crsObj->getContextRuleValue($contextItem));
                echo '</pre>';

                // Grab the context item rule from the RCRS.  We can move on to the next action because we've got
                // all we need from this RCRS

            }
        }
    }
}
?>