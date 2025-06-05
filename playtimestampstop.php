<?php
session_start();
require("rb-sqlite.php");
R::setup("sqlite:Saved_Info.db");
if (isset($_SESSION["CurrentGID_Code"])) {$viewingCode = $_SESSION["CurrentGID_Code"];}

//if in match already
$currentMatches_CheckArray = [];
$findall_currentMatches = R::findAll("save");
foreach ($findall_currentMatches as $row) {
  if (intval($_SESSION["id"]) == intval(json_decode($row->u_id_1)[0]) || intval($_SESSION["id"]) == intval(json_decode($row->u_id_2)[0])) {
    array_push($currentMatches_CheckArray, intval($row->id));
  } 
}
if (intval(count($currentMatches_CheckArray)) != 0 && isset($_GET["ServerLink"]) && $_GET["ServerLink"] != $currentMatches_CheckArray[0]){
  header("location: playtimestampstop.php?ServerLink=".$currentMatches_CheckArray[0]);
  exit;
}
//server request GID change valid
if (isset($_GET["ServerLink"]) && (R::findOne('save', 'id = ?', [ $_GET["ServerLink"] ]) != null || $_GET["ServerLink"] == 0)) {
  $viewingCode = $_GET["ServerLink"]; 
  $_SESSION["CurrentGID_Code"] = $_GET["ServerLink"];
  header("location: playtimestampstop.php");
}
//server request GID chnage invalid
else if (isset($_GET["ServerLink"]) && R::findOne('save', 'id = ?', [ $_GET["ServerLink"] ]) == null)  {
  $viewingCode = 0;
  $_SESSION["CurrentGID_Code"] = 0;
  header("location: playtimestampstop.php?error=10&&invalid=".$_GET["ServerLink"]);
  exit;
}
if (R::findOne('save', 'id = ?', [ $_SESSION["CurrentGID_Code"] ]) == null && $_SESSION["CurrentGID_Code"] != 0) {
  $viewingCode = 0;
  $_SESSION["CurrentGID_Code"] = 0;
  header("location: playtimestampstop.php?error=11");
  exit;
}

$item = R::load("save", $viewingCode);
$response = ["G_ID_code" => [], "d1" => [], "d1_side" => [],"d2" => [],"d3" => [], 
"currentColour" => [], "playerA" => [], "playerB" => [], "turn_id" => [], "closedWinner_UID" => []];
    $response["G_ID_code"] = $item->id;
    $decodedD1 = json_decode($item->d1, true);
    $decodedD1_side = json_decode($item->d1_side, true);
    $decodedD2 = json_decode($item->d2, true);
    $decodedD3 = json_decode($item->d3, true);
    if (is_array($decodedD1)) { $response["d1"] = array_merge($response["d1"], $decodedD1); }
    if (is_array($decodedD1)) { $response["d1_side"] = array_merge($response["d1_side"], $decodedD1_side); }
    if (is_array($decodedD2)) { $response["d2"] = array_merge($response["d2"], $decodedD2); }
    if (is_array($decodedD3)) { $response["d3"] = array_merge($response["d3"], $decodedD3); }
    $response["currentColour"] = $item->turn_open; 
    $response["playerA"] = json_decode($item->uID_1);
    $response["playerB"] = json_decode($item->uID_2);
    $response["turn_id"] = json_decode($item->turn_id);
    $response["closedWinner_UID"] = json_decode($item->winner_uid);
    if (isset($_GET['fetch_data'])) {
    echo json_encode($response);
    exit;
    }

if (isset($_GET["match_destroy"]) && $_GET["match_destroy"] == "true") {
  $matchDestroy = R::load("save", $viewingCode);
  if (isset($_SESSION["id"]) && (($_SESSION["id"] == json_decode($matchDestroy->uID_1)[0] && isset(json_decode($matchDestroy->uID_1)[0])) || 
  ($_SESSION["id"] == json_decode($matchDestroy->uID_2)[0] && isset(json_decode($matchDestroy->uID_1)[0])))) {
    if (json_decode($matchDestroy->winner_uid)[0] == -1 && json_decode($matchDestroy->uID_1)[0] != json_decode($matchDestroy->uID_2)[0] && json_decode($matchDestroy->uID_2)[0] != null) {
      $userLoad_forfeit = R::load("save".$_SESSION["id"], 1);
      $userLoad_forfeit->save4 = 0;
      R::store($userLoad_forfeit);
      if (intval($_SESSION["id"]) == intval(json_decode($matchDestroy->uID_1)[0])) { $userLoad_UID_win_forfeit = R::load(("save".strval(json_decode($matchDestroy->uID_2)[0])), 1); }
      else { $userLoad_UID_win_forfeit = R::load(("save".strval(json_decode($matchDestroy->uID_1)[0])), 1); }
      $userLoad_UID_win_forfeit->save3++;
      $userLoad_UID_win_forfeit->save4++;
      R::store($userLoad_UID_win_forfeit);
    }
    R::trash($matchDestroy);
    $_SESSION["CurrentGID_Code"] = 0;
  }
  header("location: playtimestampstop.php");
}

