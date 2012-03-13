<?php
/*
 * This code tests whether a particular role is able to perform a particular action in a particular context
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
$roleObj = ChaperoneRole::loadByName('tmcadmin');
$crsObj = $roleObj->getContextRuleSet(array('tmc'=>'xyz'));                     // Context Rule Set
$actionArray = $roleObj->getActions();                                          // Actions and their rulesets


// Can this user do b2b.biz_view on TMC 'xyz', business 123?
$contextArray = array('tmc'=>'xyz', 'business'=>123, 'email'=>'fred');
foreach ($actionArray AS $actionObj) {

    // Action found
    if ($actionObj->getFullName() == 'b2b.order_resend') {

        // Are there any rules for the action
        $actionRuleSetArray = $actionObj->getRuleSets();
        if (count($actionRuleSetArray) === 0) {
            echo 'No rules on action - allowed';
        } else {
            foreach ($actionRuleSetArray AS $actionRuleSetObj) {
                try {
                    $acrsObj = $actionRuleSetObj->getContextRuleSet($contextArray);
                    if ($acrsObj->isSubsetOf($crsObj)) {
                        echo '<pre>';
                        var_dump($actionRuleSetObj->getContextRuleSet($contextArray));
                        var_dump($crsObj);
                        echo '</pre>';
                    }
                    unset($acrsObj);
                } catch(Exception $e) {
                }
            }
        }
    }
}
?>