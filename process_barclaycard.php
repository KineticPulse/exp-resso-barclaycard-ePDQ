<?php

$transaction_status = $_POST["transactionstatus"];
$transaction_id = $_POST["oid"];
$amount = $_POST["total"];

$path = "/home/sites/kineticpulse.net/public_html/ee/logs/";
#set your logfile directory path here
$FILE = fopen($path."$transaction_id-return.csv", "a");
fwrite($FILE, "orderID='$transaction_id',");
fwrite($FILE, "status='$transaction_status',");
fwrite($FILE, "total='$amount'");

fclose($FILE);



?>