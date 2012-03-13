<?php
if (!array_key_exists('id', $_GET)) die('No Action ID specified');

define('U_DATABASE_HOST', 'localhost');
define('U_DATABASE_USERNAME', 'chaptest');
define('U_DATABASE_PASSWORD', 'chaptest');

try {
    $pdo = new PDO('mysql:host='.U_DATABASE_HOST, U_DATABASE_USERNAME, U_DATABASE_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    require_once('classes/ChaperoneAction.php');
    Chaperone::setPDO($pdo);
    $actionObj = ChaperoneAction::loadById($_GET['id']);

    $rules = nl2br($actionObj->getReadableRules());
    
} catch (Exception $e) {
    die($e);
}
?><html>
    <head><title>Action "<?php echo htmlspecialchars($actionObj->getFullName()); ?>"</title></head>
    <body>
        <h1>Action "<?php echo htmlspecialchars($actionObj->getFullName()); ?>"</h1>
        <h3>Rules</h3>
        <?php echo $rules; ?>
        
        <h3>Roles</h3>
    </body>
</html>