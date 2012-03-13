<?php
if (!array_key_exists('role', $_GET)) die('No Role specified');

define('U_DATABASE_HOST', 'localhost');
define('U_DATABASE_USERNAME', 'chaptest');
define('U_DATABASE_PASSWORD', 'chaptest');

try {
    $pdo = new PDO('mysql:host='.U_DATABASE_HOST, U_DATABASE_USERNAME, U_DATABASE_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    require_once('classes/ChaperoneRole.php');
    Chaperone::setPDO($pdo);
    $roleObj = ChaperoneRole::loadByName($_GET['role']);

    $ruleSetObj = $roleObj->getRuleSet();
    $rules = ($ruleSetObj === NULL) ? '- None -' : $ruleSetObj->getReadableRules();

    $actionArray = $roleObj->getActions();

} catch (Exception $e) {
    die($e);
}
?><html>
    <head><title>Role "<?php echo htmlspecialchars($roleObj->getFullName()); ?>"</title></head>
    <body>
        <h1>Role "<?php echo htmlspecialchars($roleObj->getFullName()); ?>"</h1>
        <h3>Rules</h3>
        <?php echo $rules; ?>
        <h3>Actions</h3>
        <table border="1" cellspacing="0" cellpadding="3">
            <?php
            foreach ($actionArray AS $actionObj) {
                echo '<tr><td>'.htmlspecialchars($actionObj->getFullName()).'</td></tr>';
            }
            ?>
        </table>
    </body>
</html>