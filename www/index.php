<?php require('../api.php'); ?>
<!DOCTYPE html>
<html lang = 'en'>
    <head>
        <title>sfhacks live results</title>
        <meta charset = 'utf-8'/>
        <meta name = 'author' content = 'sfhacks'/>
        <meta name = 'copyright' content = 'Copyright (c) 2016 sfhacks'/>
        <link rel = 'icon' href = 'favicon.png'/>
        <link rel = 'stylesheet' type = 'text/css' href = 'style.css'/>
        <script type = 'text/javascript' src = 'https://code.jquery.com/jquery-3.1.0.min.js' integrity = 'sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=' crossorigin = 'anonymous'></script>
        <script type = 'text/javascript' src = 'pocket.js'></script>
        <script type = 'text/javascript'>
            var servers = {
                rest: "<?php echo basename($_SERVER['PHP_SELF'])?>",
                pocket: {
                    domain: document.domain,
                    port: 7998,
                    page: 'results.php'
                }
            };
        </script>
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
                <b>Test Data</b><br/>
                <input placeholder = 'Name' type = 'text' id = 'testKey'/><br/>
                <input placeholder = 'Answer' type = 'text' id = 'testVal'/><br/>
                <input placeholder = 'Password' type = 'password' id = 'testPwd'/><br/>
                <button id = 'testSub'>Send Data</button>
                <span class = 'response' id = 'testData'></span>
            </div>
            <div class = 'form' id = 'admin'>
                <br/><b>Admin Panel</b><br/>
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
