<link rel="stylesheet" type="text/css" href="chaperone.css" />
<?php
/*
 * This code tests whether a particular role is able to perform a particular action in a particular context
 */
require_once('classes/Chaperone.php');

// Test whether the user is logged in
if (!Chaperone::isLoggedIn()) die('Not logged in');

// Who am I?
echo '<b>User: </b>'.Chaperone::getEmailAddress().'<p />';

// Test items
Chaperone::setNamespace('b2b');
echo '<table><tr><th>Action</th><th>Context</th><th>Allowed?</th></tr>';
actionTest('sys_admin_list');       // System administrators only
echo '</table>';

echo '<p><table><tr><th>Action</th><th>Context</th><th>Allowed?</th></tr>';
actionTest('tmc_view', array('tmc'=>'abc'));
actionTest('tmc_view', array('tmc'=>'def'));
actionTest('tmc_view', array('tmc'=>'ghi'));
actionTest('tmc_view', array('tmc'=>'jkl'));
echo '</table>';

echo '<p><table><tr><th>Action</th><th>Context</th><th>Allowed?</th></tr>';
actionTest('biz_view', array('tmc'=>'abc', 'business'=>123));
actionTest('biz_view', array('tmc'=>'def', 'business'=>999));
actionTest('biz_view', array('tmc'=>'ghi', 'business'=>123));
actionTest('biz_view', array('tmc'=>'ghi', 'business'=>999));
actionTest('biz_view', array('tmc'=>'jkl', 'business'=>123));
echo '</table>';

echo '<p><table><tr><th>Action</th><th>Context</th><th>Allowed?</th></tr>';
actionTest('order_resend', array('tmc'=>'abc', 'business'=>123));       // TMC admin, any business
actionTest('order_resend', array('tmc'=>'abc', 'business'=>999));       // TMC admin, any business
actionTest('order_resend', array('tmc'=>'ghi', 'business'=>123));       // Business admin, business 123
actionTest('order_resend', array('tmc'=>'ghi', 'business'=>123, 'email'=>'fred'));
actionTest('order_resend', array('tmc'=>'ghi', 'business'=>123, 'email'=>'wilma'));
actionTest('order_resend', array('tmc'=>'ghi', 'business'=>999));       // Business admin, business 123

// Business user, my email address
actionTest('order_resend', array('tmc'=>'jkl', 'business'=>123, 'email'=>'fred'));

// Business user, not my email address
actionTest('order_resend', array('tmc'=>'jkl', 'business'=>123, 'email'=>'wilma'));

echo '</table>';

function actionTest($action, $contextArray=array()) {
    if (count($contextArray) === 0) {
        $contextString = '- None -';
    } else {
        $contextStringArray = array();
        foreach ($contextArray AS $key=>$value) {
            $contextStringArray[] = $key.'='.$value;
        }
        $contextString = htmlentities(join(', ', $contextStringArray));
    }
    echo '<tr><td>'.$action.'</td><td>';
    echo $contextString;
    echo '</td><td>';
    echo (Chaperone::isActionAllowed($action, $contextArray)) ? 'Yes' : 'No';
    echo '</td></tr>';
}
?>