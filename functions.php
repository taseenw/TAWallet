<?php
    /**
    * Author: Taseen Waseq
    * Created on 15-06-2022
    * PHP file containing all major functions called throughout the project
    * Include dbProperties containing necessary credentials for database access
    */

    include('dbProperties.php');

    /* Function loginValidation
    *
    * @desc function to validate login information from index page, and prompt according message
    * @Created on 15-06-2022
    * @param String emailInput, String passwordInput
    * @return Boolean loginValid
    */

    function loginValidation($emailInput, $passwordInput){
        global $pdo;
        
        $query = $pdo -> prepare("SELECT userPassword FROM users WHERE userEmail =:entry;");
        $query -> execute(array(
            'entry' => $emailInput
        ));

        $passwordArr = $query -> fetchAll(PDO::FETCH_ASSOC);

        //If user entered email is valid, $passwordStr will be set (not null)
        //Validate password if email exists in db, *note passwords are stored hashed
        if(isset($passwordArr[0]['userPassword'])){            
            if(password_verify($passwordInput, $passwordArr[0]['userPassword'])){ //Right password
                return true; 
            }
            else{
                return false;
            }
        }else{
            return false;
        }
    
    }
    
    /* Function registerNewUser
    *
    * @desc Function to register new user, inserting new credentials/info in user database
    * @Created on 15-06-2022
    * @param String fullName, String email, String password
    */

    function registerNewUser($fullName, $email, $password){
        $hashedPassword=password_hash($password, PASSWORD_DEFAULT);
        try{
            global $pdo;
            
            $qry = $pdo -> prepare("INSERT INTO users (fullName, userEmail, userPassword) VALUES (:fullName, :userEmail, :userPassword)");
            $qry -> execute(array(
                'fullName' => $fullName,
                'userEmail' => $email,
                'userPassword' => $hashedPassword
            ));

            createNewWallet($email);

            }catch (Exception $e){
            echo "Email Already Exists";
        }
    }

    /* Function createNewWallet
    *
    * @desc Function to create a blank wallet for new users
    * @Created on 16-06-2022
    * @param String email
    */

    function createNewWallet($email){
        global $pdo;
            
        $qry = $pdo -> prepare("INSERT INTO wallets (userEmail, userWallet) VALUES (:userEmail, :userWallet)");
        $qry -> execute(array(
            'userEmail' => $email,
            'userWallet' => "{}"
        ));

    }



    /* Function pullUserData
    *
    * @desc Function to pull in the data of the user thats logged in through the database
    * @Created on 15-06-2022
    * @param String emailLoggedIn
    * @return Array userData
    */

    function pullUserData($emailLoggedIn){
        global $pdo;
        
        $qry = $pdo -> prepare("SELECT * FROM users WHERE userEmail =:email;");
        $qry -> execute(array(
            'email' => $emailLoggedIn
        ));

        $userDataSet = $qry -> fetchAll(PDO::FETCH_ASSOC);
        $userData = $userDataSet[0];

        return $userData; //Sending back all the data in the table that corresponds with the logged in user
    }

    /* Function pullUserWallet
    *
    * @desc Function to pull in the wallet of the user thats logged in through the database
    * @Created on 15-06-2022
    * @param String emailLoggedIn
    * @return String userWallet (JSON Formatted)
    */

    function pullUserWallet($emailLoggedIn){
        global $pdo;
        
        $qry = $pdo -> prepare("SELECT userWallet FROM wallets WHERE userEmail =:email;");
        $qry -> execute(array(
            'email' => $emailLoggedIn
        ));

        $userWalletArr = $qry -> fetchAll(PDO::FETCH_ASSOC);
        $userWallet = $userWalletArr[0]["userWallet"];

        return $userWallet; //Sending back all the data in the table that corresponds with the logged in user
    }

    /* Function constructWalletHoldings
    *
    * @desc Function populate userHome with the value of all users holdings
    * @Created on 17-06-2022
    * @param String userWallet (JSON Formatted)
    */

    function constructWalletHoldings($userWallet){
        //Isolate all tickers from wallet, bear in mind all keys = ticker names
        //Error checking for the possiblity of API returning NULL, hold count based on DB
        $tickerCount = 0;
        $holdCount=0;
        $jsonWallet = json_decode($userWallet);
        $portfolioTotalValue = 0;

        //Iterate all tickers (stocks/coins in wallet), print holding data
        foreach($jsonWallet as $ticker => $quantHeld) {
            $currentTickerValue = getTickerValues($ticker);
            //Return case of invalid ticker
            if($currentTickerValue != ""){
                $currentTickerHoldingValue = $currentTickerValue * $quantHeld;
                echo $ticker.' : '.$quantHeld." | Current Price: $".$currentTickerValue." | Holding Value: $".$currentTickerHoldingValue."<br>";
                $portfolioTotalValue += $currentTickerHoldingValue;
                $tickerCount++;
            }else{
                echo $ticker.' : '.$quantHeld." | <i>API Call Limit Reached - Reload after 1 minute for price calculations</i><br>";
            }
            $holdCount++;
        }
        if($portfolioTotalValue == 0 && $holdCount !=0){
            echo "<p style='font-size: larger; margin: 3px; padding-top: 4px; padding-bottom: 0px'>Portfolio Value: <i>API Call Limit Reached - Reload after 1 minute for value calculation</i></p>";
        }else{
            echo "<p style='font-size: large; margin: 3px; padding-top: 4px; padding-bottom: 0px'>Portfolio Value : $".round($portfolioTotalValue, 2)."</p>";
        }

    }

    /* Function getTickerValues
    *
    * @desc Function to pull the information for the passed ticker, using the STOCK API
    * @Created on 17-06-2022
    * @param String ticker
    * @return String tickerPrice
    */

    function getTickerValues($ticker){
        //Dynamic data
        $API_KEY = "0NXZXOGWYWERI0NF";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,("https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=$ticker&interval=5min&apikey=$API_KEY"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        curl_close ($ch);
        $result = json_decode($server_output);
        
        if(isset($result->{'Time Series (5min)'})){
            $dataForRecentTime = $result->{'Time Series (5min)'};

            $dArr = array($dataForRecentTime);
            $dateMostRecentTime = key($dArr[0]);

            $dataForSingleTime = $dataForRecentTime->{$dateMostRecentTime};

            $tickerPrice = $dataForSingleTime->{'4. close'};

        }else{
            $tickerPrice="";
        }
        return $tickerPrice;

    }

    /* Function readInTickers
    *
    * @desc Function to pull in the information of all existing stock listings from the API
    * @Created on 22-06-2022
    * @return String[] allTickers
    */

    function readInTickers(){
        $API_KEY = "0NXZXOGWYWERI0NF";
        //allTickers format, first index will represent the ticker,
        //and the second represents a specific info on the ticker
        //s[x][0], is the symbol name for stock number x
        $data = file_get_contents("https://www.alphavantage.co/query?function=LISTING_STATUS&apikey=$API_KEY");
        $rows = explode("\n",$data);
        $allTickers = array();
        foreach($rows as $row) {
            $allTickers[] = str_getcsv($row);
        }

        return $allTickers;
    }

    /* Function makeWalletSale
    *
    * @desc function to modify the users wallet according to the sale, such that it is valid
    * @Created on 25-06-2022
    * @param String userWallet (JSON Formatted), String sellSymbol, String sellQuantity
    */

    function makeWalletSale($userWallet, $sellSymbol, $sellQuantity){
        $jsonExistingWalletToModify = json_decode($userWallet);
        $newSymbolQuantity = $jsonExistingWalletToModify->$sellSymbol-$sellQuantity;

        if($newSymbolQuantity > 0){
            $jsonExistingWalletToModify->$sellSymbol=$newSymbolQuantity;
            $updatedUserWallet = json_encode($jsonExistingWalletToModify);
            return $updatedUserWallet;
        }else if($newSymbolQuantity == 0){    //If user sells all of a stock, remove it from the wallet
            $jsonExistingWalletToModify->$sellSymbol=$newSymbolQuantity;
            $updatedUserWallet = json_encode($jsonExistingWalletToModify);
            $updatedUserWallet = deleteSymbolFromWallet($updatedUserWallet, $sellSymbol);
            return $updatedUserWallet;
        }else{  //If user tries to sell more than they have, return error
            return null;
        }
    }

    /* Function makeWalletPurchase
    *
    * @desc function to modify the users wallet according to the buy, such that it is valid
    * @Created on 25-06-2022
    * @param String userWallet (JSON Formatted), String buySymbol, String buyQuantity
    */

    function makeWalletPurchase($userWallet, $buySymbol, $buyQuantity){
        //Consider cases where user tries to buy more of a stock than they have, or a stock hold none of yet
        $userAlreadyHasSymbol = false;
        $jsonExistingWalletToModify = json_decode($userWallet);

        //Wallet already has the symbol, add to the quantity
        foreach($jsonExistingWalletToModify as $ticker => $quantHeld) {
            if($ticker == $buySymbol){
                $userAlreadyHasSymbol = true;
                $newSymbolQuantity = $jsonExistingWalletToModify->$buySymbol+$buyQuantity;
                $jsonExistingWalletToModify->$buySymbol=$newSymbolQuantity;
                $updatedUserWallet = json_encode($jsonExistingWalletToModify);
                return $updatedUserWallet;
            }
        }

        if(!$userAlreadyHasSymbol){ //Wallet does not have the symbol yet, and it's real, add it to the wallet
            $symbolExistanceCheck = getTickerValues($buySymbol);
            if($symbolExistanceCheck != ""){
                $jsonExistingWalletToModify->$buySymbol=$buyQuantity;
                $updatedUserWallet = json_encode($jsonExistingWalletToModify);
                return $updatedUserWallet;
            //Symbol doesn't exist, return null
            }else{
                return null;
            }
        }
    }


    /* Function deleteSymbolFromWallet
    *
    * @desc function to delete a symbol from a wallet when it is sold out
    * @Created on 25-06-2022
    * @param String userWallet (JSON Formatted), String sellSymbol
    */

    function deleteSymbolFromWallet($userWallet, $sellSymbol){
        $jsonExistingWalletToModify = json_decode($userWallet);
        unset($jsonExistingWalletToModify->$sellSymbol);
        $updatedUserWallet = json_encode($jsonExistingWalletToModify);
        return $updatedUserWallet;
    }

    /* Function updateUserWallet
    *
    * @desc function to update the users wallet
    * @Created on 25-06-2022
    * @param String userEmail, String updatedUserWallet
    */

    function updateUserWallet($emailLoggedIn, $updatedUserWallet){
        global $pdo;
        $qry = $pdo->prepare("UPDATE wallets SET userWallet = :userWallet WHERE userEmail = :userEmail");
        $qry -> execute(array(
            'userWallet' => $updatedUserWallet,
            'userEmail' => $emailLoggedIn
        ));

    }

    /* Function modifyPassword
    *
    * @desc function to change the password of the user logged in
    * @Created on 19-06-2022
    * @param String emailLoggedIn, String newPassword
    */

    function modifyPassword($emailLoggedIn, $newPassword){
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        global $pdo;
        
        $qry = $pdo->prepare("UPDATE users SET userPassword = :userPassword WHERE userEmail = :userEmail");
        $qry -> execute(array(
            'userPassword' => $hashedNewPassword,
            'userEmail' => $emailLoggedIn
        ));
    }

?>
