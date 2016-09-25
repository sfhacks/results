<?php

$cli = true;
require('../pocket.php');
$db = '../db.json';
$passwords = '../passwords.json';
session_start();

$passwords = json_decode(file_get_contents($passwords), true);
$answer = isset($_SESSION['correct']) ? $_SESSION['correct'] : 'true';
if (isset($_POST['password'])) {
    if ($_POST['password'] === $passwords['password']) {
        if (isset($_POST['name']) && is_string($_POST['name'])) {
            if (trim($_POST['name']) == '')
                die('Name cannot be blank');
            $snapshot = json_decode(file_get_contents($db), true);
            array_push($snapshot, [
                'name' => $_POST['name'],
                'answer' => $_POST['answer'],
                'correct' => (($_POST['answer'] == $answer) ? 'true' : 'false')
            ]);
            $json = json_encode($snapshot);
            if (trim($json) == '' || trim($json) == '{}') $json = '[]';
            if (file_put_contents($db, $json) === false)
                die('Failure to Update');
            else die('Database Updated');
        } else die('Invalid Request');
    } elseif ($_POST['password'] === $passwords['admin']) {
        if (isset($_POST['clear']) && $_POST['clear']) {
            if (file_put_contents($db, "[]") === false)
                die('Failure to Clear');
            else die ('Database Cleared');
        } elseif (isset($_POST['number'])) {
            if (trim($_POST['number']) == '')
                die('Number to delete cannot be blank');
            $snapshot = json_decode(file_get_contents($db), true);
            if (isset($snapshot[$_POST['number']]))
                unset($snapshot[$_POST['number']]);
            else die('Invalid Number');
            $json = json_encode($snapshot);
            if (trim($json) == '' || trim($json) == '{}') $json = '[]';
            if (file_put_contents($db, $json) === false)
                die('Failure to Update');
            else die('Database Updated');
        } elseif (isset($_POST['correct'])) {
            if (trim($_POST['correct']) == '')
                die('New correct answer cannot be blank');
            $_SESSION['correct'] = $_POST['correct'];
            if ($_POST['correct'] != $_SESSION['correct'])
                die('Failure to update answer');
            $snapshot = json_decode(file_get_contents($db), true);
            foreach ($snapshot as $key => $entry) {
                if ($_POST['correct'] == '__N/A')
                    $snapshot[$key]['correct'] = '__N/A';
                elseif ($snapshot[$key]['answer'] != $_POST['correct'])
                    $snapshot[$key]['correct'] = 'false';
                else $snapshot[$key]['correct'] = 'true';
            }
            $json = json_encode($snapshot);
            if (trim($json) == '' || trim($json) == '{}') $json = '[]';
            if (file_put_contents($db, $json) === false)
                die('Failure to Update');
            die('Correct answer updated');
        } else die('Invalid Request');
    } else die('Invalid Password');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>sfhacks live results</title>
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
        <h1>sfhacks live results</h1>
        <br/>
        <div id = 'table'>
            <table>
                <tr class = 'darker'><th>Connecting</th></tr>
                <noscript><tr><th><span style = 'color: #BB3333'>THIS SITE REQUIRES JAVASCRIPT</span></th></tr></noscript>
            </table>
        </div>
        <br/><br/>
        <div class = 'form' id = 'test'>
            <b>Data</b><br/>
            <input placeholder = 'Name' type = 'text' id = 'testKey'/><br/>
            <input placeholder = 'Answer' type = 'text' id = 'testVal'/><br/>
            <input placeholder = 'Password' type = 'password' id = 'testPwd'/><br/>
            <button id = 'testSub'>Send Data</button>
            <span class = 'response' id = 'testData'></span>
        </div>
        <div class = 'form' id = 'admin'>
            <br/><b>Admin</b><br/>
            <div>
                <div class = 'half'>
                    <input placeholder = 'ID' type = 'text' id = 'adminNum'/><br/>
                    <button id = 'adminDel'>Delete Entry</button><br/>
                </div>
                <div class = 'half'>
                    <input placeholder = 'Answer' type = 'password' id = 'adminCor'/><br/>
                    <button id = 'adminAns'>Set Answer</button><br/>
                </div>
            </div>
            <input placeholder = 'Password' type = 'password' id = 'adminPwd'/>
            <button id = 'adminClr'>Clear Database</button>
            <span class = 'response' id = 'adminData'></span>
        </div>
        <br/>
    </div>
</body>
</html>
