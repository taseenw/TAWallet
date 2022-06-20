<?php
    include("functions.php");
    
    $tickerPrice = getTickerValues($_GET["ticker"]);
    echo $tickerPrice;
?>