<?php
/* ---------------------------------------------------------------------------
*  GooseRSS the YouTube and EZTV RSS Generator.
*
*  COPYRIGHT NOTICE
*  Copyright 2025-2026 Arnan de Gans. All Rights Reserved.
*
*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from its use.
--------------------------------------------------------------------------- */

if(!defined('MAIN_PATH')) {
	define('MAIN_PATH', __DIR__);
}

require_once(MAIN_PATH . '/config.php');
require_once(MAIN_PATH . '/functions.php');

$access_key = isset($_GET['access']) ? sanitize($_GET['access']) : '';
$handle = isset($_GET['handle']) ? strtolower(sanitize($_GET['handle'])) : '';

// Basic "security"
if(empty($access_key) OR $access_key !== trim(ACCESS)) {
	die("Access key incorrect!");
	if(ERROR_LOG) logger('YT: Access key incorrect on subscribe.php.');
	exit;
}

if(!empty($handle)) {
	// It's a YouTube Handle?
	if(substr($handle, 0, 3) == "%40" OR substr($handle, 0, 1) == "@") {
		$handle = str_replace(array("%40", "@"), "", $handle);
		$feed_file = "/ytrss.php";
	}

	// It's a IMDb ID?
	if(substr($handle, 0, 2) == "tt") {
		$handle = str_replace("tt", "", $handle);
		$feed_file = "/eztvrss.php";
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>GooseRSS: RSS Link Generator</title>
	<link rel="stylesheet" href="./assets/embed-simple.css">

	<meta name="description" content="Generate Youtube and EZTV Rss feeds that you can subscribe to in a RSS reader." />
	<meta name="generator" content="GooseRSS - RSS Link Generator" />

	<meta property="og:type" content="website" />
	<meta property="og:locale" content="en_US" />
	<meta property="og:url" content="<?php echo MAIN_URL; ?>/subscribe.php" />
	<meta property="og:site_name" content="GooseRSS - RSS Link Generator" />
	<meta property="og:title" content="RSS Link Generator" />
	<meta property="og:description" content="Generate Youtube and EZTV Rss feeds that you can subscribe to in a RSS reader." />
</head>

<body id="top">

	<header>
		<h1>RSS Link Generator</h1>
	</header>
	
	<main>
		<section id="text">

			<div class="card">
		    <?php if(!empty($handle)) { ?>

	        <div class="result">
	            <strong>Your RSS Link:</strong><br>
	            <p>[ <a href="<?php echo MAIN_URL.$feed_file."?access=".ACCESS."&id=".$handle; ?>">Click to subscribe</a> ]</p>
	            
	            <p class="copy-hint">If clicking the above link doesn't prompt your RSS reader to subscribe, right-click the link and select "Copy Link Address" to paste it into your RSS reader.</p>
	            <p><a href="<?php echo MAIN_URL."/subscribe.php?access=".ACCESS; ?>">Make another link</a></p>
	        </div>

		    <?php } else { ?>

	        <p>Enter a YouTube Channel Handle (prefixed with an '@') or an IMDb ID (including the initial 'tt') and click 'Generate Link' to get a RSS link you can subscribe to.</p>

		    <form method="GET">
		        <label for="handle">Enter a YouTube Handle or IMDb ID:</label>
		        <input type="hidden" name="access" id="access" value="<?php echo $access_key; ?>">

		        <input type="text" name="handle" id="handle" placeholder="e.g. @arnandegans or tt0758774" required="1">

		        <button type="submit">GENERATE LINK</button>
		    </form>

	        <p><small>The generated link won't work if you enter the wrong value. Check the error.log file for details if a feed doesn't work.</small></p>

		    <?php } ?>
			</div>

		</section>
	</main>

	<footer>
		<p>This page does not store or distribute videos.</p>
	</footer>

</body>
</html>