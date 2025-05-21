<?php
session_start();
require("rb-sqlite.php");
R::setup("sqlite:Saved_Info.db");

$user = R::findOne("user", "username = ?", [$_SESSION["username"]]);


if (isset($_GET["id"]) && $_GET["id"] == "save") {

    $username = $_POST["rickandmorty"];
    $password = $_POST["stars"];

    $newUser = R::dispense("usercomment");
    $newUser->username = $username;
    $newUser->password = $password;
    R::store($newUser);
    header("location: /Website%2011.3.2/review.php?id=save_display");
    setcookie("review_save", $username);
    setcookie("stars_save", $password);

}
?>

<html>
<head>
<link rel="stylesheet" href="reviewPage_style.css">
</head>
<body>

<?php 
    if (isset($_GET["id"]) && $_GET["id"] == "newreview") {
                echo "<input type=\"button\" value=\"back to website\"class=\"inputCSS_homebutton\" onClick = \"return backTologin()\">";
        echo "<h2>make a review</h2>";
        echo "<form action=\"/Website%2011.3.2/review.php?id=save\" method=\"post\"> <br> <br>";
        echo "<select reqiured class=\"move_left\" name=\"stars\">";
        echo     "<option value=\"1\">1 ⭐</option>";
        echo     "<option value=\"2\">2 ⭐ </option>";
        echo     "<option value=\"3\">3 ⭐</option>";
        echo     "<option value=\"4\">4 ⭐</option>";
        echo     "<option value=\"5\">5 ⭐</option>";
        echo "</select>";

        echo "<br>";
        echo "<input required class=\"inputCSS\" minlength=\"10\" name=\"rickandmorty\">:review</input>";
        echo "<br>";
        echo "<input type=\"submit\" value=\"send review\" class=\"center\">";
        echo "</from>";
    }

    if (isset($_GET["id"]) && $_GET["id"] == "save_display") {
        $comment = $_COOKIE["review_save"];
        $stars = $_COOKIE["stars_save"];
        echo "<h2>".$comment."</h2>";
        echo "<br><br><h2> stars:".$stars."</h2>";
        echo "<input type=\"button\" value=\"see all reviews\"class=\"inputCSS\" onClick = \"return viewAllComments()\">" . "<br>";
        echo "<input type=\"button\" value=\"back to website\"class=\"inputCSS_homebutton\" onClick = \"return backTologin()\">";
    }   

    if (isset($_GET["id"]) && $_GET["id"] == "view") {
        echo "<input type=\"button\" value=\"back to website\"class=\"inputCSS_homebutton\" onClick = \"return backTologin()\">";
        echo "<br><br><br><br><br><br>";
        echo "</from>";
        $getAllComments = R::findAll("usercomment");
        foreach ($getAllComments as $row) {
        echo "<div id=\"review_display\">";
        echo "<h1>Review(".$row->id."): <a id=\"starText\">".$row->password."</a>⭐ </h1>";
        echo "<h1><a id=\"blackText\">".$row->username."</a></h1>";
        echo "</div>";
        echo "<br>";
        }
        echo "<br><br><br><br><br><br><br><br><br>";
    }
    echo "<script>";
    echo "function viewAllComments() {";
    echo "window.location.assign(\"/Website%2011.3.2/review.php?id=view\")";
    echo "}"; 
    echo "function backTologin() {";
    echo "window.location.assign(\"/Website%2011.3.2/login.php\")";
    echo "}";     
    echo "</script>"; 
?>

<!-- 
    // if ($_GET["saveView_Pref"] == -0) {
    // R::exec('DROP TABLE IF EXISTS save13');
    // }



        // $userBanned_list = R::findAll('user');
    // $userBanned_array = array();
    // foreach($userBanned_list as $row_bannedlist) {
    //     if ($row_bannedlist->banned == true) {
    //     array_push($userBanned_array, $row_bannedlist->id);
    //     }
    // }
    // if ($receiver != "" || in_array($receiver, $userBanned_array)) {
    //     header("location: home.php?save_viewPref=Global");
    // }    
    // else  -->

</body>
</html>


