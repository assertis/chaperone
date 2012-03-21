<?php
/*
 * This code tests whether a particular role is able to perform a particular action in a particular context
 */
require_once('classes/Chaperone.php');
require_once('classes/ChaperoneSession.php');
/*
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

// Get session and attach a couple of roles
*/
$session = ChaperoneSession::getSession();
/*
$session->attachRole('tmcadmin', array('tmc'=>'abc'));
$session->attachRole('tmcadmin', array('tmc'=>'xyz'));
*/
// Action and context to be tested
$action = 'b2b.order_resend';
$contextArray = array('tmc'=>'xyz', 'business'=>123, 'email'=>'fred');

// Test it
echo ($session->actionCheck($action, $contextArray)) ? 'Allowed' : 'Denied';
?>