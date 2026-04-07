# GooseRSS

Versioning is loose and lax, in fact there are no actual versions.  
But here is the list of changes made over time, sorted by 'release' date.

## April 6, 2026
- Fix: Consistent use trailing / for MAIN_URL
- New: When adding a feed a welcome item is added as a placeholder
- New: Successful deletion of old cache files logged in success.log 
- New: MAIN_PATH must be defined for included files to work
- Change: Feeds now keep up to 50 items in cache
- Change: moved functions.php to /functions/functions.php
- Change: index.php files replaced with index.html
- Change: Old (and orphaned) cache files cleared once a month around 11AM GMT
- Change: check_config now also checks for index.html in cache
- Rollback: Feeds silently fail again if any error occurs while fetching new data
- Rollback: Removed ERROR_FEED from default-config.php

## April 1, 2026
- Update: Refined cache cleanup routine
- New: Moved check if /cache exists into check_config()
- New: Moved check if /cache/timer.tmp exists into check_config()
- Change: Output errors as part of feed now an option in default-config.php
- Fix: Empty channel_name when the feed has an error
- Fix: Empty channel_url when the feed has an error

## March 29, 2026
- Fix: Cache delete timer better check if cache should be deleted

## March 29, 2026
- New: Output errors as part of feed
- New: Separate cleanup function (cache_delete()) for cache
- Change: Improved handling when feed name and url are empty/missing
- Change: Config descriptions for valious features
- Change: Last build date for feeds now matches newest/latest item in the feed
- Change: Improved error handling when fetching feeds
- Fix: Youtube video publish date now processed as string, not an int
- Fix: removed unused feed.php

## March 23, 2026
- Change: Fetched variables are now cast as a string/int/bool
- Change: Merged YT and EZTV field on subscribe.php for easier use
- Change: Merged cache_delete() back into cache_get()
- Change: Better channel url for Youtube
- Change: Better cURL headers to better mimic a browser
- Fix: Now links/generates EZTV links with the right filename in the url
- Fix: Removed broken duration indicator from video descriptions
- Fix: Fail earlier on the watch page so there are no PHP warnings
- New: Supports cookies to better imitate a browser

## March 15, 2026
- New: Video duration added to description
- New: Ignore/skip Premiere videos until they're 'live'
- New: Subscribe page, see default-config.php for details
- Change: Video description now omited if value is empty

## March 15, 2026
- Rollback: Reformatted the watch page url to use separate arguments again
- Fix: Feed formatting to use CDATA in item links so arguments don't get messed up
- Fix: Better formatting for errors that go into the log
- Change: Now uses cURL for all outgoing requests to better mimic a browser request
- Change: Watch page now uses youtube.com instead of youtube-nocookie.com
- Change: Watch page thumbnail optional and now comes from cache
- Change: YouTube Channel ID stored in main cache file
- New: Expired cache files deleted at the end of each run through cache_delete()

## March 12, 2026
- Change: Improved handling for IMDb ID for eztv
- Change: Less complex formatting for IMDb ID
- Added: Links section for TV Show items
- Added: TV Show thumbnail clickable to start download

## March 11, 2026
- Change: Removed @ from handle and only add it when finding the Channel ID
- Change: Reformatted the watch page url to work with NetNewsWire
- Fixed: Unique item filter when importing data
- Fixed: Watch page linking up with the wrong cached title
- Fixed missing &id from watch page urls

## March 9, 2026
- First release.