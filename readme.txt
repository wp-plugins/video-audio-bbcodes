=== Plugin Name ===
Contributors: antonchanning
Donate link: http://wp.antonchanning.com/donate/
Tags: bbpress, bbpress2, bbpress-plugin, buddypress, bbcode, youtube, googlevideo, vimeo, freesound
Requires at least: 2.5
Tested up to: 3.5.1
Stable tag: 1.1

This plugin adds support for video and audio shortcodes to posts and pages.  
If you have 'bbPress2 shortcode whitelist' installed, you can also opt to
allow them in comments, bbpress 2.0 forums and buddypress activity, group 
forums and private messages.  

== Description ==

This plugin adds support for video and audio shortcodes to wordpress. 
It integrates with the 'bbPress2 shortcode whitelist' plugin to provide 
a safe way of enabling BBCode without giving your users access to all 
shortcodes.  If installed with the latter the admin will have the option
of allowing users to use these audio and video bbcodes in their posts.

`
Youtube Links: [youtube]Youtube ID or URL (long and short supported)[/youtube]
Video URLS Links: [vimeo]Vimeo URL or ID[/vimeo]
Google Video Links: [gvideo]Google Video ID[/gvideo] [googlevideo]Google Video ID[/googlevideo]
Video URLS Links: [video]Youtube, Vimeo or GoogleVideo URLs only[/video]
Freesound audio: [freesound]freesound.org sound ID only[/freesound]
`

== Installation ==

1. Upload the `video-audio-bbcodes` folder and its contents to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. If you have 'bbPress2 shortcode whitelist' plugin installed, you can use it to approve these bbcodes on its settings page.

== Upgrade Notice ==

Welcome to using safelist compatible video and audio shortcodes.
If you haven't already install 'bbPress2 shortcode whitelist' to
make these shortcodes available to your users in BuddyPress, bbPress
and comments.

== Frequently Asked Questions ==

= Can you add support for more video and audio sites? =
I intend to extend this plugin, but I can't make promises
regarding specific sites.  It doesn't hurt to ask me though.
If there is enough demand and it looks possible, I will probably
do it.

== Screenshots ==

== Changelog ==

= 1.1 =
* Updated and improved description and install instructions in readme.txt
* Improved status messages in admin screen

= 1.0 =
* Support for Youtube using [youtube] bbcode.  Supports long and short urls plus id.
* Support for Google video using both [gvideo] and [googlevideo] bbcodes.
* Support for Vimeo using [vimeo] bbcode.
* Support for Freesound.org using [freesound] 









