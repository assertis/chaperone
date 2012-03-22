<?php
/*
 * This code tests whether a particular role is able to perform a particular action in a particular context
 */
require_once('classes/Chaperone.php');

// Test whether the user is logged in
if (!Chaperone::isLoggedIn()) die('Not logged in');

// Who am I?
echo '<b>User: </b>'.Chaperone::getEmailAddress().'<br />';

// Action and context to be tested
$action = 'biz_view';
$contextArray = array('tmc'=>'abc', 'business'=>123);

// Test it
Chaperone::setNamespace('b2b');
echo (Chaperone::isActionAllowed($action, $contextArray)) ? 'Allowed' : 'Denied';
?>