<?php
    /**
    * Author: Taseen Waseq
    * Created on 15-06-2022
    * PHP file containing necessary credentials for database access
    */

    $mysql_hostname = "localhost";
    $mysql_username = "root";
    $mysql_password = "";
    $mysql_database = "tawallet";

    $dsn = "mysql:host=".$mysql_hostname.";dbname=".$mysql_database;

    $debug = false;

    try{
        $pdo= new PDO($dsn, $mysql_username,$mysql_password, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }

    catch (PDOException $e){
        echo 'PDO error: could not connect to DB, error: '.$e;
    }
?>