<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUCK/play</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

    <div id="main" class="globe">
    <iframe src="https://globe.gl/example/clouds/" width="69.1%" height="100%%" frameBorder="0" ></iframe>
</div>

<div class="logincenter">
    <h1 id="title">LUCK</h1>
    <h1>Login To Account</h1>
    <form method="post" action="play.php?id=loginCheck">
        <br>
        <?php
        if (isset($_GET["id"]) && ($_GET["id"] == "error1" || $_GET["id"] == "error4")){
            echo "<img src=\"images&fonts/account_login.svg\" class=\"icon_svg\"> <input class=\"login-error-username\" name=\"username\" placeholder=\"Enter Username\" autocomplete=\"off\" onkeypress=\"return event.charCode != 32\" id=\"username\" maxlength=\"18\" required > <br>";
        if($_GET["id"] == "error1"){
            echo "<h2 class=\"login-error-noUser-h2\">Username Cannot Be Found</h2>";
            }
        if($_GET["id"] == "error4"){
            echo "<h2 class=\"login-error-noUser-h2\">" . $_SESSION["username"] . ": Account Has Been Banned" . "</h2>";
            }
        }
        else {
            echo "<img src=\"images&fonts/account_login.svg\" class=\"icon_svg\"> <input class=\"svg_button_mover\" name=\"username\" placeholder=\"Enter Username\" autocomplete=\"off\" onkeypress=\"return event.charCode != 32\" id=\"username\" maxlength=\"18\" required > <br>";
        }

        if($_GET["id"] == "error2"){
            echo "<img src=\"images&fonts/lock2-4.svg\" class=\"icon_svg_password\"> <input class=\"login-error-incorrectPassword\" name=\"password\"  placeholder=\"Enter Password\" type=\"password\" autocomplete=\"off\" onkeypress=\"return event.charCode != 32\" id=\"password\" required ><br> ";
            echo "<h2 class=\"login-error-incorrectPassword-h2\">Incorrect Password</h2>";
            }
            else { echo "<img src=\"images&fonts/lock2-4.svg\" class=\"icon_svg_password\"> <input class=\"svg_button_mover\" name=\"password\" placeholder=\"Enter Password\" type=\"password\" autocomplete=\"off\" id=\"password\" required ><br>";}
        ?>
        <br>
        <br>
                <div class="login_modifier">
    <input type="submit" value="☞ Login to Account" name="signin" class="account_button">  
    <a href="signup.php">
        <input type="button" value="⇪ Create Account" name="signup" id="signup" class="account_button"> 
    </a>
    </div>     
    </form>
    <br><br><br><br>
    <div> 
        <input type="button" value="see all reviews" id="button_list_notifcation" onClick = "return reviewRedirect_view()">
        <input type="button" value="make a review" id="button_list_notifcation" onClick = "return reviewRedirect_new()">
        <script>
        function reviewRedirect_view() {
        window.location.assign("/Website%2011.3.2/review.php?id=view");
        }
        function reviewRedirect_new() {
        window.location.assign("/Website%2011.3.2/review.php?id=newreview");
        }
        </script>
        </div>
    </div>  



    </div>

        <!-- </div> -->
</body>
</html>

