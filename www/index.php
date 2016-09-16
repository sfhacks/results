<?php

$cli = true;
require('../pocket.php');
$db = '../db.json';

$password = 'jeanghantous';
if (isset($_POST['password'])) {
    if ($_POST['password'] === md5($password)) {
        if ((isset($_POST['key']) && isset($_POST['value'])) && (is_string($_POST['key']) && is_string($_POST['value']))) {
            if (trim($_POST['key']) == '' || trim($_POST['value']) == '') die('No Blank Fields');
            $snapshot = json_decode(file_get_contents($db), true);
            $snapshot[$_POST['key']] = $_POST['value'];
            if (file_put_contents($db, json_encode($snapshot)) === false) die('Failure to Update');
            else die('Database Updated');
        } elseif (isset($_POST['clear']) && $_POST['clear']) {
            if (file_put_contents($db, "{}") === false) die('Failure to Clear');
            else die ('Database Cleared');
        }
    } else die ('Invalid Password');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>sfhacks live answers</title>
    <link rel = 'icon' href = 'favicon.png'/>
    <link rel = 'stylesheet' type = 'text/css' href = 'style.css'/>
    <script type = 'text/javascript' src = 'https://code.jquery.com/jquery-3.1.0.min.js' integrity = 'sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=' crossorigin = 'anonymous'></script>
    <script type = 'text/javascript' src = 'pocket.js'></script>
    <script type = 'text/javascript'> var phpServer = "<?php echo basename($_SERVER['PHP_SELF'])?>"; </script>
    <script type = 'text/javascript' src = 'app.js'></script>
</head>
<body>
    <div id = 'main'>
        <img src = 'favicon.png'/>
        <h1>sfhacks live answers</h1>
        <br/>
        <div id = 'table'>
            <table></table>
        </div>
        <br/><br/>
        <div id = 'form'>
            <b>Test Data</b><br/>
            <input placeholder = 'Key' type = 'text' id = 'testKey'/><br/>
            <input placeholder = 'Value' type = 'text' id = 'testVal'/><br/>
            <input placeholder = 'Password' type = 'password' id = 'testPwd'/><br/>
            <button id = 'testClr'>Clear</button><button id = 'testSub'>Send</button><br/>
            <span id = 'testData'></span>
        </div>
    </div>
</body>
</html>
