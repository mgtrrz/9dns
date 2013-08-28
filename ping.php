<?php
require 'Net/DNS2.php';
include('phpwhois-4.2.2/whois.main.php');
include('testsub.php');

if (isset($domain) && $domain != "") {
    $dnsArrayWasEmpty = 0;
    if (!isset($comment)) {
    	$comment = 1;
    }

    echo "<pre>";
    
    
    
    // We'll take away www. from in front of the domain if it exists. 
    $prewwwdomain = substr($domain,0, 4);
    if ($prewwwdomain == "www.") {
        $domain = substr($domain, 4);
    }
    
    // Detecting if any subdomains exist
    $subdomainsDetected = extract_subdomains($domain);
    
    if (!empty($subdomainsDetected)) {
        // We've detected a subdomain, but we don't want to try to parse it as an actual domain
        // We're going to remove it from the main $domain variable but set it aside.
        $subdomainsDetected = $subdomainsDetected.".";
        $domain = str_replace($subdomainsDetected, "" ,$domain);
        
    }
    
    
    $whoisDomain = new Whois();
    $whoisresult = $whoisDomain->Lookup($domain);
    
    $myFile = "query_log";
    $fh = fopen($myFile, 'a') or die("can't open file");
    $stringData = date("Y-m-d H:i:s")."\t".$_SERVER['REMOTE_ADDR']."\t".$domain."\n";
    fwrite($fh, $stringData);
	
    if ($domain == "about") {
        echo "// What's the purpose of this tool?<br>";
        echo "To provide quick and important DNS and basic WHOIS information in an easy-to-read fashion.<br>";
        echo "The output of the script is similar to running dig commands in terminal.<br>";
        echo "Additionally, it's easy to copy and paste information to provide to others.<br><br>";
        echo "// Contact<br>";
        echo "This script was written by Marcus Gutierrez.<br><br>";
        echo "If you come across any weird bugs or have suggestions for improvements on this tool, email me at:<br>";
        $visitor = $_SERVER['REMOTE_ADDR'];
        if ($visitor == "216.110.94.228" || $visitor == "74.202.255.244") {
            echo "marcusgutierrez@hostgator.com";
        } else {
            echo "markg90@gmail.com";
        }
        echo "<br>";
        
    } elseif ($whoisresult['regrinfo']['registered'] == 'unknown') {
        echo "[!] That doesn't seem to be a valid domain name. ";
    
    
    } elseif ($whoisresult['regrinfo']['registered'] != 'no') {
        
        // Getting WHOIS information
        if (!isset($whois) || $whois == 1) {
            
            if ($comment != 0) { echo "// WHOIS information<br>"; }
            echo "Domain information was updated on: ". $whoisresult['regrinfo']['domain']['changed'];
            echo "<br>";
            echo "Domain expires on: ". $whoisresult['regrinfo']['domain']['expires'];
            echo "<br>";
            echo "<br>";
            if (isset($whoisresult['regrinfo']['domain']['sponsor'])) {
                echo "Domain registrar is: ".$whoisresult['regrinfo']['domain']['sponsor']." (through: ".$whoisresult['regyinfo']['registrar'].")";
                echo "<br>";
            }  else {
                echo "Registrar is: ". $whoisresult['regyinfo']['registrar'];
                echo "<br>";
            }
            echo "<br>See more WHOIS information at: <a href=\"http://whois.com/whois/$domain\">http://whois.com/whois/$domain</a><br><br><br>";
        }
        
        if ($comment != 0) { echo "// Name servers at the registrar<br>"; }
    
        foreach ($whoisresult['regrinfo']['domain']['nserver'] as $whoisns => $whoisip) {
            print $whoisns."    ->    ".$whoisip;
            
            // We're also going to push these values to an array to match against zone file NS later
            if (!isset($registrant_nameservers_array)) {
                $registrant_nameservers_array = array($whoisns => $whoisip);
            } else {
                $registrant_nameservers_array[$whoisns] = $whoisip;
            }
            
            echo "<br>";
        }
        echo "<br><br>";
        
        //echo "registrant_nameservers_array: ";
        //print_r($registrant_nameservers_array);
    
        // Name servers at the DNS zone
        $dnsNS = dns_get_record($domain,DNS_NS);
        if (!empty($dnsNS)) {
            if ($comment != 0) { echo "// Name servers at the DNS zone<br>"; }
            if ($dig) { echo "dig ns $domain +short<br>"; }
            foreach ($dnsNS as $nameserver) {
                $nsip = dns_get_record($nameserver[target],DNS_A);
                print $nameserver[target]."    ->    ".$nsip[0][ip];
                
                // We're also going to push these values to an array to match against zone file NS later
                if (!isset($registrant_nameservers_array)) {
                    $dns_nameservers_array = array($whoisns => $whoisip);
                } else {
                    $dns_nameservers_array[$whoisns] = $whoisip;
                }
                
                echo "<br>";
            }
            echo "<br><br>";
        } else { $dnsArrayWasEmpty++; }
        
        
        // We're going to make a quick check to see if the name servers match, if not, we'll present a warning.
        // First we'll check if the name servers match. Then if that's okay, check to see if IPs match.
        /*
        if (!empty($DNS)) {
            
            foreach($registrant_nameserver_array in $nscheck => $nsip) {
                
                if (!in_array($nscheck, $dns_nameservers_array)) {
                    // then we couldn't find a name server at the registrar set in the zone.
                }
                
                
            }
            
        }
        */
    
        if (strpos($nameserver[target], "cloudflare")) {
            // We detected this domain name uses Cloudflare Nameservers.
        
            $cfdomain = "direct.".$domain;
            $cfDirect = dns_get_record($cfdomain,DNS_A);
            if (!empty($cfDirect)) {
                echo "// Cloudflare direct IP<br>";
                print $cfdomain."    ->    ".$cfDirect[0][ip];
                echo "<br>";
            }
            echo "<br><br>";
        }
    
    
    	// Getting A records
    
    	$dnsA = dns_get_record($domain,DNS_A);
        if (!empty($dnsA)) {
            if ($comment != 0) { if (!empty($subdomainsDetected)) { echo "// A Records for $domain<br>" ; } else { echo "// A Records<br>"; }}
    	    if ($dig) { echo "dig a $domain +short<br>"; }
    	    foreach ($dnsA as $a_record) {
    		    print $a_record[ip];
    		    echo "<br>";
    	    }
    	    echo "<br><br>";
        } else { $dnsArrayWasEmpty++; }
        
        // Getting the Subdomain A records (if one was entered)
        if (!empty($subdomainsDetected)) {
            $domainwithsub = $subdomainsDetected.$domain; 
    
            $subdnsA = dns_get_record($domainwithsub,DNS_A);
            if (!empty($subdnsA)) {
                if ($comment != 0) { echo "// A Records for $domainwithsub<br>"; }
        	    if ($dig) { echo "dig a $domainwithsub +short<br>"; }
        	    foreach ($subdnsA as $sub_a_record) {
        		    print $sub_a_record[ip];
        		    echo "<br>";
        	    }
        	    echo "<br><br>";
            } else {
                echo "[!] Could not retrieve any A records for $domainwithsub";
                echo "<br><br><br>";
            }
    	
        }
        
        // Getting WWW cname
        $wwwdomain = "www.".$domain;
    	$dnsCNAME = dns_get_record($wwwdomain,DNS_CNAME);
        
        if (!empty($dnsCNAME)) {
            if ($comment != 0) { if (!empty($subdomainsDetected)) { echo "// www CNAME for $domain<br>" ; } else { echo "// www CNAME<br>"; }}
    	    if ($dig) { echo "dig a $wwwdomain +short<br>"; }
            $dnsCNAMEpointsto = dns_get_record($wwwdomain,DNS_A);
            echo $dnsCNAME[0][target]."    ->    ".$dnsCNAMEpointsto[0][ip];
            //print_r($dnsCNAME);
    	    echo "<br><br><br>";
        } else { $dnsArrayWasEmpty++; }
        
    	
    	// Getting MX records
    	
    	$dnsMX = dns_get_record($domain,DNS_MX);
        
        if (!empty($dnsMX)) {
            if ($comment != 0) { if (!empty($subdomainsDetected)) { echo "// MX record for $domain<br>" ; } else { echo "// MX record<br>"; }}
    	    if ($dig) { echo "dig mx $domain +short<br>"; }
    	    foreach ($dnsMX as $mx_record) {
                $mx_ip = dns_get_record($mx_record[target],DNS_A);
    		    print $mx_record[pri]." ".$mx_record[target]."    ->    ".$mx_ip[0][ip];
    		    echo "<br>";
    	    }
    	    echo "<br><br>";
        } else { $dnsArrayWasEmpty++; }
    	
    	// Getting TXT records
    
    	
    	$dnsTXT = dns_get_record($domain,DNS_TXT);
        if (!empty($dnsTXT)) {
            if ($comment != 0) { if (!empty($subdomainsDetected)) { echo "// TXT records for $domain<br>" ; } else { echo "// TXT records<br>"; }}
    	    if ($dig) { echo "dig txt $domain +short<br>"; }
    	    foreach ($dnsTXT as $txt_record) {
    		    print $txt_record[txt];
    		    echo "<br>";
    	    }
            echo "<br><br>";
        } else { $dnsArrayWasEmpty++; }
    
    
    	// Getting SOA record
    
        $dnsSOA = dns_get_record($domain,DNS_SOA);
        if (!empty($dnsSOA)) {
            if ($comment != 0) { if (!empty($subdomainsDetected)) { echo "// SOA record for $domain<br>" ; } else { echo "// SOA record<br>"; }}
    	    if ($dig) { echo "dig soa $domain +short<br>"; }
    	    // print $dnsSOA[0][mname]." ".$dnsSOA[0][rname]." ".$dnsSOA[0][serial]." ".$dnsSOA[0][refresh]." ".$dnsSOA[0][retry]." ".$dnsSOA[0][expire]." ".$dnsSOA[0]['minimum-ttl'];
            print $dnsSOA[0][mname]."   ".$dnsSOA[0][rname]."<br>";
            echo "                      ".$dnsSOA[0][serial]." ; Serial Number<br>";
            echo "                      ".$dnsSOA[0][refresh]." ; Refresh<br>";
            echo "                      ".$dnsSOA[0][retry]." ; Retry<br>";
            echo "                      ".$dnsSOA[0][expire]." ; Expire<br>";
            echo "                      ".$dnsSOA[0]['minimum-ttl']." ; Minimum TTL";
    	    echo "<br><br>";
        } else { $dnsArrayWasEmpty++; }
        
        // If all of the array checks were blank, then there is an issue with the domain
        if ($dnsArrayWasEmpty == 6) {
            echo "[!] We couldn't retrieve any DNS records at the name servers above for $domain.<br>";
            echo "Is there an issue with the zone file?";
            echo "<br>";
        }
        

        
    } else {
        echo "[!] $domain is not yet registered.<br><br>Were you interested in purchasing it? Why not? What if I told you that 0.05% of all proceeds<br>";
        echo "go towards the 'Save an IPv4 Address' foundation? It's a serious issue. Nobody wants<br>"; 
        echo "to try to enter an IPv6 address into their zone file. I bet you won't even enter it<br>";
        echo "correctly the first time. Do yourself a favor and buy this lonely domain today.<br>";
        echo "The IPv4 addresses will thank you.";
        
    }
    
    if (isset($raw) && $raw == 1) { print_r($whoisresult); }
    
    echo "</pre>";
}