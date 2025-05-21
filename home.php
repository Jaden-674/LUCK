<?php
session_start();
require("rb-sqlite.php");
R::setup("sqlite:Saved_Info.db");
$user = R::findOne("user", "username = ?", [$_SESSION["username"]]);

if(isset($_GET["id"]) && $_GET["id"] == "SaveNewUser") {
    $username = $_POST["username"];
    if (ctype_alnum($username) != 1) {
        header("Location: signup.php?id=error7");
        exit();
    }
    else {$clean_username = $username;}
    $username_verify_availability = R::findAll('user');    
    $username_verify_array = array();
    foreach($username_verify_availability as $row) {
        array_push($username_verify_array, $row->username);
    }
    if (in_array($clean_username, $username_verify_array)) {
        header("Location: signup.php?id=error6");
        exit();
    }
    else {
    $password = $_POST["password"];
    $password_hashed = password_hash($password,PASSWORD_DEFAULT);
    $firstRow_Check = R::count('user');
    $newUser = R::dispense("user");
    $newUser->username = $username;
    $newUser->banned = "false";
    if ($firstRow_Check == 0 || $firstRow_Check == null) {
    $newUser->accountlevel = "admin";
    // $create_server_settings = R::dispense("server_settings");
    // $create_server_settings->save_point1 = "";
    // $create_server_settings->save_point2 = "";
    // $create_server_settings->save_point3 = "";
    // $create_server_settings->save_point4 = "";
    // R::store($create_server_settings);
    }
    else {$newUser->accountlevel = "base";}
    $newUser->password = $password_hashed;
    R::store($newUser);
    // create private save data file for user
    $save_personal = R::dispense("save".$newUser->id);
        $save_personal->type = "info";
        $save_personal->name = $newUser->username;
        $save_personal->save1 = "#defef0";
        $save_personal->save2 = "#888888";
        $save_personal->save3 = "limegreen";
        $save_personal->save4 = "#00c7d9";
    R::store($save_personal);
    // global chat private save to user save
        $save_pGlobal = R::dispense("save".$newUser->id);
        $save_pGlobal->type = "Timestamp";
        $save_pGlobal->name = "Global";
        $save_pGlobal->save1 = time();;
    R::store($save_pGlobal);
    // add row for every account to user save (excluding banned accounts and own account)
    $user_tableSave = R::findAll('user');
    foreach($user_tableSave as $row) {
        if ($row->banned == "false" && $row->id != $newUser->id){
        $save_p_user = R::dispense("save".$newUser->id);
        $save_p_user->type = "Timestamp";
        $save_p_user->name = $row->id;
        $save_p_user->save1 = time();;
        $save_p_user->save2 = $row->username;
    R::store($save_p_user);
    }
}
// add own row to every private save (excluding banned accounts and own account)
$user_addNewSave = R::findAll('user');
    foreach($user_addNewSave as $row42) {
            if ($row42->banned == "false" && $row42->id != $newUser->id){
            $save_p_accounts = R::dispense("save".$row42->id);
            $save_p_accounts->type = "Timestamp";
            $save_p_accounts->name = $newUser->id;
            $save_p_accounts->save1 = time();
            $save_p_accounts->save2 = $username;
            R::store($save_p_accounts);
        }
    }
    header("location: home.php?saveView_Pref=Global"); 
}    
}

if((isset($_GET["id"]) && $_GET["id"] == "loginCheck" )|| !isset($_SESSION["username"])){
    $username = $_POST["username"];
    $password = $_POST["password"];
    $user = R::findOne("user", "username = ?", [$username]);
    if($user == NULL){
    header("Location: login.php?id=error1");
}
else if(!password_verify($password, $user->password)){
    header("Location: login.php?id=error2");
}
else{
    if(password_verify($password, $user->password)){
        $_SESSION["username"] = $username;
        $_SESSION["accountlevel"] = $user->accountlevel;
        $_SESSION["id"] = $user->id;
        header("location: home.php?saveView_Pref=Global");
    }
}
}


if(isset($_GET["id"]) && $_GET["id"] == "userSend")  {
    if ($user["banned"] == "true"){ 
    header("Location: login.php?id=error4");
    exit();
    }
    $receiver = $_SESSION['saveView_Pref'];
    $user_id = $user["id"];
    $textinput = $_POST['textinput'];
    $timeStamp_full = date('h:i:s d/m/Y', time()+34200);
    $timeStamp_display = date('h:ia d/m/Y', time()+34200);
    $timeStamp = ltrim($timeStamp_display, '0');

    $pack = R::dispense('packets');
    $pack->user_id = $user_id;
    $pack->receiver = $receiver;
    $pack->sender_username = $_SESSION["username"];
    $pack->package = $textinput;
    $pack->Sent_timeStamp = $timeStamp;
    $pack->Sent_timeStamp_full = time();

    R::store($pack);
    header("location: home.php?saveView_Pref=".$receiver);
}


