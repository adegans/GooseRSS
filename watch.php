<?php
/* ---------------------------------------------------------------------------
*  gooseRSS the YouTube and EZTV RSS Generator.
*
*  COPYRIGHT NOTICE
*  Copyright 2025-2026 Arnan de Gans. All Rights Reserved.
*
*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from its use.
--------------------------------------------------------------------------- */

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/functions.php');

// Fetch the url parameters
$handle = isset($_GET['id']) ? strtolower(sanitize($_GET['id'])) : '';
$video_id = isset($_GET['vid']) ? sanitize($_GET['vid']) : '';

// Replace encoded @ with a real @
if(substr($handle, 0, 3) == "%40") {
	$handle = '@'.substr($handle, 3);
}

// Maybe add missing @ for the channel name
if(substr($handle, 0, 1) != "@") {
	$handle = '@'.$handle;
}

// Only cached videos can be watched here
$channel = cache_get($handle, CACHE_YT_PREFIX, 31104000); // 360 days. We don't care for the cache age, just that it's there.
$video = false;
if(is_array($channel)) {
	$key = array_search($video_id, $channel['items']);
	if(isset($channel['items'][$key])) $video = $channel['items'][$key];
}

// Figure out the URL (for sharing this page)
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$current_url .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>gooseRSS: <?php echo $video['title']; ?></title>
	<link rel="stylesheet" href="./assets/embed-simple.css">

	<meta name="description" content="<?php echo $channel['channel_name']; ?>: <?php echo $video['title']; ?>" />
	<meta name="generator" content="gooseRSS" />

	<meta property="og:type" content="website" />
	<meta property="og:locale" content="en_US" />
	<meta property="og:url" content="<?php echo $current_url; ?>" />
	<meta property="og:site_name" content="gooseRSS Watch Page" />
	<meta property="og:title" content="Watch this video from <?php echo $channel['channel_name']; ?>" />
	<meta property="og:description" content="<?php echo $video['title']; ?>" />
	<meta property="og:image" content="https://img.youtube.com/vi/<?php echo $video_id; ?>/0.jpg" />
	<meta property="og:image:alt" content="<?php echo $video['title']; ?>" />
</head>

<body id="top">
	<header>
		<h1><?php echo $video['title']; ?></h1>
	</header>
	
	<main>
		<section id="text">

			<?php if($video AND !empty($video_id)) { ?>
			<div class="videowrap">
				<iframe 
					src="https://www.youtube-nocookie.com/embed/<?php echo $video_id; ?>" 
					title="YouTube video player" 
					frameborder="0" 
					allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
					referrerpolicy="strict-origin-when-cross-origin" 
					allowfullscreen
				></iframe>
			</div>
			<?php } else { ?>
			<p>Please refresh your feeds and provide a valid Video ID.</p>
			<?php } ?>

		</section>
	</main>

	<footer>
		<p>This page does not store or distribute videos.</p>
	</footer>

	<script src="./assets/embed-keepalive.js"></script>

</body>
</html>