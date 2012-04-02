<?php
require_once('config.php');
if (!array_key_exists('name', $_GET)) die('No Role specified');

try {
    $pdo = new PDO('mysql:host='.U_DATABASE_HOST, U_DATABASE_USERNAME, U_DATABASE_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    require_once('../classes/ChaperoneRole.php');
    Chaperone::setPDO($pdo);
    $roleObj = ChaperoneRole::loadByName($_GET['name']);

    $roleRules = $roleObj->getReadableRules();

    $actionArray = $roleObj->getActions();
    
    $roleActionArray = array();
    foreach ($actionArray as $actionObj) {
        $rulesArray = explode("\n", $actionObj->getReadableRules());
        $roleActionArray[] = array('name'=>$actionObj->getFullName(), 'rules'=>$rulesArray);
    }

} catch (Exception $e) {
    die($e);
}
?><html>
    <link rel="stylesheet" type="text/css" href="chaperone.css" />
    <head><title>Chaperone Role: <?php echo htmlspecialchars($roleObj->getFullName()); ?></title></head>
    <body>
        <h1>Chaperone Role: <?php echo htmlspecialchars($roleObj->getFullName()); ?></h1>
        <table>
            <tr><th>Rules</th></tr>
            <tr><td><?php echo htmlentities($roleRules); ?></td></th>
        </table>
        <p />
        <table>
            <tr><th>Actions</th><th>Rules</th></tr>
            <?php
            foreach ($roleActionArray AS $actionRow) {
                $ruleCount = count($actionRow['rules']); ?>
            <tr>
                <td rowspan="<?php echo $ruleCount; ?>"><?php echo htmlspecialchars($actionRow['name']); ?></td>
                <td><?php echo htmlspecialchars($actionRow['rules'][0]); ?></td>
                <?php
                    for ($i=1; $i<$ruleCount; $i++) { ?>
            </tr><td><?php echo htmlspecialchars($actionRow['rules'][$i]); ?></td><tr>
                <?php
                    } ?>
            </tr>
            <?php
            } ?>
        </table>
    </body>
</html>