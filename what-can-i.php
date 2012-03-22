<?php
require_once('classes/Chaperone.php');

// Test whether the user is logged in
if (!Chaperone::isLoggedIn()) die('Not logged in');

// Who am I?
echo '<b>User: </b>'.Chaperone::getEmailAddress().'<br />';

// Within the TMC 'abc', which businesses can I do biz_view on?
$action = 'b2b.biz_view';
$contextArray = array('tmc'=>'abc');
$contextItem = 'business';

$contextListObj = Chaperone::getContextValueList($action, $contextItem, $contextArray);
if ($contextListObj->isWildcard()) {
    echo 'All!';
} else {
    if ($contextListObj->isEmpty()) {
        echo 'None';
    } else {
        var_dump($contextListObj->getItems());
    }
}
?>