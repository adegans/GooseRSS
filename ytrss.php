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

/* ------------------------------------------------------------------------ */
/* MAIN LOGIC                                 								*/
/* ------------------------------------------------------------------------ */

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/functions.php');

$access_key = isset($_GET['access']) ? sanitize($_GET['access']) : '';
$handle = isset($_GET['id']) ? strtolower(sanitize($_GET['id'])) : '';

// Basic "security"
if(empty($access_key) OR $access_key !== trim(ACCESS)) {
	if(ERROR_LOG) logger('YT: Access key incorrect.');
	exit;
}

// Retrieve Channel Handle */
if(empty($handle)) {
	if(ERROR_LOG) logger('YT: Missing `id` query parameter.');
	exit;
}

// Replace encoded @ with a real @
if(substr($handle, 0, 3) == "%40") {
	$handle = '@'.substr($handle, 3);
}

// Maybe add missing @ for the channel name
if(substr($handle, 0, 1) != "@") {
	$handle = '@'.$handle;
}

// Fetch from cache or YouTube */
$filtered = cache_get($handle, CACHE_YT_PREFIX, CACHE_YT_TTL);

if(!$filtered) {
	// Find the Channel ID
	$channel_id = get_youtube_channel_id($handle);

	// Fetch the XML content from YouTube
	$xmlContent = file_get_contents('https://www.youtube.com/feeds/videos.xml?channel_id=' . $channel_id, false, set_headers());

	if($xmlContent === false) {
		if(ERROR_LOG) logger('YT: Failed to fetch the feed for Channel ID `'.$handle.'`.');
		exit;
	}

	// Maybe some kind of error page?
	if(stripos($xmlContent, '<!DOCTYPE html>') !== false) {
		preg_match('/<title>(Error\s[0-9]{3})/i', $xmlContent, $errors);
		$error = (stripos($errors[1], "Error ")) ? $errors[1] : 'Unknown';

		if(ERROR_LOG) logger('YT: Response error for Channel ID `'.$handle.'`, with error '.$error.'.');
		exit;
	}

	// Load the XML
	$xml = new SimpleXMLElement($xmlContent);

	$filtered = array();

	// Get Channel meta information
	$filtered['channel_name'] = sanitize($xml->title);
	$filtered['channel_url'] = sanitize($xml->author->uri);
	$filtered['items'] = array();

	// Loop through each item
	foreach($xml->entry as $entry) {
		// Get all data/meta data
		$namespaces = $entry->getNameSpaces(true);
		$yt = $entry->children($namespaces['yt']);
		$media = $entry->children($namespaces['media']);

		// Skip/ignore live videos
		$status = (!isset($yt->status)) ? sanitize($yt->status) : '';
		if(!empty($status) AND $status === 'live') {
			continue;
		}

		// Find basic information
		$video_id = (isset($yt->videoId)) ? sanitize($yt->videoId) : '';
		$title = (isset($entry->title)) ? sanitize($entry->title) : '';
		$video_url = (isset($entry->link['href'])) ? sanitize($entry->link['href']) : '#';
		$published = (isset($entry->published)) ? strtotime(sanitize($entry->published)) : 0;

		// Find additional information
		$thumbnail = (isset($media->group->thumbnail->attributes()->url)) ? sanitize($media->group->thumbnail->attributes()->url) : '';
		$description = (isset($media->group->description)) ? sanitize($media->group->description, true) : '';

		// Ignore if video id is missing
		// Ignore if title is missing
		if(empty($video_id) OR empty($title)) {
			continue;
		}
	
		// Ignore ads
		if(strpos($video_id, 'googleads') !== false) {
			continue;
		}

		// Only add unique videos
		$key = array_search($video_id, $filtered['items']);
		if(!isset($filtered['items'][$key])) {
			// Format description, if there is a description
			if(strlen($description) > 0) {
				$description = htmlspecialchars($description);
				$description = nl2br($description);
				
				// Regex came from repo BetterVideoRss of VerifiedJoseph.
				$description = preg_replace('/(https?:\/\/(?:www\.)?(?:[a-zA-Z0-9-.]{2,256}\.[a-z]{2,20})(\:[0-9]{2,4})?(?:\/[a-zA-Z0-9@:%_\+.,~#"\'!?&\/\/=\-*]+|\/)?)/ims', '<a href="$1" target="_blank">$1</a>', $description);
			}

			// Set up the embed url
			$url_embed = http_build_query(array(
				'vid' => $video_id, 
				'id' => $handle
			));
			$url_embed = trim(MAIN_URL).'watch.php?'.$url_embed;

			// Sort out the description/item content
			$content = '';
		    if(!empty($thumbnail)) {
			    $content .= "<p><a href=\"".$url_embed."\"><img src=\"".$thumbnail."\" /></a></p>";
			}
			$content .= "<p>Video links: <a href=\"".$url_embed."\">Watch embedded in browser</a> or <a href=\"".$video_url."\">watch on YouTube</a>.</p>";
			$content .= $description;

		    $filtered['items'][] = array(
			    'id' => $video_id,
				'title' => $title,
				'link' => $url_embed,
				'date_released' => $published,
				'description' => $content
		    );
		}

		unset($entry, $namespaces, $yt, $media, $status, $video_id, $title, $video_url, $published, $thumbnail, $description, $url_embed, $content);
	}

	// Sort by date_released DESC */
	usort($filtered['items'], fn($a, $b) => $b['date_released'] <=> $a['date_released']);

	cache_set($handle, $filtered, CACHE_YT_PREFIX);
}

/* ------------------------------------------------------------------------ */
/* BUILD AND OUTPUT THE RSS FEED											*/
/* ------------------------------------------------------------------------ */
$now = time();

if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) AND strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $now) {
	header('HTTP/1.1 304 Not Modified', true);
	header('Cache-Control: max-age='.CACHE_YT_TTL.', private', true);
	exit;
}

header('Content-Type: application/rss+xml; charset=UTF-8', true);
header('Cache-Control: max-age='.CACHE_YT_TTL.', private', true);
header('Last-Modified: '.date('r', $now), true);
header('ETag: "'.$handle.'-'.$now.'"', true);

echo generate_rss_feed($filtered, $now);
if(SUCCESS_LOG) logger('YT: Feed processed for Channel ID `' . $filtered['channel_name'] . '`.', false);

// Clean up
unset($handle, $access_key, $filtered);

exit;
?>