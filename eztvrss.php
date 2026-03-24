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
/* MAIN LOGIC                                                               */
/* ------------------------------------------------------------------------ */

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/functions.php');

$access_key = isset($_GET['access']) ? sanitize($_GET['access']) : '';
$handle = isset($_GET['id']) ? strtolower(sanitize($_GET['id'])) : '';

// Basic "security" */
if(empty($access_key) OR $access_key !== trim(ACCESS)) {
	if(ERROR_LOG) logger('EZTV: Access key incorrect.');
	exit;
}

// Check IMDb id */
if(empty($handle)) {
	if(ERROR_LOG) logger('EZTV: Missing `id` query parameter.');
	exit;
}

// Add prefix if it's not there
if(substr($handle, 0, 2) != "tt") {
	$handle = "tt".$handle;
}

// Fetch from cache or EZTV */
$filtered = cache_get($handle, CACHE_EZTV_PREFIX, CACHE_EZTV_TTL);

if(!$filtered) {
	// Fetch the Json content from eztv
	$handle_numeric = str_ireplace('tt', '', $handle);
	$jsonContent = make_request(EZTV_API_URL.'?imdb_id='.$handle_numeric.'&limit=100');

	if($jsonContent === false) {
		if(ERROR_LOG) logger('EZTV: Failed to fetch the feed for IMDb id `'.$handle.'`.');
		exit;
	}

    // Decode JSON
    $json = json_decode($jsonContent, true);
    if(!is_array($json) OR !isset($json['torrents'])) {
		if(ERROR_LOG) logger('EZTV: Invalid response from EZTV API for IMDb id `'.$handle.'`.');
		exit;
    }

	// Bail if there are no torrents
    if($json["torrents_count"] == 0) {
		if(ERROR_LOG) logger('EZTV: No torrents for IMDb id `'.$handle.'`.');
		exit;
    }
	
	$filtered = array();

	// Get Channel meta information
	preg_match('/^(.+?)\s[Ss]\d{2}(?:[Ee]\d{2})?/', sanitize($json['torrents'][0]['title']), $m);
	$filtered['channel_name'] = $m[1];
	$filtered['channel_url'] = "https://eztvx.to/search/".urlencode($handle);
	$filtered['items'] = array();

	// Loop through each item
	foreach($json['torrents'] as $torrent) {
		// Get the basic information
		$hash = (isset($torrent['hash'])) ? sanitize((string)$torrent['hash']) : 0;
		$title = (isset($torrent['title'])) ? sanitize((string)$torrent['title']) : '';
		$url_magnet = (isset($torrent['magnet_url'])) ? sanitize((string)$torrent['magnet_url']) : '';
		$published = (isset($torrent['date_released_unix'])) ? sanitize((int)$torrent['date_released_unix']) : null;

		// Find additional information
		$season = (isset($torrent['season'])) ? sanitize((int)$torrent['season']) : 0;
		$episode = (isset($torrent['episode'])) ? sanitize((int)$torrent['episode']) : 0;
		$thumbnail = (isset($torrent['small_screenshot'])) ? sanitize((string)$torrent['small_screenshot']) : '';
		$seeders = (isset($torrent['seeds'])) ? sanitize((int)$torrent['seeds']) : 0;
		$size = (isset($torrent['size_bytes'])) ? sanitize((int)$torrent['size_bytes']) : 0;
		$filename = (isset($torrent['filename'])) ? sanitize((string)$torrent['filename']) : '';

		// Ignore if title is missing
		// Ignore if magnet link is missing
		if(empty($title) OR empty($url_magnet)) {
			continue;
		}

	    // Filter video quality
		$pattern = implode('|', QUALITY_FILTER);
	    if(!preg_match('/\b('.$pattern.')p\b/i', $filename)) {
	        continue;
	    }
	
		// Only add unique torrents
		if(!array_search($hash, array_column($filtered['items'], 'id'))) {
			// Clean up season and episode number
			if($season < 10) $season = '0'.$season;
			if($episode < 10) $episode = '0'.$episode;

			// Sort out the description/item content
			$content = '';
		    if(!empty($thumbnail)) {
			    $content .= "<p><a href=\"".$url_magnet."\"><img src=\"".$thumbnail."\" /></a></p>";
			}
			$content .= "<p><strong>Seeds:</strong> ".$seeders."<br /><strong>Size:</strong> ".human_filesize($size)."<br /><strong>Magnet:</strong> <a href=\"".$url_magnet."\">".$filename."</a></p>";
			$content .= "<p><strong>Links:</strong> <a href=\"https://www.imdb.com/title/".$handle."/\">IMDb page</a> / <a href=\"".$filtered['channel_url']."\" title=\"Watch out for redirects and popups!\">All EZTV magnets</a><br /><strong>Magnet Hash:</strong> ".$hash."</p>";

	        $filtered['items'][] = array(
	            'id' => $hash,
	            'title' => $title,
	            'link' => $url_magnet,
	            'date_released' => $published,
	            'description' => $content
	        );
	    }

		unset($filename, $seeders, $season, $episode, $title, $thumbnail, $url_magnet, $hash, $published, $size, $content, $torrent);
	}
	
	// Sort by date_released DESC */
	usort($filtered['items'], fn($a, $b) => $b['date_released'] <=> $a['date_released']);
	
	cache_set($handle, $filtered, CACHE_EZTV_PREFIX);
}

/* ------------------------------------------------------------------------ */
/* BUILD AND OUTPUT THE RSS FEED											*/
/* ------------------------------------------------------------------------ */
$now = time();

if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) AND strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $now) {
	header('HTTP/1.1 304 Not Modified', true);
	header('Cache-Control: max-age='.CACHE_EZTV_TTL.', private', true);
	exit;
}

header('Content-Type: application/rss+xml; charset=UTF-8', true);
header('Cache-Control: max-age='.CACHE_EZTV_TTL.', private', true);
header('Last-Modified: '.date('r', $now), true);
header('ETag: "'.$handle.'-'.$now.'"', true);

echo generate_rss_feed($filtered, $now);
if(SUCCESS_LOG) logger('EZTV: Feed processed for `' . $filtered['channel_name'] . '`.', false);

// Clean up
unset($handle, $handle_numeric, $access_key, $filtered);

exit;
?>