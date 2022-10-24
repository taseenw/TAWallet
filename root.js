/**
* Author: Taseen Waseq
* Created on 24-10-2022
* JavaScript file containing all js functions called throughout the project
*/

/* Function updateBuySummary
*
* @desc function to update the buy modal summary, with the updated quantity, ticker, and total price
*/

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
        pricePerToBuy.innerHTML = "$ "+this.responseText;
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
        document.getElementById('tickerQuantityToBuy').innerHTML = tickerQuantToBuy;
        if(isNaN(totalBuyPrice)){totalBuyPrice="";}
        document.getElementById('totalBuyPrice').innerHTML = "$ "+totalBuyPrice;
    }

}

/* Function updateSellSummary
*
* @desc function to update the sell modal summary, with the updated quantity, ticker, and total price
*/

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
            pricePer.innerHTML = "$ "+this.responseText;
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
    if(isNaN(totalSalePrice)){totalSalePrice="";}
    document.getElementById('totalSalePrice').innerHTML = "$ "+totalSalePrice;

}

/* Function openBuyModal
*
* @desc function to open the buy modal
*/

function openBuyModal(){
    var buyModal = document.getElementById('buyModal');
    buyModal.style.display = "block";
}

/* Function openSellModal
*
* @desc function to open the sell modal
*/

function openSellModal(){
    var sellModal = document.getElementById('sellModal');
    sellModal.style.display = "block";
}

/* Function closeModal
*
* @desc function to close the buy and sell modal
*/

function closeModal() {
    buyModal.style.display = "none";
    sellModal.style.display = "none";
}

/* Function exportWallet
*
* @desc function to export the portfolio table to an excel file, and download it
*/

function exportWallet(type, fn, dl){
var elt = document.getElementById('portfolioTable');
var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
return dl ?
    XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) :
    XLSX.writeFile(wb, fn || ('Portfolio.' + (type || 'xlsx')));
}