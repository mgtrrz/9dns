<?php

function dnslookup($hostname, $record) {
    
    switch ($record) {
        case "PTR":
            $recordType = "DNS_PTR"
            break;
        case "CNAME":
            $recordType = "DNS_CNAME";
            break;
        case "MX":
            $recordType = "DNS_MX";
            break;
        case "NS":
            $recordType = "DNS_NS";
            break;
        case "A":
        default:
            $recordType = "DNS_A";
            break;
        
    }
    
    $result = dns_get_record($hostname,$recordType)
    
    if (!empty($result)) {
        
        // If the result only returns one item, then we're okay with providing a string.
        // If it returns more than one, we'll return an array.
        // ...is this good programming practice?
        // Probably not.
        // Unpredictable returns are not the best idea.
        
    } else {
        // Couldn't retrieve anything
        return NULL;
    }
    
}