if ($user["banned"] == "true"){ 
    header("Location: login.php?id=error4");
}
if(isset($_GET["saveView_Pref"]) && $_GET["saveView_Pref"] == $_SESSION["id"] || isset($_GET["saveView_Pref"]) && $_GET["saveView_Pref"] == "") {
header("location: home.php?saveView_Pref=Global");
}
?>


<html>
<head>
<link rel="stylesheet" href="homePage_style.css">
<link rel="icon" type="image/x-icon" href="JDimage3.jpg">
<script src="jquery-3.7.1.min.js"></script> 
<script>
    setTimeout(() => {
    window.scrollTo(0, document.body.scrollHeight);
}, 100);
</script>
</head>
<body>

<!-- start of side bar -->
<div class="sidebar_fixed">
<!-- <input type="button" value="test"> -->
<h4>

    <?php 
        if(isset($_SESSION["username"])){
        echo "Welcome " . $_SESSION["username"];
        $admin_users = R::findAll('user');
        echo "<br>";
        foreach($admin_users as $admin_row) {
    if ($admin_row->accountlevel == "admin" && $admin_row->id == $_SESSION["id"]) {
        echo "<a href=\"users.php\"> Admin Panel</a><br>";
    }
    }
        // echo "<script>";
        // echo "function Switch_display() {";
        // if (!isset($_GET["openDisplay_sidebar"])) {
        // echo "window.location.assign('/Website%2011.3.2/home.php?openDisplay_sidebar=request&&saveView_Pref=".$_SESSION["saveView_Pref"]."');";
        // }
        // else if (isset($_GET["openDisplay_sidebar"]) && $_GET["openDisplay_sidebar"] == "request") {
        // echo "window.location.assign('/Website%2011.3.2/home.php?saveView_Pref=".$_SESSION["saveView_Pref"]."');";
        // }
        // echo "}";
        // echo "</script>";
        // if (!isset($_GET["openDisplay_sidebar"])) {
        // echo "<input value=\"Add Friends\" type=\"button\" onClick=\"return Switch_display()\">";      
        // }
        // else if (isset($_GET["openDisplay_sidebar"]) && $_GET["openDisplay_sidebar"] == "request") {
        // echo "<input value=\"exit\" type=\"button\" onClick=\"return Switch_display()\">";    
        // }    
    }
?>
</h4>  
</form>

