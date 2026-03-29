# gooseRSS

Versioning is loose and lax, in fact there are no actual versions.  
But here is the list of changes made over time, sorted by 'release' date.

## March 29, 2026
- New: Separate cleanup function (cache_delete()) for cache
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