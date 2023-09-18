<?php
    define('HOST', 'localhost');
    define('USER', 'root');
    define('PASS', '');
    define('DB', 'simple-banking-api');

    $conn = new mysqli(HOST, USER, PASS, DB) or die('Koneksi error untuk mengakses database');
?>