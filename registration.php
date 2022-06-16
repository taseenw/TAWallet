<?php
    /**
    * Author: Taseen Waseq
    * Created on 15-06-2022
    * PHP file constructing the registration encountered when users go to signup
    * Include dbProperties.php: containing necessary credentials for database access
    * Include functions.php: containing all frequently used functions
    */

    include('dbProperties.php');
    include('functions.php');

    session_start();
  
    //If signup was pressed, verify confirm password, and proceed with registration
    if(isset($_POST["submit"])){ 
        if(strcmp($_POST["password"], $_POST["confPassword"]) != 0){
            echo "Passwords do not match";
            $confPassword = false;
        }else{
            $confPassword = true;
            registerNewUser($_POST["name"], $_POST["email"], $_POST["password"]);
            $_SESSION['userEmail'] = $_POST["email"];
            header("location:userHome.php");
        }
    }

    if(!isset($_POST["submit"]) || !$confPassword){
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
        <div class="animated bounceInDown">
            <div class="regContainer">
                <span class="error animated tada" id="msg"></span>
                <form name="form1" class="regBox" method='POST'>
                    <h2>TAWallet</h2>
                    <h4>Portflio<span> Sign Up</span></h4>
                    <h5>Sign up for your account</h5>
                        <input type="text" name="name" placeholder="Full Name" autocomplete="off">
                        <i class="typcn typcn-eye" id="eye"></i>
                        <input type="email" name="email" placeholder="Email" autocomplete="off">
                        <input type="password" name="password" placeholder="Password" id="pwd" autocomplete="off">
                        <input type="password" name="confPassword" placeholder="Confirm Password" id="pwd" autocomplete="off">
                        <input type="submit" name="submit" value="Sign Up" class="regBtn1">
                </form>
                <a href="#" class="returnLogin" onclick="location.href='index.php'">Return to Login</a>
            </div>
        </div>   
        <?php }?>
    </body>

</html>