
<?php
session_start();
require("rb-sqlite.php");
R::setup("sqlite:Saved_Info.db");
$packs = R::findAll('packets');
$users = R::findAll('user');
$Post_Package = $_SESSION["saveView_Pref"];
$Session_id = $_SESSION["id"];
$current_timeStamp = date('H:i:s d/m/y');
$banCheck_array = array();
$verifiedCheck_array = array();
$usernamesCheck_array = array();


foreach($users as $row) {
    if ($row->banned == 'true') {
    array_push($banCheck_array, $row->id);
    }
    if ($row->accountlevel == 'admin') {
    array_push($verifiedCheck_array, $row->id);
    }
    array_push($usernamesCheck_array, $row->username);
    
        // $packtest = R::loadAll('save'.$Post_Package);
        // if ($packtest->id == $Post_Package) {
        // $packtest->save1 = time();
        // }
        // R::store($packtest);

}

echo "<br>";
foreach($packs as $row) {
//         $countMessagesPrivate.$row->id = R::count('package');
//     if ($countMessagesPrivate.$row->id == 0)  {
// echo "It doesnt look there is much happening here, maybe send a message";
//     }
    if($Session_id == $row->receiver && $row->user_id == $Post_Package) {
        echo "<div id=\"moveleft_nametag\">" . $row->sender_username;
        if (in_array($row->user_id, $verifiedCheck_array)) {echo "<img src=\"verified-8.svg\">";}
        echo "<a id=\"timeColour\">" . "  Sent at: " . $row->Sent_timeStamp . "</a>" . "<br>" ."</div>";
        echo "<div class=\"textbubble\" id=\"receivedmessage\">"; 
          if ((str_ends_with($row->package,  ".png")) || (str_ends_with($row->package,  ".gif")) || (str_starts_with($row->package, "https://encrypted-tbn0.gstatic.com/images?")) || (str_starts_with($row->package, "data:image"))) {
            echo "<img src=\"". $row->package. "\">";
        }
        else {
        print htmlspecialchars($row->package);
        }
        echo "</div>" . "<br>";
    }
    if($Session_id == $row->user_id && $row->receiver == $Post_Package && $Post_Package != "Global") { 
        echo "<div id=\"moveleft_nametag\">" . $row->sender_username;
        if (in_array($row->user_id, $verifiedCheck_array)) {echo "<img src=\"verified-8.svg\">";}
        echo " - me ".  "<a id=\"timeColour\">" . "  Sent at: " . $row->Sent_timeStamp . "</a>" ."</div>" . "<br>";
        echo "<div class=\"textbubble\" id=\"sendmessage\">"; 
        if ((str_ends_with($row->package,  ".jpg")) || (str_ends_with($row->package,  ".gif")) || (str_starts_with($row->package, "https://encrypted-tbn0.gstatic.com/images?")) || (str_starts_with($row->package, "data:image"))) {
            echo "<img src=\"". $row->package. "\">";
        }
        else {
        print htmlspecialchars($row->package);
        }
        echo "</div>" . "<br><br>";
    }

//global chat
    if ($Post_Package == "Global" && $row->receiver == "Global" && !in_array($row->user_id, $banCheck_array) && in_array($row->sender_username ,$usernamesCheck_array)){
        echo "<div id=\"moveleft_nametag\">" . $row->sender_username;
        if (in_array($row->user_id, $verifiedCheck_array)) {echo "<img src=\"verified-8.svg\">";}
        if ($row->user_id == $Session_id) {echo " - me ";}
        echo "<a id=\"timeColour\">" . "  Sent at: " . $row->Sent_timeStamp . "</a>" . "</div>" . "<br>";            
        echo "<div class=\"textbubble\"";
        if ($row->user_id == $Session_id) {echo "id=\"sendmessage\"";}
        else {echo "id=\"receivedmessage_global\"";}
        echo ">"; 
        // print htmlspecialchars($row->package);
          if ((str_ends_with($row->package,  ".jpg")) || (str_ends_with($row->package,  ".gif")) || (str_ends_with($row->package,  ".gif")) || (str_starts_with($row->package, "https://encrypted-tbn0.gstatic.com/images?")) || (str_starts_with($row->package, "data:image"))) {
            echo "<img src=\"". $row->package. "\">";
        }
        else {
            echo $row->package;
        // print htmlspecialchars($row->package);
        }
        echo "</div>" . "<br><br>";
    }
}

    $colour_savefiles = R::findAll('save'.$Session_id);
    foreach($colour_savefiles as $saveRow_colour) {
    if ($saveRow_colour->id == 1) {
        // echo $saveRow_colour->save2;
        echo "<script>";
        // echo "document.getElementById('timeColour').style.color=\"".$saveRow_colour->save2."\";";
        echo "document.querySelectorAll('#timeColour').forEach(el => { el.style.color=\"" . $saveRow_colour->save2 . "\"; });";
        echo "document.querySelectorAll('#sendmessage').forEach(el => { el.style.borderColor=\"" . $saveRow_colour->save3 . "\"; });";
        echo "document.querySelectorAll('#receivedmessage').forEach(el => { el.style.borderColor=\"" . $saveRow_colour->save4 . "\"; });";
        echo "</script>";
    }
    }
?>
<!-- <script>
    document.getElementById('timeColour').style.color = "red";
</script> -->