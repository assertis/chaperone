<?php
if (!array_key_exists('id', $_GET)) die('No ID specified');

define('U_DATABASE_HOST', 'localhost');
define('U_DATABASE_USERNAME', 'chaptest');
define('U_DATABASE_PASSWORD', 'chaptest');

try {
    $pdo = new PDO('mysql:host='.U_DATABASE_HOST, U_DATABASE_USERNAME, U_DATABASE_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    require_once('classes/ChaperoneRuleSet.php');
    Chaperone::setPDO($pdo);
    $ruleSetObj = ChaperoneRuleSet::loadById($_GET['id']);

    /*
     * Build rule array
     */
    $ruleArray = array();
    foreach($ruleSetObj->getWildcardRules() AS $context_item) $ruleArray[] = htmlspecialchars ($context_item).'=*';
    foreach($ruleSetObj->getContextRules() AS $context_item) $ruleArray[] = htmlspecialchars ($context_item).'=...';
    $rules = (count($ruleArray) == 0) ? '- None -' : join(', ', $ruleArray);
    
} catch (Exception $e) {
    die($e);
}
?><html>
    <head><title>Rule Set #<?php echo $_GET['id']; ?></title></head>
    <body>
        <h1>Rule Set #<?php echo $_GET['id']; ?></h1>
        <?php echo $rules; ?>
    </body>
</html>