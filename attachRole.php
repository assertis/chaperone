<?php
require_once('classes/Chaperone.php');
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
} catch (Exception $e) {
    die($e);
}

Chaperone::setNamespace('b2b');
Chaperone::setPDO($pdo);
Chaperone::newSession('fred');

//Chaperone::attachRole('sysadmin');

Chaperone::attachRole('tmcadmin', array('tmc'=>'abc'));
Chaperone::attachRole('tmcadmin', array('tmc'=>'def'));

Chaperone::attachRole('businessadmin', array('tmc'=>'ghi', 'business'=>123));
Chaperone::attachRole('businessadmin', array('tmc'=>'ghi', 'business'=>456));

Chaperone::attachRole('businessuser', array('tmc'=>'jkl', 'business'=>123));
Chaperone::attachRole('businessuser', array('tmc'=>'jkl', 'business'=>456));

echo 'Attached as '.Chaperone::getEmailAddress();
?>