<?php
if (!array_key_exists('id', $_GET) AND !array_key_exists('name', $_GET)) die('No ID or name specified');

define('U_DATABASE_HOST', 'localhost');
define('U_DATABASE_USERNAME', 'chaptest');
define('U_DATABASE_PASSWORD', 'chaptest');

try {
    $pdo = new PDO('mysql:host='.U_DATABASE_HOST, U_DATABASE_USERNAME, U_DATABASE_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    require_once('classes/ChaperoneNamespace.php');
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
    
} catch (Exception $e) {
    die($e);
}
?><html>
    <head><title>Namespace #<?php echo $id; ?></title></head>
    <body>
        <h1>Namespace #<?php echo $id; ?></h1>
        <?php echo $namespace; ?>
    </body>
</html>