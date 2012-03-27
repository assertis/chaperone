<link rel="stylesheet" type="text/css" href="chaperone.css" />
<?php
require_once('classes/Chaperone.php');

// Test whether the user is logged in
if (!Chaperone::isLoggedIn()) die('Not logged in');

// Who am I?
echo '<b>User: </b>'.Chaperone::getEmailAddress().'<p />';

// Test items
Chaperone::setNamespace('b2b');

echo '<table><tr><th>Action</th><th>Context Item</th><th>Context</th><th>Context Filter</th></tr>';
actionFilter('tmc_view', 'tmc');                                                // Which TMCs can I view?
actionFilter('biz_view', 'business', array('tmc'=>'abc'));                      // Which businesses can I view in TMC "abc"
actionFilter('biz_view', 'business', array('tmc'=>'def'));                      // Which businesses can I view in TMC "def"
actionFilter('biz_view', 'business', array('tmc'=>'ghi'));                      // Which businesses can I view in TMC "ghi"
actionFilter('biz_view', 'business', array('tmc'=>'jkl'));                      // Which businesses can I view in TMC "jkl"
echo '</table>';

function actionFilter($action, $contextItem, $contextArray=array()) {

    if (count($contextArray) === 0) {
        $contextString = '- None -';
    } else {
        $contextStringArray = array();
        foreach ($contextArray AS $key=>$value) {
            $contextStringArray[] = $key.'='.$value;
        }
        $contextString = htmlentities(join(', ', $contextStringArray));
    }

    $contextFilterObj = Chaperone::getContextFilter($action, $contextItem, $contextArray);
    if ($contextFilterObj->isWildcard()) {
        $contextFilterList = '- All -';
    } else {
        if ($contextFilterObj->isEmpty()) {
            $contextFilterList = '- None -';
        } else {
            $contextFilterList = htmlentities(join(',', $contextFilterObj->getItems()));
        }
    }
    
    echo '<tr><td>';
    echo htmlentities($action);
    echo '</td><td>';
    echo htmlentities($contextItem);
    echo '</td><td>';
    echo $contextString;
    echo '</td><td>';
    echo $contextFilterList;
    echo '</td></tr>';
}
?>