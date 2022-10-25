<?php
    /**
    * Author: Taseen Waseq
    * Created on 20-06-2022
    * PHP file responding to AJAX request returning the price of the sent symbol
    */

    include("functions.php");
    
    $tickerPrice = getTickerValues($_GET["ticker"])['tickerPrice'];
    echo $tickerPrice;
?>