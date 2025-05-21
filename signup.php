<?php
    session_start();
?>

<!DOCTYPE html>
<html>
<link rel="stylesheet" href="main.css">
<head>

</head>
<body>

<div id="main" class="globe">
    <iframe src="https://globe.gl/example/clouds/" width="69.1%" height="100%" frameBorder="0"></iframe>
</div> 

<div class="logincenter">
<h1>Create Account</h1>
<form method="post" action="play.php?id=SaveNewUser">
<?php
     if(isset($_SESSION["username"])){
       echo "<h1> YOU ARE ALREADY LOGGED IN PLEASE RETURN TO HOME </h1>";
       echo "<a href=\"play.php\"> <input type=\"button\" value=\"Return to Home\" name=\"R_home\" class=\"input_R_home\">  </a>";
       echo "<a href=\"logout.php\"> <input type=\"button\" value=\"Logout Account\" class=\"input_signup\">  </a>";   
     }     
     else{

        if (isset($_GET["id"])){
        echo "<img src=\"account_login.svg\" class=\"icon_svg\"> <input class=\"login-error-username\" name=\"username\" placeholder=\"Create Username\" autocomplete=\"off\"   onkeypress=\"return event.charCode != 32\" id=\"username\" maxlength=\"18\" required > <br>";
         if($_GET["id"] == "error6"){
            echo "<h2 class=\"login-error-incorrectPassword-h2\">Username Already Taken</h2>";
        }   
        if($_GET["id"] == "error7"){
            echo "<h2 class=\"login-error-incorrectPassword-h2\">Special Characters Not Supported</h2>";
        }   
            }
        else {
            echo "<img src=\"account_login.svg\" class=\"icon_svg\"> <input class=\"svg_button_mover\" name=\"username\" placeholder=\"Create Username\" autocomplete=\"off\"   onkeypress=\"return event.charCode != 32\" id=\"username\" maxlength=\"18\" required > <br>";
        }
        echo "<img src=\"lock2-4.svg\" class=\"icon_svg_password\"> <input class=\"svg_button_mover\" name=\"password\" placeholder=\"Create Password\" type=\"password\" id=\"password\" required ><br>";


       
     }
    ?>
    <input type="submit" class="account_button"value="↓ Save Account">

    <a href="login.php">
        <input type="button" value="⏎ Back To login" name="signup" id="signup-cancel" class="account_button"> 
    </a>

    </form>
    </div>

</body>
</html>