if (isset($_GET["newServerLink"]) && $_GET["newServerLink"] == "clean") {
  $findSessionMatch = R::findAll("save");
  $foundSessionMatches = [];
  foreach ($findSessionMatch as $row) {
    if (intval($_SESSION["id"]) == intval(json_decode($row->u_id_1)[0]) || intval($_SESSION["id"]) == intval(json_decode($row->u_id_2)[0])) {
      array_push($foundSessionMatches, intval($row->id));
    } 
  }
  if (intval(count($foundSessionMatches)) == 0) {
  $createNewMatch = R::dispense("save");
  $createNewMatch->d1 = json_encode([96, 97, 98, 111, 112, 113, 126, 127, 128]);
  $createNewMatch->d1_side = json_encode([81,82,83,95,99,110,114,125,129,141,142,143]);
  $createNewMatch->d2 = json_encode([]);
  $createNewMatch->d3 = json_encode([]);
  $createNewMatch->turnOpen = "blue";   
  $createNewMatch->winner_UID = json_encode([-1]);
  $createNewMatch->uID_1 = json_encode([intval($_SESSION["id"]), $_SESSION["username"]]);
  $createNewMatch->wins_counter = json_encode([0, 0]);
  $_SESSION["CurrentGID_Code"] = $createNewMatch->id;
  R::store($createNewMatch);
  header("location: playtimestampstop.php?ServerLink=".$createNewMatch->id);
  }
  else {
    header("location: playtimestampstop.php?ServerLink=".$foundSessionMatches[0]);
  }
}

if (isset($_GET["reset"])){
      $userUpdatereset = R::load("save", $viewingCode);
      $uID_1_array = [];
      $uID_2_array = [];
      if ($_GET["reset"] == "true") {
      if (!is_int(json_decode($userUpdatereset->uID_1)[0])) {
      array_push($uID_1_array, intval($_SESSION["id"]), $_SESSION["username"]);
      $userUpdatereset->uID_1 = json_encode($uID_1_array);
      }
      else if (isset($userUpdatereset->uID_1) && !is_int(json_decode($userUpdatereset->uID_2)[0])  && $userUpdatereset->uID_1[0] != intval($_SESSION["id"])) {
      $uID_2_array = [];
      array_push($uID_2_array, intval($_SESSION["id"]), $_SESSION["username"]);
      $userUpdatereset->uID_2 = json_encode($uID_2_array);
      if(json_decode($userUpdatereset->uID_2)[0] != json_decode($userUpdatereset->uID_1)[0]) {
          $userLoad_UID1_scorecardMod = R::load("save".json_decode($userUpdatereset->uID_1)[0], 1);
          $userLoad_UID1_scorecardMod->save2++;
          R::store($userLoad_UID1_scorecardMod);
          $userLoad_UID2_scorecardMod = R::load("save".json_decode($userUpdatereset->uID_2)[0], 1);
          $userLoad_UID2_scorecardMod->save2++;
          R::store($userLoad_UID2_scorecardMod);
        }
      }
      if(json_decode($userUpdatereset->winner_UID)[0] != -1 && (isset($_SESSION["id"]) && (($_SESSION["id"] == json_decode($userUpdatereset->uID_1)[0] && isset(json_decode($userUpdatereset->uID_1)[0])) || 
      ($_SESSION["id"] == json_decode($userUpdatereset->uID_2)[0] && isset(json_decode($userUpdatereset->uID_1)[0])))) ) {
        $userReset_UID1_scorecardMod = R::load("save".json_decode($userUpdatereset->uID_1)[0], 1);
        $userReset_UID1_scorecardMod->save2++;
        R::store($userReset_UID1_scorecardMod);
        $userReset_UID2_scorecardMod = R::load("save".json_decode($userUpdatereset->uID_2)[0], 1);
        $userReset_UID2_scorecardMod->save2++;
        R::store($userReset_UID2_scorecardMod);
      }
        if ($userUpdatereset->turn_id == $userUpdatereset->uID_1) {$userUpdatereset->turn_id = $userUpdatereset->uID_2;}
        else {$userUpdatereset->turn_id = $userUpdatereset->uID_1;}
      }
      if (isset($_SESSION["id"]) && (($_SESSION["id"] == json_decode($userUpdatereset->uID_1)[0] && isset(json_decode($userUpdatereset->uID_1)[0])) || 
      ($_SESSION["id"] == json_decode($userUpdatereset->uID_2)[0] && isset(json_decode($userUpdatereset->uID_1)[0])))) {
          $userUpdatereset->d1 = json_encode([96, 97, 98, 111, 112, 113, 126, 127, 128]);
          $userUpdatereset->d1_side = json_encode([81,82,83,95,99,110,114,125,129,141,142,143]);
          $userUpdatereset->d2 = json_encode([]);
          $userUpdatereset->d3 = json_encode([]);
          $userUpdatereset->turnOpen = "blue";   
          $userUpdatereset->winner_UID = json_encode([-1]);
        }
      if ($_GET["reset"] == "trueMAJOR" && ((isset($_SESSION["id"]) && ($_SESSION["id"] == json_decode($userUpdatereset->uID_1)[0])) || $_SESSION["id"] == 1))  {
      $userUpdatereset->uID_1 = json_encode($uID_1_array);
      $userUpdatereset->uID_2 = json_encode($uID_2_array);
      $userUpdatereset->turn_id = json_encode([]);   
      $userUpdatereset->wins_counter = json_encode([0, 0]);   
      }
        R::store($userUpdatereset);
      header("location: playtimestampstop.php");
}

