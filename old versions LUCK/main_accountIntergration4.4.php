<?php
session_start();
require("rb-sqlite.php");
R::setup("sqlite:Saved_Info.db");
$item = R::findAll("save");
$response = ["d1" => [],"d2" => [],"d3" => [], "currentColour" => [], "playerA" => [], "playerB" => [], "turn_id" => []];
foreach ($item as $testRow) {
    $decodedD1 = json_decode($testRow->d1, true);
    $decodedD2 = json_decode($testRow->d2, true);
    $decodedD3 = json_decode($testRow->d3, true);
    if (is_array($decodedD1)) { $response["d1"] = array_merge($response["d1"], $decodedD1); }
    if (is_array($decodedD2)) { $response["d2"] = array_merge($response["d2"], $decodedD2); }
    if (is_array($decodedD3)) { $response["d3"] = array_merge($response["d3"], $decodedD3); }
    $response["currentColour"] = $testRow->turn_open; 
    $response["playerA"] = json_decode($testRow->uID_1);
    $response["playerB"] = json_decode($testRow->uID_2);
    $response["turn_id"] = $testRow->turn_id;

    // if (!isset($response["U#ID_A"][0])) {$response["U#ID_A"][0] = $_SESSION["id"];}
    // else if (!isset($response["U#ID_A"][1])) {$response["U#ID_A"][1] = $_SESSION["id"];}
    // print_r($response);
}
    if (isset($_GET['fetch_data'])) {
    echo json_encode($response);
    exit;
    }

