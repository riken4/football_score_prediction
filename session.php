<?php
session_start();
if(!isset($_SESSION['username'])){
    header("location:http://localhost/6workingsem/login.php");
    exit;
}