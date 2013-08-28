<?php
require 'Net/DNS2.php';
echo "<pre>";
$server = gethostbyname('b2.me.afilias-nst.org'); // 192.48.79.30

$r = new Net_DNS2_Resolver(array('nameservers' => array($server)));
$result = $r->query('mk9.me', 'NS');

print_r ($result);
echo "</pre>";