if (isset($_GET["reset"])){
      $userUpdatereset = R::load("save", 385);
      $userUpdatereset->d1 = json_encode([96, 97, 98, 111, 112, 113, 126, 127, 128]);
      $userUpdatereset->d2 = json_encode([]);
      $userUpdatereset->d3 = json_encode([]);
      $userUpdatereset->turn_open = json_encode(["red"]);
      if (!isset($userUpdatereset->uID_1)) {
      $userUpdatereset->uID_1 = "[".$_SESSION["id"]."]";
      }
      else if (isset($userUpdatereset->uID_1) && !isset($userUpdatereset->uID_2) && $userUpdatereset->uID_1 != $_SESSION["id"]) {
      $userUpdatereset->uID_2 = "[".$_SESSION["id"]."]";
      }
      $userUpdatereset->turn_id = $userUpdatereset->uID_1;
      R::store($userUpdatereset);
      header("location: main.php");
}
if (isset($_GET["GridUpdate"])) {
    $saveUpdate = R::findAll("save");
    foreach ($saveUpdate as $row) {
        $newOpenArray = json_decode($row->d1, true);
        array_push($newOpenArray, $_GET["OpenA0"], $_GET["OpenA1"]);
        sort($newOpenArray);
        $red_used_Array = json_decode($row->d2, true) ?: []; 
        $blue_used_Array = json_decode($row->d3, true) ?: [];

        if ($_GET["GridUpdate"] == "red") {
            array_push($red_used_Array, $_GET["Locked"]);
            sort($red_used_Array);
        } else if ($_GET["GridUpdate"] == "blue") {
            array_push($blue_used_Array, $_GET["Locked"]);
            sort($blue_used_Array);
        }
    }
    $userUpdate = R::load("save", 1);
    $userUpdate->d1 = json_encode($newOpenArray);
    $userUpdate->d2 = json_encode($red_used_Array); 
    $userUpdate->d3 = json_encode($blue_used_Array);
    $userUpdate->turnOpen = ($_GET["GridUpdate"] == "red") ? "blue" : "red";
    if ($userUpdate->turn_id == $userUpdate->uID_1) {
    $userUpdate->turn_id = $userUpdate->uID_2;
    }
    else {
      $userUpdate->turn_id = $userUpdate->uID_1;
    }
    R::store($userUpdate);
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
        $save_personal->save1 = "#defef0";
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

?>
<html style="background: #21323b">
<head>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
let grid = []
let tileSize;
let gridSize = 15;
let all_sides = []
let localSelected_OpenPosID = 0;
let width_devide = 4
let local_UID = <?php echo $_SESSION["id"];?>;

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
   if(this.type == "red") {
    fill(250, 100, 100)
  }
 if(this.type == "blue") {
    fill(100, 100, 250)
  }

  if(this.type == "yellow") {
    fill("yellow")
  }
  stroke("#21323b");
    rect(this.x + width / width_devide, this.y + windowHeight / 17, this.size, this.size);
    if (mouseX > this.x+width/width_devide && mouseX < this.x+width/width_devide+this.size &&
        mouseY > this.y + windowHeight/17 && mouseY < this.y+windowHeight/17+this.size && this.type == "open") {
          push()
          if(colourRelay == "red" ) {
          fill(255, 0, 0 , 30)
          }
          else if(colourRelay == "blue") {
          fill(0, 0, 255 , 30)
          }
            rect(this.x + width / width_devide, this.y + windowHeight / 17, this.size, this.size);
          pop()
        }

    textFont(font);
    textSize(20);
    fill("#21323b");
    textAlign(CENTER)
    text(this.id, this.x + width / width_devide + this.size/2, this.y + windowHeight / 17 + this.size / 2+7.5);
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
      setInterval(fetchData, 1500); 
      frameRate(30);
}
function draw() {
  background("#283740");
  for (let square of grid) {
    square.draw();
  }
}
  function fetchData() {
    fetch(window.location.href + '?fetch_data=1')
    .then(response => response.json())
    .then(data => {
        colorCookie2= Array.isArray(data.d1) ? data.d1 : [];
        P5red_used_Array2 = Array.isArray(data.d2) ? data.d2 : [];
        P5blue_used_Array2 = Array.isArray(data.d3) ? data.d3 : [];
        colourRelay = data.currentColour;
        player_UID_1 = data.playerA; 
        player_UID_2 = data.playerB;
        turn_id_current = data.turn_id;
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
function windowResized() {
  resizeCanvas(windowWidth, windowHeight);
}

function touchStarted() {
  mouseClicked()
}
function mouseClicked() {
  localSelected_OpenPosID = 0
  for (let i = 0; i < 224; i++) {
        if (mouseX > grid[i].x+width/width_devide && mouseX < grid[i].x+width/width_devide+grid[i].size &&
        mouseY > grid[i].y + windowHeight/17 && mouseY < grid[i].y+windowHeight/17+grid[i].size && grid[i].type == "open") {
      grid[i].type = colourRelay;
      turn_colour_ghost = colourRelay;
      localSelected_OpenPosID = i;
      changeWord()
      }
    }
}

function changeWord() {    
all_sides.length = 0
shuffle_all_sides = 0
    for (let p = 0; p < 224; p++) {
        if (grid[p].type == "side") {
            all_sides.push(p)
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
        if (shuffle_all_sides[1] + 15 < 224 && grid[shuffle_all_sides[1]+15].type == "disabled") grid[shuffle_all_sides[1]+15].type = "side"; 

        location.href = "main.php?OpenA0="+shuffle_all_sides[0]+"&&OpenA1="+shuffle_all_sides[1]+"&&Locked="+localSelected_OpenPosID+"&&GridUpdate="+turn_colour_ghost;
        // localhost/phpGameTest/main.php?OpenA0=2&&OpenA1=0&&Locked=1&&GridUpdate=red
}

// <script type="text/javascript">
    </script>
        <div class="sidebar_fixed">HTML overlapping P5js test 21.3<br>
                <?php
$user_found = R::findAll('user');
$my_array = [];
foreach($user_found as $row) {
        echo $row->username."<br>";
        array_push($my_array, $row->id);
    }
?>
<a href="main.php?reset=true">reset</a>;
        </div>
</body>
</html>