//player update server with playtimestampstop, including win and validation for both
// localhost/LUCK/playtimestampstop.php?GridUpdate=win&&ClickBase=153&&Check1=139&&Check2=125&&Check3=111&&Check4=97&&TurnColour=blue
// localhost/LUCK/playtimestampstop.php?Locked=***&&GridUpdate=red
if (isset($_GET["GridUpdate"]) && $viewingCode != 0) {
  if ($_GET["GridUpdate"] == "win") {
    $winUpdate = R::load("save", $viewingCode);
        if (isset($_SESSION["id"]) && (($_SESSION["id"] == json_decode($winUpdate->uID_1)[0] && isset(json_decode($winUpdate->uID_1)[0])) || ($_SESSION["id"] == json_decode($winUpdate->uID_2)[0] && isset(json_decode($winUpdate->uID_1)[0])))) {
        $newOpenArray = json_decode($winUpdate->d1, true);
        $red_used_Array = json_decode($winUpdate->d2, true) ?: []; 
        $blue_used_Array = json_decode($winUpdate->d3, true) ?: [];
        $currentColourWinCheck = $winUpdate->turn_open;
        $checkGroup_array = [];
        if (isset($_GET["Check1"]) && isset($_GET["Check2"]) && isset($_GET["Check3"]) && isset($_GET["Check4"]) && isset($_GET["ClickBase"]) && isset($_GET["TurnColour"]) && $currentColourWinCheck == $_GET["TurnColour"]) {
        array_push($checkGroup_array, intval($_GET["Check1"]), intval($_GET["Check2"]), intval($_GET["Check3"]), intval($_GET["Check4"]), intval($_GET["ClickBase"]));
        sort($checkGroup_array);
        $commonDiff_array = [];
        for ($common_diff_counter = 0; $common_diff_counter <= 3; $common_diff_counter++) {
          if ($_GET["TurnColour"] == "red" && in_array($checkGroup_array[$common_diff_counter], $red_used_Array)) { array_push($commonDiff_array, ($checkGroup_array[$common_diff_counter+1]-$checkGroup_array[$common_diff_counter])); }
          if ($_GET["TurnColour"] == "blue" && in_array($checkGroup_array[$common_diff_counter], $blue_used_Array)) { array_push($commonDiff_array, ($checkGroup_array[$common_diff_counter+1]-$checkGroup_array[$common_diff_counter])); }
        }
        if (count(array_unique($commonDiff_array)) == 1) {
          if($_GET["TurnColour"] == "red") {
            array_push($red_used_Array, intval($_GET["ClickBase"]));
            $winUpdate->d2 = json_encode($red_used_Array);
          }
          if($_GET["TurnColour"] == "blue") {
            array_push($blue_used_Array, intval($_GET["ClickBase"])); 
            $winUpdate->d3 = json_encode($blue_used_Array);
          }
          $winUpdate->winner_uid = json_encode([$_SESSION["id"], json_encode($checkGroup_array), rand(0,100), time()+7]);
          $winUpdate->turn_id[0] = "[-1";
          if (json_decode($winUpdate->uID_1)[0] != json_decode($winUpdate->uID_2)[0] && json_decode($winUpdate->uID_2)[0] != null) {
            if (intval($_SESSION["id"]) == intval(json_decode($winUpdate->uID_1)[0])) { $userLoad_UID_Lost = R::load(("save".strval(json_decode($winUpdate->uID_2)[0])), 1); }
            else { $userLoad_UID_Lost = R::load(("save".strval(json_decode($winUpdate->uID_1)[0])), 1); }
            $userLoad_UID_Lost->save4 = 0;
            R::store($userLoad_UID_Lost);
            $userLoad_UID_winner = R::load("save".$_SESSION["id"], 1);
            $userLoad_UID_winner->save3++;
            $userLoad_UID_winner->save4++;
            if (intval($userLoad_UID_winner->save4) > intval($userLoad_UID_winner->save5)) {
            $userLoad_UID_winner->save5 = $userLoad_UID_winner->save4;
            }
            R::store($userLoad_UID_winner);
          }
          }
        }
      }
    R::store($winUpdate);
  }
  if ($_GET["GridUpdate"] == "red" || $_GET["GridUpdate"] == "blue") {
    $saveUpdate = R::load("save", $viewingCode);
      if (isset($_SESSION["id"]) && $_SESSION["id"] == json_decode($saveUpdate->turn_id)[0] && (($_SESSION["id"] == json_decode($saveUpdate->uID_1)[0] && isset(json_decode($saveUpdate->uID_1)[0])) || ($_SESSION["id"] == json_decode($saveUpdate->uID_2)[0] && isset(json_decode($saveUpdate->uID_1)[0])))) 
        $newOpenArray = json_decode($saveUpdate->d1, true);
        $red_used_Array = json_decode($saveUpdate->d2, true) ?: []; 
        $blue_used_Array = json_decode($saveUpdate->d3, true) ?: [];
        $grey_side_Array = json_decode($saveUpdate->d1_side, true);
        if(in_array(intval($_GET["Locked"]), $newOpenArray) && $saveUpdate->turn_open == $_GET["GridUpdate"] && ((!in_array(intval($_GET["Locked"]), $red_used_Array) && !in_array(intval($_GET["Locked"]), $blue_used_Array)) || (count($red_used_Array) == 0 && count($blue_used_Array) == 0))) {
        if ($_GET["GridUpdate"] == "red") {
            array_push($red_used_Array, intval($_GET["Locked"]));
        } 
        else if ($_GET["GridUpdate"] == "blue") {
            array_push($blue_used_Array, intval($_GET["Locked"]));
        }
        shuffle($grey_side_Array);
        if (count($grey_side_Array) >= 2){
        array_push($newOpenArray, intval($grey_side_Array[0]), intval($grey_side_Array[1]));
        }
        for ($grey_find_count = 0; $grey_find_count < count($newOpenArray); $grey_find_count++ ) {
          if (intval($newOpenArray[$grey_find_count]-1)%15 != 14 && intval($newOpenArray[$grey_find_count]-1) >= 0 && !in_array(intval($newOpenArray[$grey_find_count]-1),$newOpenArray) && !in_array(intval($newOpenArray[$grey_find_count]-1),$grey_side_Array)) { array_push($grey_side_Array, intval($newOpenArray[$grey_find_count]-1));}
          if (intval($newOpenArray[$grey_find_count]+1)%15 != 0 && intval($newOpenArray[$grey_find_count]+1) <= 224 && !in_array(intval($newOpenArray[$grey_find_count]+1),$newOpenArray) && !in_array(intval($newOpenArray[$grey_find_count]+1),$grey_side_Array)) { array_push($grey_side_Array, intval($newOpenArray[$grey_find_count]+1));}
          if (intval($newOpenArray[$grey_find_count]-15) >= 0 && !in_array(intval($newOpenArray[$grey_find_count]-15),$newOpenArray) && !in_array(intval($newOpenArray[$grey_find_count]-15),$grey_side_Array)) { array_push($grey_side_Array, intval($newOpenArray[$grey_find_count]-15));}
          if (intval($newOpenArray[$grey_find_count]+15) <= 224 && !in_array(intval($newOpenArray[$grey_find_count]+15),$newOpenArray) && !in_array(intval($newOpenArray[$grey_find_count]+15),$grey_side_Array)) { array_push($grey_side_Array, intval($newOpenArray[$grey_find_count]+15));}
        }
        sort($grey_side_Array);
        sort($newOpenArray);
        $clean_OpenArray = array_values(array_diff($grey_side_Array, $newOpenArray));
        $saveUpdate->d1 = json_encode($newOpenArray);
        $saveUpdate->d2 = json_encode($red_used_Array); 
        $saveUpdate->d3 = json_encode($blue_used_Array);
        $saveUpdate->d1_side = json_encode($clean_OpenArray);
        $saveUpdate->turnOpen = ($_GET["GridUpdate"] == "red") ? "blue" : "red";
        if ($saveUpdate->turn_id == $saveUpdate->uID_1) { $saveUpdate->turn_id = $saveUpdate->uID_2; }
        else { $saveUpdate->turn_id = $saveUpdate->uID_1; }
    }
    R::store($saveUpdate);
  }
  header("location: playtimestampstop.php");
  exit;
}

