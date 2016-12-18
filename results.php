<?php

require('pocket.php');

$ip = $argv[2];
$port = $argv[3];
$pocket = new Pocket($ip, $port, 10, 1);
$snapshot = '';

// event for pushing updates to clients
$pocket->bind('update', function () use (&$pocket, &$snapshot) {
    // get database
    if (($db = file_get_contents(realpath(dirname(__FILE__)) . '/db.json')) === false)
        return;
    if (trim($db) == '') return;
    // check for updates
    if ($db != $snapshot) {
        // push updates to all
        $pocket->sendAll('update', $db);
        $snapshot = $db;
        system('clear');
        print_r(json_decode($db));
    }
});

// update clients indefinitely
$pocket->onRun(function () use (&$pocket) {
    $pocket->call('update');
});

$pocket->open();

?>
