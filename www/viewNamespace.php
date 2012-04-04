<?php
require_once('config.php');
if (!array_key_exists('id', $_GET) AND !array_key_exists('name', $_GET)) die('No ID or name specified');

try {
    $pdo = new PDO('mysql:host='.U_DATABASE_HOST, U_DATABASE_USERNAME, U_DATABASE_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    require_once('../classes/ChaperoneNamespace.php');
    Chaperone::setPDO($pdo);

    if (array_key_exists('id', $_GET)) {
        $id = $_GET['id'];
        $namespace = ChaperoneNamespace::getNameForId($id);
        if ($namespace === NULL) die('Namespace #'.$id.' not found');
    } else {
        $namespace = $_GET['name'];
        $id = ChaperoneNamespace::getIdForName($namespace);
        if ($id === NULL) die('Namespace "'.htmlspecialchars($namespace).'" not found');
    }
    
    require_once('../classes/ChaperoneRole.php');
    $roleArray = ChaperoneRole::getAllRolesForNamespace($id);

    require_once('../classes/ChaperoneAction.php');
    $actionArray = ChaperoneAction::getAllActionsForNamespace($id);
} catch (Exception $e) {
    die($e);
}
?><html>
    <link rel="stylesheet" type="text/css" href="chaperone.css" />
    <head><title>Chaperone Namespace: <?php echo $namespace; ?></title></head>
    <body>
        <h1>Chaperone Namespace: <?php echo $namespace; ?></h1>
        <table>
            <tr><th>Roles</th><th>Rules</th></tr>
            <?php
                if (count($roleArray) === 0) { ?>
            <tr><td>No roles defined</td></tr>   
            <?php
                } else {
                    foreach($roleArray AS $roleRow) {
                        $fullName = $namespace.'.'.$roleRow['role'];
            ?>
            <tr>
                <td><a href="viewRole.php?name=<?php echo urlencode($fullName); ?>"><?php echo htmlentities($fullName); ?></a></td>
                <td><?php echo htmlentities($roleRow['rules']); ?></td>
            </tr>
            <?php
                    }
                } ?>
        </table>
        <p />
        <table>
            <tr><th>Actions</th><th>Rules</th></tr>
            <?php
                if (count($actionArray) === 0) { ?>
            <tr><td>No actions defined</td></tr>   
            <?php
                } else {
                    foreach($actionArray AS $actionRow) {
                        $fullName = $namespace.'.'.$actionRow['action'];
                        $ruleCount = count($actionRow['rules']);
            ?>
                <tr>
                    <td rowspan="<?php echo max($ruleCount, 1); ?>"><a href="viewAction.php?id=<?php echo $actionRow['id']; ?>"><?php echo htmlentities($fullName); ?></a></td>
                    <td><?php
                        if ($ruleCount > 0) echo htmlentities($actionRow['rules'][0]);
                        for ($i=1; $i<$ruleCount; $i++) {
                            ?></td></tr><tr><td><?php
                            echo htmlentities($actionRow['rules'][$i]);
                        }
                        ?>
                    </td>
                </tr>
            <?php
                    }
                }
            ?>
        </table>
    </body>
</html>