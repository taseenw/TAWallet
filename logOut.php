<?php
    /**
    * Author: Taseen Waseq
    * Created on 17-06-2022
    * PHP file destroying session upon logout request from user
    */

    session_start();
    session_unset();
    session_destroy();
    header("location: index.php");

?>
