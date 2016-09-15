<?php

require('pocket.php');

$pocket = new Pocket('127.0.0.1', 7998, 10);
$snapshot = '';

$pocket->bind('update', function () {
    global $pocket;
    // get database
    if (($db = file_get_contents('db.json')) === false)
        return;
    if (trim($db) == '') return;
    // check for updates
    if ($db != $snapshot) {
        $pocket->sendAll('update', $db);
        $snapshot = $db;
    }
    system('clear');
    print_r(json_decode($db));
});

$pocket->open();

?>
