<?php

require('pocket.php');

$ip = isset($argv[3]) && is_string($argv[3]) && trim($argv[3]) != '' ? $argv[3] : '127.0.0.1';
$pocket = new Pocket($ip, 7998, 10, 1);
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

$pocket->onRun(function () {
    global $pocket;
    $pocket->call('update');
});

$pocket->open();

?>
