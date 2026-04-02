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

$access_key = isset($_GET['access']) ? sanitize($_GET['access']) : '';
$handle = isset($_GET['id']) ? strtolower(sanitize($_GET['id'])) : '';
$now = time();
$check_interval = $now - CACHE_YT_TTL;

// Basic "security"
if(empty($access_key) OR $access_key !== trim(ACCESS)) {
	if(ERROR_LOG) logger('YT: Access key incorrect.');
	exit;
}

// Check Channel Handle
if(empty($handle)) {
	if(ERROR_LOG) logger('YT: Missing `id` query parameter.');
	exit;
}

// Remove encoded @
if(substr($handle, 0, 3) == "%40") {
	$handle = substr($handle, 3);
}

// Remove @
if(substr($handle, 0, 1) == "@") {
	$handle = substr($handle, 1);
}

// Make sure certain files and folders exist and clean up cache
check_config();

// Fetch from cache or YouTube
$feed = cache_get($handle, CACHE_YT_PREFIX);

if(!$feed OR (isset($feed['checked']) AND $feed['checked'] < $check_interval)) {
	// Create initial item for feeds without cache
	if(!is_array($feed)) {
		$interval = floor(CACHE_EZTV_TTL / 3600);

		$feed = array();
		$feed['channel_name'] = $handle;
		$feed['channel_url'] = "https://youtube.com/@".$handle;
	    $feed['items'][] = array(
			'id' => 'init',
			'title' => 'Welcome to your new feed for channel '.$handle.'!',
			'link' => $feed['channel_url'],
			'date_released' => $now,
			'description' => "<p>The feed will be processed shortly and videos will start to show up here!<br /><small>Feeds are refreshed approximately every ".$interval." hours.</small></p>",
			'thumbnail' => ''
	    );
	}

	$has_error = false;

	// Find the Channel ID
	if(!isset($feed['channel_id'])) {
		$feed['channel_id'] = get_youtube_channel_id($handle);
	}

	if($feed['channel_id'] === false) {
		if(ERROR_LOG) logger('YT: Missing Channel ID '.$handle.'.');
		$has_error = true;
	}

	// Fetch the XML content from YouTube
	$response = make_request('https://www.youtube.com/feeds/videos.xml?channel_id='.$feed['channel_id']);

	// Handle response errors
	if($response['errno'] !== 0) {
		if(ERROR_LOG) logger('CURL: Channel '.$handle.'. Error: '.$response['error'].'.');
		$has_error = true;
	} 
	
	if($response['code'] !== 200) {
		if(ERROR_LOG) logger('YT: Could not fetch feed for channel '.$handle.'. Error: '.$response['code'].'.');
		$has_error = true;
	}

	// Handle content errors
	if(substr($response['body'], 0, 6) !== '<?xml ') {
		if(ERROR_LOG) logger('YT: Invalid data for channel '.$handle.'.');
		$has_error = true;
	}

	if(!$has_error) {	
		// Load the XML
		$xml = new SimpleXMLElement($response['body']);

		// Get Channel meta information
		$feed['channel_name'] = (strlen($xml->title) > 0) ? sanitize($xml->title) : $handle;
		$feed['channel_url'] = (strlen($xml->author->uri) > 0) ? sanitize($xml->author->uri)."/videos" : "https://youtube.com/@".$handle;
		$feed['checked'] = $now;
		$feed['http_code'] = $response['code'];
	
		// Loop through each item
		foreach($xml->entry as $entry) {
			// Get all data/meta data
			$namespaces = $entry->getNameSpaces(true);
			$yt = $entry->children($namespaces['yt']);
			$media = $entry->children($namespaces['media']);
	
			// Find basic information
			$video_id = (isset($yt->videoId)) ? sanitize((string)$yt->videoId) : "";
			$title = (isset($entry->title)) ? sanitize((string)$entry->title) : "";
			$video_url = (isset($entry->link['href'])) ? sanitize((string)$entry->link['href']) : "#";
			$published = (isset($entry->published)) ? strtotime(sanitize((string)$entry->published)) : 0;
	
			// Find additional information
			$thumbnail = (isset($media->group->thumbnail->attributes()->url)) ? sanitize((string)$media->group->thumbnail->attributes()->url) : "";
			$description = (isset($media->group->description)) ? sanitize((string)$media->group->description, true) : "";
	
			// Ignore if video id or title is missing, and ignore ads
			if(empty($video_id) OR empty($title) OR strpos($video_id, 'googleads') !== false) {
				continue;
			}

			// Only add unique videos
			if(!array_search($video_id, array_column($feed['items'], 'id'))) {
				// Format description, if there is a description
				if(strlen($description) > 0) {
					$description = htmlspecialchars($description);
					$description = nl2br($description);
					
					// Regex came from repo BetterVideoRss of VerifiedJoseph.
					$description = preg_replace('/(https?:\/\/(?:www\.)?(?:[a-zA-Z0-9-.]{2,256}\.[a-z]{2,20})(\:[0-9]{2,4})?(?:\/[a-zA-Z0-9@:%_\+.,~#"\'!?&\/\/=\-*]+|\/)?)/ims', '<a href="$1" target="_blank">$1</a>', $description);
				}
	
				$url_embed = http_build_query(array(
					'vid' => $video_id,
					'ch' => $handle
				));
	
				// Set up the embed url
				$url_embed = trim(MAIN_URL)."/watch.php?".$url_embed;
	
				// Sort out the description/item content
				$content = '';
			    if(!empty($thumbnail)) {
				    $content .= "<p><a href=\"".$url_embed."\"><img src=\"".$thumbnail."\" /></a></p>";
				}
				$content .= "<p>Video links: <a href=\"".$url_embed."\">Watch embedded in browser</a> or <a href=\"".$video_url."\">watch on YouTube</a>.</p>";
				if(strlen($description) > 0) {
					$content .= $description;
				}
	
			    $feed['items'][] = array(
					'id' => $video_id,
					'title' => $title,
					'link' => $url_embed,
					'date_released' => $published,
					'description' => $content,
					'thumbnail' => $thumbnail
			    );
			}
	
			unset($entry, $namespaces, $yt, $media, $video_id, $title, $video_url, $published, $thumbnail, $description, $url_embed, $content);
		}
	
		// Sort by date_released DESC */
		usort($feed['items'], fn($a, $b) => $b['date_released'] <=> $a['date_released']);
		
		// Keep the 50 newest items
		$feed['items'] = array_slice($feed['items'], 0, 50);
	}

	cache_set($handle, $feed, CACHE_YT_PREFIX);
}

// BUILD AND OUTPUT THE RSS FEED
$builddate = $feed['items'][0]['date_released']; // Get date from newest item

if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) AND strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $now) {
	header('HTTP/1.1 304 Not Modified', true);
	header('Cache-Control: max-age='.CACHE_YT_TTL.', private', true);
	exit;
}

header('Content-Type: application/rss+xml; charset=UTF-8', true);
header('Cache-Control: max-age='.CACHE_YT_TTL.', private', true);
header('Last-Modified: '.date('r', $builddate), true);
header('ETag: "'.$handle.'-'.$builddate.'"', true);

echo generate_rss_feed($feed, $builddate);
if(SUCCESS_LOG) logger('YT: Feed processed for Channel ID `' . $feed['channel_name'] . '`.', false);

exit;
?>