if (isset($_GET["protanopiaToggle"])) {
    $userUpdatePROtoggle = R::load("save".$_SESSION["id"], 1);
    if ($userUpdatePROtoggle->save1 == "protanopia") {
    $userUpdatePROtoggle->save1 = "off";
    }
    else {$userUpdatePROtoggle->save1 = "protanopia";}
    R::store($userUpdatePROtoggle);
    header("location: playtimestampstop.php");

  }

//user account login start
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
    }
    else {$newUser->accountlevel = "base";}
    $newUser->password = $password_hashed;
    R::store($newUser);
    // create private save data file for user
    $save_personal = R::dispense("save".$newUser->id);
        $save_personal->type = "info";
        $save_personal->name = $newUser->username;
        $save_personal->save1 = "off";
        $save_personal->save2 = 0;
        $save_personal->save3 = 0;
        $save_personal->save4 = 0;
        $save_personal->save5 = 0;
    R::store($save_personal);
    $_SESSION["CurrentGID_Code"] = 0;
  }
  header("location: playtimestampstop.php");    
}

if((isset($_GET["id"]) && $_GET["id"] == "loginCheck" )|| !isset($_SESSION["username"])){
    $username = $_POST["username"];
    $password = $_POST["password"];
    $user = R::findOne("user", "username = ?", [$username]);
    if($user == NULL){
    header("Location: login.php?id=error");
}
else if(!password_verify($password, $user->password)){
    header("Location: login.php?id=error2");
}
else{
    if(password_verify($password, $user->password)){
        $_SESSION["username"] = $username;
        $_SESSION["accountlevel"] = $user->accountlevel;
        $_SESSION["id"] = $user->id;
        $_SESSION["CurrentGID_Code"] = 0;
        header("location: playtimestampstop.php");
    }
}
}

$added_users_all = R::findAll('save'.$_SESSION["id"]);
foreach($added_users_all as $row) {
  if ($row->id == 1) {
  if ($row->save1 == "off") {
    $colourPreset_p5relay = "off";
  }
  if ($row->save1 == "protanopia"){
    $colourPreset_p5relay = "protanopia";
  }
  }
}
//ip display;
$ip = trim(shell_exec("ipconfig getifaddr en0"));
?>
<html style="background: #21323b">
<head>
    <title>LUCK/playtimestampstop</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.9.0/p5.js"></script>
    <style>
        body {
            margin: 0px;
            overflow: hidden;
        }
        #overlay {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 20px;
            z-index: 1;
        }
    </style>
