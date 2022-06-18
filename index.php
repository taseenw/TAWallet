<?php
    /**
    * Author: Taseen Waseq
    * Created on 15-06-2022
    * PHP file constructing the homepage encountered when the website is first accessed
    * Include dbProperties.php: containing necessary credentials for database access
    * Include functions.php: containing all frequently used functions
    */

    include('dbProperties.php');
    include('functions.php');

    session_start();
  
    //If login was pressed, check if the login was correct
    if(isset($_POST["submit"])){ 
        $loginValid = loginValidation($_POST["email"],$_POST["password"]);
        
        if($loginValid){
            $_SESSION['userEmail'] = $_POST["email"];
            header("location:userHome.php");
        }
    }

    if(!isset($_POST["submit"]) || !$loginValid){
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
        <div class="headline">
            <h2 id="title">TAWallet</h2>
            <h1>
                All Your Assets<br>One Hub
            </h1>
        </div>

        <div class="animated bounceInDown">
            <div class="container" id = "cont">
                <span class="error animated tada" id="msg"></span>
                <form name="form1" class="box" method='POST'>
                    <h2>TAWallet</h2>
                    <h4>Portflio<span> Login</span></h4>
                    <h5 id ="prompt" >Sign in to your account.</h5>
                        <input type="email" name="email" placeholder="Email" id="em" autocomplete="off">
                        <i class="typcn typcn-eye" id="eye"></i>
                        <input type="password" name="password" placeholder="Password" id="pwd" autocomplete="off">
                        <input type="submit" name ="submit" value="Sign in" class="btn1">
                </form>
                <a href="#" class="dnthave" onclick="location.href='registration.php'">Don't have an account? Sign up</a>
            </div>
        </div>

        <script>
            <?php 
            //Sign In was pressed and login is invalid
            } if(isset($loginValid) && !$loginValid){ ?>

            document.getElementById("cont").style.height = "550px";
            document.getElementById("prompt").innerHTML="Sign in to your account. <br><br>*Login Unsuccessful* <br>Please check Email / Password and try again.";

            <?php } ?>
        </script>
    </body>

</html>