<?php
$user_found = R::findAll('user');
$user_found_timeCheck = R::findAll('packets');
$Session_id = $_SESSION["id"];
$verifiedCheck_array = array();
$added_users_all = R::findAll('save'.$_SESSION["id"]);
$added_users_array = [];
foreach($added_users_all as $added_row) {
    if ($added_row->type == "Timestamp" && $added_row->id != 2) {
        array_push($added_users_array, $added_row);
        // print_r($added_users_array);
    }
}
foreach($user_found as $row) {

    if ($row->accountlevel == 'admin') {
        array_push($verifiedCheck_array, $row->id);
    }
}

    //acc show all from 231
    if (isset($_GET["saveView_Pref"]) && $_GET["saveView_Pref"] == "Global"){
    echo "<div id=\"custom_border_colour\"class=\"myDiv_selected\" onClick = \"return userChat_Global()\">";
      echo "Selected - ";  
    }
    else {
    echo "<div id=\"custom_border_colour\" class=\"myDiv\" onClick = \"return userChat_Global()\">"; 
    }
    echo "Global Chat" . "<br>";
    echo "<input type=\"button\" id=\"button_list\">";
    echo"</div>";

    echo "<script>";
    echo "function userChat_Global() {";
    echo "window.location.assign(\"/Website%2011.3.2/home.php?saveView_Pref=Global\"".")";
    echo "}";
    echo "</script>";

    //selected user display
    foreach($user_found as $row) {
    if ($row->accountlevel == 'admin') {
    array_push($verifiedCheck_array, $row->id);
    }

        foreach($user_found_timeCheck as $row_layer2) {
    if (($row_layer2->user_id == $row->id && $row_layer2->receiver == $_SESSION["id"]) || ($row_layer2->user_id == $_SESSION["id"] && $row_layer2->receiver == $row->id)) {
        $last_text_selected = $row_layer2->package;
        }
        }
     if ($row->banned == "false" && $row->id != $_SESSION["id"] && $row->id == $_GET["saveView_Pref"]) {
        echo "<div id=\"custom_border_colour\" class=\"myDiv_selected\" onClick = \"return userChat_".$row->id."()\">";
        echo "Selected - ".$row->username;
        if (in_array($row->id, $verifiedCheck_array)) {echo "<img src=\"verified-8.svg\">";}

        echo "<div id=\"test12\">";
        print htmlspecialchars($last_text_selected);
        echo $last_text_selected = "";
        if ($last_text_selected == "") {
        echo "<br>";
        }
        echo "</div>";

        echo "<input type=\"button\" id=\"button_list_notifcation\">";
        echo"</div>"; 

        echo "<script>";
        echo "function userChat_".$row->id."() {";
        echo "window.location.assign(\"/Website%2011.3.2/home.php?saveView_Pref=".$row->id."\"".")";
        echo "}";
        echo "</script>";
           if(isset($_GET["saveView_Pref"])) {
    $_SESSION["saveView_Pref"] = $_GET["saveView_Pref"];
   }
        $test12_11_1141 = R::findAll('save'.$_SESSION["id"]);
        foreach($test12_11_1141 as $nestRow) {
            if($nestRow->type == "Timestamp" && $nestRow->name == $_GET["saveView_Pref"]){
                $nestRow->save1 = time();
                R::store($nestRow);
            }
        }
    }
}

    $user_found_new = R::findAll('save'.$_SESSION["id"]);
    $packets_newMessage= R::findAll('packets');
    $messages_new_array = array();

    foreach($user_found as $row) {
            $verifiedCheck_array = array();
    if ($row->accountlevel == 'admin') {
    array_push($verifiedCheck_array, $row->id);
    }
        if ($row->id != $_SESSION["id"] && $row->id != $_GET["saveView_Pref"] && $row->banned == "false") {
            foreach($user_found_new as $row_stack2) {
                if ($row_stack2->type == "Timestamp" && $row_stack2->name == $row->id ) {   
                    foreach($packets_newMessage as $row_stack3) {
                        if ($row_stack3->user_id == $row_stack2->name && $row_stack3->receiver == $_SESSION["id"]) {
                            if ($row_stack3->_sent_time_stamp_full > $row_stack2->save1 && !in_array($row_stack3->user_id, $messages_new_array)){
                                array_push($messages_new_array, $row_stack3->user_id);
                                if (in_array($row_stack3->user_id, $messages_new_array)) {
                                echo "<div id=\"custom_border_colour\" class=\"myDiv_newMessage\" onClick = \"return userChat_".$row_stack3->user_id."()\">";
                                echo $row->username;
                                if (in_array($row->id, $verifiedCheck_array)) {echo "<img src=\"verified-8.svg\">";}
                                echo "<div id=\"test12\">". $row_stack3->package. "</div>";

                                echo "<svg id=\"circle_move\" height=\"45\" width=\"300\">";
                                echo "<circle r=\"12.5\" cx=\"260\" cy=\"20\" fill=\"red\"/>";
                                echo "</svg>";
                                
                                echo "<div  id=\"notification_move\" > New Message </div>";

                                echo "<input type=\"button\" id=\"button_list_notifcation_translate\">";
                                echo"</div>";
                                echo "<script>";
                                echo "function userChat_".$row_stack3->user_id."() {";
                                echo "window.location.assign(\"/Website%2011.3.2/home.php?saveView_Pref=".$row_stack3->user_id."\"".")";
                                echo "}";
                                echo "</script>";
                                if(isset($_GET["saveView_Pref"])) {
                                $_SESSION["saveView_Pref"] = $_GET["saveView_Pref"];
                                    }
                                }
                            }
                        }
                    }
                }
            }       
        }   
    }