</head>
<body>

<script>
  window.addEventListener('wheel', function (e) {
    if (e.ctrlKey || e.metaKey) {
      e.preventDefault();
    }
  }, { passive: false });

  window.addEventListener('keydown', function (e) {
    const zoomKeys = ['+', '=', '-', '0'];
    if ((e.ctrlKey || e.metaKey) && zoomKeys.includes(e.key)) {
      e.preventDefault();
    }
  });

let player_UID_1 = -1 
let player_UID_2 = -1
let turn_id_current = -1
let Game_ID_code = "UNDEF"
let sides_counter_display = 0;
let P5red_used_Array2 = []
let P5blue_used_Array2 = []
let player_UID_1_usrn = "ƒ";
let player_UID_2_usrn = "ƒ";
let set_winner_UID = -1;
let set_winning_array = [];
let sides_display_Array = [];

  function fetchData() {
    fetch(window.location.href + '?fetch_data=1')
    .then(response => response.json())
    .then(data => {
        Game_ID_code = data.G_ID_code; 
        open_choices_Array = data.d1;
        sides_display_Array = data.d1_side;
        P5red_used_Array2 = data.d2;
        P5blue_used_Array2 = data.d3;
        colourRelay = data.currentColour;
        player_UID_1 = parseInt(data.playerA); 
        player_UID_2 = parseInt(data.playerB);
        turn_id_current = parseInt(data.turn_id);
        if (data.turn_id == null) turn_id_current_ursn = "ƒ";
        else { turn_id_current_ursn = data.turn_id[1]; }

        if (data.playerA == null) {window.location.assign("/LUCK/playtimestampstop.php?error=12");}
        player_UID_1_usrn = data.playerA[1];
        if (data.playerB != null) { player_UID_2_usrn = data.playerB[1]; }
        if (player_UID_1_usrn == null) player_UID_1_usrn = "ƒ";
        if (player_UID_2_usrn == null) player_UID_2_usrn = "ƒ";
        set_winner_UID = parseInt(data.closedWinner_UID[0]);
        if (data.closedWinner_UID.length == 4) {
        set_winning_array = JSON.parse(data.closedWinner_UID[1]);
        }
        else { set_winning_array = ["win_array_error"]; }
        win_splash_random = parseInt(data.closedWinner_UID[2]);

    for (let u = 0; u<225; u++) {
      grid[u].type = "disabled"
  }
    for (let sides_counter = 0; sides_counter<sides_display_Array.length; sides_counter++) {
    grid[sides_display_Array[sides_counter]].type = "side";
  }
    for(let k2 = 0; k2<open_choices_Array.length; k2++) {
    grid[open_choices_Array[k2]].type = "open";
  }
    for(let k_red2 = 0; k_red2<P5red_used_Array2.length; k_red2++) {
    grid[P5red_used_Array2[k_red2]].type = "red";
  }
    for(let k_blue2 = 0; k_blue2<P5blue_used_Array2.length; k_blue2++) {
    grid[P5blue_used_Array2[k_blue2]].type = "blue";
  }
    })
}

let grid = []
let tileSize;
let gridSize = 15
let all_sides = []
let localSelected_OpenPosID = 0;
let width_divide = 4
let move_shift = 0;
let local_UID = <?php echo intval($_SESSION["id"]);?>;
let en0_redirect_display = "<?php echo $ip;?>";
let colourBlind_mode = "<?php echo $colourPreset_p5relay; ?>";
let check_lineX = [];
let check_lineY = []; 
let check_linePD = []; 
let check_lineND = [];

class Square {
  constructor(x, y, size, type, id) {
    this.x = x;
    this.y = y;
    this.size = size;
    this.type = type;
    this.id = id;
  }

