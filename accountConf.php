<?php
    /**
    * Author: Taseen Waseq
    * Created on 18-06-2022
    * PHP file constructing the change password page for existing users
    * Include dbProperties.php: containing necessary credentials for database access
    * Include functions.php: containing all frequently used functions
    */

    include('dbProperties.php');
    include('functions.php');

    session_start();
    if (!$_SESSION["userEmail"]) header("location:index.php");

    //Validate old password with logged in email, then that new password and confirmation are equal
    if(isset($_POST["submit"])){ 
        $confPasswordValid = false;
        $oldPassValidation = loginValidation($_SESSION["userEmail"],$_POST["oldPassword"]);
        if($oldPassValidation){
            if(strcmp($_POST["newPassword"], $_POST["confNewPassword"]) != 0){
            }else{ //Correct case
                modifyPassword($_SESSION["userEmail"],$_POST["newPassword"]);
                $confPasswordValid = true;
                header("location:userHome.php");
            }
        }
    }
    
        if(!isset($_POST["submit"]) || !$oldPassValidation || !$confPasswordValid){
  
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
            <div class="changePassContainer" id = "cpCont">
                <span class="error animated tada" id="msg"></span>
                <form name="form1" class="changePassBox" method='POST'>
                    <br><br>
                    <h4>Change<span> Password</span></h4>
                    <h5 id="prompt">Enter Valid Credentials</h5>
                        <input type="password" name="oldPassword" placeholder="Old Password" autocomplete="off">
                        <input type="password" name="newPassword" placeholder="New Password" id="pwd" autocomplete="off">
                        <input type="password" name="confNewPassword" placeholder="Confirm New Password" id="pwd" autocomplete="off">
                        <input type="submit" name="submit" value="Confirm Change" class="regBtn1">
                </form>
            </div>
        </div>   
    </body>
    <script>
        <?php 
        //Attempt was made, prompt reason for unsuccessfulness
        } if(isset($oldPassValidation) && !$oldPassValidation){ ?>
        document.getElementById("cpCont").style.height = "475px";
        document.getElementById("prompt").innerHTML="Enter Valid Credentials <br><br>*Password Entered is Incorrect*";
        <?php } if($oldPassValidation && !$confPasswordValid){ ?>
        document.getElementById("cpCont").style.height = "475px";
        document.getElementById("prompt").innerHTML="Enter Valid Credentials <br><br>*New Password and Confirmation Do NOT Match*";
        <?php } ?>
    </script>
</html>