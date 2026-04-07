# GooseRSS

Turn YouTube channels into RSS feeds you can subscribe to so you'll always know when new videos are uploaded.  
Watch Youtube videos from your favorite channels through the included watch page without ads.  
The watch page is responsive and mobile compatible. It tries to get a wake lock from the device so the screen won't sleep while watching longer videos.  
Wake lock is supported in all major, modern, browsers.

If you're into torrenting TV Shows, never miss the latest episode again.
Get notified when new TV Shows you follow become available through EZTV, ready for download in a torrent client.

## Installation

Installation is simple and only takes a few minutes.  
You'll need a working (localhost) server that works with PHP 8 or newer. SimpleXML and common PHP modules as standard, and that's it.

- Download the [zip file](https://github.com/adegans/GooseRSS/archive/refs/heads/main.zip) from Github.
- Extract and upload all files to your webserver, this can be in the document root or a subfolder.  
For example https://domain.tld/gooserss/ or simply https://domain.tld/.
- Copy `gooserss.htaccess` to `.htaccess`.
- Copy `default-config.php` to `config.php`.
- Open the `config.php` file and set your settings.  
Each setting is briefly explained in the file. There are a few settings for caching, what torrent quality to look for, and you set your shared access key here.
- For testing you can enable the `ERROR_LOG` and `SUCCESS_LOG` settings.  
This logs errors and successful runs to `error.log` and `success.log` in the root folder.

## Usage

**Generate the RSS links you subscribe to:**
Visit: https://yourdomain.com/subscribe.php?access=the-access-key  
This page can be bookmarked for ease of use.

Enter the YouTube Handle or, for TV Shows enter a valid IMDb ID in the correct field.  
Press the Generate button and a link is generated for you to subscribe to.

**Using RSS feeds:**  
To subscribe to a RSS feed you need a RSS reader or an RSS service online. Add the feeds you create to your favorite RSS Reader.  
For example NetNewsWire, Vienna or a (self)hosted service like FreshRSS, Nextcloud News or TinyTinyRSS. Any decent RSS reader will work.

**Make a feed yourself for YouTube:**  
https://yourdomain.com/ytrss.php?access=the-access-key&id=channel_handle

The YouTube channel you're subscribing to must be a valid Channel Name/Handle. This is the username prefixed with an '@'.  
For GooseRSS, remove the '@' and use the plain name as the ID.  

The Channel ID is usually visible on the main channel's page, next to the channel thumbnail.

**Make a feed yourself for EZTV Magnet links (TV Shows):**  
https://yourdomain.com/eztvrss.php?access=the-access-key&id=tt12345678

The TV Show you're subscribing to must be provided as a valid imdb id. This is a numeric value prefixed with 'tt'.  
You can find imdb ids on the IMdB.com website and elsewhere. GooseRSS accepts the imdb id with or with the 'tt' prefix.

### Finding the YouTube Channel Handle.

This looks a bit like a Instagram or Telegram name and has a **@** in front of it.  
You can find it on most Channel main pages below the header image.

[![Find a channel handle](https://ajdg.solutions/assets/github-repo-assets/youtubeembed-channel-screenshot.webp)](https://ajdg.solutions/assets/github-repo-assets/youtubeembed-channel-screenshot.webp)

If it's not there you can get it from the channel details or the channel url in your browser.