  draw() {
    if(this.type == "disabled")
    fill("#21323b");
  if(this.type == "side") {
  fill("#6f8087")
  // fill("hotpink")
  // fill("#21323b");
  }
    if (this.type == "open") 
    fill(220)
   if(this.type == "red" && colourBlind_mode == "off") {
    fill(250, 100, 100)
  }
  if (this.type == "red" && colourBlind_mode == "protanopia") {
    fill("#ffd940")
  }
 if(this.type == "blue") {
    fill(100, 100, 250)
  }
  push()
    if (set_winner_UID == -1) {stroke("#21323b");}
    else{stroke("#525D64");}
    rect(this.x + width / width_divide, this.y + windowHeight / 17, this.size, this.size);
    if (mouseX > this.x+width/width_divide && mouseX < this.x+width/width_divide+this.size &&
        mouseY > this.y + windowHeight/17 && mouseY < this.y+windowHeight/17+this.size && this.type == "open") {
          push()
          if (((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && player_UID_2 > 0 && set_winner_UID == -1) {
            if (colourRelay == "red" && colourBlind_mode == "off"){fill(255, 0, 0 , 30)}
            if (colourRelay == "red" && colourBlind_mode == "protanopia"){fill("#ffefb0")}
            if (colourRelay == "blue") {fill(0, 0, 255, 30)}
          }
          else if (player_UID_2 > 0 && set_winner_UID == -1){fill(0, 0, 0, 20)}
          rect(this.x + width / width_divide, this.y + windowHeight / 17, this.size, this.size);
          pop()
        }
pop()
push()
noStroke()
    textFont(font);
    textSize(20);
    fill("#21323b");
    if (set_winning_array.includes(this.id)) {
      fill(220, 220, 0)
    }
    if (set_winning_array.includes(this.id) && colourRelay == "red" && colourBlind_mode == "protanopia") {
      fill("black")
    }
    // fill("white")
    textAlign(CENTER)
    text(this.id, this.x + width / width_divide + this.size/2, this.y + windowHeight / 17 + this.size / 2+7.5);
    pop()

  if (set_winner_UID != -1 && !set_winning_array.includes(this.id)) {
    push()
    stroke("#5B656A");
    fill(180, 180, 180, 150)
    rect(this.x + width / width_divide, this.y + windowHeight / 17, this.size, this.size);
    pop()
  }
  }
}

function preload() {
  font = loadFont("RobotoMono-LightItalic.ttf");
}

function setup() {
  createCanvas(windowWidth, windowHeight);
  tileSize = windowHeight / 17;  
  let SESSION_ServerLink = <?php echo $_SESSION["CurrentGID_Code"];?>;
  let idCounter = 0;
  for (let row = 0; row < gridSize; row++) {
    for (let col = 0; col < gridSize; col++) {
      let x = col * tileSize;
      let y = row * tileSize;
      grid.push(new Square(x, y, tileSize, "disabled", idCounter));
      idCounter++;
    }
  }
  if(SESSION_ServerLink != 0) {
      fetchData();
      setInterval(fetchData, 500);
  } 
      frameRate(30);
}

function touchStarted() {
  mouseClicked()
}
function mouseClicked() {
  if (((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && player_UID_2 > 0 && set_winner_UID == -1) {
      localSelected_OpenPosID = 0
  for (let i = 0; i < 224; i++) {
        if (mouseX > grid[i].x+width/width_divide && mouseX < grid[i].x+width/width_divide+grid[i].size &&
        mouseY > grid[i].y + windowHeight/17 && mouseY < grid[i].y+windowHeight/17+grid[i].size && grid[i].type == "open") {
      grid[i].type = colourRelay;
      turn_colour_ghost = colourRelay;
      localSelected_OpenPosID = i;
      checklocation(i);
      }
    }
  }
}

function checklocation(base) {
  if (((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && player_UID_2 > 0 && set_winner_UID == -1) {
  check_lineX = []; check_lineY = []; check_linePD = []; check_lineND = [];
  for (let check_PositionX_negative = 1; check_PositionX_negative<5;) { if(base-1 >= 0 && base-check_PositionX_negative%15 != 14 && grid[base].type == grid[base-check_PositionX_negative].type) {check_lineX.push(grid[base-check_PositionX_negative].id);check_PositionX_negative++;} else{check_PositionX_negative=16} } 
  for (let check_PositionX_positive = 1; check_PositionX_positive<5;) { if(base+1 <= 224 && base+check_PositionX_positive%15 != 0 && grid[base].type == grid[base+check_PositionX_positive].type) {check_lineX.push(grid[base+check_PositionX_positive].id);check_PositionX_positive++;} else{check_PositionX_positive=16} }
  for (let check_PositionY_negative = 1; check_PositionY_negative<5;) { if(base-15 >= 0 && grid[base].type == grid[base-(15*check_PositionY_negative)].type) {check_lineY.push(grid[base-(15*check_PositionY_negative)].id);check_PositionY_negative++;} else{check_PositionY_negative=16} }
  for (let check_PositionY_positive = 1; check_PositionY_positive<5;) { if(base+15 <= 224 && grid[base].type == grid[base+(15*check_PositionY_positive)].type) {check_lineY.push(grid[base+(15*check_PositionY_positive)].id);check_PositionY_positive++;} else{check_PositionY_positive=16} }
  for (let check_PositionND_negative = 1; check_PositionND_negative<5;) { if((base-(16*check_PositionND_negative))%15 != 14 && base-(16*check_PositionND_negative) >= 0 && grid[base].type == grid[base-(16*check_PositionND_negative)].type) {check_lineND.push(grid[base-(16*check_PositionND_negative)].id);check_PositionND_negative++;} else{check_PositionND_negative=16} }
  for (let check_PositionND_positive = 1; check_PositionND_positive<5;) { if((base+(16*check_PositionND_positive))%15 != 0 && base+(16*check_PositionND_positive) <= 224 && grid[base].type == grid[base+(16*check_PositionND_positive)].type) {check_lineND.push(grid[base+(16*check_PositionND_positive)].id);check_PositionND_positive++;} else{check_PositionND_positive=16} }
  for (let check_PositionPD_negative = 1; check_PositionPD_negative<5;) { if((base-(14*check_PositionPD_negative))%15 != 0 && base-(14*check_PositionPD_negative) >= 0 && grid[base].type == grid[base-(14*check_PositionPD_negative)].type) {check_linePD.push(grid[base-(14*check_PositionPD_negative)].id);check_PositionPD_negative++;} else{check_PositionPD_negative=16} }
  for (let check_PositionPD_positive = 1; check_PositionPD_positive<5;) { if((base+(14*check_PositionPD_positive))%15 != 14 && base+(14*check_PositionPD_positive) <= 224 && grid[base].type == grid[base+(14*check_PositionPD_positive)].type) {check_linePD.push(grid[base+(14*check_PositionPD_positive)].id);check_PositionPD_positive++;} else{check_PositionPD_positive=16} }
  if (check_lineX.length>=4) {location.href = "playtimestampstop.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_lineX[0]+"&&Check2="+check_lineX[1]+"&&Check3="+check_lineX[2]+"&&Check4="+check_lineX[3]+"&&TurnColour="+turn_colour_ghost; }
  if (check_lineY.length>=4) {location.href = "playtimestampstop.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_lineY[0]+"&&Check2="+check_lineY[1]+"&&Check3="+check_lineY[2]+"&&Check4="+check_lineY[3]+"&&TurnColour="+turn_colour_ghost; }
  if (check_lineND.length>=4) {location.href = "playtimestampstop.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_lineND[0]+"&&Check2="+check_lineND[1]+"&&Check3="+check_lineND[2]+"&&Check4="+check_lineND[3]+"&&TurnColour="+turn_colour_ghost; }
  if (check_linePD.length>=4) {location.href = "playtimestampstop.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_linePD[0]+"&&Check2="+check_linePD[1]+"&&Check3="+check_linePD[2]+"&&Check4="+check_linePD[3]+"&&TurnColour="+turn_colour_ghost; }
  if (check_lineX.length<4 && check_lineY.length<4 && check_lineND.length<4 && check_linePD.length<4) {location.href = "playtimestampstop.php?Locked="+localSelected_OpenPosID+"&&GridUpdate="+turn_colour_ghost;}
  }
}

function draw() {
  background("#283740");
textFont(font);
    if (set_winner_UID != -1) {
    push()
    fill(180, 180, 180, 100)
    rect(0, 0, windowWidth, windowHeight)
    pop()
  }

  if (Game_ID_code > 0) {
  for (let square of grid) {
    square.draw();
  }
  push()
      textFont(font);
      push()
      textAlign(LEFT)
      fill("#394a55")
      text("L#"+local_UID+":P#"+player_UID_1+"?"+player_UID_2+":G#"+Game_ID_code+":WU#"+set_winner_UID+":§"+(((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && player_UID_2 > 0 && set_winner_UID == -1), width/85, height/50)
      pop()
      textAlign(CENTER)
    textSize(20);

  translate(4.35*width/5, 0)
  fill("white")
  text("P1["+player_UID_1_usrn+"]", 0, 150)
  push()
  if (player_UID_2_usrn == "ƒ") {
   fill(185, 20, 20);
   text("P2[% No Player %]", 0, 175);
  }
  else { text("P1["+player_UID_2_usrn+"]", 0, 175); }
  pop()
  text("Game Code: "+Game_ID_code, 0, 225)
  if (turn_id_current_ursn != "ƒ")
  text("It's "+turn_id_current_ursn+"'s Turn", 0, 250)
    if(!((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && (local_UID == player_UID_1 || local_UID == player_UID_2) && player_UID_2 > 0 && set_winner_UID == -1) {
    push()
    translate(10,-height/4.1)
    push()
    textSize(17);
    text("Waiting for Opponent", -width/30, 287.5) 
    pop()
    for (let circle_i = 0; circle_i < 3; circle_i++) {
    let active = constrain(frameCount % 60 - circle_i * 15, 0, 20);
    let progress = map(active, 0, 20, 0, PI);
    push()
    noStroke()
    fill(255, sin(progress) * 255);
    circle(70 +4*circle_i/5 * 30, 6.1*height/17 -sin(progress) * 15, 5);
    pop()
  }
    pop()
  }
text("Side Count:"+sides_display_Array.length+" ("+parseFloat((2/sides_display_Array.length*100).toFixed(2))+"%)", 0, 300)
if (P5red_used_Array2[P5red_used_Array2.length-1]) {
text("Last Red:"+P5red_used_Array2[P5red_used_Array2.length-1], 0, 325)
let move_shift = 25;
}
else {let move_shift = 0;}
if (P5blue_used_Array2[P5blue_used_Array2.length-1])
text("Last Blue:"+P5blue_used_Array2[P5blue_used_Array2.length-1], 0, 350+move_shift)
// text(set_winning_array, 0, 375) 
text("Connect At: "+en0_redirect_display, -width/2.8, 30);
pop()

  if (set_winner_UID != -1) {
  push()
  textFont(font);
  fill("yellow");
  textAlign(CENTER)
    if (win_splash_random != 1) {
    textSize(70);
    if (player_UID_1 == set_winner_UID) { text(player_UID_1_usrn+" Wins!!", width/2, height/5); }
    else if (player_UID_2 == set_winner_UID) { text(player_UID_2_usrn+" Wins!!", width/2, height/5); }
  }
    if (win_splash_random == 1) {
      textSize(50);
      if (player_UID_1 == set_winner_UID) { text("Skill? Luck? Who Cares,", width/2, height/6); text(player_UID_1_usrn+" Wins!", width/2, height/4); }
      else if (player_UID_2 == set_winner_UID) { text("Skill? Luck? Who Cares,", width/2, height/6); text(player_UID_2_usrn+" Wins!", width/2, height/4); }
    }
    pop()
  }
}
}

    </script>
<div class="sidebar_fixed">LUCK Beta 5.4.[29/4]<br><br>

        <?php
echo "<script>";
echo "function myFunction() {";
echo "navigator.clipboard.writeText('http://".strval($ip)."/LUCK/playtimestampstop.php?ServerLink=".strval($_SESSION['CurrentGID_Code'])."')";
echo "}";
echo "</script>";

$user_found = R::findAll('user');
$verifiedCheck_array = [];
foreach($user_found as $row) {
      if ($row->accountlevel == 'admin') {
    array_push($verifiedCheck_array, $row->id);
    }
    if (in_array($_SESSION["id"], $verifiedCheck_array) && $_SESSION["id"] == $row->id) {
        echo "<a href=\"users.php\">Admin Panel</a><br>";
    }
    if ($row->banned != "true") { 
      echo "<div class=\"sidebar_namePlate\">";
      echo "<a id=\"right_Sidebar_align\">".$row->username."</a>";
      if (in_array($row->id, $verifiedCheck_array)) {echo "<img src=\"verified-8.svg\">";}
      echo "<br>";
      echo "</div>";
    }
  }

  echo "<div class=\"sidebar_namePlate\">";
  echo "</div>";
?>
        <a href="logout.php">logout</a><br>
        <a href="playtimestampstop.php?protanopiaToggle=true">Protanopia</a><br>
<br>
<div class="sidebar_tabs">
<a>Game Settings:</a><br>

</div>
<?php
// echo $_SESSION["CurrentGID_Code"];

    $userUpdatepppp = R::load("save", $viewingCode);
    if ($_SESSION["CurrentGID_Code"] != 0) {
      echo "<a href=\"playtimestampstop.php?reset=true\">reset/join match</a><br>";
      if ((intval($_SESSION["id"]) != json_decode($userUpdatepppp->uID_1)[0] && intval($_SESSION["id"]) != json_decode($userUpdatepppp->uID_2)[0])) { echo "<a href=\"playtimestampstop.php?ServerLink=0\">Return to lobby</a><br>"; }
      }
    if (intval($_SESSION["id"]) == json_decode($userUpdatepppp->uID_1)[0] || intval($_SESSION["id"]) == json_decode($userUpdatepppp->uID_2)[0]){ 
      echo "<a href=\"playtimestampstop.php?match_destroy=true\">Close Match</a>";
      echo "<br><br><input type=\"button\" value=\"Copy Game Link\" onClick=\"return myFunction()\">";
    }
    else {
      echo "<a href=\"playtimestampstop.php?newServerLink=clean\">Create fresh Match</a>";
    }
?>
  </div>
  <!--html game overlay code start -->
  <script>
    function joinCode_entered() { 
      window.location.assign("/LUCK/playtimestampstop.php?reset=true&&ServerLink="+document.getElementById("join_code_input").value); 
    }
    function newMatch_Requested() { 
      window.location.assign("/LUCK/playtimestampstop.php?newServerLink=clean"); 
    }
    function ChangeToLocalMatch() { 
      window.location.assign("/LUCK/playtimestampstop.php?reset=true"); 
    }
  </script>
  <?php
  //game overlay buttons start
  $match_overlay_dataPull = R::load("save", $viewingCode);

  // if ($match_overlay_dataPull->u_id_2 == null && $_SESSION["CurrentGID_Code"] != 0) {
  //   echo "<div id=\"PreMatch_overlay\">";
  //   echo "<input type=\"button\" id=\"PreMatch_Requests\" value=\"Play as Local Device\" onclick=\"return ChangeToLocalMatch()\"></input>";  
  //   echo "</div>";
  // }
  // if (json_decode($match_overlay_dataPull->winner_UID)[0] >= 1 && json_decode($match_overlay_dataPull->winner_UID) != null) {
  // echo "<div id=\"PreMatch_overlay\">";
  // echo "<input type=\"button\" id=\"PreMatch_Requests222\" onclick=\"return ChangeToLocalMatch()\"></input>";  
  // echo "</div>";
  // }

  //lobby code start
  if ($_SESSION["CurrentGID_Code"] == 0) {
    echo "<div id=\"lobby_overlay_html\">";
  echo "<input type=\"button\" id=\"testing_newMatch_button\" value=\"Create Match\" onclick=\"return newMatch_Requested()\"></input>";  
  echo "<input id=\"join_code_input\" type=\"number\" placeholder=\"Enter Game Code\" onkeydown=\"if (event.keyCode == 13) { joinCode_entered(); return false }\" onkeypress=\"return event.charCode >= 48 && event.charCode <= 57\" min=\"0\" \"> </input>";

  echo "<div class=\"user_scoreCard_Lobbydisplay\">";
    $User_LobbyScore = R::load("save".$_SESSION["id"], 1);
    echo "<h1>".$_SESSION["username"]."'s Stats:</h1>";
    echo "<h2>Matches Played: ".$User_LobbyScore->save2."</h2>";
    echo "<h2>Matches Won: ".$User_LobbyScore->save3."</h2>";
    echo "<h2>Win Rate: "; if ($User_LobbyScore->save2 > 0) {echo round((intval($User_LobbyScore->save3)/intval($User_LobbyScore->save2))*100, 1)."%"; } else {echo "No Data";} echo "</h2>";
    echo "<h2>Win Streak: ".$User_LobbyScore->save4."</h2>";
    echo "<h2>Max Streak: "; if (intval($User_LobbyScore->save5) > 0) { echo $User_LobbyScore->save5; } else { echo "No Data"; } if (!isset($User_LobbyScore->save5)) { echo "Error:OOD-stat";} echo "</h2>";
    echo "</div>";
  echo "</div>";
  }
  ?>
</body>
</html>