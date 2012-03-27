<?php
require_once('config.php');
try {
    $pdo = new PDO('mysql:host='.U_DATABASE_HOST, U_DATABASE_USERNAME, U_DATABASE_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    require_once('classes/ChaperoneNamespace.php');
    Chaperone::setPDO($pdo);

    // Get namespace list.  Suppress caching because we're not looking up anything else
    $namespaceArray = ChaperoneNamespace::getAllNamespaces(FALSE);
    if (count($namespaceArray) === 0) die('No namespaces found');
    
} catch (Exception $e) {
    die($e);
}
?><html>
    <link rel="stylesheet" type="text/css" href="chaperone.css" />
    <head><title>Chaperone Namespaces</title></head>
    <body>
        <h1>Chaperone Namespaces</h1>
        <table border="1" cellspacing="0" cellpadding="3">
            <tr><th>Namespaces</th></tr>
            <?php foreach ($namespaceArray AS $namespaceRow) {
                $namespace = $namespaceRow['namespace']; ?>
            <tr><td><a href="viewNamespace.php?name=<?php echo urlencode($namespace); ?>"><?php echo htmlentities($namespace); ?></a></td></?/tr>
            <?php } ?>
        </table>
    </body>
</html>