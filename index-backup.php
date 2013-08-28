<?php
if (isset($_POST['domain'])) {
	$domain = ereg_replace("[^A-Za-z0-9.\-]", "", trim($_POST['domain']) );
    header("Location: $domain");
}

if (isset($_SERVER['REQUEST_URI'])) {
	$catch = ereg_replace("[^A-Za-z0-9.\-]", "", trim($_SERVER['REQUEST_URI']) );
	
	if ($catch != "index.php") {
		$domain = $catch;
	} else {
		// Do nothing.
	} 
}

?>
<!DOCTYPE html>
<html>
<head>
<title>dns.mk9.me</title>
  <link rel="stylesheet" href="style.css">
  <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,700' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Pathway+Gothic+One' rel='stylesheet' type='text/css'>
</head>

<body>

<div id="wrap">
	<div id="header">
	<h1><a href="index.php">
	
			<span class="fade">d</span><span class="fade">n</span><span class="fade">s</span><span class="fade">.</span><span class="fade">m</span><span class="fade">k</span><span class="fade">9</span><span 
class="fade">.</span><span class="fade">m</span><span class="fade">e</span>


	</a></h1>
	</div>


	<!--[error]-->
	<div id="search_bar">
	<form action="index.php" method="post" >
	
		<input type="text" name="domain" id="input" size="40" class="textInput" placeholder="<?php if(isset($domain) && $domain != ""){echo $domain;} else {echo "google.com";}  ?>">
		<input type="submit" value="Go" class="btn">
	
	</form>
	</div>

	<div id="results">
	<?php	
	
	if (isset($domain) && $domain != "") {
		$dnsArrayWasEmpty = 0;
        
        
		// Getting Name servers
		echo "<pre><br><br><br>";
        
		$dnsNS = dns_get_record($domain,DNS_NS);
        if (!empty($dnsNS)) {
            echo "// Name servers<br>";
		    echo "dig ns $domain +short<br>";
		    foreach ($dnsNS as $nameserver) {
                $nsip = dns_get_record($nameserver[target],DNS_A);
			    print $nameserver[target]."    ->    ".$nsip[0][ip];
			    echo "<br>";
		    }
		    echo "<br><br>";
        } else { $dnsArrayWasEmpty++; }
		
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
            echo "// A Records<br>";
		    echo "dig a $domain +short<br>";
		    foreach ($dnsA as $a_record) {
			    print $a_record[ip];
			    echo "<br>";
		    }
		    echo "<br><br>";
        } else { $dnsArrayWasEmpty++; }
		
        
        
        // Getting WWW cname
        $wwwdomain = "www.".$domain;
		$dnsCNAME = dns_get_record($wwwdomain,DNS_CNAME);
        
        if (!empty($dnsCNAME)) {
            echo "// www CNAME<br>";
		    echo "dig a $wwwdomain +short<br>";
            echo $dnsCNAME[0][target];
            //print_r($dnsCNAME);
		    echo "<br><br><br>";
        } else { $dnsArrayWasEmpty++; }
        
		
		// Getting MX records
		
		$dnsMX = dns_get_record($domain,DNS_MX);
        
        if (!empty($dnsMX)) {
            echo "// MX Records<br>";
		    echo "dig mx $domain +short<br>";
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
            echo "// TXT Records<br>";
		    echo "dig txt $domain +short<br>";
		    foreach ($dnsTXT as $txt_record) {
			    print $txt_record[txt];
			    echo "<br>";
		    }
            echo "<br><br>";
        } else { $dnsArrayWasEmpty++; }


		// Getting SOA record

        $dnsSOA = dns_get_record($domain,DNS_SOA);
        if (!empty($dnsSOA)) {
            echo "// SOA record<br>";
		    echo "dig soa $domain +short<br>";
		    print $dnsSOA[0][mname]." ".$dnsSOA[0][rname]." ".$dnsSOA[0][serial]." ".$dnsSOA[0][refresh]." ".$dnsSOA[0][retry]." ".$dnsSOA[0][expire]." ".$dnsSOA[0]['minimum-ttl'];
		    echo "<br><br>";
        } else { $dnsArrayWasEmpty++; }
        
        
        // If all of the array checks were blank, then there is an issue with the domain
        if ($dnsArrayWasEmpty == 6) {
            echo "[!] We couldn't retrieve any DNS records for $domain.";
            
        }
        
        echo "</pre>";
	}
	
	?>
	</div>
    
</div>
    <div id="footer">
      <div class="wrapper">
        <p class="text-muted credit">Other tools: Proxy | DNS | Paste</p>
      </div>
    </div>
</body>
