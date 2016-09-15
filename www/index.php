<?php

$cli = true;
require('../pocket.php');
$db = '../db.json';

function valid($string) {
    return (isset($string) && is_string($string) && trim($string) != '');
}

if ((isset($_POST['key']) && isset($_POST['value'])) && (is_string($_POST['key']) && is_string($_POST['value']))) {
    if (trim($_POST['key']) == '' || trim($_POST['value']) == '')
        die('Blank');
    $snapshot = json_decode(file_get_contents($db), true);
    $snapshot[$_POST['key']] = $_POST['value'];
    if (file_put_contents($db, json_encode($snapshot)) === false)
        die('Failure');
    else die('Success');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>sfhacks Live Answers</title>
    <link rel = 'icon' href = 'http://www.sfhacks.club/assets/icons/favicon.png'/>
    <script type = 'text/javascript' src = 'https://code.jquery.com/jquery-3.1.0.min.js' integrity = 'sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=' crossorigin = 'anonymous'></script>
    <script typ e= 'text/javascript' src = 'pocket.js'></script>
    <script type = 'text/javascript'>
        $(document).ready(function () {
            // set DOM events
            var send = function () {
                $.ajax({
                    url: '<?=$_SERVER['PHP_SELF']?>',
                    method: 'POST',
                    data: {
                        key: document.getElementById('testKey').value,
                        value: document.getElementById('testVal').value
                    },
                    dataType: 'text',
                    success: function (data) {
                        $('#testData').html(data);
                    }
                });
            };
            $('#testSub').click(send);
            $('#testKey, #testVal').on('keyup', function (e) {
                if (e.which == 13 || e.keyCode == 13)
                    send();
            });
            // set PocketJS events
            var snapshot = '';
            Pocket.bind('update', function (db) {
                if (db != snapshot) {
                    snapshot = db;
                    db = JSON.parse(snapshot);
                    console.log(db);
                    var html = '';
                    for (key in db) {
                        if (db.hasOwnProperty(key)) {
                            html += "<tr><td>" + key + "</td><td>" + db[key] + "</td></tr>";
                        }
                    }
                    $('#table table').html(html);
                }
            });
            Pocket.connect('results.sfhacks.club', 7998, 'server.php');
            // keep server alive
            setInterval(function () {
                if (Pocket.online())
                    Pocket.send('update');
            }, 1000);
        });
    </script>
    <style type = 'text/css'>
        * {
            font-family: Helvetica;
            margin: 2px;
            padding: 2px;
        }
        #main {
            margin: 0 auto;
            text-align: center;
            width: 75%;
        }
        #main b {
            font-size: 20px;
        }
        #table table {
            margin: 0 auto;
            border-spacing: 0;
        }
        #table table td {
            padding: 11px;
        }
    </style>
</head>
<body>
    <div id = 'main'>
        <h1>sfhacks Live Answers</h1>
        <div id = 'table'>
            <table border = '6px'></table>
        </div>
        <br/><br/>
        <div id = 'form'>
            <b>Test Data</b><br/>
            &nbsp;&nbsp;Key:&nbsp;&nbsp;<input type = 'text' id = 'testKey'/><br/>
            Value:&nbsp;<input type = 'text' id = 'testVal'/><br/>
            &nbsp;&nbsp;&nbsp;<button id = 'testSub'>Send Test Data</button><br/><br/>
            <span id = 'testData'></span>
        </div>
    </div>
</body>
</html>
