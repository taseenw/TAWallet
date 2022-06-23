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
                    <form name="buyForm" class="transactionBox" method='POST' id="buyForm">
                        <input type="text" id="tickerChoiceToBuy" name="tickerChoiceToBuy" placeholder="Symbol" onchange="updateBuySummary()">
                        <input type="number" min ="1" name="tickerQuantToBuy" onchange="updateBuySummary()" placeholder="Quantity" id="tickerQuantToBuy" autocomplete="off">

                        <div class = "transactionSummary">
                            <br><h4 style="text-decoration: underline">Transaction Summary</h4>
                            <table class = "transSumTable">
                                <tr>
                                    <th>Holding: </th><td id = "tickerNameToBuy"> </td>
                                </tr>
                                <tr>
                                    <th>Quantity: </th><td id = "tickerQuantToBuy"> </td>
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

                        <h2 id="confirmOrder">
                            Confirm Addition
                            <input type="submit" name="submit" value="✔️">
                        </h2>
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
                        <select id="tickerChoice" name="tickerChoice" onchange="updateSellSummary()" form="sellForm">
                            <option value="" selected disabled hidden>Select Holding</option>
                            <?php
                            $jsonWallet = json_decode($userWallet);
                            foreach($jsonWallet as $ticker => $quantHeld) {
                                echo "<option value = '".$ticker."'>".$ticker."</option>";    
                            }
                            ?>
                        </select>
                        <input type="number" min ="1" name="tickerQuant" onchange="updateSellSummary()" placeholder="Quantity" id="tickerQuant" autocomplete="off">

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

                        <h2 id="confirmOrder">
                            Confirm Subtraction
                            <input type="submit" name="submit" value="✔️">
                        </h2>
                    </form>
                </div>
            </div>
        </div>

        <h1 class="welcome">Welcome <?php echo $userData["fullName"];?></h1>

        <div class="warning"><h2 id="warning"> </h2></div>
        <div class="walletContainer">
            <h5><?php constructWalletHoldings($userWallet);?></h5>
            <p id = "holdings"> </p>
        </div>

        <script>
            //JavaScript functions: updateSummary for Sell/Buy Modals, and necessary functions for Modal functionality/display
            function updateBuySummary(){
                var curTickerToBuy = document.getElementById('tickerChoiceToBuy').value;
                var tickerQuantToBuy = document.getElementById('tickerQuantToBuy').value;
                var pricePerToBuy = document.getElementById('pricePerToBuy');
                //AJAX Request to get current ticker price, using existing PHP methods
                //Approach is used in order to use js obtained variable in PHP (ticker)
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                    // this.responseText is the response
                    pricePerToBuy.innerHTML = "$"+this.responseText;
                    }
                };
                xhttp.open("GET", "jsTickerPriceReq.php?ticker="+curTickerToBuy);//Looking up employee via another page, passing phone number to identify the specific employee we're looking for
                xhttp.send();

                //Extract the ticker price as a number, to then calculate the total transaction price
                var pricePerNumToBuy = pricePerToBuy.innerHTML;
                //Return case of an invalid symbol
                if(pricePerNumToBuy != ""){
                    //Remember, pricePerNum format is a string: $12345
                    pricePerNumToBuy = parseInt(pricePerNumToBuy.slice(1));

                    var totalBuyPrice = pricePerNumToBuy*tickerQuantToBuy;

                    document.getElementById('tickerNameToBuy').innerHTML = curTickerToBuy;
                    document.getElementById('tickerQuantity').innerHTML = tickerQuantToBuy;
                    if(isNaN(totalBuyPrice)){totalBuyPrice="";}
                    document.getElementById('totalBuyPrice').innerHTML = "$"+totalBuyPrice;
                }

            }

            function updateSellSummary(){
                var curTicker = document.getElementById('tickerChoice').value;
                var tickerQuant = document.getElementById('tickerQuant').value;
                var pricePer = document.getElementById('pricePer');
                //AJAX Request to get current ticker price, using existing PHP methods
                //Approach is used in order to use js obtained variable in PHP (ticker)
                var yhttp = new XMLHttpRequest();
                yhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                    // this.responseText is the response
                        pricePer.innerHTML = "$"+this.responseText;
                    }
                };
                yhttp.open("GET", "jsTickerPriceReq.php?ticker="+curTicker);//Looking up employee via another page, passing phone number to identify the specific employee we're looking for
                yhttp.send();

                //Extract the ticker price as a number, to then calculate the total transaction price
                var pricePerNum = pricePer.innerHTML;

                //Remember, pricePerNum format is a string: $12345
                pricePerNum = parseInt(pricePerNum.slice(1));

                var totalSalePrice = pricePerNum*tickerQuant;

                document.getElementById('tickerName').innerHTML = curTicker;
                document.getElementById('tickerQuantity').innerHTML = tickerQuant;
                document.getElementById('totalSalePrice').innerHTML = "$"+totalSalePrice;

            }

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
                if (event.target == buyModal || event.target == sellModal) {
                    buyModal.style.display = "none";
                    sellModal.style.display = "none";
                }
            }
            
        </script>

    </body>
</html>