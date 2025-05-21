<?php
session_start();
require("rb-sqlite.php");
R::setup("sqlite:Saved_Info.db");
if (isset($_SESSION["CurrentGID_Code"])) {$viewingCode = $_SESSION["CurrentGID_Code"];}
if (isset($_GET["ServerLink_GID"])) {
  $viewingCode = $_GET["ServerLink_GID"]; 
  $_SESSION["CurrentGID_Code"] = $_GET["ServerLink_GID"];
  header("location: main.php");
}

$item = R::load("save", $viewingCode);
$response = ["G_ID_code" => [], "d1" => [],"d2" => [],"d3" => [], "currentColour" => [], "playerA" => [], "playerB" => [], "turn_id" => [], "closedWinner_UID" => []];
    $response["G_ID_code"] = $item->id;
    $decodedD1 = json_decode($item->d1, true);
    $decodedD2 = json_decode($item->d2, true);
    $decodedD3 = json_decode($item->d3, true);
    if (is_array($decodedD1)) { $response["d1"] = array_merge($response["d1"], $decodedD1); }
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
      

if (isset($_GET["reset"])){
      $userUpdatereset = R::load("save", $viewingCode);
  if (isset($_SESSION["id"]) && (($_SESSION["id"] == json_decode($userUpdatereset->uID_1)[0] && isset(json_decode($userUpdatereset->uID_1)[0])) || 
  ($_SESSION["id"] == json_decode($userUpdatereset->uID_2)[0] && isset(json_decode($userUpdatereset->uID_1)[0])))) {
      $userUpdatereset->d1 = json_encode([96, 97, 98, 111, 112, 113, 126, 127, 128]);
      $userUpdatereset->d2 = json_encode([]);
      $userUpdatereset->d3 = json_encode([]);
      $userUpdatereset->turnOpen = "blue";   
      $userUpdatereset->winner_UID = json_encode([-1]);
      }
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
      }
        if ($userUpdatereset->turn_id == $userUpdatereset->uID_1) {$userUpdatereset->turn_id = $userUpdatereset->uID_2;}
        else {$userUpdatereset->turn_id = $userUpdatereset->uID_1;}
      }
      if ($_GET["reset"] == "trueMAJOR" && ((isset($_SESSION["id"]) && ($_SESSION["id"] == json_decode($userUpdatereset->uID_1)[0])) || $_SESSION["id"] == 1))  {
      $userUpdatereset->uID_1 = json_encode($uID_1_array);
      $userUpdatereset->uID_2 = json_encode($uID_2_array);
      $userUpdatereset->turn_id = json_encode([]);
      }
        R::store($userUpdatereset);
      header("location: main.php");
}


// http://localhost/phpGameTest/main.php?GridUpdate=win&&ClickBase=153&&Check1=139&&Check2=125&&Check3=111&&Check4=97&&TurnColour=blue
if (isset($_GET["GridUpdate"])) {
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
        for ($x = 0; $x <= 3; $x++) {
          if ($_GET["TurnColour"] == "red" && in_array($checkGroup_array[$x], $red_used_Array)) { array_push($commonDiff_array, ($checkGroup_array[$x+1]-$checkGroup_array[$x])); }
          if ($_GET["TurnColour"] == "blue" && in_array($checkGroup_array[$x], $blue_used_Array)) { array_push($commonDiff_array, ($checkGroup_array[$x+1]-$checkGroup_array[$x])); }
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
          $winUpdate->winner_uid = json_encode([$_SESSION["id"], json_encode($checkGroup_array), json_encode(rand(0,100))]);
          $winUpdate->turn_id[0] = "[-1";
        }
      }
    }
    R::store($winUpdate);
    }

  if ($_GET["GridUpdate"] == "red" || $_GET["GridUpdate"] == "blue") {
    $saveUpdate = R::load("save", $viewingCode);
      if (isset($_SESSION["id"]) && $_SESSION["id"] == json_decode($saveUpdate->turn_id)[0] && (($_SESSION["id"] == json_decode($saveUpdate->uID_1)[0] && isset(json_decode($saveUpdate->uID_1)[0])) || ($_SESSION["id"] == json_decode($saveUpdate->uID_2)[0] && isset(json_decode($saveUpdate->uID_1)[0])))) {
        $newOpenArray = json_decode($saveUpdate->d1, true);
        if(isset($_GET["OpenA0"]) && isset($_GET["OpenA1"])) {
        $red_used_Array = json_decode($saveUpdate->d2, true) ?: []; 
        $blue_used_Array = json_decode($saveUpdate->d3, true) ?: [];
        if(in_array(intval($_GET["Locked"]), $newOpenArray) && !in_array(intval($_GET["Locked"]), $red_used_Array) && !in_array(intval($_GET["Locked"]), $blue_used_Array))
        if ($_GET["GridUpdate"] == "red") {
            array_push($red_used_Array, $_GET["Locked"]);
        } else if ($_GET["GridUpdate"] == "blue") {
            array_push($blue_used_Array, $_GET["Locked"]);
        }
        array_push($newOpenArray, $_GET["OpenA0"], $_GET["OpenA1"]);
        }  
        $saveUpdate->d1 = json_encode($newOpenArray);
        $saveUpdate->d2 = json_encode($red_used_Array); 
        $saveUpdate->d3 = json_encode($blue_used_Array);
        $saveUpdate->turnOpen = ($_GET["GridUpdate"] == "red") ? "blue" : "red";
        if ($saveUpdate->turn_id == $saveUpdate->uID_1) {
        $saveUpdate->turn_id = $saveUpdate->uID_2;
        }
        else {
          $saveUpdate->turn_id = $saveUpdate->uID_1;
        }
      }
        R::store($saveUpdate);
    }
    header("location: main.php");
}

