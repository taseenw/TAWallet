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
    if (!$_SESSION["userEmail"]) header("location:index.php");

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
                <li><a onclick="openBuyModal()">Buy</a></li>
                <li><a onclick="openSellModal()">Sell</a></li>
                <li style="float:right"><a href="logOut.php">Logout</a></li>
                <li style="float:right"><a href="accountConf.php">Account Settings</a></li>
            </ul>
        </div>

        <!-- Buy Modal -->
        <div id="buyModal" class="modal">
            <!-- Modal content -->
            <div class="modal-content">

                <div class="modal-body">
                    <div class="modal-header">
                        <span class="close" onclick="closeModal()">&times;</span>
                    </div>
                    <h3>Buy</h3>
                    <p id="buyModaltext">Some text in the Modal Body</p>
                    <h2 id="confirmOrder">Confirm Addition</h2>
                </div>
            </div>
        </div>

        <!-- Sell Modal -->
        <div id="sellModal" class="modal">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="modal-header">
                        <span class="close" onclick="closeModal()">&times;</span>
                    </div>
                    <h3>Sell</h3>
                    <form name="form1" class="transactionBox" method='POST' id="sellForm">
                        <select id="tickerChoice" name="tickerChoice" form="sellForm">
                            <option value="" selected disabled hidden>Select Holding</option>
                            <?php
                            $jsonWallet = json_decode($userWallet);
                            foreach($jsonWallet as $ticker => $quantHeld) {
                                echo "<option value = '".$ticker."'>".$ticker."</option>";    
                            }
                            ?>
                        </select>
                        <input type="number" name="tickerQuant" placeholder="Quantity" id="tickerQuant" autocomplete="off">
                        <h2 id="confirmOrder">
                            Confirm Subtraction
                            <input type="submit" name="submit" value="✔️">
                        </h2>
                    </form>
                </div>
            </div>
        </div>

        <h1 class="welcome">Welcome <?php echo $userData["fullName"];?></h1>
        <div class="walletContainer">
            <h5><?php constructWalletHoldings($userWallet);?></h5>
            <p id = "holdings"> </p>
        </div>

        <script>
            function openBuyModal(){
                var buyModal = document.getElementById('buyModal');
                buyModal.style.display = "block";
            }

            function openSellModal(){
                var sellModal = document.getElementById('sellModal');
                sellModal.style.display = "block";            }

            function closeModal() {
                buyModal.style.display = "none";
                sellModal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    buyModal.style.display = "none";
                    sellModal.style.display = "none";
                }
            }
            
        </script>

    </body>
</html>