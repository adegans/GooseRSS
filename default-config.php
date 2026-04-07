<?php
/* ---------------------------------------------------------------------------
 * GooseRSS the YouTube and EZTV RSS Generator
 *
 * Generate links to subscribe to:
 *   https://yourdomain.com/subscribe.php?access=the-access-key
 *   Submit the YouTube channel handle that you want to subscribing to.
 *   Or, for TV Shows enter a valid IMDb ID in the correct field.
 *   This page can be bookmarked for ease of use.
 *
 * Follow YouTube channels:
 *   https://yourdomain.com/ytrss.php?access=the-access-key&id=channel_handle
 *   The YouTube channel you're subscribing to must be a valid channel name/handle. This is the username prefixed with or without an '@'.
 *   The Channel ID is usually visible on the main channel's page, next to the channel thumbnail.
 *
 * Follow EZTV releases (TV Shows):
 *   https://yourdomain.com/eztvrss.php?access=the-access-key&id=tt12327578
 *   The TV Show you're subscribing to must be provided as a valid imdb id. This is a numeric value prefixed with or without 'tt'.
 *   You can find imdb ids on the IMdB.com website and elsewhere.
--------------------------------------------------------------------------- */

/* ------------------------------------------------------------------------ */
/* CONFIGURATION															*/
/* ------------------------------------------------------------------------ */
	
// Where is gooseRSS hosted? (without a trailing slash)
define('MAIN_URL', 'https://example.com/gooserss'); 

// Access key to be used in the URLs.
// This access key is not super secret, but it helps against surface level attacks and general misuse.
// Treat it as a shared secret. Use alphanumeric characters and dashes only. Length is up to you, minimum is 1 character long.
define('ACCESS', '1234-2468-1357');

// Which torrent video qualities to look for? 
// Add the quality (ex. 720p) without the 'p' or other characters. 
// Add as many as you want, in the same format as below.
define('QUALITY_FILTER', array('720', '1080', '2160'));

// Where to get the magnet links?
// Is eztvx.to blocked for you? Use one of these urls as an alternative.
// Try: eztv1.xyz, eztv.wf, eztv.tf, eztv.yt
define('EZTV_API_URL', 'https://eztvx.to/api/get-torrents');

// Set a user-agent for gooseRSS to identify as. 
// The services you use prefer to deal with a browser, so we need to pretend to be a browser.
// Goosle tries hard to look like Firefox so the default often works best. Other browsers may work, but Firefox is nice and neutral.
define('USER_AGENT', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:142.0) Gecko/20100101 Firefox/142.0');

// Where to keep the cache (without a trailing slash).
define('CACHE_DIR', '/cache');

// Cache lifetime in seconds (3600 = 1 hour, 86400 = 1 day).
define('CACHE_YT_TTL', 21600); // Default: 21600 (6 hours). 
define('CACHE_EZTV_TTL', 86400); // Default: 86400 (24 hours).

// Cache file prefix. This is to help differentiate filenames in the /cache/ folder and usually does not need to be changed.
define('CACHE_YT_PREFIX', 'yt_'); 
define('CACHE_EZTV_PREFIX', 'eztv_');

// Log runs per feed into error.log or success.log?
// Leaving this on may result in large log files over time. Deleting either log file 'resets' the log.
// Set to true or false.
define('SUCCESS_LOG', false);
define('ERROR_LOG', false);
?>