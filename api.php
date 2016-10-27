<?php

$cli = true;
$db = '../db.json';
$pwds = '../passwords.json';

$passwords = json_decode(file_get_contents($pwds), true);
$answer = isset($passwords['correct']) ? $passwords['correct'] : 'true';
if (isset($_POST['password'])) {
    if ($_POST['password'] === $passwords['password']) {
        if (isset($_POST['name']) && is_string($_POST['name'])) {
            if (trim($_POST['name']) == '')
                die('Name cannot be blank');
            $snapshot = json_decode(file_get_contents($db), true);
            $isCorrect = 'false';
            if ($answer == '__N/A') $isCorrect = '__N/A';
            elseif (strval($_POST['answer']) == strval($answer)) $isCorrect = 'true';
            array_push($snapshot, [
                'name' => $_POST['name'],
                'answer' => $_POST['answer'],
                'correct' => $isCorrect
            ]);
            $json = json_encode(array_values($snapshot));
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
            $json = json_encode(array_values($snapshot));
            if (trim($json) == '' || trim($json) == '{}') $json = '[]';
            if (file_put_contents($db, $json) === false)
                die('Failure to Update');
            else die('Database Updated');
        } elseif (isset($_POST['correct'])) {
            if (trim($_POST['correct']) == '')
                die('New correct answer cannot be blank');
            $passwords['correct'] = $_POST['correct'];
            if ($passwords['correct'] != $_POST['correct'] || file_put_contents($pwds, json_encode($passwords, JSON_PRETTY_PRINT)) === false)
                die('Failure to update answer');
            $snapshot = json_decode(file_get_contents($db), true);
            foreach ($snapshot as $key => $entry) {
                if ($_POST['correct'] == '__N/A')
                    $snapshot[$key]['correct'] = '__N/A';
                elseif (strval($snapshot[$key]['answer']) != strval($_POST['correct']))
                    $snapshot[$key]['correct'] = 'false';
                else $snapshot[$key]['correct'] = 'true';
            }
            $json = json_encode(array_values($snapshot));
            if (trim($json) == '' || trim($json) == '{}') $json = '[]';
            if (file_put_contents($db, $json) === false)
                die('Failure to Update');
            die('Correct answer updated');
        } else die('Invalid Request');
    } else die('Invalid Password');
}

?>
