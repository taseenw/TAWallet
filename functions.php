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
        $body = "";
        //Put holdings in a table

        $body .= "<table class='portfolioTable' id='portfolioTable'>
                <thead>
                    <tr>
                        <th scope='col'>Ticker</th>
                        <th scope='col'>Quantity</th>
                        <th scope='col'>Current Price</th>
                        <th scope='col'>Total Value</th>
                        <th scope='col'>Day Change</th>
                    </tr>
                </thead>";

        //Iterate all tickers (stocks/coins in wallet), print holding data
        foreach($jsonWallet as $ticker => $quantHeld) {
            $tickerValues = getTickerValues($ticker);
            $currentTickerValue = $tickerValues['tickerPrice'];
            $previousDayChange = $tickerValues['previousDayChange'];
            //Return case of invalid ticker
            if($currentTickerValue != ""){
                $currentTickerHoldingValue = $currentTickerValue * $quantHeld;

                $body .= "<tr>
                        <td>".$ticker."</td>
                        <td>".$quantHeld."</td>
                        <td>$".$currentTickerValue."</td>
                        <td>$".$currentTickerHoldingValue."</td>
                        <td>".$previousDayChange."%</td>
                    </tr>";



                // echo $ticker.' : '.$quantHeld." | Current Price: $".$currentTickerValue." | Holding Value: $".$currentTickerHoldingValue."<br>";
                $portfolioTotalValue += $currentTickerHoldingValue;
                $tickerCount++;
            }else{
                //Print error message in table properly
                $body .= "<tr>
                        <td>".$ticker."</td>
                        <td>".$quantHeld."</td>
                        <td colspan='2'><i>API Call Limit Reached</i></td>
                        <td></td>
                    </tr>";
            }
            $holdCount++;
        }
        if($portfolioTotalValue == 0 && $holdCount !=0){
            $body .= "<tr class = 'totalRow'>
                    <td colspan='4'>API Call Limit Reached</td>
                </tr>";
        }else{
            $body .= "<tr class = 'totalRow'>
                    <th colspan='5'>Portfolio Value : $".round($portfolioTotalValue, 2)."</th>
                </tr>";
        }
        $body .= "</table>";

        return $body;
    }

    /* Function getTickerValues
    *
    * @desc Function to pull the information for the passed ticker, using the STOCK API
    * @Created on 17-06-2022
    * @param String ticker
    * @return Array tickerData [tickerPrice, tickerChange]
    */

    function getTickerValues($ticker){
        $tickerData = array();
        $queryString = http_build_query([
            'access_key' => 'edcb0c1f1de2d9d2c9c3b0a67e2fe39b'
        ]);
        $ch = curl_init(sprintf('%s?%s', 'http://api.marketstack.com/v1/tickers/'.$ticker.'/intraday', $queryString));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);

        $apiResult = json_decode($json, true);
        //Not found, or API call limit reached, return error message
        if(isset($apiResult['error'])){
            $tickerData['tickerPrice'] = $apiResult['error']['message'];
            $tickerData['previousDayChange'] = $apiResult['error']['message'];
        }else{
            $data = $apiResult['data'];
            // $tickerPrice = $data['eod'][0]['close'];
            $tickerData['tickerPrice'] = $data['intraday'][0]['last'];
            // $tickerData['previousDayChange'] = $tickerData['tickerPrice'] - $data['intraday'][0]['close']; 
            //previous day change is the percentage difference between the current price and the previous day close
            $tickerData['previousDayChange'] = round((($tickerData['tickerPrice'] - $data['intraday'][0]['close'])/$data['intraday'][0]['close'])*100, 2);
        }

        return $tickerData;
    }

    /* Function getLastClose
    *
    * @desc Function to pull the close of the previous day, used when market is closed
    * @Created on 17-06-2022
    * @param String ticker
    * @return String getLastEODclose
    */

    function getLastEODclose($ticker){
        $queryString = http_build_query([
            'access_key' => 'edcb0c1f1de2d9d2c9c3b0a67e2fe39b'
        ]);

        $ch = curl_init(sprintf('%s?%s', 'http://api.marketstack.com/v1/tickers/'.$ticker.'/eod', $queryString));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);

        $apiResult = json_decode($json, true);
        //Not found, or API call limit reached, return error message
        if(isset($apiResult['error'])){
            $lastEODclose = $apiResult['error']['message'];
        }else{
            $data = $apiResult['data'];
            $lastEODclose = $data['eod'][0]['close'];
        }
        return $lastEODclose;
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
            $symbolExistanceCheck = getTickerValues($buySymbol)['tickerPrice'];
            if($symbolExistanceCheck != "Not Found"){
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
