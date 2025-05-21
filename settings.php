<?php

session_start();
require("rb-sqlite.php");
R::setup("sqlite:coins.db");

$BannedUsers_array = array();
$users = R::findAll('user');
foreach($users as $row) {
    if($row->banned == "true") {
    array_push($BannedUsers_array, $row->id);
    echo "<h1>" . $row->id . "</h1>";
    }
}
$message = R::findAll('coin');
foreach($message as $row) {
    if (in_array($row->user_id, $BannedUsers_array)) {
        echo "<h1>" . $row->user_id . "?" . "</h1>";
        R::trash($row);
    }
}


?>

<html>
<head>
<link rel="stylesheet" href="settingsPage_style.css">
<link rel="icon" type="image/x-icon" href="icon_TAB.png">
<script src="jquery-3.7.1.min.js"></script> 
</head>
<body>
    <h1>huiu</h1>
</body>
</html>