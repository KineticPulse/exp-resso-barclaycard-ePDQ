<?php

$transaction_status = $_POST["transactionstatus"];
$transaction_id = $_POST["oid"];
$amount = $_POST["total"];


$dbhost = 'localhost';
$dbuser = 'web156-eedev';
$dbpass = 'EEDev';

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');

$dbname = 'web156-eedev';
mysql_select_db($dbname, $conn);



mysql_query("INSERT INTO exp_store_epdq (transaction_id, transaction_status, transaction_amount, transaction_time)
VALUES ('$transaction_id', '$transaction_status','$amount',NOW())");

mysql_close($conn)

?>;