foreach($user_found as $row) {
    $verifiedCheck_array = array();
    $rangaCheck_array = array();
    if ($row->accountlevel == 'admin') {
    array_push($verifiedCheck_array, $row->id);
    }

    foreach($user_found_timeCheck as $row_layer2) {
    if (($row_layer2->user_id == $row->id && $row_layer2->receiver == $_SESSION["id"] && $row_layer2->package != "") || ($row_layer2->user_id == $_SESSION["id"] && $row_layer2->receiver == $row->id && $row_layer2->package != "")) {
        $last_text = $row_layer2->package;
        }
    }
    if ($row->banned == "false" && $row->id != $_SESSION["id"] && $row->id != $_GET["saveView_Pref"] && !in_array($row->id, $messages_new_array)) {
        echo "<div id=\"custom_border_colour\" class=\"myDiv\" onClick = \"return userChat_".$row->id."()\">";
        echo $row->username;
        if (in_array($row->id, $verifiedCheck_array)) {echo "<img src=\"verified-8.svg\">";}
            
        echo "<div id=\"test12\">";
        print htmlspecialchars($last_text);
        echo $last_text = "";
        if ($last_text == "") {
        echo "<br>";
        }
        echo "</div>";
        echo "<input type=\"button\" id=\"button_list_notifcation\">";
        echo"</div>";

        echo "<script>";
        echo "function userChat_".$row->id."() {";
        echo "window.location.assign(\"/Website%2011.3.2/home.php?saveView_Pref=".$row->id."\"".")";
        echo "}";
        echo "</script>";
           if(isset($_GET["saveView_Pref"])) {
    $_SESSION["saveView_Pref"] = $_GET["saveView_Pref"];
   }
        $test12_11_1141 = R::findAll('save'.$_SESSION["id"]);
        foreach($test12_11_1141 as $nestRow) {
            if($nestRow->type == "Timestamp" && $nestRow->name == $_GET["saveView_Pref"]){
                $nestRow->save1 = time();
                // echo strtotime(date('H:i:s d/m/y'));
                R::store($nestRow);
            }
        }
    }
} 

    echo "<div id=\"custom_border_colour\" class=\"myDiv\" >";
    echo"</div>";



    $colour_savefiles = R::findAll('save'.$Session_id);
    foreach($colour_savefiles as $saveRow_colour) {
    if ($saveRow_colour->id == 1) {
        echo "<script>";
        echo "document.querySelectorAll('#custom_border_colour').forEach(el => { el.style.borderColor=\"" . $saveRow_colour->save2 . "\"; });";
        echo "</script>";
    }
    }
?>
</div>
<!-- end of side bar -->

<!-- start of input bar -->
<div class="inputbar_fixed">
<form action="home.php?id=userSend" method="post" >
<div id="center_inputfield">
<input type="button" class="button" value="⌫" id="delete_textInput" onclick="return inputField()"> 
<?php echo "<input type=\"text\" id=\"textinput\" name=\"textinput\" autocomplete=\"off\"";if(!isset($_GET["userSearch_Path"])){echo "autofocus";}echo" required>";?>
<input type="submit" class="button" value="⇪" >
<script>
    function inputField(){
    testingnggg = document.getElementById("delete_textInput");
    testingnggg = "";
    }
</script>

<?php
    $colour_savefiles = R::findAll('save'.$_SESSION["id"]);
    foreach($colour_savefiles as $saveRow_colour) {
    if ($saveRow_colour->id == 1) {
        echo "<script>";
        echo "document.body.style.backgroundColor = '" . $saveRow_colour->save1 . "';";
        echo "</script>";
    }
    }
?>
</div>
</form>
    </div>
    <!-- end of input bar -->
        <!-- <?php
        $selectedUsername_display = R::findAll('user');
        foreach($selectedUsername_display as $row) {
            if ($row->id == $_GET["saveView_Pref"] && isset($_GET["saveView_Pref"]) && $_GET["saveView_Pref"] != "Global" ){

            echo "<div class=\"chatview_display\">";
            echo "<h1>".$row->username."</h1>";
            // if (in_array($row->id, $verifiedCheck_array)) {echo "<img src=\"verified-8.svg\">";}
            echo "</div>";    
            }
        }
        ?> -->
    <a href="user_settings.php">
    <div class="settings_button">
    <svg height="55" width="55">
    <circle r="25" cx="29.5" cy="29.5" fill="white"/>
    </svg>
    <img src="account-settings.svg" id="circlewhite">
    </div>   
    </a>

<div class="read_left" onclick="scrollToBottom()"> 
    <br>
    <br>

    <script>
    $(document).ready(function(){
    $(".read_left").load("text_Display.php")
    setInterval((function () {
    $(".read_left").load("text_Display.php");
    }), 2500)  
    }); 
    </script>
</div>
<script>
    function scrollToBottom() {
        window.scrollTo(0, document.body.scrollHeight);
    }
</script>

 

</body>
</html>