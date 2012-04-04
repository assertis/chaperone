<?php
require_once('config.php');
require_once('../classes/Chaperone.php');

try {
    $pdo = new PDO('mysql:host='.U_DATABASE_HOST, U_DATABASE_USERNAME, U_DATABASE_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die($e);
}

Chaperone::setNamespace('b2b');
Chaperone::setPDO($pdo);
Chaperone::newSession('fred@test.com');

//Chaperone::attachRole('sysadmin');

Chaperone::attachRole('tmcadmin', array('tmc'=>'abc'));
Chaperone::attachRole('tmcadmin', array('tmc'=>'def'));

Chaperone::attachRole('businessadmin', array('tmc'=>'ghi', 'biz'=>123));
Chaperone::attachRole('businessadmin', array('tmc'=>'ghi', 'biz'=>456));

Chaperone::attachRole('businessuser', array('tmc'=>'jkl', 'biz'=>123));
Chaperone::attachRole('businessuser', array('tmc'=>'jkl', 'biz'=>456));

echo 'Attached as '.Chaperone::getEmailAddress();
?>