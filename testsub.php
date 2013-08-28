<?php
/*
$testArray = array(
    'sub1.sub2.example.co.uk',
    'sub1.example.com',
    'example.com',
    'sub1.sub2.sub3.example.co.uk',
    'sub1.sub2.sub3.example.com',
    'sub1.sub2.example.com'
);

foreach($testArray as $k => $v) {
    echo $k." => ".extract_subdomains($v)."\n";
}
*/
function extract_domain($domain) {
    if(preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches))
    {
        return $matches['domain'];
    } else {
        return $domain;
    }
}

function extract_subdomains($domain) {
    $subdomains = $domain;
    $domain = extract_domain($subdomains);

    $subdomains = rtrim(strstr($subdomains, $domain, true), '.');

    return $subdomains;
}