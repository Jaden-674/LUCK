<?php
session_start();
require("rb-sqlite.php");
R::setup("sqlite:Saved_Info.db");

if (isset($_SESSION["id"])) {
$bannedCheck = R::load("user", $_SESSION["id"]);
if ($bannedCheck->banned == "true") {
    header("location: login.php?id=error4");
    exit;
  }
}

if (isset($_SESSION["CurrentGID_Code"])) { $viewingCode = $_SESSION["CurrentGID_Code"]; }
if (isset($_SESSION["CurrentGID_Code"]) && $_SESSION["CurrentGID_Code"] == 0 && $_SESSION["local_totalMoves"] > 0) {  $_SESSION["local_totalMoves"] = 0; }

//if in match already
$currentMatches_CheckArray = [];
$findall_currentMatches = R::findAll("save");
foreach ($findall_currentMatches as $row) {
  if (intval($_SESSION["id"]) == intval(json_decode($row->u_id_1)[0]) || intval($_SESSION["id"]) == intval(json_decode($row->u_id_2)[0])) {
    array_push($currentMatches_CheckArray, intval($row->id));
  } 
}
if (intval(count($currentMatches_CheckArray)) != 0 && isset($_GET["ServerLink"]) && $_GET["ServerLink"] != $currentMatches_CheckArray[0]){
  header("location: play.php?ServerLink=".$currentMatches_CheckArray[0]);
  exit;
}
//server request GID change valid
if (isset($_GET["ServerLink"]) && (R::findOne('save', 'id = ?', [ $_GET["ServerLink"] ]) != null || $_GET["ServerLink"] == 0)) {
  $viewingCode = $_GET["ServerLink"]; 
  $_SESSION["CurrentGID_Code"] = $_GET["ServerLink"];
  header("location: play.php");
}
//server request GID chnage invalid
else if (isset($_GET["ServerLink"]) && R::findOne('save', 'id = ?', [ $_GET["ServerLink"] ]) == null)  {
  $viewingCode = 0;
  $_SESSION["CurrentGID_Code"] = 0;
  header("location: play.php?error=10&&invalid=".$_GET["ServerLink"]);
  exit;
}
//failsafe for dync 
if (R::findOne('save', 'id = ?', [ $_SESSION["CurrentGID_Code"] ]) == null && $_SESSION["CurrentGID_Code"] != 0) {
  $viewingCode = 0;
  $_SESSION["CurrentGID_Code"] = 0;
  header("location: play.php?error=11");
  exit;
} 

