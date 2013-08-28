<?php
require_once "Net/Whois.php";

$server = "whois.denic.de";
$query  = "loudservers.com";     // get information about
                               // this domain
$whois = new Net_Whois;
$data = $whois->query($query);
echo $data;
?> 
