# gooseRSS

Turn YouTube channels into RSS feeds you can subscribe to so you'll always know when new videos are uploaded.  
Watch Youtube videos from your favorite channels through the included watch page without ads.  
The watch page is responsive and mobile compatible. It tries to get a wake lock from the device so the screen won't sleep while watching longer videos.  
Wake lock is supported in all major, modern, browsers.

If you're into torrenting TV Shows, never miss the latest episode again.
Get notified when new TV Shows you follow become available through EZTV, ready for download in a torrent client.

## Installation

You'll need a working (localhost) server that works with PHP 8 or newer. SimpleXML and common PHP modules need to work.

Upload the files to your webserver, this can be in the document root or a subfolder.  
For example https://domain.tld/gooserss/ or simply https://domain.tld/.

Copy `default-config.php` to `config.php`.

Open the `config.php` file and set your settings.  
Each setting is briefly explained in the file.  
There are a few settings for caching, what torrent quality to look for, and you set your shared access key here.

For testing you can enable the `ERROR_LOG` and `SUCCESS_LOG` settings.  
This logs errors and successful runs to `error.log` and `success.log` in the root folder.

## Usage

**For YouTube:**  
https://yourdomain.com/ytrss.php?access=the-access-key&id=channel_handle

The YouTube channel you're subscribing to must be a valid Channel Name/Handle. This is the username prefixed with an '@'.  
For gooseRSS, remove the '@' and use the plain name as the ID.  

The Channel ID is usually visible on the main channel's page, next to the channel thumbnail.

**For EZTV Magnet links (TV Shows):**  
https://yourdomain.com/eztvrss.php?access=the-access-key&id=tt12345678

The TV Show you're subscribing to must be provided as a valid imdb id. This is a numeric value prefixed with 'tt'.  
You can find imdb ids on the IMdB.com website and elsewhere. gooseRSS accepts the imdb id with or with the 'tt' prefix.

**Using the feeds**  
To subscribe, simply add the feeds you create to your favorite RSS Reader. Any decent RSS reader will work.  
You can also load them in a browser and it should redirect to the feed reader.  


### Finding the YouTube Channel Handle.

This looks a bit like a Instagram or Telegram name and has a **@** in front of it.  
You can find it on most Channel main pages below the header image.

[![Find a channel handle](https://ajdg.solutions/assets/github-repo-assets/youtubeembed-channel-screenshot.webp)](https://ajdg.solutions/assets/github-repo-assets/youtubeembed-channel-screenshot.webp)

If it's not there you can get it from the channel details or the channel url in your browser.

## Technical Stuff
- All feeds are cached as serialized files. 
- The cache default is 6 hours for YouTube and 24 hours for EZTV.
- Each feed is compatible with the `304 NOT MODIFIED` header for reduced traffic.
- The Watch Page has a nice dark layout to reduce eye strain.
- The Watch Page on mobile uses a technology called Wake Lock, this prevents the device screen from sleeping while you watch a video.
- All TV Shows are presented as a Magnet link.
- The Watch Page can only embed videos that are have their info in the cache but ignores the cache expiry.

