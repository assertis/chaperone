<?php
require_once('classes/Chaperone.php');
require_once('classes/ChaperoneSession.php');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
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

$session = ChaperoneSession::getSession();
$session->clear();
$session->attachRole('tmcadmin', array('tmc'=>'abc'));
$session->attachRole('tmcadmin', array('tmc'=>'xyz'));

$x = serialize($session);
var_dump($x);
?>