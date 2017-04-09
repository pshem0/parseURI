<?php
declare(strict_types=1);

require '../vendor/autoload.php';

$status = null;
if (isset($_POST['uriRef'])) {

    $uriRef = $_POST['uriRef'];
    $uri = new Pshemo\UriParser\Uri();
    try {
        $components = $uri->parse($_POST['uriRef']);
        $status = true;
    } catch (Exception $e) {
        $status = false;
    }
}



?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>URI parse</title>
    </head>
    <body>
    <?php

    if ($status === true) {
        printf("<p>The URL '<code>%s</code>' is <b>valid</b></p>" . PHP_EOL,
            htmlspecialchars($_POST['uriRef'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        printf('<pre>%s</pre>', print_r($components));
    } elseif ($status === false) {
        printf("<p>The URL '<code>%s</code>' is not valid or unable to parse.</p>" . PHP_EOL,
            htmlspecialchars($_POST['uriRef'], ENT_QUOTES | ENT_HTML5, 'UTF-8')
        );
    }

    ?>
    <h2>Parse an URI:</h2>
    <form method="post">
            URI
            <input type="text" name="uriRef" />
            <input type="submit" />
        </form>
    </body>
    </html>
