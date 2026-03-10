<?php
/**
 * EZTV‑RSS Generator
 *
 * Usage:
 *   https://yourdomain.com/ezrss.php?access=the-access-key&id=tt12327578
 *   For some 'not-so-secure-security' use the ACCESS hash from below in the url. This stops unauthorized users from using the generator.
 *   The imdb id you're subscribing to must be a valid imdb id. This is a numeric value prefixed with or without 'tt'.
 *
 */

/* ------------------------------------------------------------------------ */
/* DEFAULTS																	*/
/* ------------------------------------------------------------------------ */
define('CACHE_TTL', 43200); // Cache in seconds (3600 = 1 hour, 86400 = 1 day)
define('CACHE_PREFIX', 'eztv_'); // cache file prefix

/* -------------------------------------------------------------------------- */
/* MAIN LOGIC                                                                  */
/* -------------------------------------------------------------------------- */

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/functions.php');

$access_key = isset($_GET['access']) ? sanitize($_GET['access']) : '';
$handle = isset($_GET['id']) ? sanitize($_GET['id']) : '';

/** Basic "security" */
if(empty($access_key) OR $access_key != ACCESS) {
	http_response_code(403);
	die('Access key incorrect.');
}

/** Retrieve IMDb id */
if(empty($handle)) {
	http_response_code(400);
	die('Missing `id` query parameter.');
}

/** Strip leading "tt" if present – API expects numeric part only */
$handle_numeric = preg_replace('/^tt/i', '', $handle);
if (!ctype_digit($handle_numeric)) {
    http_response_code(400);
    die('Invalid IMDb id format.');
}

/** Fetch all pages from cache or EZTV */
$filtered = cache_get($handle, CACHE_PREFIX);

if(!$filtered) {
	// Fetch the Json content from eztv
    $jsonContent = file_get_contents(EZTV_API_URL.'?imdb_id='.$handle_numeric.'&limit=100', false, set_headers());

	if($jsonContent === false) {
		http_response_code(410);
		die("Failed to fetch the feed for channel '".$handle."'.");
	}

    // Decode JSON
    $json = json_decode($jsonContent, true);
    if(!is_array($json) OR !isset($json['torrents'])) {
		http_response_code(400);
		die("Invalid response from EZTV API for imdb id '".$handle."'.");
    }

	// Bail if there are no torrents
    if($json["torrents_count"] == 0) {
		http_response_code(200);
		die("No torrents for imdb id '".$handle."'.");
    }
	
	$filtered = array();

	// Get Channel meta information
	preg_match('/^(.+?)\s[Ss]\d{2}[Ee]\d{2}/', sanitize($json['torrents'][0]['filename']), $m);
	$filtered['channel_name'] = $m[1];

	// Loop through each item
	foreach($json['torrents'] as $torrent) {
		$filename = (isset($torrent['filename'])) ? sanitize($torrent['filename']) : '';
		$seeders = (isset($torrent['seeds'])) ? sanitize($torrent['seeds']) : 0;
		$episode = (isset($torrent['episode'])) ? sanitize($torrent['episode']) : 0;
		$season = (isset($torrent['season'])) ? sanitize($torrent['season']) : 0;
		$title = (isset($torrent['title'])) ? sanitize($torrent['title']) : '';
		$magnet_url = (isset($torrent['magnet_url'])) ? sanitize($torrent['magnet_url']) : '';
		$published = (isset($torrent['date_released_unix'])) ? sanitize($torrent['date_released_unix']) : null;
		$size = (isset($torrent['size_bytes'])) ? sanitize($torrent['size_bytes']) : 0;

		// Ignore if magnet link is missing
		if(empty($magnet_url)) {
			continue;
		}

		// Clean up season and episode number
		if($season < 10) $season = '0'.$season;
		if($episode < 10) $episode = '0'.$episode;

		// Unique key for filtering
	    $key = sprintf('%02d-%03d', $season, $episode);

	    // Filter video quality
	    $pattern = implode('|', QUALITY_FILTER);
	    if(!preg_match('/\b('.$pattern.')p\b/i', $filename)) {
	        continue;
	    }
	
	    // Skip entries without seeders
	    if($seeders === 0) {
	        continue;
	    }
	
	    // Skip entries without proper season/episode
	    if($season === 0 OR $episode === 0) {
	        continue;
	    }
	
	    // Keep torrent with highest seeders
	    if (!isset($filtered[$key]) OR $seeders > ($filtered[$key]['seeds'])) {
			// Sort out the description/item content
			$content = '';
			$content .= '<p>Seeds: '.$seeders.' / Size: '.human_filesize($size).'.</p>';
			$content .= '<p>Download: <a href="'.$magnet_url.'">Magnet link</a></p>';

	        $filtered['items'][$key] = [
	            'title' => $title,
	            'link' => $magnet_url,
	            'seeds' => $seeders,
	            'date_released' => $published,
	            'description' => $content
	        ];
	    }

		unset($key, $filename, $seeders, $season, $episode, $title, $magnet_url, $published, $size, $torrent, $content);
	}
	
	/** Sort by date_released DESC */
	usort($filtered['items'], fn($a, $b) => $b['date_released'] <=> $a['date_released']);
	
	cache_set($handle, $filtered, CACHE_PREFIX, CACHE_TTL);
}

/* ------------------------------------------------------------------------ */
/* BUILD AND OUTPUT THE RSS FEED											*/
/* ------------------------------------------------------------------------ */
$now = time();

$rss = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$rss .= "<rss version=\"2.0\">\n";
$rss .= "  <channel>\n";
$rss .= "    <title>".xml_escape($filtered['channel_name'])."</title>\n";
$rss .= "    <description>RSS feed for ".xml_escape($filtered['channel_name'])."</description>\n";
$rss .= "    <link>".xml_escape("//".$_SERVER['HTTP_HOST'])."</link>\n";
$rss .= "    <lastBuildDate>".date("r", $now)."</lastBuildDate>\n";
$rss .= "    <generator>EZRss</generator>\n";

foreach($filtered['items'] as $item) {
	$rss .= "    <item>\n";
	$rss .= "      <title>".xml_escape($item['title'])."</title>\n";
	$rss .= "      <link>".xml_escape($item['link'])."</link>\n";
	$rss .= "      <pubDate>".date("r", $item['date_released'])."</pubDate>\n";
	$rss .= "      <guid isPermaLink=\"false\">".md5($item['link'])."</guid>\n";
	$rss .= "      <description><![CDATA[".$item['description']."]]></description>\n";
	$rss .= "    </item>\n";
	
	unset($item);
}

$rss .= "  </channel>\n";
$rss .= "</rss>";

if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) AND strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $now) {
	header('HTTP/1.1 304 Not Modified', true);
	header('Cache-Control: max-age='.CACHE_TTL.', private', true);
	exit;
}

header('Content-Type: application/rss+xml; charset=UTF-8', true);
header('Cache-Control: max-age='.CACHE_TTL.', private', true);
header('Last-Modified: '.date('r', $now), true);
header('ETag: "'.$handle.'-'.$now.'"', true);

// Clean up
unset($handle, $access_key, $filtered);

echo $rss;
exit;
?>