//Json package 
if($_SESSION["CurrentGID_Code"] != 0) {
  $item = R::load("save", $viewingCode);
  //Large
  $fetch_response_L = ["G_ID_code" => [], "d1" => [], "d1_side" => [],"d2" => [],"d3" => [], "currentColour" => [], 
  "playerA" => [], "playerB" => [], "turn_id" => [], "closedWinner_UID" => [], "game_presetVal" => [], "total_movesSync" => []];
  $fetch_response_L["G_ID_code"] = $item->id;
  $decodedD1 = json_decode($item->d1, true);
  $decodedD1_side = json_decode($item->d1_side, true);
  $decodedD2 = json_decode($item->d2, true);
  $decodedD3 = json_decode($item->d3, true);
  if (is_array($decodedD1)) { $fetch_response_L["d1"] = array_merge($fetch_response_L["d1"], $decodedD1); }
  if (is_array($decodedD1) && $item->game_mode != 2) { $fetch_response_L["d1_side"] = array_merge($fetch_response_L["d1_side"], $decodedD1_side); }
  else if ($item->game_mode == 2 ) { $fetch_response_L["d1_side"] = json_encode([]); }
  if (is_array($decodedD2)) { $fetch_response_L["d2"] = array_merge($fetch_response_L["d2"], $decodedD2); }
  if (is_array($decodedD3)) { $fetch_response_L["d3"] = array_merge($fetch_response_L["d3"], $decodedD3); }
  $fetch_response_L["currentColour"] = $item->turn_open; 
  $fetch_response_L["playerA"] = json_decode($item->uID_1);
  $fetch_response_L["playerB"] = json_decode($item->uID_2);
  $fetch_response_L["turn_id"] = json_decode($item->turn_id);
  $fetch_response_L["closedWinner_UID"] = json_decode($item->winner_uid);
  $fetch_response_L["game_presetVal"] = $item->game_mode;
  $fetch_response_L["total_movesSync"] = $item->total_moves;
  //small
  $fetch_response_S = ["d1" => [], "d1_side" => [],"Gd_C" => [], "currentColour" => [], "turn_id" => [], "closedWinner_UID" => [], "total_movesSync" => []];
  if ($item->turn_open == "blue") { $fetch_response_S["Gd_C"] = $decodedD2[count($decodedD2)-1]; }
  if ($item->turn_open == "red") { $fetch_response_S["Gd_C"] = $decodedD3[count($decodedD3)-1]; }
  if (is_array($decodedD1)) { $fetch_response_S["d1"] = array_merge($fetch_response_S["d1"], $decodedD1); }
  if (is_array($decodedD1) && $item->game_mode != 2) { $fetch_response_S["d1_side"] = array_merge($fetch_response_S["d1_side"], $decodedD1_side); }
  $fetch_response_S["currentColour"] = $item->turn_open;
  $fetch_response_S["turn_id"] = json_decode($item->turn_id);
  $fetch_response_S["closedWinner_UID"] = json_decode($item->winner_uid);
  $fetch_response_S["total_movesSync"] = $item->total_moves;
  //check
  $check_response = ["T_M" => []];
  $check_response["T_M"] = $item->total_moves;
  //FindingPlayer
  $Find_player_response = ["playerB" => []];
  $Find_player_response["playerB"] = json_decode($item->uID_2);
}
else { $fetch_response_L = []; }
if (isset($_GET['fetch_data'])) {
  $_SESSION["local_totalMoves"] = $item->total_moves;
  if ($_GET['fetch_data'] == "Large") { echo json_encode($fetch_response_L); }
  if ($_GET['fetch_data'] == "Small") { echo json_encode($fetch_response_S); }
  exit;
}
if (isset($_GET['check_data'])) {
  echo json_encode($check_response);
  exit;
}
if (isset($_GET["FindingPlayer_data"])) {
  echo json_encode($Find_player_response);
  exit;
}
// localhost/LUCK/play.php?GridUpdate=win&&ClickBase=153&&Check1=139&&Check2=125&&Check3=111&&Check4=97&&TurnColour=blue
// localhost/LUCK/play.php?Locked=***&&GridUpdate=red
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
          $winUpdate->winner_uid = json_encode([$_SESSION["id"], json_encode($checkGroup_array), rand(0,100), time()+6]);
          $winUpdate->turn_id[0] = "[-1";
          $winUpdate->total_moves = intval($winUpdate->total_moves)+1; 
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
        $red_used_Array = json_decode($saveUpdate->d2, true) ?: []; 
        $blue_used_Array = json_decode($saveUpdate->d3, true) ?: [];
        $newOpenArray = array_values(array_merge(json_decode($saveUpdate->d1, true), $red_used_Array, $blue_used_Array));
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
        if ($saveUpdate->game_mode != 3 || $saveUpdate->game_mode != 5) {
        for ($grey_find_count = 0; $grey_find_count < count($newOpenArray); $grey_find_count++ ) {
          if (intval($newOpenArray[$grey_find_count]-1)%15 != 14 && intval($newOpenArray[$grey_find_count]-1) >= 0 && !in_array(intval($newOpenArray[$grey_find_count]-1),$newOpenArray) && !in_array(intval($newOpenArray[$grey_find_count]-1),$grey_side_Array)) { array_push($grey_side_Array, intval($newOpenArray[$grey_find_count]-1));}
          if (intval($newOpenArray[$grey_find_count]+1)%15 != 0 && intval($newOpenArray[$grey_find_count]+1) <= 224 && !in_array(intval($newOpenArray[$grey_find_count]+1),$newOpenArray) && !in_array(intval($newOpenArray[$grey_find_count]+1),$grey_side_Array)) { array_push($grey_side_Array, intval($newOpenArray[$grey_find_count]+1));}
          if (intval($newOpenArray[$grey_find_count]-15) >= 0 && !in_array(intval($newOpenArray[$grey_find_count]-15),$newOpenArray) && !in_array(intval($newOpenArray[$grey_find_count]-15),$grey_side_Array)) { array_push($grey_side_Array, intval($newOpenArray[$grey_find_count]-15));}
          if (intval($newOpenArray[$grey_find_count]+15) <= 224 && !in_array(intval($newOpenArray[$grey_find_count]+15),$newOpenArray) && !in_array(intval($newOpenArray[$grey_find_count]+15),$grey_side_Array)) { array_push($grey_side_Array, intval($newOpenArray[$grey_find_count]+15));}
        }
        sort($grey_side_Array);
      }
      if ($saveUpdate->game_mode == 3) {
        $grey_side_Array = [];
        for($GM3_counter = 0; $GM3_counter<=224; $GM3_counter++) {
          array_push($grey_side_Array, $GM3_counter);
        }
      }
      if ($saveUpdate->game_mode == 5) {
        $newOpenArray = [];
        for($GM5_counter = 0; $GM5_counter<=224; $GM5_counter++) {
          array_push($newOpenArray, $GM5_counter);
        }
      }
        sort($newOpenArray);
        $saveUpdate->d1 = json_encode(array_values(array_diff($newOpenArray, $red_used_Array, $blue_used_Array)));
        $saveUpdate->d2 = json_encode($red_used_Array); 
        $saveUpdate->d3 = json_encode($blue_used_Array);
        $saveUpdate->total_moves = intval($saveUpdate->total_moves)+1; 
        $saveUpdate->d1_side = json_encode(array_values(array_diff($grey_side_Array, $newOpenArray)));
        $saveUpdate->turnOpen = ($_GET["GridUpdate"] == "red") ? "blue" : "red";
        if ($saveUpdate->turn_id == $saveUpdate->uID_1) { $saveUpdate->turn_id = $saveUpdate->uID_2; }
        else { $saveUpdate->turn_id = $saveUpdate->uID_1; }
    }
    R::store($saveUpdate);
  }
  if ($_GET["GridUpdate"] == "red" || $_GET["GridUpdate"] == "blue") {
  $item = R::load("save", $viewingCode);
  $GridUpdate_response = ["d1" => [], "d1_side" => [], "currentColour" => [], "turn_id" => [], "T_M" => []];
  $GridUpdate_response["d1"] = json_decode($item->d1);
  $GridUpdate_response["d1_side"] = json_decode($item->d1_side);
  $GridUpdate_response["currentColour"] = $item->turn_open;
  $GridUpdate_response["turn_id"] = json_decode($item->turn_id);
  $GridUpdate_response["T_M"] = intval($item->total_moves);
  echo json_encode($GridUpdate_response);
  exit;
  }
  else { header("location: play.php"); }
}


//destroy match
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
  if (json_decode($matchDestroy->uID_2)[0] == null || json_decode($matchDestroy->uID_1)[0] == json_decode($matchDestroy->uID_2)[0]) { $localmatch_redirect = "true"; }
    else { $localmatch_redirect = "false"; }
    R::trash($matchDestroy);
    $_SESSION["CurrentGID_Code"] = 0;
  }
  if (json_decode($matchDestroy->winner_uid)[0] != -1) { header("location: play.php"); exit; }
  if ($localmatch_redirect == "false") { header("location: play.php?error=12.2"); } 
  else { header("location: play.php"); }
}