if (isset($_GET["protanopiaToggle"])) {
    $userUpdatePROtoggle = R::load("save".$_SESSION["id"], 1);
    if ($userUpdatePROtoggle->save1 == "protanopia") {
    $userUpdatePROtoggle->save1 = "off";
    }
    else {$userUpdatePROtoggle->save1 = "protanopia";}
    R::store($userUpdatePROtoggle);
    header("location: main.php");

  }



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
        $save_personal->save2 = "#888888";
        $save_personal->save3 = "limegreen";
        $save_personal->save4 = "#00c7d9";
    R::store($save_personal);
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
        header("location: main.php");
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

?>
<html style="background: #21323b">
<head>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
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
            z-index: 10;
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

  function fetchData() {
    fetch(window.location.href + '?fetch_data=1')
    .then(response => response.json())
    .then(data => {
        Game_ID_code = data.G_ID_code; 
        colorCookie2= Array.isArray(data.d1) ? data.d1 : [];
        P5red_used_Array2 = Array.isArray(data.d2) ? data.d2 : [];
        P5blue_used_Array2 = Array.isArray(data.d3) ? data.d3 : [];
        colourRelay = data.currentColour;
        player_UID_1 = parseInt(data.playerA); 
        player_UID_2 = parseInt(data.playerB);
        turn_id_current = parseInt(data.turn_id);
        player_UID_1_usrn = data.playerA[1];
        player_UID_2_usrn = data.playerB[1];
        if (player_UID_1_usrn == null) player_UID_1_usrn = "ƒ";
        if (player_UID_2_usrn == null) player_UID_2_usrn = "ƒ";
        set_winner_UID = parseInt(data.closedWinner_UID[0]);
        if (data.closedWinner_UID.length == 3) {
        set_winning_array = JSON.parse(data.closedWinner_UID[1]);
        }
        else {
          set_winning_array = [];
        }
        win_splash_random = parseInt(data.closedWinner_UID[2]);

    for (let u = 0; u<225; u++) {
      grid[u].type = "disabled"
    }
    for(let k2 = 0; k2<colorCookie2.length; k2++) {
    grid[colorCookie2[k2]].type = "open";
        if (int(colorCookie2[k2]) % 15 !== 14 && int(colorCookie2[k2]) + 1 < 225 && grid[int(colorCookie2[k2])+1].type == "disabled") grid[int(colorCookie2[k2])+1].type = "side";
        if (int(colorCookie2[k2]) % 15 !== 0 && int(colorCookie2[k2]) - 1 >= 0 && grid[int(colorCookie2[k2])-1].type == "disabled") grid[int(colorCookie2[k2])-1].type = "side";
        if (int(colorCookie2[k2]) - 15 >= 0 && grid[int(colorCookie2[k2])-15].type == "disabled") grid[int(colorCookie2[k2])-15].type = "side"; 
        if (int(colorCookie2[k2]) + 15 < 225 && grid[int(colorCookie2[k2])+15].type == "disabled") grid[int(colorCookie2[k2])+15].type = "side";  
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
  fill("#021a21")
  fill("#6f8087")
  }
    if (this.type == "true")
    fill("grey")
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

  if(this.type == "yellow") {
    fill("yellow")
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
  let idCounter = 0;
  for (let row = 0; row < gridSize; row++) {
    for (let col = 0; col < gridSize; col++) {
      let x = col * tileSize;
      let y = row * tileSize;
      grid.push(new Square(x, y, tileSize, "disabled", idCounter));
      idCounter++;
    }
  }
      fetchData();
      setInterval(fetchData, 450); 
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
  for (let check_PositionX_negative = 1; check_PositionX_negative<5;) { if(grid[base-check_PositionX_negative].id%15 != 14 && grid[base].type == grid[base-check_PositionX_negative].type) {check_lineX.push(grid[base-check_PositionX_negative].id);check_PositionX_negative++;} else{check_PositionX_negative=16} } 
  for (let check_PositionX_positive = 1; check_PositionX_positive<5;) { if(grid[base+check_PositionX_positive].id%15 != 0 && grid[base].type == grid[base+check_PositionX_positive].type) {check_lineX.push(grid[base+check_PositionX_positive].id);check_PositionX_positive++;} else{check_PositionX_positive=16} }
  for (let check_PositionY_negative = 1; check_PositionY_negative<5;) { if(grid[base].id-15 >= 0 && grid[base].type == grid[base-(15*check_PositionY_negative)].type) {check_lineY.push(grid[base-(15*check_PositionY_negative)].id);check_PositionY_negative++;} else{check_PositionY_negative=16} }
  for (let check_PositionY_positive = 1; check_PositionY_positive<5;) { if(grid[base].id+15 <= 224 && grid[base].type == grid[base+(15*check_PositionY_positive)].type) {check_lineY.push(grid[base+(15*check_PositionY_positive)].id);check_PositionY_positive++;} else{check_PositionY_positive=16} }
  for (let check_PositionND_negative = 1; check_PositionND_negative<5;) { if((grid[base].id-(16*check_PositionND_negative))%15 != 14 && grid[base].id-(16*check_PositionND_negative) >= 0 && grid[base].type == grid[base-(16*check_PositionND_negative)].type) {check_lineND.push(grid[base-(16*check_PositionND_negative)].id);check_PositionND_negative++;} else{check_PositionND_negative=16} }
  for (let check_PositionND_positive = 1; check_PositionND_positive<5;) { if((grid[base].id+(16*check_PositionND_positive))%15 != 0 && grid[base].id+(16*check_PositionND_positive) <= 224 && grid[base].type == grid[base+(16*check_PositionND_positive)].type) {check_lineND.push(grid[base+(16*check_PositionND_positive)].id);check_PositionND_positive++;} else{check_PositionND_positive=16} }
  for (let check_PositionPD_negative = 1; check_PositionPD_negative<5;) { if((grid[base].id-(14*check_PositionPD_negative))%15 != 0 && grid[base].id-(14*check_PositionPD_negative) >= 0 && grid[base].type == grid[base-(14*check_PositionPD_negative)].type) {check_linePD.push(grid[base-(14*check_PositionPD_negative)].id);check_PositionPD_negative++;} else{check_PositionPD_negative=16} }
  for (let check_PositionPD_positive = 1; check_PositionPD_positive<5;) { if((grid[base].id+(14*check_PositionPD_positive))%15 != 14 && grid[base].id+(14*check_PositionPD_positive) <= 224 && grid[base].type == grid[base+(14*check_PositionPD_positive)].type) {check_linePD.push(grid[base+(14*check_PositionPD_positive)].id);check_PositionPD_positive++;} else{check_PositionPD_positive=16} }
  if (check_lineX.length>=4) {location.href = "main.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_lineX[0]+"&&Check2="+check_lineX[1]+"&&Check3="+check_lineX[2]+"&&Check4="+check_lineX[3]+"&&TurnColour="+turn_colour_ghost; }
  if (check_lineY.length>=4) {location.href = "main.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_lineY[0]+"&&Check2="+check_lineY[1]+"&&Check3="+check_lineY[2]+"&&Check4="+check_lineY[3]+"&&TurnColour="+turn_colour_ghost; }
  if (check_lineND.length>=4) {location.href = "main.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_lineND[0]+"&&Check2="+check_lineND[1]+"&&Check3="+check_lineND[2]+"&&Check4="+check_lineND[3]+"&&TurnColour="+turn_colour_ghost; }
  if (check_linePD.length>=4) {location.href = "main.php?GridUpdate=win&&ClickBase="+base+"&&Check1="+check_linePD[0]+"&&Check2="+check_linePD[1]+"&&Check3="+check_linePD[2]+"&&Check4="+check_linePD[3]+"&&TurnColour="+turn_colour_ghost; }
  if (check_lineX.length<4 && check_lineY.length<4 && check_lineND.length<4 && check_linePD.length<4) {new_randomised();}
  }
}

function new_randomised() {    
if (((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && player_UID_2 > 0 && set_winner_UID == -1) {
all_sides.length = 0
shuffle_all_sides = 0
    for (let p = 0; p < 224; p++) {
        if (grid[p].type == "side") {
            all_sides.push(p);
        }
    }
    shuffle_all_sides = shuffle(all_sides)
    grid[shuffle_all_sides[0]].type = "open";
        if (shuffle_all_sides[0] % 15 !== 14 && shuffle_all_sides[0] + 1 < 224 && grid[shuffle_all_sides[0]+1].type == "disabled") grid[shuffle_all_sides[0]+1].type = "side";
        if (shuffle_all_sides[0] % 15 !== 0 && shuffle_all_sides[0]-1 >= 0 && grid[shuffle_all_sides[0]-1].type == "disabled") grid[shuffle_all_sides[0]-1].type = "side";
        if (shuffle_all_sides[0] - 15 >= 0 && grid[shuffle_all_sides[0]-15].type == "disabled") grid[shuffle_all_sides[0]-15].type = "side"; 
        if (shuffle_all_sides[0] + 15 < 224 && grid[shuffle_all_sides[0]+15].type == "disabled") grid[shuffle_all_sides[0]+15].type = "side"; 
    grid[shuffle_all_sides[1]].type = "open";
        if (shuffle_all_sides[1] % 15 !== 14 && shuffle_all_sides[1] + 1 < 224 && grid[shuffle_all_sides[1]+1].type == "disabled") grid[shuffle_all_sides[1]+1].type = "side";
        if (shuffle_all_sides[1] % 15 !== 0 && shuffle_all_sides[1]-1 >= 0 && grid[shuffle_all_sides[1]-1].type == "disabled") grid[shuffle_all_sides[1]-1].type = "side";
        if (shuffle_all_sides[1] - 15 >= 0 && grid[shuffle_all_sides[1]-15].type == "disabled") grid[shuffle_all_sides[1]-15].type = "side"; 
        if (shuffle_all_sides[1] + 15 <= 224 && grid[shuffle_all_sides[1]+15].type == "disabled") grid[shuffle_all_sides[1]+15].type = "side"; 
        location.href = "main.php?OpenA0="+shuffle_all_sides[0]+"&&OpenA1="+shuffle_all_sides[1]+"&&Locked="+localSelected_OpenPosID+"&&GridUpdate="+turn_colour_ghost;        // localhost/phpGameTest/main.php?OpenA0=2&&OpenA1=0&&Locked=1&&GridUpdate=red
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

  for (let square of grid) {
    square.draw();
  }
    sides_counter_display=0
    for(let y = 0; y<225; y++) {
      if (grid[y].type == "side") {
sides_counter_display++;
      }
    }

  push()
      textFont(font);
    textSize(20);
    textAlign(CENTER)
  translate(4.35*width/5, 0)
  fill("white")
  text("P1["+player_UID_1_usrn+"{"+player_UID_1+"}]", 0, 150)
  text("P2["+player_UID_2_usrn+"{"+player_UID_2+"}]", 0, 175)
  text("U#ID["+local_UID+"] G#ID["+Game_ID_code+"]", 0, 225)
  text("turn["+turn_id_current+"] DEBUG_WIN_U#ID:["+set_winner_UID+"]", 0, 250)
      text("DEBUG_canmove:"+(((player_UID_1 == turn_id_current && turn_id_current == local_UID) || (player_UID_2 == turn_id_current && turn_id_current == local_UID)) && player_UID_2 > 0 && set_winner_UID == -1), 0, 275) 
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
text("Side Count:"+sides_counter_display+" ("+parseFloat((1/sides_counter_display*100).toFixed(2))+"%)", 0, 300)
if (P5red_used_Array2[P5red_used_Array2.length-1]) {
text("Last Red:"+P5red_used_Array2[P5red_used_Array2.length-1], 0, 325)
let move_shift = 25;
}
else {let move_shift = 0;}
if (P5blue_used_Array2[P5blue_used_Array2.length-1])
text("Last Blue:"+P5blue_used_Array2[P5blue_used_Array2.length-1], 0, 350+move_shift)
text(set_winning_array, 0, 375)
  pop()



  if (set_winner_UID != -1) {
  push()
  textFont(font);
  textAlign(CENTER)
    if (win_splash_random != 1) {
    textSize(70);
    text(player_UID_1_usrn+" Wins!!", width/2, height/5)
  }
    if (win_splash_random == 1) {
      textSize(50);
    text("Skill? Luck? Who Cares,", width/2, height/6)
    text(player_UID_1_usrn+" Wins!", width/2, height/4)
    }
    pop()
  }
}




    </script>

<div class="sidebar_fixed">LUCK test 3.2.[5/4]<br><br>
      
        <?php

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
?>
        <a href="logout.php">logout</a><br>
        <a href="main.php?protanopiaToggle=true">Protanopia</a><br>
<br>
<div class="sidebar_tabs">
<a>Game_settings_debug:</a><br>
<a href="main.php?reset=true">reset/join match</a><br>

</div>
<?php
    $userUpdatepppp = R::load("save", 1);
    if (intval($_SESSION["id"]) == json_decode($userUpdatepppp->uID_1)[0] || $_SESSION["id"] == 1){ 
      echo "<a href=\"main.php?reset=trueMAJOR\">G#owner:reset</a>";
    }
?>
        </div>
</body>
</html>
