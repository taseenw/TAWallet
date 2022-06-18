<?php
    /**
    * Author: Taseen Waseq
    * Created on 18-06-2022
    * PHP file constructing the registration encountered when users go to signup
    * Include dbProperties.php: containing necessary credentials for database access
    * Include functions.php: containing all frequently used functions
    */

    include('dbProperties.php');
    include('functions.php');

    session_start();
  
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>TAWallet</title>
        <link rel="stylesheet" href="styles.css">
    </head>

    <body id="particles-js" class="fullbkg" background = "normalBackground.jpg">
        
        <div class="navBar">
            <ul>
                <li><a href="userHome.php">Home</a></li>
                <li style="float:right"><a href="logOut.php">Logout</a></li>
            </ul>
        </div>


        <div class="animated bounceInDown">
            <div class="changePassContainer">
                <span class="error animated tada" id="msg"></span>
                <form name="form1" class="changePassBox" method='POST'>
                    <br><br>
                    <h4>Change<span> Password</span></h4>
                    <h5>Enter Valid Credentials</h5>
                        <input type="password" name="oldPassword" placeholder="Old Password" autocomplete="off">
                        <input type="password" name="newPassword" placeholder="New Password" id="pwd" autocomplete="off">
                        <input type="password" name="confNewPassword" placeholder="Confirm New Password" id="pwd" autocomplete="off">
                        <input type="submit" name="submit" value="Confirm Change" class="regBtn1">
                </form>
                <a href="#" class="returnLogin" onclick="location.href='userHome.php'">Return to Home</a>
            </div>
        </div>   
    </body>

</html>