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

        // Grab the context item rule from the RCRS

        // Are there any rules for the action
        $actionRuleSetArray = $actionObj->getRuleSets();
        if (count($actionRuleSetArray) > 0) {
            foreach ($actionRuleSetArray AS $actionRuleSetObj) {
                
                echo '<pre>';
                var_dump($crsObj);

                // If the context item is in the Action RuleSet, we need to create a replacement
                // CRS for checking
                
                // If the context item is not in the action ruleset, we use the CRS for checking
                var_dump($actionRuleSetObj->getContextRuleSetExceptFor($contextItem, $contextArray));
                echo '</pre>';
            }
        }
    }
}
?>