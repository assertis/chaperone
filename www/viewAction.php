<?php
require_once('config.php');

if (!array_key_exists('id', $_GET)) die('No Action ID specified');

try {
    $pdo = new PDO('mysql:host='.U_DATABASE_HOST, U_DATABASE_USERNAME, U_DATABASE_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    require_once('../classes/ChaperoneAction.php');
    Chaperone::setPDO($pdo);
    $actionObj = ChaperoneAction::loadById($_GET['id']);

    $ruleArray = explode("\n", $actionObj->getReadableRules());

    $roleArray = $actionObj->getRoles();
} catch (Exception $e) {
    die($e);
}
?><html>
    <link rel="stylesheet" type="text/css" href="chaperone.css" />
    <head><title>Chaperone Action: <?php echo htmlspecialchars($actionObj->getFullName()); ?></title></head>
    <body>
        <h1>Chaperone Action: <?php echo htmlspecialchars($actionObj->getFullName()); ?></h1>
        <table>
            <tr><th>Rules</th></tr>
        <?php
            if (count($ruleArray) === 0) {
        ?>
            <tr><td>- No Rules</td></tr>
        <?php
            } else {
                foreach ($ruleArray AS $ruleSet) {
        ?>
            <tr><td><?php echo htmlentities($ruleSet); ?></td></tr>
        <?php
                }
            }
        ?>
        </table>
        <p />
        <table>
            <tr><th>Roles</th></tr>
        <?php
            if (count($roleArray) === 0) {
        ?>
            <tr><td>- No Roles</td></tr>
        <?php
            } else {
                foreach ($roleArray AS $roleRow) {
        ?>
            <tr><td><?php echo htmlentities($roleRow['role']); ?></td></tr>
        <?php
                }
            }
        ?>
        </table>
    </body>
</html>