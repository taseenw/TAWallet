<?php
    /**
    * Author: Taseen Waseq
    * Created on 15-06-2022
    * PHP file constructing the user homepage upon login
    * Include dbProperties.php: containing necessary credentials for database access
    * Include functions.php: containing all frequently used functions
    */

    include('dbProperties.php');
    include('functions.php');

    //If page is attempted to be accessed without previously logging in
    session_start();
    if (!$_SESSION['userEmail']) header("location:index.php");

    //Call to function fetching all the users data based on email logged in, and fetch users wallet
    $userData = pullUserData($_SESSION['userEmail']);
    $userWallet = pullUserWallet($_SESSION['userEmail']);
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
                <li><a href="#home">Buy</a></li>
                <li><a href="#news">Sell</a></li>
                <li style="float:right"><a href="logOut.php">Logout</a></li>
                <li style="float:right"><a href="#contact">Account Settings</a></li>
            </ul>
        </div>


        <h1 class="welcome">Welcome <?php echo $userData["fullName"];?></h1>
        <div class="walletContainer">
            <h5><?php constructWalletHoldings($userWallet);?></h5>
            <p id = "holdings"> </p>
        </div>

    </body>
</html>