if (isset($_GET["SwitchGameMode"]) && $_GET["SwitchGameMode"] == true) {
  $SwitchGM = R::load("save", $viewingCode);
  $gamemodes_total = 5;
  if (isset($_SESSION["id"]) && $_SESSION["id"] == json_decode($SwitchGM->uID_1)[0] && json_decode($SwitchGM->uID_2)[0] == null) {
    if ($SwitchGM->game_mode == 3) { $SwitchGM->d1_side = json_encode([81,82,83,95,99,110,114,125,129,141,142,143]); }
    if ($SwitchGM->game_mode == 5) { $SwitchGM->d1 = json_encode([96, 97, 98, 111, 112, 113, 126, 127, 128]); }
    if ($SwitchGM->game_mode < $gamemodes_total) { $SwitchGM->game_mode++ ; } 
    else if ($SwitchGM->game_mode == $gamemodes_total){ $SwitchGM->game_mode = 1; }
    if ($SwitchGM->game_mode == 3) {
      $grey_side_Array = [];
      for($GM3_counter = 0; $GM3_counter<=224; $GM3_counter++) {
        array_push($grey_side_Array, $GM3_counter);
      }
      $SwitchGM->d1_side = json_encode(array_values(array_diff($grey_side_Array, json_decode($SwitchGM->d1))));
    }
    if ($SwitchGM->game_mode == 5) {
    $newOpenArray = [];
    for($GM5_counter = 0; $GM5_counter<=224; $GM5_counter++) {
      array_push($newOpenArray, $GM5_counter);
    }
      $SwitchGM->d1 = json_encode($newOpenArray);
    }
  }
  R::store($SwitchGM);
  header("location: play.php");
}

//create new match
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
  $createNewMatch->game_mode = 1;
  $createNewMatch->total_moves = 0;
  $_SESSION["CurrentGID_Code"] = $createNewMatch->id;
  R::store($createNewMatch);
  header("location: play.php?ServerLink=".$createNewMatch->id);
  }
  else {
    header("location: play.php?ServerLink=".$foundSessionMatches[0]);
  }
}

if (!isset($_SESSION["rotate_Leaderboard"])) {
  $_SESSION["rotate_Leaderboard"] = 1; 
  }
if (isset($_GET["rotate_Leaderboard"])){
  if ($_SESSION["rotate_Leaderboard"] >= 3) {
    $_SESSION["rotate_Leaderboard"] = 1;
  }
  else {
    $_SESSION["rotate_Leaderboard"] = $_SESSION["rotate_Leaderboard"]+1;
  }
  header("location: play.php");
}
// $_SESSION["rotate_Leaderboard"]=1;
// echo $_SESSION["rotate_Leaderboard"];

//reset match and join match if avalible
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
      if(json_decode($userUpdatereset->uID_2)[0] != json_decode($userUpdatereset->uID_1)[0] && json_decode($userUpdatereset->uID_2)[0] > 0) {
          $userLoad_UID1_scorecardMod = R::load("save".json_decode($userUpdatereset->uID_1)[0], 1);
          $userLoad_UID1_scorecardMod->save2++;
          R::store($userLoad_UID1_scorecardMod);
          $userLoad_UID2_scorecardMod = R::load("save".json_decode($userUpdatereset->uID_2)[0], 1);
          $userLoad_UID2_scorecardMod->save2++;
          R::store($userLoad_UID2_scorecardMod);
        }
      }
        if ($userUpdatereset->turn_id == $userUpdatereset->uID_1 && json_decode($userUpdatereset->winner_uid)[0] == -1 && (json_decode($userUpdatereset->uID_1)[0] == $_SESSION["id"] ||json_decode($userUpdatereset->uID_2)[0] == $_SESSION["id"])) { $userUpdatereset->turn_id = $userUpdatereset->uID_2; }
        else if (json_decode($userUpdatereset->winner_uid)[0] == -1 && (json_decode($userUpdatereset->uID_1)[0] == $_SESSION["id"] ||json_decode($userUpdatereset->uID_2)[0] == $_SESSION["id"])) {$userUpdatereset->turn_id = $userUpdatereset->uID_1; }
      }
      if (isset($_SESSION["id"]) && (($_SESSION["id"] == json_decode($userUpdatereset->uID_1)[0] && isset(json_decode($userUpdatereset->uID_1)[0])) || 
      ($_SESSION["id"] == json_decode($userUpdatereset->uID_2)[0] && isset(json_decode($userUpdatereset->uID_1)[0])))) {
        if ($userUpdatereset->game_mode != 5) $userUpdatereset->d1 = json_encode([96, 97, 98, 111, 112, 113, 126, 127, 128]);
        else if ($userUpdatereset->game_mode == 5) {
          $newOpenArray = [];
          for($GM5_counter = 0; $GM5_counter<=224; $GM5_counter++) {
            array_push($newOpenArray, $GM5_counter);
          }
            $userUpdatereset->d1 = json_encode($newOpenArray);
        }
          if ($userUpdatereset->game_mode != 3) { $userUpdatereset->d1_side = json_encode([81,82,83,95,99,110,114,125,129,141,142,143]); }
          $userUpdatereset->d2 = json_encode([]);
          $userUpdatereset->d3 = json_encode([]); 
          $userUpdatereset->turnOpen = "blue";   
          $userUpdatereset->winner_UID = json_encode([-1]);
          $userUpdatereset->total_moves = 0; 
        }
        R::store($userUpdatereset);
      header("location: play.php");
}

// colour blind toggle
if (isset($_GET["protanopiaToggle"])) {
    $userUpdatePROtoggle = R::load("save".$_SESSION["id"], 1);
    if ($userUpdatePROtoggle->save1 == "protanopia") {
    $userUpdatePROtoggle->save1 = "off";
    }
    else {$userUpdatePROtoggle->save1 = "protanopia";}
    R::store($userUpdatePROtoggle);
    header("location: play.php");
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
  header("location: play.php");    
}

// login
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
        $_SESSION["CurrentGID_Code"] = 0;
        header("location: play.php");
    }
}
}

$added_users_all = R::load('save'.$_SESSION["id"], 1);
$colourPreset_p5relay = $added_users_all->save1;
?>
<html style="background: #21323b">
<head>
    <title>LUCK/play</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="p5.min.js"></script>
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
  // remove zoom in
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


// default changing varibles
let player_UID_1 = -1;
let player_UID_2 = -1;
let turn_id_current = -1;
let Game_ID_code = "UNDEF";
let sides_counter_display = 0;
let P5red_used_Array2 = []
let P5blue_used_Array2 = []
let player_UID_1_usrn = "ƒ";
let player_UID_2_usrn = "ƒ";
let set_winner_UID = -1;
let set_winning_array = [];
let sides_display_Array = [];
let win_timeframe = 0;
let Set_GameMode = 1;
let client_totalMoves = -1;
let local_UID = <?php echo intval($_SESSION["id"]);?>;
let First_LargePacket_set = "false";

