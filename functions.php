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
    * @return Array userWallet (JSON Formatted)
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
        $tickerCount = 0;
        $jsonWallet = json_decode($userWallet);
        $portfolioTotalValue = 0;

        //Iterate all tickers (stocks/coins in wallet), print holding data
        foreach($jsonWallet as $ticker => $quantHeld) {
            $currentTickerValue = getTickerValues($ticker);
            $currentTickerHoldingValue = $currentTickerValue * $quantHeld;
            echo $ticker.' : '.$quantHeld." | Current Price: $".$currentTickerValue." | Holding Value: $".$currentTickerHoldingValue."<br>";
            $portfolioTotalValue += $currentTickerHoldingValue;
            $tickerCount++;
        }

        echo "Portfolio Value : $".round($portfolioTotalValue, 2);

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
        $date = "2022-06-17 15:45:00";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,("https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=$ticker&interval=5min&apikey=$API_KEY"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        curl_close ($ch);
        $result = json_decode($server_output);


        //Using the non-premium version of API, there is a limit to calls/minute, and will return error message if overused
        try{
            $dataForRecentTime = $result->{'Time Series (5min)'};

            $dataForSingleTime = $dataForRecentTime->{$date};

            $tickerPrice = $dataForSingleTime->{'4. close'};

        }catch(Exception $e){
            $tickerPrice = "API MAX: 5 calls a minute, please refresh after time passed, as this is the demo version";
        }
        return $tickerPrice;

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
