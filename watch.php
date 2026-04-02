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
$handle = isset($_GET['ch']) ? sanitize($_GET['ch']) : '';
$video_id = isset($_GET['vid']) ? sanitize($_GET['vid']) : '';

// Only cached videos can be watched here
$channel = cache_get($handle, CACHE_YT_PREFIX); // 360 days. We don't care for the cache age, just that it's there.

$video = false;
if(is_array($channel)) {
	$key = array_search($video_id, array_column($channel['items'], 'id'));

	if($key !== false) {
		$video = $channel['items'][$key];
	}
}

if(!$video) {
	if(ERROR_LOG) logger('YT: Invalid or missing Video ID ('.$handle.': '.$video_id.') on watch.php.');
	die("Please refresh your feeds and provide a valid Video ID.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>GooseRSS: <?php echo $video['title']; ?></title>
	<link rel="stylesheet" href="./assets/embed-simple.css">

	<meta name="description" content="<?php echo $video['title']; ?> by <?php echo $channel['channel_name']; ?>" />
	<meta name="generator" content="GooseRSS - Youtube Embeds" />

	<meta property="og:type" content="website" />
	<meta property="og:locale" content="en_US" />
	<meta property="og:url" content="<?php echo MAIN_URL; ?>/watch.php?vid=<?php echo $video_id; ?>&ch=<?php echo $handle; ?>" />
	<meta property="og:site_name" content="GooseRSS - Youtube Embeds" />
	<meta property="og:title" content="Watch this embedded video:" />
	<meta property="og:description" content="<?php echo $video['title']; ?>" />
	<?php
	if(isset($video['thumbnail'])) {
		echo '<meta property="og:image" content="'.$video['thumbnail'].'" />';
		echo '<meta property="og:image:alt" content="'.$video['thumbnail'].'" />';
	}
	?>
</head>

<body id="top">
	<header>
		<h1><?php echo $video['title']; ?></h1>
	</header>
	
	<main>
		<section id="text">

			<div class="videowrap">
				<iframe 
					id="player" 
					type="text/html"
					src="https://www.youtube.com/embed/<?php echo $video_id; ?>" 
					title="YouTube video player" 
					frameborder="0" 
					allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
					referrerpolicy="strict-origin-when-cross-origin" 
					allowfullscreen
				></iframe>
			</div>

		</section>
	</main>

	<footer>
		<p>This page does not store or distribute videos.</p>
	</footer>

	<script src="./assets/embed-keepalive.js"></script>
</body>
</html>