function checkData() {
  if (player_UID_2_usrn == "ƒ") {
    fetch(window.location.href + '?FindingPlayer_data')
    .then(check_response => check_response.json())
    .then(data => {
      if (data.playerB != null) {
      player_UID_2 = parseInt(data.playerB);
      if (data.playerB != null) { 
      player_UID_2_usrn = data.playerB[1]; 
      turn_id_current = player_UID_1;
      }
      if (player_UID_2_usrn == null) player_UID_2_usrn = "ƒ";
      }
    })
  }
  if ((turn_id_current != local_UID || set_winner_UID != -1) && player_UID_2_usrn != "ƒ") {
    fetch(window.location.href + '?check_data')
    .then(check_response => check_response.json())
    .then(data => {
      if (data == null) {window.location.assign("/LUCK/play.php?error=13");}
      if (data.T_M == 0 && player_UID_2_usrn == "ƒ") {
        fetchData("Large");
      }
      if (client_totalMoves <= data.T_M || First_LargePacket_set == "false") {
        fetchData("Small");
      }
    })
  }
}

  function fetchData(queryType) {
    fetch(window.location.href + '?fetch_data=' + queryType)
    .then(fetch_response => fetch_response.json())
    .then(data => {
      if (queryType == "Large") {
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
        if (data.playerA == null) {window.location.assign("/LUCK/play.php?error=12.1");}
        player_UID_1_usrn = data.playerA[1];
        if (data.playerB != null) { player_UID_2_usrn = data.playerB[1]; }
        if (player_UID_1_usrn == null) player_UID_1_usrn = "ƒ";
        if (player_UID_2_usrn == null) player_UID_2_usrn = "ƒ";
        set_winner_UID = parseInt(data.closedWinner_UID[0]);
        if (data.closedWinner_UID.length == 4) {
        set_winning_array = JSON.parse(data.closedWinner_UID[1]);
        win_timeframe = data.closedWinner_UID[3];
        win_splash_random = parseInt(data.closedWinner_UID[2]);
        }
        else { set_winning_array = ["win_array_error"]; }
        Set_GameMode = parseInt(data.game_presetVal);
        client_totalMoves = parseInt(data.total_movesSync)+1;
        for (let u = 0; u<225; u++) {
          grid[u].type = "disabled";
        }
      }
      if (queryType == "Small") {
        client_totalMoves = parseInt(data.total_movesSync)+1;
        if (data.currentColour == "blue") { P5red_used_Array2.push(data.Gd_C); }
        if (data.currentColour == "red") { P5blue_used_Array2.push(data.Gd_C); }
        colourRelay = data.currentColour;
        open_choices_Array = data.d1;
        sides_display_Array = data.d1_side;
        turn_id_current = parseInt(data.turn_id);
        if (data.turn_id == null) turn_id_current_ursn = "ƒ";
        else { turn_id_current_ursn = data.turn_id[1]; }
        set_winner_UID = parseInt(data.closedWinner_UID[0]);
        if (data.closedWinner_UID.length == 4) {
        set_winning_array = JSON.parse(data.closedWinner_UID[1]);
        win_timeframe = data.closedWinner_UID[3];
        }
        else { set_winning_array = ["win_array_error"]; }
        win_splash_random = parseInt(data.closedWinner_UID[2]);
      }   
  if ( Set_GameMode != 2 ) {
    for (let sides_counter = 0; sides_counter<sides_display_Array.length; sides_counter++) {
    grid[sides_display_Array[sides_counter]].type = "side";
  }
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
// lets for values
let grid = []
let tileSize;
let gridSize = 15
let all_sides = []
let width_divide = 4
let move_shift = 0;
let en0_redirect_display = "<?php echo $ip;?>";
let colourBlind_mode = "<?php echo $colourPreset_p5relay; ?>";
let SESSION_ServerLink = <?php echo $_SESSION["CurrentGID_Code"];?>;
let check_lineX = [];
let check_lineY = []; 
let check_linePD = []; 
let check_lineND = [];
let localbutton_active = false;
let RightSet_Button;
let overlay;
let Set_GameMode_display;
let winning_username = -1;

class Square {
  constructor(x, y, size, type, id) {
    this.x = x;
    this.y = y;
    this.size = size;
    this.type = type;
    this.id = id;
  }

  draw() {
    if (frameCount == 2 && Set_GameMode == 4) {
    this.x = round(random(1,45)) * gridSize;
    this.y = round(random(1,45)) * gridSize;
    }
    if(this.type == "disabled")
    fill("#21323b");
    if(this.type == "side") {
    fill("#6f8087")
    }
    if (this.type == "open") 
    fill(220)
   if(this.type == "red" && colourBlind_mode == "off") {
    fill(250, 100, 100)
    if (set_winner_UID == -1 && Set_GameMode == 2) 
    fill("#21323b");
  }
  if (this.type == "red" && colourBlind_mode == "protanopia") {
    fill("#ffd940")
    if (set_winner_UID == -1 && Set_GameMode == 2) 
    fill("#21323b");
  }
 if(this.type == "blue") {
    fill(100, 100, 250)
    if (set_winner_UID == -1 && Set_GameMode == 2 ) 
    fill("#21323b");
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
// if (this.type == "red" || this.type == "blue") {
// push()
// if (colourBlind_mode == "off" ) { tint(225, 225, 225, 80); }
// else { tint(25, 25, 25, 80) }
// // pixelDensity(1)
// noSmooth()
// image(imagetest, this.x + width / width_divide, this.y + windowHeight / 17)
// pop()
// }
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
  imagetest = loadImage("star-166.svg");
}

function setup() {
  createCanvas(windowWidth, windowHeight);
  tileSize = windowHeight / 17;  
  let SESSION_ServerLink_setup = <?php echo $_SESSION["CurrentGID_Code"];?>;
  let idCounter = 0;
  //create all squares
  for (let row = 0; row < gridSize; row++) {
    for (let col = 0; col < gridSize; col++) {
      let x = col * tileSize;
      let y = row * tileSize;
      grid.push(new Square(x, y, tileSize, "disabled", idCounter));
      idCounter++;
    }
  }
  //call data when needed 
  if(SESSION_ServerLink_setup != 0) {
      fetchData("Large");
      setInterval(checkData, 500);
      First_LargePacket_set = "true";
  }
  frameRate(60);

  // overlay buttons
  overlay = createDiv();
  overlay.id("PreMatch_overlay");
  RightSet_Button = createInput();
  RightSet_Button.attribute("hidden", "true");
  RightSet_Button.attribute("type", "button");
  LeftSet_Button = createInput();
  LeftSet_Button.attribute("hidden", "true");
  LeftSet_Button.attribute("type", "button");
  MidSet_Button = createInput();
  MidSet_Button.attribute("hidden", "true");
  MidSet_Button.attribute("type", "button");
  
  imagetest.resize(windowHeight / 17, windowHeight / 17)
}

function touchStarted() {
  mouseClicked()
}
function mouseClicked() {
  if (((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && player_UID_2 > 0 && set_winner_UID == -1) {
  for (let i = 0; i < 224; i++) {
        if (mouseX > grid[i].x+width/width_divide && mouseX < grid[i].x+width/width_divide+grid[i].size &&
        mouseY > grid[i].y + windowHeight/17 && mouseY < grid[i].y+windowHeight/17+grid[i].size && grid[i].type == "open") {
      grid[i].type = colourRelay;
      turn_colour_ghost = colourRelay;
      checklocation(i);
      }
    }
  }
}

function checklocation(base) {
  if (((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && player_UID_2 > 0 && set_winner_UID == -1) {
    check_lineX = []; check_lineY = []; check_linePD = []; check_lineND = [];
    for (let check_PositionX_negative = 1; check_PositionX_negative<5;) { if(base-1 >= 0 && base-check_PositionX_negative%15 != 14 && grid[base].type == grid[base-check_PositionX_negative].type) {check_lineX.push(base-check_PositionX_negative);check_PositionX_negative++;} else{check_PositionX_negative=16} } 
    for (let check_PositionX_positive = 1; check_PositionX_positive<5;) { if(base+1 <= 224 && base+check_PositionX_positive%15 != 0 && grid[base].type == grid[base+check_PositionX_positive].type) {check_lineX.push(base+check_PositionX_positive);check_PositionX_positive++;} else{check_PositionX_positive=16} }
    if (check_lineX.length>=4) {location.href = "play.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_lineX[0]+"&&Check2="+check_lineX[1]+"&&Check3="+check_lineX[2]+"&&Check4="+check_lineX[3]+"&&TurnColour="+turn_colour_ghost; }
    for (let check_PositionY_negative = 1; check_PositionY_negative<5;) { if(base-15 >= 0 && grid[base].type == grid[base-(15*check_PositionY_negative)].type) {check_lineY.push(base-(15*check_PositionY_negative));check_PositionY_negative++;} else{check_PositionY_negative=16} }
    for (let check_PositionY_positive = 1; check_PositionY_positive<5;) { if(base+15 <= 224 && grid[base].type == grid[base+(15*check_PositionY_positive)].type) {check_lineY.push(base+(15*check_PositionY_positive));check_PositionY_positive++;} else{check_PositionY_positive=16} }
    if (check_lineY.length>=4) {location.href = "play.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_lineY[0]+"&&Check2="+check_lineY[1]+"&&Check3="+check_lineY[2]+"&&Check4="+check_lineY[3]+"&&TurnColour="+turn_colour_ghost; }
    for (let check_PositionND_negative = 1; check_PositionND_negative<5;) { if((base-(16*check_PositionND_negative))%15 != 14 && base-(16*check_PositionND_negative) >= 0 && grid[base].type == grid[base-(16*check_PositionND_negative)].type) {check_lineND.push(base-(16*check_PositionND_negative));check_PositionND_negative++;} else{check_PositionND_negative=16} }
    for (let check_PositionND_positive = 1; check_PositionND_positive<5;) { if((base+(16*check_PositionND_positive))%15 != 0 && base+(16*check_PositionND_positive) <= 224 && grid[base].type == grid[base+(16*check_PositionND_positive)].type) {check_lineND.push(base+(16*check_PositionND_positive));check_PositionND_positive++;} else{check_PositionND_positive=16} }
    if (check_lineND.length>=4) {location.href = "play.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_lineND[0]+"&&Check2="+check_lineND[1]+"&&Check3="+check_lineND[2]+"&&Check4="+check_lineND[3]+"&&TurnColour="+turn_colour_ghost; }
    for (let check_PositionPD_negative = 1; check_PositionPD_negative<5;) { if((base-(14*check_PositionPD_negative))%15 != 0 && base-(14*check_PositionPD_negative) >= 0 && grid[base].type == grid[base-(14*check_PositionPD_negative)].type) {check_linePD.push(base-(14*check_PositionPD_negative));check_PositionPD_negative++;} else{check_PositionPD_negative=16} }
    for (let check_PositionPD_positive = 1; check_PositionPD_positive<5;) { if((base+(14*check_PositionPD_positive))%15 != 14 && base+(14*check_PositionPD_positive) <= 224 && grid[base].type == grid[base+(14*check_PositionPD_positive)].type) {check_linePD.push(base+(14*check_PositionPD_positive));check_PositionPD_positive++;} else{check_PositionPD_positive=16} }
    if (check_linePD.length>=4) {location.href = "play.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_linePD[0]+"&&Check2="+check_linePD[1]+"&&Check3="+check_linePD[2]+"&&Check4="+check_linePD[3]+"&&TurnColour="+turn_colour_ghost; }
    if (check_lineX.length<4 && check_lineY.length<4 && check_lineND.length<4 && check_linePD.length<4) { 
      fetch(window.location.href+"?Locked="+base+"&&GridUpdate="+turn_colour_ghost).then(clicked_response => clicked_response.json()).then(data => { 
      colourRelay = data.currentColour; 
      open_choices_Array = data.d1;
      sides_display_Array = data.d1_side;
      turn_id_current = parseInt(data.turn_id);
      client_totalMoves = parseInt(data.T_M)+1;
      if ( Set_GameMode != 2 ) {
        for (let sides_counter = 0; sides_counter<sides_display_Array.length; sides_counter++) {
          grid[sides_display_Array[sides_counter]].type = "side";
        }
      }
        for(let k2 = 0; k2<open_choices_Array.length; k2++) {
          grid[open_choices_Array[k2]].type = "open";
        }
      });
    }
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
      textSize(14)
      text("L#"+local_UID+":P#"+player_UID_1+"?"+player_UID_2+":G#"+Game_ID_code+":T#"+turn_id_current+":WU#"+set_winner_UID+":§"+(((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && player_UID_2 > 0 && set_winner_UID == -1)+":<"+client_totalMoves+">", width/85, height/50)
      pop()
      textAlign(CENTER)
    textSize(20);

  translate(4.35*width/5, 0)
  push()
  fill(100, 100, 250); 
  text("P1["+player_UID_1_usrn+"]", 0, 150)
  pop()
  push()
  if (player_UID_2_usrn == "ƒ") {
  fill("#ff8800");
  text("P2[% No Player %]", 0, 175);
  }
  else { 
    if(colourBlind_mode == "off") {
      fill(250, 100, 100)
    }
    if (colourBlind_mode == "protanopia") {
      fill("#ffd940")
    }
    text("P2["+player_UID_2_usrn+"]", 0, 175); 
  }
  pop()
    fill("white")
  text("Game Code: "+Game_ID_code, 0, 225)
  if (turn_id_current_ursn != "ƒ" && player_UID_1_usrn != player_UID_2_usrn)
  text("It's "+turn_id_current_ursn+"'s Turn", 0, 250)
    if(!((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && (local_UID == player_UID_1 || local_UID == player_UID_2) && player_UID_2 > 0 && set_winner_UID == -1) {
    push()
    translate(10,-height/4.1)
    push()
    textSize(17);
    text("Waiting for Opponent", -width/30, 287.5) 
    pop()
    for (let circle_i = 0; circle_i < 3; circle_i++) {
    let active = constrain((frameCount)/2 % 60 - circle_i * 15, 0, 20);
    let progress = map(active, 0, 20, 0, PI);
    push()
    noStroke()
    fill(255, sin(progress) * 255);
    circle(70 +4*circle_i/5 * 30, 6.1*height/17 -sin(progress) * 15, 5);
    pop()
  }
    pop()
  }
  if (Set_GameMode != 5) { text("Side Count:"+sides_display_Array.length+" ("+parseFloat((2/sides_display_Array.length*100).toFixed(2))+"%)", 0, 300); } else { text("Side Count: 0? (maybe%)" , 0, 300);}
if (P5red_used_Array2[P5red_used_Array2.length-1] && Set_GameMode != 2) {
if (colourBlind_mode == "off")
text("Last Red:"+P5red_used_Array2[P5red_used_Array2.length-1], 0, 325)
if (colourBlind_mode == "protanopia")
text("Last Yellow:"+P5red_used_Array2[P5red_used_Array2.length-1], 0, 325)
let move_shift = 25;
}
else {let move_shift = 0;}
if (P5blue_used_Array2[P5blue_used_Array2.length-1] && Set_GameMode != 2)
text("Last Blue:"+P5blue_used_Array2[P5blue_used_Array2.length-1], 0, 350+move_shift)
pop()

  if (set_winner_UID != -1) {
    if (player_UID_1 != player_UID_2) { 
      if (player_UID_1 == set_winner_UID) { winning_username = player_UID_1_usrn };
      if (player_UID_2 == set_winner_UID) { winning_username = player_UID_2_usrn };
    }
    else {
      if (colourRelay == "red" && colourBlind_mode == "protanopia") { winning_username = "Yellow" };
      if (colourRelay == "red" && colourBlind_mode == "off") { winning_username = "Red" };
      if (colourRelay == "blue") winning_username = "Blue";
    }
  push()
  textFont(font);
  fill("yellow");
  textAlign(CENTER)
    if (win_splash_random != 1) {
    
    textSize(70);
    if (player_UID_1 == set_winner_UID) { text(winning_username+" Wins!!", width/2, height/5); }
    else if (player_UID_2 == set_winner_UID) { text(winning_username+" Wins!!", width/2, height/5); }
  }
    if (win_splash_random == 1) {
      textSize(50);
      if (player_UID_1 == set_winner_UID) { text("Skill? Luck? Who Cares,", width/2, height/6); text(winning_username+" Wins!", width/2, height/4); }
      else if (player_UID_2 == set_winner_UID) { text("Skill? Luck? Who Cares,", width/2, height/6); text(winning_username+" Wins!", width/2, height/4); }
    }
    pop()
  }
}
  if(frameCount == 2 && SESSION_ServerLink > 0 && player_UID_2_usrn == "ƒ") {
    RightSet_Button.attribute("value", "Cancel Match and Exit");
    RightSet_Button.removeAttribute("hidden");
    RightSet_Button.id("rightSet_match_button");
    RightSet_Button.mousePressed(CloseMatch_Request);
    RightSet_Button.parent(overlay);

    LeftSet_Button.attribute("value", "Play as Local Device");
    LeftSet_Button.removeAttribute("hidden");
    LeftSet_Button.id("leftSet_match_button");
    LeftSet_Button.mousePressed(ChangeToLocalMatch);
    LeftSet_Button.parent(overlay);

    if (Set_GameMode == 1 ) {Set_GameMode_display = "Normal";} 
    if (Set_GameMode == 2 ) {Set_GameMode_display = "Blind";} 
    if (Set_GameMode == 3 ) {Set_GameMode_display = "GreyWave";} 
    if (Set_GameMode == 4 ) {Set_GameMode_display = "MagicRandom";} 
    if (Set_GameMode == 5 ) {Set_GameMode_display = "SandBox";} 
    MidSet_Button.attribute("value", "Current Mode: " + Set_GameMode_display);
    MidSet_Button.removeAttribute("hidden");
    MidSet_Button.id("midSet_match_button");
    MidSet_Button.mousePressed(GameModeSwitchRequest);
    MidSet_Button.parent(overlay);
    localbutton_active = "used_1";
  }

  if (localbutton_active == "used_1" && SESSION_ServerLink > 0 && player_UID_2_usrn != "ƒ") {
    RightSet_Button.attribute("hidden", "true");
    LeftSet_Button.attribute("hidden", "true");
    MidSet_Button.attribute("hidden", "true");
    localbutton_active = "used_2";
  }

  if (frameCount == 2 && !((player_UID_1 == local_UID) || (player_UID_2 == local_UID)) && player_UID_1 > 0 && player_UID_2 > 0) {
    RightSet_Button.attribute("value", "Exit To Lobby");
    RightSet_Button.removeAttribute("hidden");
    RightSet_Button.id("rightSet_match_button");
    RightSet_Button.mousePressed(ReturnHome_SPEC_Request);
    RightSet_Button.parent(overlay);
  }

  if (((player_UID_1 == local_UID) || (player_UID_2 == local_UID)) && player_UID_1 > 0 && player_UID_2 > 0) {
      if(localbutton_active != "close_displayed" && SESSION_ServerLink > 0 && set_winner_UID != -1) {
      RightSet_Button.attribute("type", "button");
      RightSet_Button.removeAttribute("hidden");
      RightSet_Button.id("rightSet_CloseDelay");
      RightSet_Button.parent(overlay);
      localbutton_active = "close_displayed";
      }
    if (win_timeframe-round((new Date()).getTime() / 1000) > 0) { close_match_Delay_text = win_timeframe-round((new Date()).getTime() / 1000); close_match_text_ghost = "Close Match In: "+close_match_Delay_text}
    else { 
      close_match_text_ghost = "Close Match"; 
      if (SESSION_ServerLink > 0 && set_winner_UID != -1)
      RightSet_Button.mousePressed(CloseMatch_Request);
      RightSet_Button.id("rightSet_match_button");
    }
    if (frameCount >= 2 && SESSION_ServerLink > 0 && set_winner_UID != -1) {
      RightSet_Button.attribute("value", close_match_text_ghost);
    }
  }
}
</script>
<?php
$user_found = R::findAll('user');
$verifiedCheck_array = [];
foreach($user_found as $row) {
  if ($row->accountlevel == 'admin') {
    array_push($verifiedCheck_array, $row->id);
  }
}
// if (in_array($_SESSION["id"], $verifiedCheck_array)) { 
echo "<div class=\"sidebar_fixed\">LUCK Beta 5.4.[29/4]<br><br>";

echo "<script>";
echo "function myFunction() {";
echo "navigator.clipboard.writeText('http://".strval($ip)."/LUCK/play.php?ServerLink=".strval($_SESSION['CurrentGID_Code'])."')";
echo "}";
echo "</script>";

foreach($user_found as $row) {
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

  echo "<a href=\"logout.php\">logout</a><br>";
  echo "<a href=\"play.php?protanopiaToggle=true\">Protanopia</a><br>";
  echo "<br>";
  if (in_array($_SESSION["id"], $verifiedCheck_array)) {
    echo "<a href=\"users.php\">Admin Panel</a><br>";
  }
  echo "<div class=\"sidebar_tabs\">";
  echo "<a>Game Settings:</a><br>";
  echo "</div>";

    $userUpdatepppp = R::load("save", $viewingCode);
    if ($_SESSION["CurrentGID_Code"] != 0) {
      echo "<a href=\"play.php?reset=true\">reset/join match</a><br>";
      if ((intval($_SESSION["id"]) != json_decode($userUpdatepppp->uID_1)[0] && intval($_SESSION["id"]) != json_decode($userUpdatepppp->uID_2)[0])) { echo "<a href=\"play.php?ServerLink=0\">Return to lobby</a><br>"; }
      }
    if (intval($_SESSION["id"]) == json_decode($userUpdatepppp->uID_1)[0] || intval($_SESSION["id"]) == json_decode($userUpdatepppp->uID_2)[0]){ 
      echo "<a href=\"play.php?match_destroy=true\">Close Match</a>";
      echo "<br><br><input type=\"button\" value=\"Copy Game Link\" onClick=\"return myFunction()\">";
    }
    else {
      echo "<a href=\"play.php?newServerLink=clean\">Create fresh Match</a>";
    }
  // }
?>
  </div>
  <!--html game overlay code start -->
  <script>
    function joinCode_entered() { 
      window.location.assign("/LUCK/play.php?reset=true&&ServerLink="+document.getElementById("join_code_input").value); 
    }
    function newMatch_Requested() { 
      window.location.assign("/LUCK/play.php?newServerLink=clean"); 
    }
    function ChangeToLocalMatch() { 
      window.location.assign("/LUCK/play.php?reset=true"); 
    }
    function CloseMatch_Request() { 
      window.location.assign("/LUCK/play.php?match_destroy=true"); 
    }
    function ReturnHome_SPEC_Request() { 
      window.location.assign("/LUCK/play.php?ServerLink=0"); 
    }
    function GameModeSwitchRequest() {
      window.location.assign("/LUCK/play.php?SwitchGameMode=true")
    }
    function rotate_Leaderboard() {
      window.location.assign("/LUCK/play.php?rotate_Leaderboard=true")
    }
  </script>
  <?php
// ip splash
echo "<div id=\"en0_splashDisplay\">Connect At: ".trim(shell_exec("ipconfig getifaddr en0"))."</div>";
  //lobby code start
  $match_overlay_dataPull = R::load("save", $viewingCode);
  if ($_SESSION["CurrentGID_Code"] == 0) {
    echo "<div id=\"titleSplash_text\"> LUCK </div>";
    echo "<div id=\"lobby_overlay_html\">";
  echo "<input id=\"join_code_input\" type=\"number\" placeholder=\"Enter Game Code\" onkeydown=\"if (event.keyCode == 13) { joinCode_entered(); return false }\" onkeypress=\"return event.charCode >= 48 && event.charCode <= 57\" min=\"0\"> </input>";
  echo "<input type=\"button\" id=\"CreateMatch_button\" hidden=\"false\" disabled=\"false\" value=\"Create Match\" onclick=\"return newMatch_Requested()\"></input>";
  echo "<input type=\"button\" id=\"JoinCode_button\" hidden=\"true\" disabled=\"true\" value=\"Join Match\" onclick=\"return joinCode_entered()\"></input>";

  echo "<div class=\"user_scoreCard_Lobbydisplay\">";
    $User_LobbyScore = R::load("save".$_SESSION["id"], 1);
    echo "<h1>".$_SESSION["username"]."'s Stats:</h1>";
    echo "<h2>Matches Played: ".$User_LobbyScore->save2."</h2>";
    echo "<h2>Matches Won: ".$User_LobbyScore->save3."</h2>";
    echo "<h2>Win Rate: "; if ($User_LobbyScore->save2 > 0) {echo round((intval($User_LobbyScore->save3)/intval($User_LobbyScore->save2))*100, 1)."%"; } else {echo "No Data";} echo "</h2>";
    echo "<h2>Win Streak: ".$User_LobbyScore->save4."</h2>";
    echo "<h2>Max Streak: "; if (intval($User_LobbyScore->save5) > 0) { echo $User_LobbyScore->save5; } else { echo "No Data"; } if (!isset($User_LobbyScore->save5)) { echo "Error:OOD-stat";} echo "</h2>";
    echo "</div>";

  echo "<div class=\"leaderBoard_Lobbydisplay\" >";
  $leaderboard_Aplicants = R::findAll("user");
  $leaderB_usersArray = []; $leaderB_statArray = []; $leaderB_globalArray = [];
  if ($_SESSION["rotate_Leaderboard"] == 1) { $Leaderboard_pref = "save3"; }
  if ($_SESSION["rotate_Leaderboard"] == 2) { $Leaderboard_pref = "save2"; }
  if ($_SESSION["rotate_Leaderboard"] == 3) { $Leaderboard_pref = "save5"; }
  foreach($leaderboard_Aplicants as $row) {
    $User_statpull = R::load("save".$row->id, 1);
    if ($row->banned == "false" && intval($User_statpull->$Leaderboard_pref) != 0) {
    array_push($leaderB_usersArray, $User_statpull->name);
    if ($_SESSION["rotate_Leaderboard"] != 2) { array_push($leaderB_statArray, $User_statpull->$Leaderboard_pref); }
    else { array_push($leaderB_statArray, round((intval($User_statpull->save3)/intval($User_statpull->save2))*100, 1)); }
    }
  }
  $leaderB_globalArray = array_combine($leaderB_usersArray, $leaderB_statArray);
  arsort($leaderB_globalArray);
  echo "<h1 onclick=\"return rotate_Leaderboard()\">";
  if ($_SESSION["rotate_Leaderboard"] == 1) { echo "Wins "; }; 
  if ($_SESSION["rotate_Leaderboard"] == 2) { echo "Win% "; }; 
  if ($_SESSION["rotate_Leaderboard"] == 3) { echo "<span>Max Streak</span> "; }; 
  echo "<span>Leader Board</span></h1>";
  for ($lb_display_counter = 0; $lb_display_counter<count($leaderB_globalArray); $lb_display_counter++) {
    if (isset(array_values($leaderB_globalArray)[$lb_display_counter])) {
      echo "<h2";
      if (array_keys($leaderB_globalArray)[$lb_display_counter] == $_SESSION["username"]) {echo " id=\"leaderboard_localBG\" ";} 
      echo ">";
      if($lb_display_counter<3){ echo "<span"; }
      if( $lb_display_counter==0 ){ echo " id=\"goldtext_LeaderBoard\""; }
      if( $lb_display_counter==1 ){ echo " style=\"color:silver\""; }
      if( $lb_display_counter==2 ){ echo " style=\"color:#B87333\""; }  
      if($lb_display_counter<3){ echo ">"; } 
      echo "#".($lb_display_counter+1).": ".array_keys($leaderB_globalArray)[$lb_display_counter]; 
      if($lb_display_counter<3){ echo "</span>"; } 
      echo " <span style=\"color:rgb(255, 183, 0)\">(".array_values($leaderB_globalArray)[$lb_display_counter];
      if ($_SESSION["rotate_Leaderboard"] == 1) { echo "👑"; }; 
      if ($_SESSION["rotate_Leaderboard"] == 2) { echo "%"; }; 
      if ($_SESSION["rotate_Leaderboard"] == 3) { echo "🔥"; }; 
      echo ")</span></h2>";
    }
  }
  echo "</div>";

  echo "</div>";
  }
  //errorbanners
  if (isset($_GET["invalid"]) && isset($_GET["error"]) && $_GET["error"] == 10) {
    echo "<div id=\"errorTab\">Game Code: \"".$_GET["invalid"]."\" Cannot Be Found</div>";
  }
  if (isset($_GET["error"]) && $_GET["error"] == 11) {
    echo "<div id=\"errorTab\">Something Went Wrong: ServerDync</div>";
  }
  if (isset($_GET["error"]) && $_GET["error"] == 12.1) {
    echo "<div id=\"errorTab\">Opponent Forfitted - Win Saved</div>";
  }
  if (isset($_GET["error"]) && $_GET["error"] == 12.2) {
    echo "<div id=\"errorTab\">Match Forfitted - Loss Saved</div>";
  }
  if (isset($_GET["error"]) && $_GET["error"] == 13) {
    echo "<div id=\"errorTab\">Match Spectating was Canceled</div>";
  }
?>
<script> 
const input = document.getElementById("join_code_input");
const buttonEmpty = document.getElementById("CreateMatch_button");
const buttonFilled = document.getElementById("JoinCode_button");
window.onload = function() {
  buttonEmpty.hidden = false;
  buttonEmpty.disabled = false;
};
input.addEventListener("input", () => {
  if (input.value.trim() !== "") {
    buttonEmpty.hidden = true;
    buttonFilled.hidden = false;
    buttonEmpty.disabled = true;
    buttonFilled.disabled = false;
  } else {
    buttonEmpty.hidden = false;
    buttonFilled.hidden = true;
    buttonEmpty.disabled = false;
    buttonFilled.disabled = true;
  }
});
</script>
</body>
</html>