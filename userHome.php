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

    //Buy submitted functionality:
    if(isset($_POST["buySubmit"])){
        $buySymbol=$_POST["tickerChoiceToBuy"];
        $buyQuantity=$_POST["tickerQuantToBuy"];
        //Call to function buying the ticker, and updating the users wallet, if ticker is valid (checked in function)
        $newUserWallet = makeWalletPurchase($userWallet, $buySymbol, $buyQuantity);
        if(isset($newUserWallet)){
            updateUserWallet($_SESSION['userEmail'], $newUserWallet);
        }else{
            echo "<script>alert('Error: Symbol Does Not Exist');</script>";
        }
        header("Refresh:0");
    }
    //Sell submitted functionality:
    if(isset($_POST["sellSubmit"])){
        $sellSymbol=$_POST["tickerChoice"];
        $sellQuantity=$_POST["tickerQuant"];
        //Call function to modify wallet according to sale, then update user wallet, if valid sale
        $newUserWallet = makeWalletSale($userWallet, $sellSymbol, $sellQuantity);
        if(isset($newUserWallet)){
            updateUserWallet($_SESSION['userEmail'], $newUserWallet);
        }else{
            echo "<script>alert('Error: Attempt to sell more than held');</script>";
        }
        header("Refresh:0");
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
        <title>TAWallet</title>
        <link rel="stylesheet" href="styles.css">
        <!-- include root.js -->
        <script src="root.js"></script>
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
                    <form name="buyForm" class="transactionBox" method='POST' id="buyForm">
                        <input type="text" id="tickerChoiceToBuy" name="tickerChoiceToBuy" placeholder="Symbol" onchange="updateBuySummary()" required>
                        <input type="number" min ="1" name="tickerQuantToBuy" onchange="updateBuySummary()" placeholder="Quantity" id="tickerQuantToBuy" autocomplete="off" required>

                        <div class = "transactionSummary">
                            <br><h4 style="text-decoration: underline">Transaction Summary</h4>
                            <table class = "transSumTable">
                                <tr>
                                    <th>Holding: </th><td id = "tickerNameToBuy"> </td>
                                </tr>
                                <tr>
                                    <th>Quantity: </th><td id = "tickerQuantityToBuy"> </td>
                                </tr>
                                <tr>
                                    <th>Price Per: </th><td id = "pricePerToBuy"> </td>
                                </tr>
                                <tr>
                                    <th>Total Buy Price: </th><td id = "totalBuyPrice"> </td>
                                </tr>
                            </table>
                            <p id = "transSummary"> </p>
                        </div>
                        <button type="submit" class="confirmOrder" name="buySubmit">Confirm Addition</button>
                    </form>
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
                    <form name="sellForm" class="transactionBox" method='POST' id="sellForm">
                        <select id="tickerChoice" name="tickerChoice" onchange="updateSellSummary()" form="sellForm" required>
                            <option value="" selected disabled hidden>Select Holding</option>
                            <?php
                            $jsonWallet = json_decode($userWallet);
                            foreach($jsonWallet as $ticker => $quantHeld) {
                                echo "<option value = '".$ticker."'>".$ticker."</option>";    
                            }
                            ?>
                        </select>
                        <input type="number" min ="1" name="tickerQuant" onchange="updateSellSummary()" placeholder="Quantity" id="tickerQuant" autocomplete="off" required>

                        <div class = "transactionSummary">
                            <br><h4 style="text-decoration: underline">Transaction Summary</h4>
                            <table class = "transSumTable">
                                <tr>
                                    <th>Holding: </th><td id = "tickerName"> </td>
                                </tr>
                                <tr>
                                    <th>Quantity: </th><td id = "tickerQuantity"> </td>
                                </tr>
                                <tr>
                                    <th>Price Per: </th><td id = "pricePer"> </td>
                                </tr>
                                <tr>
                                    <th>Total Sale Price: </th><td id = "totalSalePrice"> </td>
                                </tr>
                            </table>
                            <p id = "transSummary"> </p>
                        </div>
                        <button type="submit" class="confirmOrder" name="sellSubmit">Confirm Sale</button>
                    </form>
                </div>
            </div>
        </div>

        <h1 class="welcome">Welcome <?php echo $userData["fullName"];?></h1>

        <div class="warning"><h2 id="warning"> </h2></div>
        <div class="walletContainer">
            <h5>
                <?php 
                echo constructWalletHoldings($userWallet);
                ?>
                <button class="exportButton" onclick="exportWallet('xlsx')">Export Wallet</button>
            </h5>
        </div>

    </body>
</html>