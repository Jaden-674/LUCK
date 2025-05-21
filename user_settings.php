<?php
session_start();
require("rb-sqlite.php");
R::setup("sqlite:Saved_Info.db");
?>

<html>
<head>
<link rel="stylesheet" href="settingsPage_style.css">
<link rel="icon" type="image/x-icon" href="icon_TAB.png">
<script src="jquery-3.7.1.min.js"></script> 
</head>
<body>
    <?php
    $user_db = R::findAll('user');
    $packets_db = R::findAll('packets');
    $personal_Save_db = R::findAll("save".$_SESSION["id"]);            
    // echo "<div id=\"colour_textshow\">";
 
            // echo "</script>";
    ?>
   </div>  
       <div class="settings_button">
<a href="home.php?saveView_Pref=Global">
     <h1>return home<h1>
</a>
 <a href="logout.php">
<input value="Log Out" name="signin" type="button" class="input_newtransfer">  
    </a>
    </div>
    
<div id="list" class="text"></div>
<script>
    let index = 0;
function processNext(item) {
    setTimeout(() => {
        let div = document.createElement("div");
        div.textContent = JSON.stringify(item, null, 2);
        document.getElementById("list").appendChild(div);
    }, index * 150);
    index++;
}
<?php foreach ($personal_Save_db as $item): ?>
    processNext(<?php echo json_encode($item); ?>);
<?php endforeach; ?>
</script> 

<!-- save1 custom changer -->
<h2 class="text"> change colour 1 </h2>
<input id="colorpicker_save1" type="color" value="#ffffff" onchange="changeTask_save1()">
<br><br>
<script>
    function changeTask_save1() {
        let save1A = document.getElementById('colorpicker_save1').value
        let save1B = save1A.slice(1);
        window.location.assign("/Website%2011.3.2/user_settings.php?hexChange_save1="+save1B);
    }
</script>
<?php
    if(isset($_GET["hexChange_save1"])) {;
    $saveHexUpdate_save1 = R::load("save".$_SESSION["id"], 1);
    $saveHexUpdate_save1->save1 = $_GET['hexChange_save1'];
    R::store($saveHexUpdate_save1);
    header("location: /Website%2011.3.2/user_settings.php");
    }
?>

<!-- save2 custom changer -->
 <h2 class="text"> change colour 2 </h2>
<input id="colorpicker_save2" type="color" value="#ffffff" onchange="changeTask_save2()">
 <br><br>
<script>
    function changeTask_save2() {
        let save2A = document.getElementById('colorpicker_save2').value
        let save2B = save2A.slice(1);
        window.location.assign("/Website%2011.3.2/user_settings.php?hexChange_save2="+save2B);
    }
</script>
<?php
    if(isset($_GET["hexChange_save2"])) {;
    $saveHexUpdate_save2 = R::load("save".$_SESSION["id"], 1);
    $saveHexUpdate_save2->save2 = $_GET['hexChange_save2'];
    R::store($saveHexUpdate_save2);
    header("location: /Website%2011.3.2/user_settings.php");
    }
?>

<!-- save3 custom changer -->
 <h2 class="text"> change colour 3 </h2>
<input id="colorpicker_save3" type="color" value="#ffffff" onchange="changeTask_save3()">
 <br><br>
<script>
    function changeTask_save3() {
        let save3A = document.getElementById('colorpicker_save3').value
        let save3B = save3A.slice(1);
        window.location.assign("/Website%2011.3.2/user_settings.php?hexChange_save3="+save3B);
    }
</script>
<?php
    if(isset($_GET["hexChange_save3"])) {;
    $saveHexUpdate_save3 = R::load("save".$_SESSION["id"], 1);
    $saveHexUpdate_save3->save3 = $_GET['hexChange_save3'];
    R::store($saveHexUpdate_save3);
    header("location: /Website%2011.3.2/user_settings.php");
    }
?>

<!-- save4 custom changer -->
 <h2 class="text"> change colour 4 </h2>
<input id="colorpicker_save4" type="color" value="#ffffff" onchange="changeTask_save4()">
 <br><br>
<script>
    function changeTask_save4() {
        let save4A = document.getElementById('colorpicker_save4').value
        let save4B = save4A.slice(1);
        window.location.assign("/Website%2011.3.2/user_settings.php?hexChange_save4="+save4B);
    }
</script>
<?php
    if(isset($_GET["hexChange_save4"])) {;
    $saveHexUpdate_save4 = R::load("save".$_SESSION["id"], 1);
    $saveHexUpdate_save4->save4 = $_GET['hexChange_save4'];
    R::store($saveHexUpdate_save4);
    header("location: /Website%2011.3.2/user_settings.php");
    }
?>
</body>
</head>
</html>