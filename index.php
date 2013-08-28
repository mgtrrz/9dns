<?php
if (isset($_POST['domain'])) {
    $domain = ereg_replace("[^A-Za-z0-9.\-]", "", trim($_POST['domain']) );
    header("Location: $domain");
}

if (isset($_SERVER['REQUEST_URI'])) {
    // We're grabbing variables after a domain name for adding extras into our results.
    $uriArray = explode('?', $_SERVER['REQUEST_URI']);
    parse_str($_SERVER['QUERY_STRING']);
    
	$catch = ereg_replace("[^A-Za-z0-9.\-]", "", trim($uriArray[0]) );
	
	if ($catch != "index.php") {
		$domain = $catch;
	} else {
		// Do nothing.
	} 
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Yet another DNS tool">
    <meta name="author" content="Marcus Gutierrez">

    <title>dns.mk9.me</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap.css" rel="stylesheet">

    
    <link rel="stylesheet" href="style2.css">
    
    <link rel="stylesheet" href="header-<?php if(isset($domain) && $domain != ""){echo "results";} else {echo "main";}  ?>.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Pathway+Gothic+One' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
    <script type="text/javascript">$(function() { $('#toggle').click(function() { $('.toggle').slideToggle('fast'); return false;}); });</script>
  </head>

  <body>
    <div id="wrap">
        <div id="container">
            <div id="header">
            <h1>
                <a href="index.php">
	            <span class="fade">d</span><span class="fade">n</span><span class="fade">s</span><span class="fade">.</span><span class="fade">m</span><span class="fade">k</span><span class="fade">9</span><span 
class="fade">.</span><span class="fade">m</span><span class="fade">e</span>
                </a>
            </h1>
            </div><!-- /header -->
            
            <div id="entry">
            
                <form class="form-inline" role="form"  method="post">
                    <div>
                        <div class="input-group">
                            <input type="text" class="form-control" name="domain" placeholder="<?php if(isset($domain) && $domain != ""){echo $domain;} else {echo "google.com";}  ?>">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button">Go</button>
                                </span>
                        </div>
                        
                    </div>
                </form>
            </div><!-- /entry -->
            
            <div id="results">
            
                <?php include('ping.php'); ?>
            
            
            </div><!-- /results -->
            
        </div><!-- /container -->
    </div><!-- /wrap -->
    
    <div id="footer">
      <div class="container">
        <p class="text-muted credit">Other tools: <a href="http://proxy.mk9.me">Proxy</a> | <a href="http://dns.mk9.me">DNS</a> | <a href="http://dns.mk9.me/about">Who made this?</a></p>
      </div>
    </div>
    
  </body>
</html>