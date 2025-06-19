<?php
// Start the session
session_start();
require("rb-sqlite.php");
R::setup("sqlite:Saved_Info.db");

if((isset($_SESSION["username"]) && $_SESSION["accountlevel"] !="admin") || (!isset($_SESSION["username"]))){
header("location: play.php");
}

?>
    <?php 
    $user_admin_check = R::findAll('user');
foreach($user_admin_check as $admin_row) {
    if((($admin_row->accountlevel == "base")||($admin_row->banned == "true")) && $admin_row->id == $_SESSION["id"]) {
    header("location: play.php");
}
    }
    ?>


<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="adminPage.css">
</head>
<body>
    <div id="bottom">
    <a href="/LUCK/play.php">Return Home</a>
</div>    

<select id="taskSelect"onchange="changeTask()">
        <option value="select">-- select --</option>
    <option value="BanningOptions">Banning Options</option>
    <option value="AdminOptions">Admin Options</option>
    <option value="ChangeUsername">Change Username</option>
    <option value="DeleteUser">Delete user</option>
</select><br><br>

<script>
function changeTask() {
window.location.assign("/LUCK/users.php?task="+document.getElementById('taskSelect').value);
}
</script>


<?php
$user_found = R::findAll('user');
foreach($user_found as $row) {
    $verifiedCheck_array = array();
    $rangaCheck_array = array();
    if ($row->accountlevel == 'admin') {
    array_push($verifiedCheck_array, $row->id);
    }
    
    if (($row->banned == "false" && isset($_GET["task"]) && $_GET["task"] == "BanningOptions") || ($row->accountlevel != "admin" && isset($_GET["task"]) && $_GET["task"] == "AdminOptions")) {
            echo $row->username;
            if (in_array($row->id, $verifiedCheck_array)) {echo "<img src=\"verified-8.svg\">";}
            echo " (id) = " . $row->id;  
            if (isset($_GET["task"]) && $_GET["task"] == "BanningOptions" && $row->id != 1) {
            echo "<input value=\"Ban-User\"type=\"button\" id=\"button_list\" onClick = \"return userBan_".$row->id."()\">";
            }
            if (isset($_GET["task"]) && $_GET["task"] == "AdminOptions" && $row->id != 1) {
            echo "<input value=\"Promote-User\"type=\"button\" id=\"button_list\" onClick = \"return userPromote_".$row->id."()\">";
            }

            echo "<br>";
        echo "<script>";
        echo "function userBan_".$row->id."() {";
        echo "window.location.assign(\"/LUCK/users.php?task=BanningOptions&bannedSelect=".$row->id."\"".")";
        echo "}";
        echo "function userPromote_".$row->id."() {";
        echo "window.location.assign(\"/LUCK/users.php?task=AdminOptions&PromoteSelect=".$row->id."\"".")";
        echo "}";
        echo "</script>";
        if(isset($_GET["bannedSelect"]) && $_GET["bannedSelect"] == $row->id) {;
        $id = $row->id;
        $userUpdate = R::load("user", $id);
        $userUpdate->banned = "true";
        R::store($userUpdate);
        header("location: /LUCK/users.php?task=BanningOptions");
        }
        if(isset($_GET["PromoteSelect"]) && $_GET["PromoteSelect"] == $row->id) {;
        $id = $row->id;
        $userUpdate = R::load("user", $id);
        $userUpdate->accountlevel = "admin";
        R::store($userUpdate);
        header("location: /LUCK/users.php?task=AdminOptions");
        }
    } 
    else {

        if (isset($_GET["task"]) && $_GET["task"] == "BanningOptions") {
            echo "<div id=\"banned_red\">";
        }
        if (isset($_GET["task"]) && $_GET["task"] == "AdminOptions") {
            echo "<div id=\"admin_blue\">";
        }

            echo $row->username;
            if (in_array($row->id, $verifiedCheck_array)) {echo "<img src=\"verified-8.svg\">";}
            echo " (id) = " . $row->id;  

            if (isset($_GET["task"]) && $_GET["task"] == "BanningOptions" && $row->id != 1) {
            echo "<input value=\"UnBan-User\"type=\"button\" id=\"button_list\" onClick = \"return userUnBan_".$row->id."()\">";
            }
            if (isset($_GET["task"]) && $_GET["task"] == "AdminOptions" && $row->id != 1) {
            echo "<input value=\"Demote-User\"type=\"button\" id=\"button_list\" onClick = \"return userDemote_".$row->id."()\">";
            }
            if (isset($_GET["task"]) && $_GET["task"] == "ChangeUsername" && $row->id != 1) {
            echo "<input value=\"Rename-User\"type=\"button\" id=\"button_list\" onClick = \"return userRename_".$row->id."()\">". "<input>";
            }
            echo "<br>";


            if (isset($_GET["task"])) {
        echo "</div>";
            }

        echo "<script>";
        echo "function userUnBan_".$row->id."() {";
        echo "window.location.assign(\"/LUCK/users.php?task=BanningOptions&UnBannedSelect=".$row->id."\"".")";
        echo "}";   
        echo "function userDemote_".$row->id."() {";
        echo "window.location.assign(\"/LUCK/users.php?task=AdminOptions&DemoteSelect=".$row->id."\"".")";
        echo "}";
        echo "</script>";

}
        if(isset($_GET["UnBannedSelect"]) && $_GET["UnBannedSelect"] == $row->id) {;
        $id = $row->id;
        $userUpdate = R::load("user", $id);
        $userUpdate->banned = "false";
        R::store($userUpdate);
        header("location: /LUCK/users.php?task=BanningOptions");
    }
        if(isset($_GET["DemoteSelect"]) && $_GET["DemoteSelect"] == $row->id) {;
        $id = $row->id;
        $userUpdate = R::load("user", $id);
        $userUpdate->accountlevel = "base";
        R::store($userUpdate);
        header("location: /LUCK/users.php?task=AdminOptions");        
    }
}

echo "<input type=\"button\" id=\"CycleCleanDatabase\" onClick=\"return cleanDatabase()\">";
if (isset($_GET["task"]) && $_GET["task"] == "CleanDatabase" ) {
    $valid_accounts_Array = [];
    for ($x = 0; $x < 100; $x++) {
        $allUsers_cleaning_save = R::findAll("save".$x);
        $allUsers_cleaning_users = R::load("user", $x);
        // if (isset($allUsers_cleaning_save) && !isset($allUsers_cleaning_users)) {
            // R::trash($allUsers_cleaning_save);
            echo $x;
        // }
    }
}
?>

<script>
    function cleanDatabase() {
        window.location.assign("/LUCK/users.php?task=CleanDatabase");
    }
</script>

</body>
</html>