<?php
session_start();
if(!isset($_SESSION['user_id'])){
    $_SESSION['user_id'] = 1; return;
    header("Location:../login.html");
    exit();
}
?>