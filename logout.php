<?php
// Start the session
session_start();

        
        //Check to see if already logged in
        if(isset($_SESSION["username"])){
            $_SESSION["username"] =[];
            session_destroy();
            header("Location: login.php");
        }
        else{

            ?>
            <!DOCTYPE html>
            <html>
            <head>

            </head>
            <body>
            <?php

            // echo "<h1> You are not logged in, please return to home </h1>";
            // echo "<a href=\"firstpage.php\"> Home </a>";
            header("Location: login.php?id=error3");

        }
        
    ?>

</body>
</html>